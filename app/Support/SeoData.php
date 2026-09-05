<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Product;

final readonly class SeoData
{
    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
        public string $ogType = 'website',
        public ?string $ogImage = null,
        public ?string $keywords = null,
        public string $robots = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        public array $breadcrumbItems = [],
        public array $jsonLd = [],
    ) {}

    public static function forStatic(string $title, string $description, string $canonical, ?string $keywords = null, ?string $robots = null): self
    {
        return new self(
            title: $title,
            description: $description,
            canonical: $canonical,
            keywords: $keywords,
            robots: $robots ?? 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        );
    }

    public static function forNoIndex(string $title, string $canonical): self
    {
        return new self(
            title: $title,
            description: $title,
            canonical: $canonical,
            robots: 'noindex, nofollow',
        );
    }

    public static function forHome(): self
    {
        return new self(
            title: 'INZRA — Premium High-Authority SEO Backlinks & Link Building Services',
            description: 'INZRA: Curated marketplace for verified high-authority backlinks. Guest posts, niche edits, EDU/GOV links with guaranteed DA, DR metrics. 8,400+ publishers. Transparent pricing.',
            canonical: url('/'),
            ogImage: asset('og-cover.svg'),
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    '@id' => url('/'),
                    'name' => 'INZRA',
                    'description' => 'Verified marketplace for high-authority SEO backlinks and link building services.',
                    'url' => url('/'),
                    'image' => asset('og-cover.svg'),
                    'sameAs' => ['https://twitter.com/inzra', 'https://linkedin.com/company/inzra'],
                    'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => '4.9', 'reviewCount' => '2841', 'bestRating' => '5', 'worstRating' => '1'],
                    'areaServed' => 'Worldwide',
                ],
                self::breadcrumb([['name' => 'Home', 'item' => url('/')]]),
                self::orderProcessHowTo(),
            ],
        );
    }

    public static function forMarketplace($products): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Marketplace', 'item' => route('marketplace')],
        ];

        return new self(
            title: 'Backlink Marketplace | 40+ Verified Placements | INZRA',
            description: 'Browse 40+ verified backlink placements: guest posts, PBN links, niche edits, EDU/GOV links. DA 10-95. Real traffic metrics. Order directly via WhatsApp. INZRA marketplace.',
            canonical: route('marketplace'),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => 'INZRA Marketplace',
                    'mainEntity' => [
                        '@type' => 'ItemList',
                        'itemListElement' => collect($products)->values()->map(fn ($product, $i) => [
                            '@type' => 'ListItem',
                            'position' => $i + 1,
                            'url' => route('products.show', $product),
                        ])->all(),
                    ],
                ],
            ],
        );
    }

    public static function forCategories($categories): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Categories', 'item' => route('categories')],
        ];

        return new self(
            title: 'Backlink Types & Categories | Guest Posts, PBN, EDU, GOV | INZRA',
            description: '8 backlink categories explained: guest posts, PBN links, niche edits, contextual, EDU, GOV, local citations, press releases. Filter by DA, traffic, language. INZRA.',
            canonical: route('categories'),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'ItemList',
                    'name' => 'Backlink Categories',
                    'itemListElement' => collect($categories)->values()->map(fn ($category, $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'item' => [
                            '@type' => 'Thing',
                            'name' => $category->name,
                            'description' => $category->description,
                        ],
                    ])->all(),
                ],
            ],
        );
    }

    public static function forMarketsIndex(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Markets', 'item' => route('markets.index')],
        ];

        return new self(
            title: 'International SEO & Backlinks by Market | INZRA',
            description: 'An honest, market-by-market look at link building conditions in the Netherlands, the Nordics, Israel, the UAE & Saudi Arabia, Japan, South Korea, Central Europe, and Switzerland & Austria.',
            canonical: route('markets.index'),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
            ],
        );
    }

    public static function forMarket(string $slug, array $market): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Markets', 'item' => route('markets.index')],
            ['name' => $market['name'], 'item' => route('markets.show', $slug)],
        ];

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => "Backlinks and link building for {$market['name']}",
                'description' => $market['meta_description'],
                'provider' => ['@type' => 'Organization', 'name' => 'INZRA'],
                'areaServed' => $market['countries'],
                'serviceType' => 'Link building',
            ],
            self::breadcrumb($breadcrumbItems),
            self::orderProcessHowTo(),
        ];

        if (! empty($market['faqs'])) {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($market['faqs'])->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ])->all(),
            ];
        }

        return new self(
            title: $market['meta_title'],
            description: $market['meta_description'],
            canonical: route('markets.show', $slug),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: $jsonLd,
        );
    }

    public static function forToolsIndex(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Tools', 'item' => route('tools.index')],
        ];

        return new self(
            title: 'Free SEO & Image Tools | INZRA',
            description: 'Free browser-based tools from INZRA — starting with an online image converter. No sign-up, no uploads, nothing sent to a server.',
            canonical: route('tools.index'),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
            ],
        );
    }

    public static function forImageConverter(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Tools', 'item' => route('tools.index')],
            ['name' => 'Image Converter', 'item' => route('tools.image-converter')],
        ];

        $faqs = [
            ['question' => 'How do I convert JPG to WebP?', 'answer' => 'Upload your JPG image, choose WebP as the output format, adjust the quality if needed, and click Convert. Your browser handles the conversion instantly.'],
            ['question' => 'Can I convert PNG to JPG?', 'answer' => "Yes. Since JPG doesn't support transparency, transparent areas are filled with a background color you choose (white by default)."],
            ['question' => 'Is the image converter free?', 'answer' => 'Yes, completely free with no sign-up required.'],
            ['question' => 'Are my images uploaded to your server?', 'answer' => 'No. All conversion happens directly in your browser using the Canvas API. Your images never leave your device.'],
            ['question' => 'Can I convert multiple images at once?', 'answer' => 'Yes — select multiple images, convert them as a batch, then download each one individually or all together as a ZIP file.'],
            ['question' => 'What image formats are supported?', 'answer' => 'JPG, PNG and WebP convert in every modern browser. AVIF is available as an output option only in browsers that support encoding it, such as Chrome and Edge.'],
            ['question' => 'Does converting an image reduce quality?', 'answer' => 'Converting to JPG or WebP uses a quality setting you control — higher quality means a larger file. Converting to PNG is lossless.'],
            ['question' => 'Can I resize an image while converting?', 'answer' => 'Yes — set a custom width and height, lock the aspect ratio, or resize by percentage before converting.'],
        ];

        return new self(
            title: 'Free Online Image Converter – Convert JPG, PNG, WebP & More | INZRA',
            description: 'Free online image converter with no sign-up. Convert JPG, PNG, WebP and AVIF entirely in your browser — no uploads, no server processing, completely private.',
            canonical: route('tools.image-converter'),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebApplication',
                    'name' => 'INZRA Free Online Image Converter',
                    'description' => 'Free tool to convert images between JPG, PNG, WebP and AVIF directly in your browser. No uploads, no account, no sign-up required.',
                    'url' => route('tools.image-converter'),
                    'applicationCategory' => 'MultimediaApplication',
                    'operatingSystem' => 'Any (runs in a web browser)',
                    'isAccessibleForFree' => true,
                    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => collect($faqs)->map(fn ($faq) => [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                    ])->all(),
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'HowTo',
                    'name' => 'How to convert an image online',
                    'step' => [
                        ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Upload', 'text' => 'Upload one or more images by dragging them in or clicking "Choose Images."'],
                        ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Choose format', 'text' => 'Pick the output format you want from the dropdown.'],
                        ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Adjust settings', 'text' => 'Adjust quality or resize dimensions if needed.'],
                        ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Convert', 'text' => 'Click "Convert All."'],
                        ['@type' => 'HowToStep', 'position' => 5, 'name' => 'Download', 'text' => 'Download each result individually, or all at once as a ZIP.'],
                    ],
                ],
            ],
        );
    }

    public static function forPdfEditor(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Tools', 'item' => route('tools.index')],
            ['name' => 'PDF Editor', 'item' => route('tools.pdf-editor')],
        ];

        $faqs = [
            ['question' => 'Is this PDF editor really free?', 'answer' => 'Yes, completely free with no sign-up required.'],
            ['question' => 'Is my PDF uploaded to a server?', 'answer' => 'No. Your PDF is opened, edited and exported entirely in your browser using JavaScript. The file never leaves your device — there is no upload step at all.'],
            ['question' => 'Can I edit existing text in a PDF?', 'answer' => "Yes. Click any text to edit it. The editor detects the original size, color and position, and picks the closest matching standard font, adjusting spacing so the text still fits — it's a close visual match, not always byte-for-byte identical to the original font."],
            ['question' => 'Will editing preserve the original font exactly?', 'answer' => "Not exactly — this editor matches your PDF's font to the closest standard font available (by weight, style and width) rather than re-using the original font file, and always labels a match as approximate rather than claiming it's exact."],
            ['question' => 'Can I edit scanned or image-only PDFs?', 'answer' => "The editor detects scanned pages and lets you know text editing isn't available on them yet. You can still add new text, images, annotations and signatures on top of a scanned page."],
            ['question' => 'Can I merge or split PDFs?', 'answer' => 'Yes — import additional PDFs to merge their pages in, or extract/export selected pages as a new file, all locally in your browser.'],
            ['question' => 'Can I fill out PDF forms?', 'answer' => 'Yes, existing fillable form fields (text, checkbox, radio, dropdown) are detected and can be filled in, with an option to flatten the form into the final PDF.'],
            ['question' => 'Does true redaction actually remove the content?', 'answer' => "Yes for the redaction tool: it removes the underlying text/graphics from the exported PDF rather than just covering it, falling back to flattening the page to an image on pages too complex to safely edit. The separate whiteout tool only visually covers content and is labeled as such — use redaction, not whiteout, when removal matters."],
            ['question' => 'Can I password-protect or encrypt the PDF I export?', 'answer' => "Not yet — there's no reliable way to add standard PDF encryption entirely in the browser today, so this editor doesn't offer it rather than faking it. You can still open PDFs that are already password protected."],
        ];

        return new self(
            title: 'Free Online PDF Editor – Edit, Sign, Merge & Organize PDFs | INZRA',
            description: 'Free online PDF editor that runs entirely in your browser. Edit text, add images and signatures, merge and split pages, fill forms, and redact — no sign-up, no uploads, no server processing.',
            canonical: route('tools.pdf-editor'),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebApplication',
                    'name' => 'INZRA Free Online PDF Editor',
                    'description' => 'Free tool to edit, sign, merge, split and organize PDF files directly in your browser. No uploads, no account, no sign-up required.',
                    'url' => route('tools.pdf-editor'),
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Any (runs in a web browser)',
                    'isAccessibleForFree' => true,
                    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => collect($faqs)->map(fn ($faq) => [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                    ])->all(),
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'HowTo',
                    'name' => 'How to edit a PDF online',
                    'step' => [
                        ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Open', 'text' => 'Open a PDF by dragging it in or choosing it from your device.'],
                        ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Edit', 'text' => 'Click existing text to edit it, or add new text, images, shapes, annotations and signatures.'],
                        ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Organize', 'text' => 'Reorder, rotate, delete or merge pages using the page panel.'],
                        ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Export', 'text' => 'Click Download to generate the edited PDF locally and save it to your device.'],
                    ],
                ],
            ],
        );
    }

    public static function forPricing(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Pricing', 'item' => route('pricing')],
        ];

        return new self(
            title: 'SEO Packages & Generative Engine Optimization (GEO) | INZRA',
            description: 'INZRA SEO & GEO packages: technical SEO, on-page optimization, high-authority backlinks, AI search visibility. Competitive pricing. 4.9/5 rating from 2,841+ customers.',
            canonical: route('pricing'),
            keywords: 'SEO packages, GEO packages, generative engine optimization, AI search optimization, technical SEO, on-page SEO, off-page SEO, link building packages, backlink packages',
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'name' => 'SEO & GEO Packages',
                    'provider' => ['@type' => 'Organization', 'name' => 'INZRA'],
                    'hasOfferCatalog' => [
                        '@type' => 'OfferCatalog',
                        'name' => 'SEO & GEO Packages',
                        'itemListElement' => [
                            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Backlinks Only', 'description' => '1,000 high-authority backlinks delivered every month — pure off-page link building volume.'], 'priceCurrency' => 'USD', 'price' => '100', 'priceSpecification' => ['@type' => 'UnitPriceSpecification', 'price' => '100', 'priceCurrency' => 'USD', 'billingDuration' => 'P1M']],
                            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '10 Keyword Ranking Package', 'description' => 'A focused ranking campaign built around the 10 keywords that move your revenue.'], 'priceCurrency' => 'USD', 'price' => '200', 'priceSpecification' => ['@type' => 'UnitPriceSpecification', 'price' => '200', 'priceCurrency' => 'USD', 'billingDuration' => 'P1M']],
                            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '20 Keyword Ranking + GEO Package', 'description' => 'Rank 20 keywords in Google while building the entity signals AI answer engines cite.'], 'priceCurrency' => 'USD', 'price' => '280', 'priceSpecification' => ['@type' => 'UnitPriceSpecification', 'price' => '280', 'priceCurrency' => 'USD', 'billingDuration' => 'P1M']],
                        ],
                    ],
                ],
            ],
        );
    }

    public static function forBlogIndex(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Blog', 'item' => route('blog.index')],
        ];

        return new self(
            title: 'SEO & Link Building Blog | Authority Insights & Strategy | INZRA',
            description: 'INZRA blog: SEO insights, link building strategy, backlink case studies, anchor text ratios. Learn from 4,000+ publisher conversations. Expert link building knowledge.',
            canonical: url()->full(),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                self::breadcrumb($breadcrumbItems),
            ],
        );
    }

    public static function forContact(): self
    {
        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Contact', 'item' => route('contact')],
        ];

        return new self(
            title: 'Contact INZRA | Support, FAQ & Publisher List',
            description: 'Contact INZRA support. SEO FAQ, refund policy, link delivery issues. Subscribe to weekly publisher list. Expert support team. Response in 24 hours.',
            canonical: route('contact'),
            keywords: 'contact INZRA, backlink support, SEO FAQ, publisher list, refund policy, link building support, contact form',
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => [
                        ['@type' => 'Question', 'name' => 'What payment methods do you accept?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => "We arrange orders and payment directly over WhatsApp — message us your requirements and we'll confirm pricing and details before you pay."]],
                        ['@type' => 'Question', 'name' => 'What is your refund policy?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => "We offer a 30-day money-back guarantee on all backlink purchases. If you're not satisfied or your links don't deliver as promised, we'll refund you in full."]],
                        ['@type' => 'Question', 'name' => 'How long does delivery take?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Most backlinks are delivered within 30 days. Some rush services are available for an additional fee with 7-14 day delivery windows.']],
                        ['@type' => 'Question', 'name' => 'Are backlinks guaranteed to improve rankings?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'While we cannot guarantee ranking improvements (no SEO company can), our high-quality backlinks from verified sites with real traffic significantly improve your link profile and domain authority.']],
                    ],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    '@id' => url('/'),
                    'name' => 'INZRA',
                    'url' => url('/'),
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'contactType' => 'Customer Support',
                        'email' => 'support@inzra.com',
                        'availableLanguage' => ['en'],
                    ],
                ],
                self::breadcrumb($breadcrumbItems),
            ],
        );
    }

    public static function forProduct(Product $product): self
    {
        $title = $product->seo_title ?: "{$product->name} — \${$product->formatted_price} | INZRA";
        $categoryName = $product->category?->name ?? 'Marketplace';

        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Marketplace', 'item' => route('marketplace')],
            ['name' => $categoryName],
            ['name' => $product->name, 'item' => route('products.show', $product)],
        ];

        return new self(
            title: $title,
            description: $product->seo_description ?: ($product->meta_description ?? $title),
            canonical: $product->canonical_url ?: route('products.show', $product),
            ogType: 'product',
            ogImage: $product->og_image ?: ($product->image_path ? asset($product->image_path) : asset('og-cover.svg')),
            breadcrumbItems: $breadcrumbItems,
            jsonLd: [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => $product->name,
                    'description' => $product->meta_description,
                    'sku' => $product->sku,
                    'image' => $product->image_path ? asset($product->image_path) : null,
                    'category' => $categoryName,
                    'brand' => ['@type' => 'Brand', 'name' => 'INZRA'],
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => route('products.show', $product),
                        'priceCurrency' => $product->currency,
                        'price' => number_format((float) $product->price, 2, '.', ''),
                        'availability' => 'https://schema.org/InStock',
                        'itemCondition' => 'https://schema.org/NewCondition',
                        'seller' => ['@type' => 'Organization', 'name' => 'INZRA'],
                    ],
                ],
                self::breadcrumb($breadcrumbItems),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => collect(config('inzra.pdp.faqs'))->map(fn ($faq) => [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                    ])->all(),
                ],
            ],
        );
    }

    public static function forBlogPost(BlogPost $post): self
    {
        $ogImage = $post->og_image ?: ($post->cover_image_path ? asset($post->cover_image_path) : asset('og-cover.svg'));

        $breadcrumbItems = [
            ['name' => 'Home', 'item' => url('/')],
            ['name' => 'Blog', 'item' => route('blog.index')],
            ['name' => $post->category],
            ['name' => $post->title, 'item' => route('blog.show', $post)],
        ];

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->excerpt,
                'image' => $ogImage,
                'datePublished' => optional($post->published_at)->toDateString(),
                'author' => ['@type' => 'Organization', 'name' => 'INZRA', 'url' => url('/')],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'INZRA',
                    'logo' => ['@type' => 'ImageObject', 'url' => asset('favicon.svg')],
                ],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post)],
                'articleSection' => $post->category,
            ],
            self::breadcrumb($breadcrumbItems),
        ];

        if (! empty($post->faqs)) {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($post->faqs)->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ])->all(),
            ];
        }

        return new self(
            title: $post->seo_title ?: "{$post->title} | INZRA Blog",
            description: $post->seo_description ?: ($post->excerpt ?? $post->title),
            canonical: $post->canonical_url ?: route('blog.show', $post),
            ogType: 'article',
            ogImage: $ogImage,
            breadcrumbItems: $breadcrumbItems,
            jsonLd: $jsonLd,
        );
    }

    private static function orderProcessHowTo(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => 'How to order a backlink placement with INZRA',
            'description' => 'The five steps from placing an order to receiving a live, published backlink.',
            'step' => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Order', 'text' => 'Choose a package or filter the marketplace by DA, traffic, country and niche. Add your target URL and anchor preferences at checkout.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Website selection', 'text' => 'We shortlist three publishers that fit your topic and send live metrics for each. You pick one, or ask for another round.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Content writing', 'text' => 'A native-language writer in your niche drafts the article. You get the Google Doc to comment on before it goes anywhere near the editor.'],
                ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Publishing', 'text' => 'The editor publishes on their own schedule with your link in-content and dofollow. We confirm the anchor and the surrounding paragraph.'],
                ['@type' => 'HowToStep', 'position' => 5, 'name' => 'Delivery report', 'text' => 'Live URL, screenshot, final metrics and an indexing check at day 14. Monitoring keeps running for 12 months.'],
            ],
        ];
    }

    private static function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['item'] ?? null,
            ]))->all(),
        ];
    }
}
