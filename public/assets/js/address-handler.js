/**
 * Cascading Nepal Address Selector
 * Handles province → district → municipality → ward dropdown chains
 */

function initAddressSelector(prefix) {
  var provinceEl = document.getElementById(prefix + '_province');
  var districtEl = document.getElementById(prefix + '_district');
  var municipalityEl = document.getElementById(prefix + '_municipality');
  var wardEl = document.getElementById(prefix + '_ward');
  var streetEl = document.getElementById(prefix + '_street');
  var previewEl = document.getElementById(prefix + '_preview');
  var previewTextEl = document.getElementById(prefix + '_preview_text');
  var combinedEl = document.getElementById(prefix + '_combined');

  if (!provinceEl) return;

  // Populate provinces
  var provinces = getProvinces();
  provinces.forEach(function(p) {
    var opt = document.createElement('option');
    opt.value = p.name;
    opt.textContent = p.name + ' (' + p.name_np + ')';
    opt.dataset.id = p.id;
    provinceEl.appendChild(opt);
  });

  // Province change → load districts
  provinceEl.addEventListener('change', function() {
    var selected = provinces.find(function(p) { return p.name === provinceEl.value; });
    var districtNames = selected ? getDistrictNames(selected.id) : [];

    // Reset downstream
    clearOptions(districtEl, '-- Select District --');
    clearOptions(municipalityEl, '-- Select Municipality --');
    clearWards(wardEl);
    districtEl.disabled = true;
    municipalityEl.disabled = true;
    wardEl.disabled = true;

    if (districtNames.length > 0) {
      districtNames.forEach(function(d) {
        var opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        districtEl.appendChild(opt);
      });
      districtEl.disabled = false;
    }

    updateAddressPreview(prefix);
  });

  // District change → load municipalities
  districtEl.addEventListener('change', function() {
    var selectedProvince = provinces.find(function(p) { return p.name === provinceEl.value; });
    var municipals = selectedProvince ? getMunicipalities(selectedProvince.id, districtEl.value) : [];

    clearOptions(municipalityEl, '-- Select Municipality --');
    clearWards(wardEl);
    municipalityEl.disabled = true;
    wardEl.disabled = true;

    if (municipals.length > 0) {
      municipals.forEach(function(m) {
        var opt = document.createElement('option');
        opt.value = m.name;
        opt.textContent = m.name + ' (' + m.type + ')';
        opt.dataset.wards = m.wards;
        municipalityEl.appendChild(opt);
      });
      municipalityEl.disabled = false;
    }

    updateAddressPreview(prefix);
  });

  // Municipality change → load wards
  municipalityEl.addEventListener('change', function() {
    var selectedOpt = municipalityEl.options[municipalityEl.selectedIndex];
    var wardCount = parseInt(selectedOpt ? selectedOpt.dataset.wards : 0, 10);

    clearWards(wardEl);
    wardEl.disabled = true;

    if (wardCount > 0) {
      for (var w = 1; w <= wardCount; w++) {
        var opt = document.createElement('option');
        opt.value = w;
        opt.textContent = 'Ward No. ' + w;
        wardEl.appendChild(opt);
      }
      wardEl.disabled = false;
    }

    updateAddressPreview(prefix);
  });

  // Ward change → update preview
  wardEl.addEventListener('change', function() {
    updateAddressPreview(prefix);
  });

  // Street input → update preview
  if (streetEl) {
    streetEl.addEventListener('input', function() {
      updateAddressPreview(prefix);
    });
  }

  // Listen for changes on all fields to update combined
  var allFields = [provinceEl, districtEl, municipalityEl, wardEl];
  allFields.forEach(function(el) {
    if (el) {
      el.addEventListener('change', function() {
        updateCombined(prefix);
      });
    }
  });
  if (streetEl) {
    streetEl.addEventListener('change', function() {
      updateCombined(prefix);
    });
  }
}

/**
 * Update the live address preview
 */
function updateAddressPreview(prefix) {
  var provinceEl = document.getElementById(prefix + '_province');
  var districtEl = document.getElementById(prefix + '_district');
  var municipalityEl = document.getElementById(prefix + '_municipality');
  var wardEl = document.getElementById(prefix + '_ward');
  var streetEl = document.getElementById(prefix + '_street');
  var previewEl = document.getElementById(prefix + '_preview');
  var previewTextEl = document.getElementById(prefix + '_preview_text');

  if (!provinceEl || !previewEl) return;

  var parts = [];
  if (streetEl && streetEl.value) parts.push(streetEl.value);
  if (municipalityEl && municipalityEl.value) parts.push(municipalityEl.value + (wardEl && wardEl.value ? '-' + wardEl.value : ''));
  if (districtEl && districtEl.value) parts.push(districtEl.value);
  if (provinceEl && provinceEl.value) parts.push(provinceEl.value);

  if (parts.length > 0) {
    previewTextEl.textContent = parts.join(', ');
    previewEl.style.display = 'block';
  } else {
    previewEl.style.display = 'none';
  }
}

/**
 * Update the hidden combined address field
 */
function updateCombined(prefix) {
  var provinceEl = document.getElementById(prefix + '_province');
  var districtEl = document.getElementById(prefix + '_district');
  var municipalityEl = document.getElementById(prefix + '_municipality');
  var wardEl = document.getElementById(prefix + '_ward');
  var streetEl = document.getElementById(prefix + '_street');
  var combinedEl = document.getElementById(prefix + '_combined');

  if (!combinedEl) return;

  var parts = [];
  if (streetEl && streetEl.value) parts.push(streetEl.value);
  if (municipalityEl && municipalityEl.value) parts.push(municipalityEl.value + (wardEl && wardEl.value ? '-' + wardEl.value : ''));
  if (districtEl && districtEl.value) parts.push(districtEl.value);
  if (provinceEl && provinceEl.value) parts.push(provinceEl.value);

  combinedEl.value = parts.join(', ');
}

/**
 * Set a field value programmatically (used for edit/prefill)
 */
function setAddressValue(prefix, field, value) {
  var el = document.getElementById(prefix + '_' + field);
  if (!el) return;

  el.value = value;
  el.dispatchEvent(new Event('change'));
}

/**
 * Clear select options, keeping the placeholder
 */
function clearOptions(selectEl, placeholder) {
  selectEl.innerHTML = '';
  var defaultOpt = document.createElement('option');
  defaultOpt.value = '';
  defaultOpt.textContent = placeholder;
  selectEl.appendChild(defaultOpt);
}

/**
 * Clear ward dropdown
 */
function clearWards(wardEl) {
  wardEl.innerHTML = '';
  var defaultOpt = document.createElement('option');
  defaultOpt.value = '';
  defaultOpt.textContent = 'Ward';
  wardEl.appendChild(defaultOpt);
}
