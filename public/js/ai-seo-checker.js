document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('aiseoForm');
  if (!form) return;

  var urlInput = document.getElementById('aiseoUrl');
  var errorBox = document.getElementById('aiseoError');
  var loading = document.getElementById('aiseoLoading');
  var loadingUrl = document.getElementById('aiseoLoadingUrl');
  var results = document.getElementById('aiseoResults');
  var ring = document.getElementById('aiseoOverallRing');
  var scoreEl = document.getElementById('aiseoOverallScore');
  var resultUrlEl = document.getElementById('aiseoResultUrl');
  var issueCountEl = document.getElementById('aiseoIssueCount');
  var engineGrid = document.getElementById('aiseoEngineGrid');
  var findingsList = document.getElementById('aiseoFindingsList');
  var paywallError = document.getElementById('aiseoPaywallError');
  var paywallHead = document.querySelector('.aiseo-paywall__head');
  var paypalContainer = document.getElementById('paypal-button-container');
  var successBox = document.getElementById('aiseoSuccess');
  var downloadLink = document.getElementById('aiseoDownloadLink');
  var submitBtn = document.getElementById('aiseoSubmit');
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrfMeta ? csrfMeta.content : '';

  var currentCheck = null;
  var buttonsRendered = false;

  function showError(message) {
    errorBox.textContent = message;
    errorBox.hidden = false;
  }

  function createUrl(checkId) {
    return window.AISEO_PAYPAL_CREATE_URL_TEMPLATE.replace('__ID__', checkId);
  }

  function captureUrl(checkId, orderId) {
    return window.AISEO_PAYPAL_CAPTURE_URL_TEMPLATE.replace('__ID__', checkId).replace('__ORDER__', orderId);
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    errorBox.hidden = true;

    var url = urlInput.value.trim();
    if (!url) {
      showError('Please enter a website URL.');
      return;
    }

    results.hidden = true;
    loading.hidden = false;
    loadingUrl.textContent = url;
    submitBtn.disabled = true;

    fetch(window.AISEO_CHECK_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ url: url })
    })
      .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
      .then(function (result) {
        loading.hidden = true;
        submitBtn.disabled = false;

        if (!result.ok) {
          var message = result.data.error || (result.data.errors ? Object.values(result.data.errors).flat().join(' ') : 'Could not check that URL.');
          showError(message);
          return;
        }

        renderResults(result.data);
      })
      .catch(function () {
        loading.hidden = true;
        submitBtn.disabled = false;
        showError('Something went wrong. Please try again.');
      });
  });

  function renderResults(data) {
    currentCheck = { id: data.check_id, token: data.access_token };

    scoreEl.textContent = data.overall_score;
    ring.style.setProperty('--pct', data.overall_score);
    resultUrlEl.textContent = data.url;
    issueCountEl.textContent = data.issue_count;

    engineGrid.innerHTML = '';
    var engineTpl = document.getElementById('aiseoEngineCardTemplate');
    (data.engines || []).forEach(function (engine) {
      var node = engineTpl.content.cloneNode(true);
      node.querySelector('.aiseo-engine-card__icon i').className = engine.icon;
      node.querySelector('.aiseo-engine-card__name').textContent = engine.name;
      node.querySelector('.aiseo-engine-card__bar span').style.width = engine.score + '%';
      node.querySelector('.aiseo-engine-card__score').textContent = engine.score;
      if (!engine.crawler_allowed) {
        node.querySelector('.aiseo-engine-card').classList.add('is-blocked');
      }
      engineGrid.appendChild(node);
    });

    findingsList.innerHTML = '';
    var findingTpl = document.getElementById('aiseoFindingTemplate');
    (data.findings || []).forEach(function (finding) {
      var node = findingTpl.content.cloneNode(true);
      var badge = node.querySelector('.aiseo-finding__badge');
      badge.textContent = finding.severity;
      badge.classList.add('severity-' + finding.severity);
      node.querySelector('.aiseo-finding__title').textContent = finding.title;

      var findingEl = node.querySelector('.aiseo-finding');
      var detailEl = node.querySelector('.aiseo-finding__detail');
      if (finding.locked) {
        findingEl.classList.add('is-locked');
        detailEl.innerHTML = '<i class="fa-solid fa-lock" aria-hidden="true"></i> Unlock the full report to see the fix';
      } else {
        detailEl.textContent = finding.detail + (finding.fix ? ' — Fix: ' + finding.fix : '');
      }
      findingsList.appendChild(node);
    });

    successBox.hidden = true;
    if (paypalContainer) paypalContainer.hidden = false;
    if (paywallHead) paywallHead.hidden = false;
    paywallError.hidden = true;

    results.hidden = false;
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });

    renderPayPalButtons();
  }

  function renderPayPalButtons() {
    if (buttonsRendered || !paypalContainer || typeof paypal === 'undefined') return;
    buttonsRendered = true;

    paypal.Buttons({
      style: { layout: 'horizontal', color: 'gold', shape: 'pill', label: 'paypal', height: 45 },
      createOrder: function () {
        paywallError.hidden = true;

        if (!currentCheck) {
          var msg = 'Please run a check first.';
          paywallError.textContent = msg;
          paywallError.hidden = false;
          return Promise.reject(new Error(msg));
        }

        return fetch(createUrl(currentCheck.id), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Check-Token': currentCheck.token,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ access_token: currentCheck.token })
        })
          .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
          .then(function (result) {
            if (!result.ok) { throw new Error(result.data.error || 'Could not start checkout.'); }
            return result.data.id;
          })
          .catch(function (err) {
            paywallError.textContent = err.message || 'Could not start PayPal checkout. Please try again.';
            paywallError.hidden = false;
            throw err;
          });
      },
      onApprove: function (data) {
        return fetch(captureUrl(currentCheck.id, data.orderID), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Check-Token': currentCheck.token,
            'Accept': 'application/json'
          }
        })
          .then(function (res) { return res.json(); })
          .then(function (result) {
            if (result.error) { throw new Error(result.error); }
            if (result.status !== 'COMPLETED' || !result.download_url) {
              throw new Error('Payment could not be confirmed. Please try again.');
            }
            downloadLink.href = result.download_url;
            paypalContainer.hidden = true;
            if (paywallHead) paywallHead.hidden = true;
            successBox.hidden = false;
          })
          .catch(function (err) {
            paywallError.textContent = err.message || 'Payment could not be confirmed. Please try again.';
            paywallError.hidden = false;
          });
      },
      onError: function (err) {
        console.error('PayPal checkout error', err);
        paywallError.textContent = 'Something went wrong starting PayPal checkout. Please try again in a moment.';
        paywallError.hidden = false;
      }
    }).render(paypalContainer);
  }
});
