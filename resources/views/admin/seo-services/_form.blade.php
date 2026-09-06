@php($service ??= null)

<div class="auth-group">
  <label for="name" class="auth-label">Name</label>
  <input id="name" name="name" class="auth-input @error('name') has-error @enderror" type="text" value="{{ old('name', $service?->name) }}" required maxlength="150">
  @error('name')<p class="auth-error">{{ $message }}</p>@enderror
</div>

<div class="auth-group">
  <label for="slug" class="auth-label">Slug <span style="font-weight:400; color:var(--text-2);">(optional — used in the order URL, auto-generated from the name if left blank)</span></label>
  <input id="slug" name="slug" class="auth-input @error('slug') has-error @enderror" type="text" value="{{ old('slug', $service?->slug) }}" maxlength="160">
  @error('slug')<p class="auth-error">{{ $message }}</p>@enderror
</div>

<div class="auth-group">
  <label for="description" class="auth-label">Description</label>
  <textarea id="description" name="description" class="auth-input @error('description') has-error @enderror" rows="3" maxlength="2000">{{ old('description', $service?->description) }}</textarea>
  @error('description')<p class="auth-error">{{ $message }}</p>@enderror
</div>

<div class="auth-group">
  <label for="unit_price" class="auth-label">Unit price (USD per placement)</label>
  <input id="unit_price" name="unit_price" class="auth-input @error('unit_price') has-error @enderror" type="number" step="0.0001" min="0.0001" value="{{ old('unit_price', $service?->unit_price) }}" required>
  @error('unit_price')<p class="auth-error">{{ $message }}</p>@enderror
</div>

<div class="auth-group">
  <label for="min_quantity" class="auth-label">Minimum quantity</label>
  <input id="min_quantity" name="min_quantity" class="auth-input @error('min_quantity') has-error @enderror" type="number" min="1" value="{{ old('min_quantity', $service?->min_quantity ?? 10) }}" required>
  @error('min_quantity')<p class="auth-error">{{ $message }}</p>@enderror
</div>

<div class="auth-group">
  <label for="max_quantity" class="auth-label">Maximum quantity</label>
  <input id="max_quantity" name="max_quantity" class="auth-input @error('max_quantity') has-error @enderror" type="number" min="1" value="{{ old('max_quantity', $service?->max_quantity ?? 5000) }}" required>
  @error('max_quantity')<p class="auth-error">{{ $message }}</p>@enderror
</div>

<label class="auth-check" style="margin-bottom:20px;">
  <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service?->is_active ?? true))>
  Active — visible and orderable on the public site
</label>
