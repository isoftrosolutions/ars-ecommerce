<?php
/**
 * Admin Settings Page
 * Easy Shopping A.R.S
 */
$page_title = "Settings";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Site Settings</h1>
    <button class="btn btn-primary" onclick="saveAllSettings()">
        <i class="fa-solid fa-save"></i> Save All Changes
    </button>
</div>

<div class="settings-layout">

    <!-- Settings Navigation -->
    <div class="card settings-nav-card" style="position: sticky; top: 88px; padding: 8px 0;">
        <nav id="settings-nav">
            <a href="#general" class="settings-nav-item active" onclick="showTab('general', this)"><i class="fa-solid fa-sliders"></i> General</a>
            <a href="#company" class="settings-nav-item" onclick="showTab('company', this)"><i class="fa-solid fa-building"></i> Company</a>
            <a href="#social" class="settings-nav-item" onclick="showTab('social', this)"><i class="fa-solid fa-share-nodes"></i> Social Media</a>
            <a href="#payment" class="settings-nav-item" onclick="showTab('payment', this)"><i class="fa-solid fa-credit-card"></i> Payment</a>
            <a href="#shipping" class="settings-nav-item" onclick="showTab('shipping', this)"><i class="fa-solid fa-truck"></i> Shipping</a>
            <a href="#seo" class="settings-nav-item" onclick="showTab('seo', this)"><i class="fa-solid fa-magnifying-glass"></i> SEO</a>
            <a href="#products" class="settings-nav-item" onclick="showTab('products', this)"><i class="fa-solid fa-box"></i> Products</a>
            <a href="#reviews" class="settings-nav-item" onclick="showTab('reviews', this)"><i class="fa-solid fa-star"></i> Reviews</a>
        </nav>
    </div>

    <!-- Settings Panels -->
    <div>
        <!-- General -->
        <div class="settings-tab card" id="tab-general">
            <div class="card-header"><h3 class="card-title">General Settings</h3></div>
            <div class="form-group"><label class="form-label">Site Name</label><input type="text" class="form-control" data-key="site_name"></div>
            <div class="form-group"><label class="form-label">Site Description</label><textarea class="form-control" data-key="site_description" rows="3"></textarea></div>
            <div class="form-group"><label class="form-label">Site URL</label><input type="text" class="form-control" data-key="site_url"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Admin Email</label><input type="email" class="form-control" data-key="admin_email"></div>
                <div class="form-group"><label class="form-label">Support Email</label><input type="email" class="form-control" data-key="support_email"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Currency Symbol</label><input type="text" class="form-control" data-key="currency_symbol" style="max-width:120px;"></div>
                <div class="form-group"><label class="form-label">Currency Code</label><input type="text" class="form-control" data-key="currency_code" style="max-width:120px;"></div>
                <div class="form-group"><label class="form-label">Timezone</label><input type="text" class="form-control" data-key="timezone"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Maintenance Mode</label>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" data-key="maintenance_mode" data-type="bool" style="width:18px;height:18px;">
                    <span style="font-size:13px; color:var(--text-secondary);">Enable to show a maintenance message to visitors</span>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Maintenance Message</label><textarea class="form-control" data-key="maintenance_message" rows="2"></textarea></div>
        </div>

        <!-- Company -->
        <div class="settings-tab card" id="tab-company" style="display:none;">
            <div class="card-header"><h3 class="card-title">Company Information</h3></div>
            <div class="form-group"><label class="form-label">Company Name</label><input type="text" class="form-control" data-key="company_name"></div>
            <div class="form-group"><label class="form-label">Company Address</label><textarea class="form-control" data-key="company_address" rows="3"></textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Phone</label><input type="text" class="form-control" data-key="company_phone"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" data-key="company_email"></div>
            </div>
        </div>

        <!-- Social -->
        <div class="settings-tab card" id="tab-social" style="display:none;">
            <div class="card-header"><h3 class="card-title">Social Media Links</h3></div>
            <div class="form-group">
                <label class="form-label"><i class="fa-brands fa-facebook" style="color:#1877F2;"></i> Facebook URL</label>
                <input type="url" class="form-control" data-key="facebook_url" placeholder="https://facebook.com/...">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fa-brands fa-twitter" style="color:#1DA1F2;"></i> Twitter URL</label>
                <input type="url" class="form-control" data-key="twitter_url" placeholder="https://twitter.com/...">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fa-brands fa-instagram" style="color:#E4405F;"></i> Instagram URL</label>
                <input type="url" class="form-control" data-key="instagram_url" placeholder="https://instagram.com/...">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fa-brands fa-linkedin" style="color:#0A66C2;"></i> LinkedIn URL</label>
                <input type="url" class="form-control" data-key="linkedin_url" placeholder="https://linkedin.com/...">
            </div>
        </div>

        <!-- Payment -->
        <div class="settings-tab card" id="tab-payment" style="display:none;">
            <div class="card-header"><h3 class="card-title">Payment Settings</h3></div>
            <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:20px; padding:16px; background:var(--gray-50); border-radius:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:500;">eSewa Payment</span>
                    <input type="checkbox" data-key="esewa_enabled" data-type="bool" style="width:18px;height:18px;">
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:500;">Bank QR Payment</span>
                    <input type="checkbox" data-key="bank_qr_enabled" data-type="bool" style="width:18px;height:18px;">
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:500;">Cash on Delivery (COD)</span>
                    <input type="checkbox" data-key="cod_enabled" data-type="bool" style="width:18px;height:18px;">
                </div>
            </div>
            <div class="form-group"><label class="form-label">eSewa Merchant ID</label><input type="text" class="form-control" data-key="esewa_merchant_id" placeholder="Your eSewa merchant ID"></div>
            <div class="form-group"><label class="form-label">Bank Account Details</label><textarea class="form-control" data-key="bank_account_details" rows="4" placeholder="Bank name, account number, account name..."></textarea></div>
        </div>

        <!-- Shipping -->
        <div class="settings-tab card" id="tab-shipping" style="display:none;">
            <div class="card-header"><h3 class="card-title">Shipping Settings</h3></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Shipping Cost (Rs.)</label><input type="number" class="form-control" data-key="shipping_cost" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Free Shipping Threshold (Rs.)</label><input type="number" class="form-control" data-key="free_shipping_threshold" min="0" step="0.01"><small style="color:var(--text-secondary);">Set 0 to disable free shipping</small></div>
            </div>
            <div class="form-group"><label class="form-label">Estimated Delivery Days</label><input type="text" class="form-control" data-key="estimated_delivery_days" placeholder="e.g. 3-5"></div>
        </div>

        <!-- SEO -->
        <div class="settings-tab card" id="tab-seo" style="display:none;">
            <div class="card-header"><h3 class="card-title">SEO Settings</h3></div>
            <div class="form-group"><label class="form-label">Meta Title</label><input type="text" class="form-control" data-key="meta_title"></div>
            <div class="form-group"><label class="form-label">Meta Description</label><textarea class="form-control" data-key="meta_description" rows="3"></textarea></div>
            <div class="form-group"><label class="form-label">Meta Keywords</label><input type="text" class="form-control" data-key="meta_keywords" placeholder="Comma separated keywords"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Google Analytics ID</label><input type="text" class="form-control" data-key="google_analytics_id" placeholder="G-XXXXXXXXXX"></div>
                <div class="form-group"><label class="form-label">Facebook Pixel ID</label><input type="text" class="form-control" data-key="facebook_pixel_id"></div>
            </div>
        </div>

        <!-- Products -->
        <div class="settings-tab card" id="tab-products" style="display:none;">
            <div class="card-header"><h3 class="card-title">Product Settings</h3></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Products per Page</label><input type="number" class="form-control" data-key="products_per_page" min="1"></div>
                <div class="form-group"><label class="form-label">Featured Products Limit</label><input type="number" class="form-control" data-key="featured_products_limit" min="1"></div>
            </div>
            <div class="form-group"><label class="form-label">Low Stock Threshold</label><input type="number" class="form-control" data-key="low_stock_threshold" min="0"><small style="color:var(--text-secondary);">Products below this quantity are flagged as low stock</small></div>
            <div class="form-group"><label class="form-label">Order ID Prefix</label><input type="text" class="form-control" data-key="order_prefix" placeholder="ARS"></div>
        </div>

        <!-- Reviews -->
        <div class="settings-tab card" id="tab-reviews" style="display:none;">
            <div class="card-header"><h3 class="card-title">Review Settings</h3></div>
            <div style="display:flex; flex-direction:column; gap:16px; padding:16px; background:var(--gray-50); border-radius:8px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:500;">Enable Reviews</div>
                        <div style="font-size:12px; color:var(--text-secondary);">Allow customers to submit product reviews</div>
                    </div>
                    <input type="checkbox" data-key="reviews_enabled" data-type="bool" style="width:18px;height:18px;">
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:500;">Auto-approve Reviews</div>
                        <div style="font-size:12px; color:var(--text-secondary);">Publish reviews immediately without moderation</div>
                    </div>
                    <input type="checkbox" data-key="auto_approve_reviews" data-type="bool" style="width:18px;height:18px;">
                </div>
            </div>
            <div class="form-group"><label class="form-label">Reviews per Page</label><input type="number" class="form-control" data-key="reviews_per_page" min="1" style="max-width:120px;"></div>
        </div>
    </div>
</div>

<style>
.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}
.settings-nav-item:hover { background: var(--gray-50); color: var(--text-primary); }
.settings-nav-item.active { color: var(--primary); background: var(--primary-light); border-left-color: var(--primary); }
</style>

<script>
let currentSettings = {};

async function loadSettings() {
    const res = await fetch(BASE_URL + '/api/settings/all');
    const json = await res.json();
    if (!json.success) { Toast.error('Failed to load settings.'); return; }

    // If DB is empty, fetch defaults
    let settings = json.data;
    if (Object.keys(settings).length === 0) {
        const defRes = await fetch(BASE_URL + '/api/settings/defaults');
        const defJson = await defRes.json();
        if (defJson.success) settings = defJson.data;
    }

    currentSettings = settings;
    populateFields(settings);
}

function populateFields(settings) {
    document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.dataset.key;
        const val = settings[key] ?? '';
        if (el.dataset.type === 'bool') {
            el.checked = val === '1' || val === true;
        } else {
            el.value = val;
        }
    });
}

async function saveAllSettings() {
    const updates = {};
    document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.dataset.key;
        if (el.dataset.type === 'bool') {
            updates[key] = el.checked;
        } else {
            updates[key] = el.value;
        }
    });

    const res = await fetch(BASE_URL + '/api/settings/bulk-update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ settings: updates })
    });
    const json = await res.json();

    if (json.success) {
        Toast.success(`${json.data.updated} settings saved successfully!`);
    } else {
        Toast.error(json.message || 'Failed to save settings.');
    }
}

function showTab(name, el) {
    document.querySelectorAll('.settings-tab').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.settings-nav-item').forEach(a => a.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    el.classList.add('active');
    return false;
}

loadSettings();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
