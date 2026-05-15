document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Sidebar Navigation (AJAX Loader) ──────────────────────────
    const sidebarItems = document.querySelectorAll('.sidebar-item');

    sidebarItems.forEach(item => {
        item.addEventListener('click', function (event) {
            event.preventDefault();

            const url = this.getAttribute('data-url');
            if (!url || url === '#') return;

            const mainContent = document.getElementById('main-content');
            mainContent.innerHTML = '<p class="p-6">Loading...</p>';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(data => {
                mainContent.innerHTML = data;
                console.log('Konten berhasil dimuat:', url);
                
                // Re-init semua fungsi setelah konten baru masuk
                initializeAllComponents();
            })
            .catch(error => {
                console.error('Error fetching content:', error);
                mainContent.innerHTML = '<p class="p-6 text-red-500">Error loading content.</p>';
            });
        });
    });

    // ── 2. Init pertama kali saat halaman load ───────────────────────
    initializeAllComponents();
});

/**
 * Fungsi pembungkus untuk menjalankan semua init
 * agar tidak perlu dipanggil satu-satu berkali-kali
 */
function initializeAllComponents() {
    if (document.getElementById('form-aos')) {
        initOperatorDropdown();
        initAosForm();
        syncPdfForm();
        syncExcelForm();
    }
}

// ── initAosForm ──────────────────────────────────────────────────────
function initAosForm() {
    const form = document.getElementById('form-aos');
    if (!form || form.dataset.bound === 'true') return;

    form.dataset.bound = 'true';
    form.addEventListener('submit', handleAosSubmit);
    console.log('initAosForm: listener submit dipasang');
}

// ── handleAosSubmit ──────────────────────────────────────────────────
function handleAosSubmit(e) {
    e.preventDefault();
    e.stopPropagation();

    const mainContent = document.getElementById('main-content');
    const formData = new FormData(this);
    const submitBtn = this.querySelector('[type="submit"]');

    if (submitBtn) submitBtn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Response tidak ok');
        return response.text();
    })
    .then(data => {
        mainContent.innerHTML = data;
        // Re-init setelah tabel hasil muncul
        initializeAllComponents();
    })
    .catch(error => {
        console.error('handleAosSubmit error:', error);
        alert('Gagal memuat data.');
    })
    .finally(() => {
        if (submitBtn) submitBtn.disabled = false;
    });
}

// ── initOperatorDropdown ─────────────────────────────────────────────
function initOperatorDropdown() {
    const operatorDropdown = document.getElementById('operator-dropdown');
    if (!operatorDropdown || operatorDropdown.dataset.bound === 'true') return;

    operatorDropdown.dataset.bound = 'true';
    operatorDropdown.addEventListener('change', function () {
        const operator = this.value;
        const aircraftTypeDropdown = document.getElementById('aircraft-type-dropdown');

        if (!aircraftTypeDropdown) return;
        aircraftTypeDropdown.innerHTML = '<option value="">Select Aircraft Type</option>';
        
        if (!operator) return;

        fetch(`/get-aircraft-types?operator=${operator}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.ACType;
                    option.textContent = type.ACType;
                    aircraftTypeDropdown.appendChild(option);
                });
            })
            .catch(err => console.error('Error fetch aircraft:', err));
    });
}

// ── syncPdfForm ──────────────────────────────────────────────────────
function syncPdfForm() {
    const formAos = document.getElementById('form-aos');
    const formPdf = document.getElementById('form-pdf');

    if (!formAos || !formPdf) return;

    const inputs = {
        aos: {
            period: formAos.querySelector('select[name="period"]'),
            operator: formAos.querySelector('select[name="operator"]'),
            aircraft: formAos.querySelector('select[name="aircraft_type"]')
        },
        pdf: {
            period: formPdf.querySelector('input[name="period"]'),
            operator: formPdf.querySelector('input[name="operator"]'),
            aircraft: formPdf.querySelector('input[name="aircraft_type"]')
        }
    };

    function updatePdfValues() {
        if (inputs.aos.period && inputs.pdf.period) inputs.pdf.period.value = inputs.aos.period.value;
        if (inputs.aos.operator && inputs.pdf.operator) inputs.pdf.operator.value = inputs.aos.operator.value;
        if (inputs.aos.aircraft && inputs.pdf.aircraft) inputs.pdf.aircraft.value = inputs.aos.aircraft.value;
        console.log('PDF Inputs synced');
    }

    // Bind event ke AOS select
    Object.values(inputs.aos).forEach(el => {
        if (el) el.addEventListener('change', updatePdfValues);
    });

    // Validasi saat PDF disubmit
    if (formPdf.dataset.bound !== 'true') {
        formPdf.dataset.bound = 'true';
        formPdf.addEventListener('submit', function (e) {
            updatePdfValues(); // Sync terakhir sebelum jalan
            if (!inputs.pdf.period.value || !inputs.pdf.operator.value) {
                e.preventDefault();
                alert('Pilih Periode dan Operator terlebih dahulu!');
            }
        });
    }
}


// ── syncExcelForm ────────────────────────────────────────────────────
function syncExcelForm() {
    const formAos   = document.getElementById('form-aos');
    const formExcel = document.getElementById('form-excel');

    if (!formAos || !formExcel) return;

    const inputs = {
        aos: {
            period:   formAos.querySelector('select[name="period"]'),
            operator: formAos.querySelector('select[name="operator"]'),
            aircraft: formAos.querySelector('select[name="aircraft_type"]')
        },
        excel: {
            period:   formExcel.querySelector('input[name="period"]'),
            operator: formExcel.querySelector('input[name="operator"]'),
            aircraft: formExcel.querySelector('input[name="aircraft_type"]')
        }
    };

    function updateExcelValues() {
        if (inputs.aos.period   && inputs.excel.period)   inputs.excel.period.value   = inputs.aos.period.value;
        if (inputs.aos.operator && inputs.excel.operator) inputs.excel.operator.value = inputs.aos.operator.value;
        if (inputs.aos.aircraft && inputs.excel.aircraft) inputs.excel.aircraft.value = inputs.aos.aircraft.value;
        console.log('Excel inputs synced');
    }

    // Sync setiap kali AOS select berubah
    Object.values(inputs.aos).forEach(el => {
        if (el) el.addEventListener('change', updateExcelValues);
    });

    // Validasi + sync terakhir saat Excel disubmit
    if (formExcel.dataset.bound !== 'true') {
        formExcel.dataset.bound = 'true';
        formExcel.addEventListener('submit', function (e) {
            updateExcelValues();
            if (!inputs.excel.period.value || !inputs.excel.operator.value || !inputs.excel.aircraft.value) {
                e.preventDefault();
                alert('Pilih Periode, Operator, dan Aircraft Type terlebih dahulu!');
            }
        });
    }
}