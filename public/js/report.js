// 
// GLOBAL REPORT SYSTEM
// 
// Fitur:
// 1. Sidebar AJAX Navigation
// 2. AJAX Form Submit
// 3. Operator -> Aircraft Dropdown
// 4. PDF & Excel Form Sync
// 5. Tanpa re-init manual
// 6. Tanpa dataset.bound
// 7. Support dynamic AJAX content
// 



// 
// DOM READY
// 

document.addEventListener('DOMContentLoaded', () => {

    console.log('Report JS Loaded');

    // Inisialisasi sidebar AJAX
    initSidebarNavigation();

});



// 
// SIDEBAR AJAX NAVIGATION
// 
// Menangani klik sidebar secara global
// Menggunakan Event Delegation
// 

function initSidebarNavigation() {

    document.addEventListener('click', async function (e) {

        // Cari element terdekat dengan class .sidebar-item
        const sidebarItem = e.target.closest('.sidebar-item');

        // Kalau bukan sidebar item -> hentikan
        if (!sidebarItem) return;

        // Stop reload halaman
        e.preventDefault();

        // Ambil URL dari data-url
        const url = sidebarItem.dataset.url;

        // Validasi URL
        if (!url || url === '#') return;

        console.log('Load URL:', url);

        // Load halaman via AJAX
        await loadPage(url);

    });

}



// 
// LOAD PAGE VIA AJAX
// 
// Memuat halaman report ke #main-content
// 

async function loadPage(url) {

    // Ambil container utama
    const mainContent = document.getElementById('main-content');

    // Loading state
    mainContent.innerHTML = `
        <div class="p-6 text-center">
            Loading...
        </div>
    `;

    try {

        // Fetch halaman
        const response = await fetch(url, {

            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }

        });

        // Kalau response gagal
        if (!response.ok) {
            throw new Error('Gagal memuat halaman');
        }

        // Ambil HTML
        const html = await response.text();

        // Inject HTML ke halaman
        mainContent.innerHTML = html;

        console.log('Halaman berhasil dimuat');

    } catch (error) {

        console.error('Load Page Error:', error);

        mainContent.innerHTML = `
            <div class="p-6 text-red-500">
                Error loading content
            </div>
        `;

    }

}



// 
// GLOBAL AJAX FORM SUBMIT
// 
// Semua form yang punya:
// data-ajax-form
// akan otomatis submit via AJAX
// 

document.addEventListener('submit', async function (e) {

    // Ambil form yang submit
    const form = e.target;

    // Kalau bukan AJAX form -> hentikan
    if (!form.matches('[data-ajax-form]')) return;

    // Stop reload halaman
    e.preventDefault();

    console.log('AJAX Submit:', form.action);

    // Submit AJAX
    await submitAjaxForm(form);

});



// 
// SUBMIT AJAX FORM
// 
// Generic reusable form submit
// Bisa dipakai semua report
// 

async function submitAjaxForm(form) {

    // Ambil main content
    const mainContent = document.getElementById('main-content');

    // Ambil tombol submit
    const submitBtn = form.querySelector('[type="submit"]');

    // Ambil semua form data
    const formData = new FormData(form);

    // Disable tombol submit
    if (submitBtn) {
        submitBtn.disabled = true;
    }

    try {

        // Fetch submit form
        const response = await fetch(form.action, {

            method: form.method || 'POST',

            headers: {

                'X-Requested-With': 'XMLHttpRequest',

                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .content

            },

            body: formData

        });

        // Kalau gagal
        if (!response.ok) {
            throw new Error('Submit gagal');
        }

        // Ambil HTML hasil response
        const html = await response.text();

        // Render ke halaman
        mainContent.innerHTML = html;

        console.log('Form berhasil disubmit');

    } catch (error) {

        console.error('Submit Error:', error);

        alert('Gagal submit form');

    } finally {

        // Aktifkan lagi tombol submit
        if (submitBtn) {
            submitBtn.disabled = false;
        }

    }

}



// 
// OPERATOR -> AIRCRAFT DROPDOWN
// 
// Saat operator berubah:
// otomatis load aircraft type
// 

document.addEventListener('change', async function (e) {

    // Ambil dropdown operator
    const operatorDropdown = e.target;

    // Validasi dropdown
    if (!operatorDropdown.matches('[data-aircraft-dropdown]')) {
        return;
    }

    // Ambil value operator
    const operator = operatorDropdown.value;

    // Ambil dropdown aircraft
    const aircraftDropdown =
        document.getElementById('aircraft-type-dropdown');

    // Validasi dropdown aircraft
    if (!aircraftDropdown) return;

    // Reset option
    aircraftDropdown.innerHTML = `
        <option value="">
            Select Aircraft Type
        </option>
    `;

    // Kalau operator kosong
    if (!operator) return;

    console.log('Load aircraft:', operator);

    try {

        // Fetch aircraft type
        const response = await fetch(
            `/get-aircraft-types?operator=${operator}`
        );

        // Ambil JSON
        const data = await response.json();

        // Loop data aircraft
        data.forEach(type => {

            // Buat option
            const option = document.createElement('option');

            option.value = type.ACType;
            option.textContent = type.ACType;

            // Masukkan option
            aircraftDropdown.appendChild(option);

        });

        console.log('Aircraft loaded');

    } catch (error) {

        console.error('Aircraft Error:', error);

    }

});



// 
// GLOBAL EXPORT FORM SYNC
// 
// Sync:
// - PDF
// - Excel
//
// Saat form AOS berubah
// 

document.addEventListener('change', function () {

    syncExportForms();

});



// 
// SYNC EXPORT FORMS
// 
// Mengambil value dari form utama
// lalu sync ke:
// - form-pdf
// - form-excel
// 

function syncExportForms() {

    // Ambil form AOS
    const formAos = document.getElementById('form-aos');

    // Kalau tidak ada form AOS
    if (!formAos) return;

    // Ambil semua value
    const period =
        formAos.querySelector('[name="period"]')?.value;

    const operator =
        formAos.querySelector('[name="operator"]')?.value;

    const aircraft =
        formAos.querySelector('[name="aircraft_type"]')?.value;

    // Sync PDF
    updateExportForm(
        'form-pdf',
        period,
        operator,
        aircraft
    );

    // Sync Excel
    updateExportForm(
        'form-excel',
        period,
        operator,
        aircraft
    );

}



// 
// UPDATE EXPORT FORM
// Helper function untuk update hidden input

function updateExportForm(
    formId,
    period,
    operator,
    aircraft
) {

    // Ambil form
    const form = document.getElementById(formId);

    // Kalau form tidak ada
    if (!form) return;

    // Update period
    form.querySelector('[name="period"]').value =
        period || '';

    // Update operator
    form.querySelector('[name="operator"]').value =
        operator || '';

    // Update aircraft
    form.querySelector('[name="aircraft_type"]').value =
        aircraft || '';

}