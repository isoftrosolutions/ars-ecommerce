<?php
// Load Backend Logic (includes functions.php which defines app_base_path)
require_once __DIR__ . '/backend/index-logic.php';

// Load Router and handle routing
require_once __DIR__ . '/includes/router.php';
$target_file = route($_SERVER['REQUEST_URI']);

if ($target_file && $target_file !== 'index.php') {
    if (file_exists(__DIR__ . '/' . $target_file)) {
        require_once __DIR__ . '/' . $target_file;
        exit;
    }
}

// 404 — no route matched and this isn't the homepage
$_routed_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($_routed_path !== '' && $target_file === null) {
    http_response_code(404);
    $page_title     = 'Page Not Found | Easy Shopping A.R.S Nepal';
    $page_meta_desc = 'Sorry, the page you were looking for does not exist. Browse our shop or return to the homepage.';
    include __DIR__ . '/includes/header-bootstrap.php';
    echo '<div class="container py-5 text-center">';
    echo '<i class="bi bi-exclamation-circle text-muted" style="font-size:5rem;"></i>';
    echo '<h1 class="mt-4 fw-bold">404 — Page Not Found</h1>';
    echo '<p class="text-muted mb-4">The page you\'re looking for doesn\'t exist or has been moved.</p>';
    echo '<a href="' . url('/') . '" class="btn btn-primary me-2"><i class="bi bi-house me-2"></i>Go Home</a>';
    echo '<a href="' . url('/shop') . '" class="btn btn-outline-primary"><i class="bi bi-shop me-2"></i>Browse Shop</a>';
    echo '</div>';
    include __DIR__ . '/includes/footer-bootstrap.php';
    exit;
}

// Proceed with Homepage (Index) content
$page_title    = 'Online Shopping in Nepal | Buy Electronics, Fashion & More';
$page_meta_desc= 'Easy Shopping A.R.S — Nepal\'s trusted online store. Shop electronics, fashion, home goods & more with fast delivery to Birgunj, Parsa and across Nepal. eSewa & COD accepted.';

// Load Header
require_once __DIR__ . '/includes/header-bootstrap.php';
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "<?php echo $base_url; ?>/#organization",
      "name": "Easy Shopping A.R.S",
      "url": "<?php echo $base_url; ?>",
      "logo": {
        "@type": "ImageObject",
        "url": "<?php echo $base_url; ?>/public/assets/img/og-default.jpg"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+977-9820210361",
        "contactType": "customer service",
        "areaServed": "NP",
        "availableLanguage": ["English", "Nepali"]
      },
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Birgunj",
        "addressRegion": "Parsa",
        "addressCountry": "NP"
      },
      "sameAs": []
    },
    {
      "@type": "WebSite",
      "@id": "<?php echo $base_url; ?>/#website",
      "url": "<?php echo $base_url; ?>",
      "name": "Easy Shopping A.R.S",
      "description": "Nepal's trusted online store for electronics, fashion, and home goods.",
      "publisher": {"@id": "<?php echo $base_url; ?>/#organization"},
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "<?php echo $base_url; ?>/shop?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    }
  ]
}
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

<style>
/* ═══ CSS Variables — Light Theme ════════════════════════════ */
:root {
  --void:        #ffffff;
  --deep:        #f8fafc;
  --surface:     #ffffff;
  --glass:       rgba(255,255,255,0.85);
  --ember:       #ea6c00;
  --ember-glow:  rgba(234,108,0,0.10);
  --gold:        #d97706;
  --gold-glow:   rgba(217,119,6,0.10);
  --ice:         #0f172a;
  --muted:       #64748b;
  --edge:        rgba(15,23,42,0.08);
  --shadow-sm:   0 2px 8px rgba(15,23,42,0.06);
  --shadow-md:   0 8px 32px rgba(15,23,42,0.10);
  --shadow-lg:   0 24px 64px rgba(15,23,42,0.12);
  --font-d:      'Fraunces', Georgia, serif;
  --font-b:      'DM Sans', sans-serif;
  --ease-out:    cubic-bezier(0.22,1,0.36,1);
}

/* ═══ Base Overrides ══════════════════════════════════════════ */
body {
  background: #ffffff !important;
  font-family: var(--font-b);
  color: var(--ice);
}
a { color: inherit; }
img { display: block; }

/* ═══ HERO ════════════════════════════════════════════════════ */
#hero {
  position: relative;
  height: 90vh; 
  min-height: 600px;
  display: flex; align-items: center;
  overflow: hidden;
  background: #fffbf5;
}
#hero-canvas {
  position: absolute; inset: 0;
  width: 100%; height: 100%; z-index: 0;
}
/* Giant ghost brand watermark */
.hero-watermark {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  font-family: var(--font-d);
  font-size: clamp(120px, 22vw, 280px);
  font-weight: 900;
  color: transparent;
  -webkit-text-stroke: 1px rgba(15,23,42,0.04);
  white-space: nowrap;
  pointer-events: none; z-index: 1;
  letter-spacing: -8px;
  user-select: none;
}
/* Ambient glow orbs */
.hero-orb {
  position: absolute; border-radius: 50%;
  pointer-events: none; filter: blur(90px);
  z-index: 1;
}
.hero-orb-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(234,108,0,0.09), transparent 70%);
  top: 10%; left: -5%;
  animation: orb-drift-1 12s ease-in-out infinite;
}
.hero-orb-2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(217,119,6,0.07), transparent 70%);
  bottom: 0; right: 10%;
  animation: orb-drift-2 15s ease-in-out infinite;
}
@keyframes orb-drift-1 {
  0%,100%{transform:translate(0,0) scale(1)}
  33%{transform:translate(40px,-30px) scale(1.1)}
  66%{transform:translate(-20px,40px) scale(0.95)}
}
@keyframes orb-drift-2 {
  0%,100%{transform:translate(0,0)}
  50%{transform:translate(-40px,-50px) scale(1.15)}
}

.hero-content {
  position: relative; z-index: 2; width: 100%;
}

/* Live badge */
.live-badge {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 16px; border-radius: 100px;
  background: rgba(249,115,22,0.1);
  border: 1px solid rgba(249,115,22,0.28);
  font-size: 10px; font-weight: 700;
  letter-spacing: 2.5px; text-transform: uppercase;
  color: var(--ember); margin-bottom: 24px;
  backdrop-filter: blur(12px);
  animation: fade-up 0.8s var(--ease-out) both;
}
.live-dot {
  width: 6px; height: 6px; background: var(--ember);
  border-radius: 50%; animation: pulse-dot 1.6s infinite;
}
@keyframes pulse-dot {
  0%,100%{box-shadow:0 0 0 0 rgba(249,115,22,0.7)}
  50%{box-shadow:0 0 0 6px rgba(249,115,22,0)}
}

/* Hero title */
.hero-h1 {
  font-family: var(--font-d);
  font-size: clamp(3.2rem, 9vw, 7.5rem);
  font-weight: 900; line-height: 0.92;
  letter-spacing: -3px; margin-bottom: 20px;
  animation: fade-up 0.8s 0.15s var(--ease-out) both;
}
.hero-h1 .line-outline {
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(15,23,42,0.12);
  display: block;
}
.hero-h1 .line-grad {
  background: linear-gradient(120deg, var(--ember) 0%, var(--gold) 60%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; display: block;
}
.hero-h1 .line-solid { color: #0f172a; display: block; }

.hero-desc {
  font-size: 15px; font-weight: 300;
  color: rgba(15,23,42,0.50);
  max-width: 340px; line-height: 1.75;
  margin-bottom: 38px;
  animation: fade-up 0.8s 0.28s var(--ease-out) both;
}

/* CTA Buttons */
.hero-cta-wrap {
  display: flex; flex-wrap: wrap; gap: 14px;
  animation: fade-up 0.8s 0.38s var(--ease-out) both;
}
.btn-fire {
  position: relative;
  display: inline-flex; align-items: center; gap: 10px;
  padding: 14px 30px; border-radius: 10px; border: none;
  background: linear-gradient(135deg, var(--ember) 0%, var(--gold) 100%);
  color: #fff; font-weight: 700; font-size: 14px;
  letter-spacing: 0.3px; text-decoration: none;
  overflow: hidden;
  transition: transform 0.25s var(--ease-out), box-shadow 0.25s;
}
.btn-fire:hover {
  transform: translateY(-4px);
  box-shadow: 0 24px 60px rgba(249,115,22,0.45);
  color: #fff;
}

.btn-void {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 14px 28px; border-radius: 10px;
  background: transparent;
  border: 1px solid rgba(15,23,42,0.18);
  color: #0f172a; font-size: 14px; font-weight: 500;
  text-decoration: none;
  transition: all 0.25s;
}
.btn-void:hover {
  background: rgba(15,23,42,0.05);
  transform: translateY(-3px);
  color: #0f172a;
}

/* Mini stats row */
.hero-stats {
  display: flex; gap: 28px; margin-top: 48px;
  animation: fade-up 0.8s 0.5s var(--ease-out) both;
}
.hs-sep { width: 1px; background: rgba(15,23,42,0.10); }
.hs-val {
  font-family: var(--font-d); font-size: 26px; font-weight: 900;
  color: var(--gold); line-height: 1;
}
.hs-lbl { font-size: 11px; color: var(--muted); margin-top: 3px; }

/* Features Strip */
#features {
  background: #f8fafc;
  border-top: 1px solid rgba(15,23,42,0.07);
  border-bottom: 1px solid rgba(15,23,42,0.07);
  padding: 26px 0;
}
.feat-row { display: flex; }
.feat-item {
  display: flex; align-items: center; gap: 13px;
  flex: 1; padding: 8px 20px;
  border-right: 1px solid var(--edge);
}
.feat-item:last-child { border-right: none; }
.feat-ico {
  width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 17px;
}
.feat-title { font-size: 13px; font-weight: 600; color: var(--ice); line-height: 1.2; }
.feat-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* Categories & Products */
.sec-eyebrow {
  font-size: 10px; letter-spacing: 3.5px; text-transform: uppercase;
  color: var(--ember); font-weight: 700; display: block; margin-bottom: 10px;
}
.sec-h2 {
  font-family: var(--font-d);
  font-size: clamp(2rem, 4.5vw, 3.2rem);
  font-weight: 900; color: var(--ice);
  line-height: 1.0; margin-bottom: 0;
  letter-spacing: -1px;
}
.sec-sub { color: var(--muted); font-size: 14px; font-weight: 300; margin-top: 10px; }

#categories { background: #f1f5f9; padding: 90px 0; }
.cat-tile {
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.07);
  border-radius: 20px; padding: 26px 14px 22px;
  text-align: center; text-decoration: none !important;
  display: block; transition: all 0.3s;
}
.cat-tile:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.05);
}
.cat-ico {
  width: 54px; height: 54px; border-radius: 16px; margin: 0 auto 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
}
.cat-nm { font-size: 12px; font-weight: 600; color: #0f172a; }

#products { background: #ffffff; padding: 90px 0; }
.prod-card {
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.08);
  border-radius: 20px; overflow: hidden;
  display: flex; flex-direction: column;
  height: 100%; transition: all 0.3s;
}
.prod-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 60px rgba(15,23,42,0.08);
}
.prod-img-box {
  position: relative;
  background: linear-gradient(145deg, #f8fafc, #f1f5f9);
  aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
}
.prod-img-box img {
    width: 80%;
    height: 80%;
    object-fit: contain;
}
.disc-badge {
  position: absolute; top: 11px; left: 11px;
  background: #ef4444; color: #fff; font-size: 9px; font-weight: 800;
  padding: 3px 9px; border-radius: 100px;
}
.prod-body { padding: 16px 17px 17px; flex: 1; display: flex; flex-direction: column; }
.p-cat { font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: var(--ember); font-weight: 700; margin-bottom: 5px; }
.p-name { font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 12px; flex: 1; }
.p-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; }
.p-price { font-family: var(--font-d); font-size: 19px; font-weight: 700; }
.p-add {
  width: 34px; height: 34px; border-radius: 10px; border: none;
  background: var(--ember); color: #fff; font-size: 18px;
}

#cta {
  background: #fffbf5; padding: 100px 0; position: relative; overflow: hidden;
}
#cta-canvas { position: absolute; inset: 0; opacity: 0.3; }
.cta-h2 {
  font-family: var(--font-d); font-size: clamp(2.4rem, 5.5vw, 4.2rem);
  font-weight: 900; letter-spacing: -1.5px;
}
.cta-h2 em { font-style: normal; color: var(--ember); }

@keyframes fade-up {
  from{opacity:0;transform:translateY(30px)}
  to{opacity:1;transform:translateY(0)}
}

@media (max-width: 991px) {
  #hero { height: auto; padding: 100px 0; }
  .feat-row { flex-wrap: wrap; }
  .feat-item { width: 50%; border-right: none; border-bottom: 1px solid var(--edge); }
}
</style>

<!-- ════ HERO ══════════════════════════════════════════════════ -->
<section id="hero">
  <canvas id="hero-canvas"></canvas>
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-watermark" aria-hidden="true">ARS</div>

  <div class="container hero-content">
    <div class="row align-items-center">
      <div class="col-lg-7">
          <div class="live-badge">
            <span class="live-dot"></span>
            Nepal's #1 Online Store
          </div>

          <h1 class="hero-h1">
            <span class="line-outline">Shop</span>
            <span class="line-grad">Beyond</span>
            <span class="line-solid">Limits.</span>
          </h1>

          <p class="hero-desc">
            Your destination for curated products delivered across Nepal — Birgunj, Parsa and beyond. eSewa &amp; COD accepted.
          </p>

          <div class="hero-cta-wrap">
            <a href="<?php echo url('/shop'); ?>" class="btn-fire">
              Explore Shop &nbsp; <i class="bi bi-arrow-right"></i>
            </a>
            <a href="<?php echo url('/shop?q='); ?>" class="btn-void">
              View Deals
            </a>
          </div>

          <div class="hero-stats">
            <div>
              <div class="hs-val">500+</div>
              <div class="hs-lbl">Products</div>
            </div>
            <div class="hs-sep"></div>
            <div>
              <div class="hs-val">10K+</div>
              <div class="hs-lbl">Customers</div>
            </div>
            <div class="hs-sep"></div>
            <div>
              <div class="hs-val">24/7</div>
              <div class="hs-lbl">Support</div>
            </div>
          </div>
      </div>
      
      <div class="col-lg-5 d-none d-lg-block">
          <div style="background: white; padding: 30px; border-radius: 30px; box-shadow: var(--shadow-lg); text-align: center;">
              <div style="width: 100%; aspect-ratio: 1; background: var(--deep); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="bi bi-bag-heart" style="font-size: 100px; color: var(--ember); opacity: 0.2;"></i>
              </div>
              <h3 style="font-family: var(--font-d); font-weight: 700;">Premium Essentials</h3>
              <p style="color: var(--muted); font-size: 14px;">Handpicked quality for your lifestyle.</p>
              <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                  <span style="font-size: 24px; font-weight: 800; color: var(--gold);">Rs. 999+</span>
                  <a href="<?php echo url('/shop'); ?>" class="btn btn-dark rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">→</a>
              </div>
          </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ FEATURES ══════════════════════════════════════════════ -->
<section id="features">
  <div class="container">
    <div class="feat-row">
      <div class="feat-item">
        <div class="feat-ico" style="background:rgba(59,130,246,0.1);color:#3b82f6;"><i class="bi bi-truck"></i></div>
        <div>
          <div class="feat-title">Free Shipping</div>
          <div class="feat-sub">Orders over Rs. 1,000</div>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-ico" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="bi bi-shield-check"></i></div>
        <div>
          <div class="feat-title">Secure Payment</div>
          <div class="feat-sub">100% Protected</div>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-ico" style="background:rgba(249,115,22,0.1);color:var(--ember);"><i class="bi bi-arrow-repeat"></i></div>
        <div>
          <div class="feat-title">Easy Returns</div>
          <div class="feat-sub">5-Day Replacement</div>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-ico" style="background:rgba(168,85,247,0.1);color:#a855f7;"><i class="bi bi-headset"></i></div>
        <div>
          <div class="feat-title">24/7 Support</div>
          <div class="feat-sub">Expert Help</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ CATEGORIES ════════════════════════════════════════════ -->
<section id="categories">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col-lg-7">
        <span class="sec-eyebrow">Discover</span>
        <h2 class="sec-h2">Shop by<br>Category</h2>
        <p class="sec-sub">Every need, one place</p>
      </div>
      <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
        <a href="<?php echo url('/shop'); ?>" class="btn-void">
          All Categories &nbsp; <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>

    <?php
    $cat_icons  = ['Electronics'=>'bi-cpu-fill','Fashion'=>'bi-bag-fill','Home'=>'bi-house-fill','Beauty'=>'bi-stars','Sports'=>'bi-bicycle'];
    $cat_colors = ['#f97316', '#a855f7', '#3b82f6', '#ec4899', '#22c55e', '#f59e0b'];
    ?>
    <div class="row g-4">
      <?php foreach($categories as $i => $cat): 
          $ico = $cat_icons[$cat['name']] ?? 'bi-grid-3x3-gap-fill';
          $color = $cat_colors[$i % count($cat_colors)];
      ?>
        <div class="col-6 col-md-4 col-lg-2">
          <a href="<?php echo url('/shop?category=' . $cat['id']); ?>" class="cat-tile">
            <div class="cat-ico" style="background: <?= $color ?>15; color: <?= $color ?>;">
              <i class="bi <?= $ico ?>"></i>
            </div>
            <div class="cat-nm"><?= h($cat['name']) ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════ PRODUCTS ════════════════════════════════════════════════ -->
<section id="products">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col-lg-8">
        <span class="sec-eyebrow">Trending Now</span>
        <h2 class="sec-h2">Featured<br>Products</h2>
        <p class="sec-sub">The hottest items people are buying right now</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="<?php echo url('/shop'); ?>" class="btn-void">
            All Products &nbsp; <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>

    <?php if (empty($latest_products)): ?>
        <div class="text-center py-5">
            <i class="bi bi-basket" style="font-size: 60px; color: var(--muted); opacity: 0.3;"></i>
            <p class="mt-3 text-muted">Awaiting our latest arrivals. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
          <?php foreach($latest_products as $p): ?>
            <div class="col-6 col-md-4 col-lg-3">
              <div class="prod-card">
                <div class="prod-img-box">
                  <?php if($p['discount_price']): ?>
                    <span class="disc-badge">-<?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%</span>
                  <?php endif; ?>
                  <a href="<?php echo url('/product/' . $p['slug']); ?>">
                    <img src="<?= getProductImage($p['image']) ?>" alt="<?= h($p['name']) ?>">
                  </a>
                </div>
                <div class="prod-body">
                  <div class="p-cat"><?= h($p['cat_name'] ?? 'General') ?></div>
                  <h4 class="p-name"><?= h($p['name']) ?></h4>
                  <div class="p-footer">
                    <div>
                      <div class="p-price"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></div>
                    </div>
                    <button class="p-add" onclick="event.preventDefault(); addToCart(<?= $p['id'] ?>)">
                      <i class="bi bi-plus"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
</section>

<!-- ════ CTA ════════════════════════════════════════════════════ -->
<section id="cta">
  <canvas id="cta-canvas"></canvas>
  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h2 class="cta-h2">
          Experience<br>
          <em>Premium</em> Shopping<br>
          in Nepal.
        </h2>
        <p class="text-muted mt-3 mb-5">Join 10,000+ satisfied customers. Exclusive deals, fast delivery, and 24/7 support.</p>
        <a href="<?php echo url('/auth/signup'); ?>" class="btn-fire">
          <i class="bi bi-person-plus me-2"></i> Create Account
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ════ SCRIPTS ════════════════════════════════════════════════ -->
<script>
// Particle Constellation
(function(){
  const canvas = document.getElementById('hero-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H;
  const COUNT = 60, LINK = 120;
  let pts = [];

  function resize(){
    W = canvas.width = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
    pts = Array.from({ length: COUNT }, () => ({
      x: Math.random() * W,
      y: Math.random() * H,
      vx: (Math.random() - 0.5) * 0.4,
      vy: (Math.random() - 0.5) * 0.4,
      r: Math.random() * 2 + 0.5
    }));
  }
  resize();
  window.addEventListener('resize', resize);

  function frame(){
    ctx.clearRect(0, 0, W, H);
    for (let i = 0; i < pts.length; i++){
      const p = pts[i];
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0 || p.x > W) p.vx *= -1;
      if (p.y < 0 || p.y > H) p.vy *= -1;
      
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(234, 108, 0, 0.1)';
      ctx.fill();

      for (let j = i + 1; j < pts.length; j++){
        const q = pts[j];
        const dist = Math.hypot(p.x - q.x, p.y - q.y);
        if (dist < LINK){
          ctx.beginPath();
          ctx.moveTo(p.x, p.y);
          ctx.lineTo(q.x, q.y);
          ctx.strokeStyle = `rgba(234, 108, 0, ${0.1 * (1 - dist / LINK)})`;
          ctx.stroke();
        }
      }
    }
    requestAnimationFrame(frame);
  }
  frame();
})();

// CTA Waves
(function(){
  const canvas = document.getElementById('cta-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, t = 0;
  function resize(){
    W = canvas.width = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }
  resize();
  window.addEventListener('resize', resize);
  function draw(){
    ctx.clearRect(0, 0, W, H);
    for (let i = 0; i < 3; i++){
      ctx.beginPath();
      const baseY = H * (0.3 + i * 0.2);
      ctx.moveTo(0, baseY);
      for (let x = 0; x <= W; x += 10){
        const y = baseY + Math.sin(x * 0.005 + t + i) * 30;
        ctx.lineTo(x, y);
      }
      ctx.strokeStyle = `rgba(234, 108, 0, 0.1)`;
      ctx.stroke();
    }
    t += 0.02;
    requestAnimationFrame(draw);
  }
  draw();
})();
</script>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
