<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pertemuan 11 - CRUD RESTful API AJAX</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Manajemen Data Produk</h5>
            <span id="statusApiBadge" class="badge bg-warning text-dark">Mengecek API...</span>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-primary" id="btnTambah" disabled>
                    + Tambah Produk
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCekApi">
                    Cek Ulang Koneksi API
                </button>
            </div>

            <!-- Tabel Bootstrap -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-center">Stok</th>
                            <th class="text-end">Harga</th>
                            <th width="16%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProduk">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Memeriksa koneksi RESTful API...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Create & Update -->
<div class="modal fade" id="modalProduk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formProduk" novalidate>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Tambah Data Produk</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="produk_id">

                    <div class="mb-3">
                        <label for="kode_produk" class="form-label">Kode Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode_produk" placeholder="Contoh: PRD-001" required>
                        <div class="invalid-feedback" id="err_kode_produk">Kode produk wajib diisi.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_produk" placeholder="Contoh: Keyboard Mechanical" required>
                        <div class="invalid-feedback" id="err_nama_produk">Nama produk wajib diisi.</div>
                    </div>

                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Alat Tulis">Alat Tulis</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <div class="invalid-feedback" id="err_kategori">Silakan pilih kategori produk.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stok" min="0" placeholder="0" required>
                            <div class="invalid-feedback" id="err_stok">Stok minimal 0.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="harga" min="100" placeholder="10000" required>
                            <div class="invalid-feedback" id="err_harga">Harga minimal Rp 100.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery, Bootstrap Bundle JS, & SweetAlert2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    const API_URL = '/api';
    const API_TOKEN = "{{ env('API_TOKEN') }}";
    const modalProduk = new bootstrap.Modal(document.getElementById('modalProduk'));

    // Pasang Bearer Token pada setiap request AJAX
    $.ajaxSetup({
        headers: {
            'Authorization': 'Bearer ' + API_TOKEN,
            'Accept': 'application/json'
        }
    });

    // Reset tampilan validasi form
    function resetValidation() {
        $('#formProduk .form-control, #formProduk .form-select').removeClass('is-invalid');
    }

    // 1. CEK KESIAPAN RESTFUL API TERLEBIH DAHULU
    function cekKoneksiApi() {
        $('#statusApiBadge').removeClass('bg-success bg-danger').addClass('bg-warning text-dark').text('Mengecek API...');
        $('#btnTambah').prop('disabled', true);

        $.ajax({
            url: `${API_URL}/cek-api`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    $('#statusApiBadge').removeClass('bg-warning text-dark bg-danger').addClass('bg-success').text('API Online');
                    $('#btnTambah').prop('disabled', false);
                    loadData(); // Muat data setelah API terverifikasi aktif
                }
            },
            error: function () {
                $('#statusApiBadge').removeClass('bg-warning text-dark bg-success').addClass('bg-danger').text('API Offline');
                $('#tbodyProduk').html('<tr><td colspan="7" class="text-center text-danger py-4">Gagal terhubung ke server RESTful API.</td></tr>');
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi API Gagal',
                    text: 'Pastikan server Laravel (php artisan serve) sedang berjalan!'
                });
            }
        });
    }

    // 2. READ (GET): Menampilkan data ke tabel tanpa reload
    function loadData() {
        $.ajax({
            url: `${API_URL}/produks`,
            method: 'GET',
            success: function (response) {
                let html = '';
                if (response.data.length === 0) {
                    html = '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data produk. Silakan klik "+ Tambah Produk".</td></tr>';
                } else {
                    response.data.forEach((item, index) => {
                        html += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td><span class="badge bg-dark">${item.kode_produk}</span></td>
                                <td>${item.nama_produk}</td>
                                <td>${item.kategori}</td>
                                <td class="text-center">${item.stok}</td>
                                <td class="text-end">Rp ${Number(item.harga).toLocaleString('id-ID')}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btnEdit" data-id="${item.id}">Edit</button>
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${item.id}">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tbodyProduk').html(html);
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengambil data dari API.'
                });
            }
        });
    }

    // Buka Modal Tambah
    $('#btnTambah').click(function () {
        $('#formProduk')[0].reset();
        resetValidation();
        $('#produk_id').val('');
        $('#modalTitle').text('Tambah Data Produk');
        modalProduk.show();
    });

    // Validasi sisi klien (Frontend Validation) sebelum kirim AJAX
    function validasiFormClient() {
        resetValidation();
        let valid = true;

        if (!$('#kode_produk').val().trim()) {
            $('#kode_produk').addClass('is-invalid');
            valid = false;
        }
        if (!$('#nama_produk').val().trim()) {
            $('#nama_produk').addClass('is-invalid');
            valid = false;
        }
        if (!$('#kategori').val()) {
            $('#kategori').addClass('is-invalid');
            valid = false;
        }
        if ($('#stok').val() === '' || Number($('#stok').val()) < 0) {
            $('#stok').addClass('is-invalid');
            valid = false;
        }
        if ($('#harga').val() === '' || Number($('#harga').val()) < 100) {
            $('#harga').addClass('is-invalid');
            valid = false;
        }
        return valid;
    }

    // 3. CREATE (POST) & UPDATE (PUT): Submit Form
    $('#formProduk').submit(function (e) {
        e.preventDefault();

        if (!validasiFormClient()) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: 'Mohon lengkapi semua kolom formulir dengan benar!'
            });
            return;
        }

        const id = $('#produk_id').val();
        const isUpdate = id !== '';

        $.ajax({
            url: isUpdate ? `${API_URL}/produks/${id}` : `${API_URL}/produks`,
            method: isUpdate ? 'PUT' : 'POST',
            data: {
                kode_produk: $('#kode_produk').val(),
                nama_produk: $('#nama_produk').val(),
                kategori: $('#kategori').val(),
                stok: $('#stok').val(),
                harga: $('#harga').val()
            },
            success: function (response) {
                modalProduk.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                loadData();
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    // Tampilkan error validasi dari backend Laravel (misal kode_produk duplikat)
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function (field) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#err_${field}`).text(errors[field][0]);
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Server Gagal',
                        text: 'Periksa kembali input yang ditandai merah.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.'
                    });
                }
            }
        });
    });

    // Ambil Detail Data ke Form Edit
    $(document).on('click', '.btnEdit', function () {
        const id = $(this).data('id');
        resetValidation();

        $.ajax({
            url: `${API_URL}/produks/${id}`,
            method: 'GET',
            success: function (response) {
                $('#produk_id').val(response.data.id);
                $('#kode_produk').val(response.data.kode_produk);
                $('#nama_produk').val(response.data.nama_produk);
                $('#kategori').val(response.data.kategori);
                $('#stok').val(response.data.stok);
                $('#harga').val(response.data.harga);
                $('#modalTitle').text('Edit Data Produk');
                modalProduk.show();
            },
            error: function () {
                Swal.fire('Error', 'Gagal mengambil detail data produk.', 'error');
            }
        });
    });

    // 4. DELETE (DELETE): Hapus Data dengan Konfirmasi SweetAlert2
    $(document).on('click', '.btnHapus', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data Produk?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${API_URL}/produks/${id}`,
                    method: 'DELETE',
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadData();
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error');
                    }
                });
            }
        });
    });

    $('#btnCekApi').click(cekKoneksiApi);
    cekKoneksiApi();
});
</script>

</body>
</html>
