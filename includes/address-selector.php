<?php
/**
 * Reusable Nepal Address Selector Component
 * Renders cascading dropdowns: Province → District → Municipality → Ward
 *
 * Usage:
 *   $addressData = [
 *     'province' => '',
 *     'district' => '',
 *     'municipality' => '',
 *     'ward' => '',
 *     'street' => ''
 *   ];
 *   include 'includes/address-selector.php';
 *
 * The component loads nepal-data.js and address-handler.js automatically.
 */

$addr = isset($addressData) ? $addressData : [];
$prefix = isset($addressPrefix) ? $addressPrefix : 'address';
?>

<link rel="stylesheet" href="<?php echo url('/public/assets/css/address-selector.css'); ?>">

<div class="address-selector-wrapper">
  <div class="row g-3">
    <!-- Province -->
    <div class="col-md-6 mb-3">
      <label for="<?php echo $prefix; ?>_province" class="form-label small fw-bold">Province <span class="text-danger">*</span></label>
      <select name="<?php echo $prefix; ?>_province" id="<?php echo $prefix; ?>_province" class="form-select" required>
        <option value="">-- Select Province --</option>
      </select>
    </div>

    <!-- District -->
    <div class="col-md-6 mb-3">
      <label for="<?php echo $prefix; ?>_district" class="form-label small fw-bold">District <span class="text-danger">*</span></label>
      <select name="<?php echo $prefix; ?>_district" id="<?php echo $prefix; ?>_district" class="form-select" required disabled>
        <option value="">-- Select District --</option>
      </select>
    </div>

    <!-- Municipality -->
    <div class="col-md-6 mb-3">
      <label for="<?php echo $prefix; ?>_municipality" class="form-label small fw-bold">Municipality / Rural Municipality <span class="text-danger">*</span></label>
      <select name="<?php echo $prefix; ?>_municipality" id="<?php echo $prefix; ?>_municipality" class="form-select" required disabled>
        <option value="">-- Select Municipality --</option>
      </select>
    </div>

    <!-- Ward -->
    <div class="col-md-3 mb-3">
      <label for="<?php echo $prefix; ?>_ward" class="form-label small fw-bold">Ward No. <span class="text-danger">*</span></label>
      <select name="<?php echo $prefix; ?>_ward" id="<?php echo $prefix; ?>_ward" class="form-select" required disabled>
        <option value="">Ward</option>
      </select>
    </div>

    <!-- Street / Locality -->
    <div class="col-md-3 mb-3">
      <label for="<?php echo $prefix; ?>_street" class="form-label small fw-bold">Street / Locality</label>
      <input type="text" name="<?php echo $prefix; ?>_street" id="<?php echo $prefix; ?>_street" class="form-control" placeholder="E.g. Main Road" value="<?php echo h($addr['street'] ?? ''); ?>">
    </div>
  </div>

  <!-- Hidden combined address field (auto-generated) -->
  <input type="hidden" name="<?php echo $prefix; ?>" id="<?php echo $prefix; ?>_combined" value="<?php echo h($addr['combined'] ?? ''); ?>">

  <!-- Address preview -->
  <div id="<?php echo $prefix; ?>_preview" class="address-preview text-muted small" style="display: none;">
    <i class="bi bi-geo-alt-fill"></i> <span id="<?php echo $prefix; ?>_preview_text"></span>
  </div>
</div>

<script src="<?php echo url('/public/assets/js/nepal-data.js'); ?>"></script>
<script src="<?php echo url('/public/assets/js/address-handler.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  initAddressSelector('<?php echo $prefix; ?>');

  <?php if (!empty($addr['province'])): ?>
    setAddressValue('<?php echo $prefix; ?>', 'province', '<?php echo h($addr['province']); ?>');
  <?php endif; ?>
  <?php if (!empty($addr['district'])): ?>
    setAddressValue('<?php echo $prefix; ?>', 'district', '<?php echo h($addr['district']); ?>');
  <?php endif; ?>
  <?php if (!empty($addr['municipality'])): ?>
    setAddressValue('<?php echo $prefix; ?>', 'municipality', '<?php echo h($addr['municipality']); ?>');
  <?php endif; ?>
  <?php if (!empty($addr['ward'])): ?>
    setAddressValue('<?php echo $prefix; ?>', 'ward', '<?php echo h($addr['ward']); ?>');
  <?php endif; ?>
});
</script>
