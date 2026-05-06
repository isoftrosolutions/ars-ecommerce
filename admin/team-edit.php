<?php
/**
 * Admin Team Member Edit / Create Page
 * Easy Shopping A.R.S
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
protect_admin_page();

$member_id = (int)($_GET['id'] ?? 0);
$is_edit   = $member_id > 0;
$member    = null;
$error     = null;

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$member) {
        header('Location: ' . url('/admin/team.php'));
        exit;
    }
    // Normalize backslash paths from Windows-seeded DB
    $member['profile_image'] = str_replace('\\', '/', $member['profile_image'] ?? '');
}

$page_title = $is_edit ? 'Edit Team Member' : 'Add Team Member';
$csrf_token = generate_csrf_token();
include __DIR__ . '/includes/header.php';

// Helper: field value with XSS protection
function fv(string $field, array $member = null, string $default = ''): string {
    $val = $member[$field] ?? $default;
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!-- Breadcrumb -->
<div class="page-header" style="margin-bottom: 8px;">
    <div>
        <nav style="font-size:13px; color:var(--text-secondary); margin-bottom:6px;">
            <a href="<?php echo url('/admin/team.php'); ?>" style="color:var(--text-secondary); text-decoration:none;">
                <i class="fa-solid fa-user-group"></i> Team Members
            </a>
            <span style="margin: 0 8px;">/</span>
            <span style="color:var(--text-primary);"><?php echo $is_edit ? h($member['name']) : 'New Member'; ?></span>
        </nav>
        <h1 style="margin-bottom:0;"><?php echo $page_title; ?></h1>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="<?php echo url('/admin/team.php'); ?>" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Back to List
        </a>
        <button type="button" class="btn btn-primary" id="save-btn" onclick="submitForm()">
            <i class="fa-solid fa-save"></i> <span id="save-label">Save Member</span>
        </button>
    </div>
</div>

<form id="edit-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="action"    value="<?php echo $is_edit ? 'update' : 'create'; ?>">
    <?php if ($is_edit): ?>
    <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
    <?php endif; ?>

    <div class="edit-layout">

        <!-- ── Left column: photo ─────────────────────────────── -->
        <div class="edit-sidebar">

            <!-- Photo card -->
            <div class="card" style="text-align:center; padding: 24px;">
                <div class="card-header" style="margin-bottom:20px;">
                    <span class="card-title" style="font-size:15px;">Profile Photo</span>
                </div>

                <!-- Preview -->
                <div id="photo-wrap" style="position:relative; display:inline-block; margin-bottom:20px;">
                    <div id="photo-ring" style="
                        width: 140px; height: 140px;
                        border-radius: 50%;
                        overflow: hidden;
                        border: 3px solid var(--primary-light);
                        box-shadow: 0 4px 20px rgba(37,99,235,0.12);
                        margin: 0 auto;
                        background: var(--bg-secondary);
                        display: flex; align-items: center; justify-content: center;">
                        <img id="photo-preview"
                             src="<?php echo $is_edit && !empty($member['profile_image'])
                                    ? h(url($member['profile_image']))
                                    : url('/public/assets/img/default-avatar.png'); ?>"
                             alt="Profile photo"
                             style="width:100%; height:100%; object-fit:cover;"
                             onerror="this.src='<?php echo url('/public/assets/img/default-avatar.png'); ?>'">
                    </div>
                    <!-- Camera overlay button -->
                    <label for="profile_image" style="
                        position:absolute; bottom:4px; right:4px;
                        width:34px; height:34px;
                        background:var(--primary); color:white;
                        border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        cursor:pointer; box-shadow:var(--shadow-md);
                        font-size:14px; transition: transform 0.2s;"
                        title="Change photo"
                        onmouseenter="this.style.transform='scale(1.1)'"
                        onmouseleave="this.style.transform='scale(1)'">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </div>

                <input type="file" id="profile_image" name="profile_image"
                       accept="image/jpeg,image/png,image/webp"
                       style="display:none;"
                       onchange="previewPhoto(this)">

                <p style="font-size:12px; color:var(--text-secondary); margin-bottom:16px;">
                    JPG, PNG or WebP &bull; Max 2 MB<br>Recommended: square crop
                </p>

                <button type="button" class="btn btn-ghost" style="width:100%; font-size:13px;"
                        onclick="document.getElementById('profile_image').click()">
                    <i class="fa-solid fa-upload"></i> Upload Photo
                </button>

                <?php if ($is_edit): ?>
                <div id="remove-photo-wrap" style="margin-top:10px; <?php echo empty($member['profile_image']) ? 'display:none;' : ''; ?>">
                    <button type="button" class="btn btn-ghost" style="width:100%; font-size:13px; color:var(--danger); border-color:var(--danger);"
                            onclick="removePhoto()">
                        <i class="fa-solid fa-trash"></i> Remove Photo
                    </button>
                </div>
                <input type="hidden" id="remove_photo" name="remove_photo" value="0">
                <?php endif; ?>
            </div>

            <!-- Status card -->
            <div class="card" style="padding:20px;">
                <div class="card-header" style="margin-bottom:16px;">
                    <span class="card-title" style="font-size:15px;">Visibility</span>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?php echo (!$is_edit || $member['is_active']) ? 'selected' : ''; ?>>Active (shown on website)</option>
                        <option value="0" <?php echo ($is_edit && !$member['is_active']) ? 'selected' : ''; ?>>Inactive (hidden)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Display Order
                        <span style="font-weight:400; color:var(--text-secondary); font-size:12px;">(lower = first)</span>
                    </label>
                    <input type="number" name="display_order" class="form-control"
                           min="0" value="<?php echo fv('display_order', $member, '0'); ?>">
                </div>
            </div>

            <!-- Quick preview link (edit only) -->
            <?php if ($is_edit): ?>
            <div class="card" style="padding:16px; text-align:center;">
                <a href="<?php echo url('/about'); ?>#team" target="_blank"
                   style="font-size:13px; color:var(--primary); text-decoration:none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View on About Page
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Right column: fields ───────────────────────────── -->
        <div class="edit-main">

            <!-- Personal info -->
            <div class="card" style="padding:24px; margin-bottom:20px;">
                <div class="card-header" style="margin-bottom:20px;">
                    <span class="card-title">Personal Information</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?php echo fv('name', $member); ?>"
                           placeholder="e.g. Aaditya Kumar Kushwaha" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role <span style="color:var(--danger);">*</span></label>
                        <select name="role" class="form-control" required>
                            <?php foreach (['admin' => 'Admin / Management', 'manager' => 'Manager', 'support' => 'Support', 'technical' => 'Technical'] as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo ($is_edit && $member['role'] === $val) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position / Title <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="position" class="form-control"
                               value="<?php echo fv('position', $member); ?>"
                               placeholder="e.g. Founder & CEO" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="4"
                              placeholder="A short description shown on the About page…"><?php echo fv('bio', $member); ?></textarea>
                    <p style="font-size:12px; color:var(--text-secondary); margin-top:4px;">Keep it under 200 characters for best display.</p>
                </div>
            </div>

            <!-- Contact -->
            <div class="card" style="padding:24px; margin-bottom:20px;">
                <div class="card-header" style="margin-bottom:20px;">
                    <span class="card-title">Contact Details</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-envelope" style="color:var(--text-secondary); margin-right:6px;"></i> Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo fv('email', $member); ?>"
                               placeholder="name@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-phone" style="color:var(--text-secondary); margin-right:6px;"></i> Mobile</label>
                        <input type="text" name="mobile" class="form-control"
                               value="<?php echo fv('mobile', $member); ?>"
                               placeholder="10-digit number"
                               pattern="[0-9]{10}" title="10-digit mobile number">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label"><i class="fa-brands fa-facebook" style="color:#1877F2; margin-right:6px;"></i> Facebook URL</label>
                    <input type="url" name="fb_link" class="form-control"
                           value="<?php echo fv('fb_link', $member); ?>"
                           placeholder="https://facebook.com/...">
                </div>
            </div>

            <!-- Action row (bottom) -->
            <div style="display:flex; justify-content:flex-end; gap:12px; padding-bottom: 32px;">
                <a href="<?php echo url('/admin/team.php'); ?>" class="btn btn-ghost">
                    Cancel
                </a>
                <?php if ($is_edit): ?>
                <button type="button" class="btn btn-ghost" style="color:var(--danger); border-color:var(--danger);"
                        onclick="deleteMember(<?php echo $member_id; ?>)">
                    <i class="fa-solid fa-trash"></i> Delete Member
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" onclick="submitForm()">
                    <i class="fa-solid fa-save"></i> <span class="save-label-btn">Save Member</span>
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.edit-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 20px;
    align-items: start;
}

.edit-sidebar { position: sticky; top: 88px; }

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 6px;
}

.form-group { margin-bottom: 18px; }
.form-group:last-child { margin-bottom: 0; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-control {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 14px;
    background: var(--bg-primary);
    color: var(--text-primary);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
}

textarea.form-control { resize: vertical; min-height: 90px; }

@media (max-width: 900px) {
    .edit-layout { grid-template-columns: 1fr; }
    .edit-sidebar { position: static; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photo-preview').src = e.target.result;
        const rw = document.getElementById('remove-photo-wrap');
        if (rw) rw.style.display = 'block';
        const rp = document.getElementById('remove_photo');
        if (rp) rp.value = '0';
    };
    reader.readAsDataURL(input.files[0]);
}

function removePhoto() {
    document.getElementById('photo-preview').src = '<?php echo url('/public/assets/img/default-avatar.png'); ?>';
    document.getElementById('profile_image').value = '';
    document.getElementById('remove_photo').value = '1';
    const rw = document.getElementById('remove-photo-wrap');
    if (rw) rw.style.display = 'none';
}

function submitForm() {
    const form = document.getElementById('edit-form');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const saveBtn  = document.getElementById('save-btn');
    const labels   = document.querySelectorAll('#save-btn .save-label-btn, #save-label');
    saveBtn.disabled = true;
    labels.forEach(l => l.textContent = 'Saving…');

    const formData = new FormData(form);

    fetch(window.BASE_URL + '/admin/api/team-members.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Toast.success(data.message || 'Team member saved.');
            setTimeout(() => {
                window.location.href = window.BASE_URL + '/admin/team.php';
            }, 800);
        } else {
            Toast.error(data.message || 'Could not save team member.');
            saveBtn.disabled = false;
            labels.forEach(l => l.textContent = 'Save Member');
        }
    })
    .catch(() => {
        Toast.error('Network error — please try again.');
        saveBtn.disabled = false;
        labels.forEach(l => l.textContent = 'Save Member');
    });
}

function deleteMember(id) {
    if (!confirm('Delete this team member permanently?')) return;
    fetch(window.BASE_URL + '/admin/api/team-members.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Toast.success('Team member deleted.');
            setTimeout(() => window.location.href = window.BASE_URL + '/admin/team.php', 800);
        } else {
            Toast.error(data.message || 'Could not delete.');
        }
    })
    .catch(() => Toast.error('Network error.'));
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
