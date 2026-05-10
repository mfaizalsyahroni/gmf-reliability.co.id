
// report.js — Script untuk Sidebar & Form AJAX

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Sidebar Navigation ────────────────────────────────
    // Ambil semua elemen dengan class .sidebar-item
    const sidebarItems = document.querySelectorAll('.sidebar-item');

    sidebarItems.forEach(item => {
        item.addEventListener('click', function (event) {
            // Cegah link berpindah halaman secara normal
            event.preventDefault();

            const url = this.getAttribute('data-url');

            // Kalau tidak ada URL atau URL adalah '#', abaikan
            if (!url || url === '#') return;

            const mainContent = document.getElementById('main-content');

            // Tampilkan loading sementara konten di-fetch
            mainContent.innerHTML = '<p class="p-6">Loading...</p>';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(data => {
                    // Masukkan konten hasil fetch ke #main-content
                    mainContent.innerHTML = data;

                    console.log('Konten berhasil dimuat dari:', url);
                    // Add alert for debugging
                    console.log('Ada form?', document.querySelector('#main-content form'));
                    console.log('innerHTML length:', mainContent.innerHTML.length);

                    // Add this — see the first 500 characters of the received HTML
                    console.log('HTML preview:', data.substring(0, 500))

                    setTimeout(() => {
                        // Re-init semua listener setelah konten baru masuk ke DOM
                        initOperatorDropdown();
                        initAosForm();
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error fetching content:', error);
                    mainContent.innerHTML = '<p class="p-6 text-red-500">Error loading content. Please try again.</p>';
                });
        });
    });

    // ── 2. Init pertama kali saat halaman pertama kali dibuka ─
    initOperatorDropdown();
    initAosForm();
});


// initAosForm
// Fungsi ini dipanggil setiap kali konten baru di-load ke
// #main-content, agar form submit di-intercept oleh AJAX
// dan tidak menyebabkan full page redirect.
function initAosForm() {
    const form = document.querySelector('#main-content form');

    // Kalau tidak ada form di #main-content, keluar
    if (!form) {
        console.warn('initAosForm: tidak ada form di #main-content');
        return;
    }

    console.log('initAosForm: form ditemukan, action =', form.action);

    // Cek apakah form sudah di-bind sebelumnya
    // Ini mencegah event listener menumpuk (double-bind)
    if (form.dataset.bound === 'true') {
        console.log('initAosForm: form sudah di-bind, skip');
        return;
    }

    // Tandai form sudah di-bind
    form.dataset.bound = 'true';

    // Pasang listener submit
    form.addEventListener('submit', handleAosSubmit);

    console.log('initAosForm: listener submit berhasil dipasang');
}


// handleAosSubmit
// Handler yang menangani submit form secara AJAX.
// e.preventDefault() mencegah halaman berpindah/reload.
function handleAosSubmit(e) {
    // Cegah form submit secara normal (yang menyebabkan pindah halaman)
    e.preventDefault();
    e.stopPropagation();

    console.log('handleAosSubmit: form submit di-intercept');

    const mainContent = document.getElementById('main-content');
    const formData = new FormData(this);

    // Tampilkan loading
    const submitBtn = this.querySelector('[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        headers: {
            // Memberitahu Laravel bahwa ini adalah request AJAX
            'X-Requested-With': 'XMLHttpRequest',
            // CSRF Token wajib untuk POST di Laravel
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
        .then(response => {
            if (!response.ok) throw new Error('Response tidak ok: ' + response.status);
            return response.text();
        })
        .then(data => {
            console.log('handleAosSubmit: response diterima, update konten');

            // Ganti isi #main-content dengan response dari server
            mainContent.innerHTML = data;

            // Re-init listener karena DOM sudah diganti
            initOperatorDropdown();
            initAosForm();
        })
        .catch(error => {
            console.error('handleAosSubmit: error =', error);
            mainContent.innerHTML = '<p class="p-6 text-red-500">Gagal memuat data. Silakan coba lagi.</p>';
        })
        .finally(() => {
            // Re-enable tombol submit jika masih ada
            if (submitBtn) submitBtn.disabled = false;
        });
}


// initOperatorDropdown
// Fungsi ini di-init ulang setiap kali konten baru masuk,
// agar dropdown ACType otomatis ter-filter berdasarkan Operator.
function initOperatorDropdown() {
    const operatorDropdown = document.getElementById('operator-dropdown');

    // Kalau dropdown tidak ada di halaman ini, keluar
    if (!operatorDropdown) return;

    // Cegah double-bind pada dropdown
    if (operatorDropdown.dataset.bound === 'true') return;
    operatorDropdown.dataset.bound = 'true';

    operatorDropdown.addEventListener('change', function () {
        const operator = this.value;
        const aircraftTypeDropdown = document.getElementById('aircraft-type-dropdown');

        // Reset dropdown ACType
        aircraftTypeDropdown.innerHTML = '<option value="">Select Aircraft Type</option>';

        if (!operator) return;

        console.log('Operator dipilih:', operator);

        fetch(`/get-aircraft-types?operator=${operator}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                console.log('Aircraft types diterima:', data);
                data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.ACType;
                    option.textContent = type.ACType;
                    aircraftTypeDropdown.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching aircraft types:', error));
    });
}