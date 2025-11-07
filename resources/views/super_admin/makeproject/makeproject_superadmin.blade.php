@extends('layouts.super_admin.template_superadmin')
@section('title', 'Make Project')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="page">

        <form action="{{ route('superadmin.makeproject_store') }}" method="POST" class="form-project">
            @csrf

            <div class="form-group">
                <label for="qe" class="label-qe">QE:</label>
                <select id="qe" name="qe" required class="select-field" onchange="changeFontColor(this)">
                    <option value="" disabled selected hidden>Pilih Quality Enhancement (QE)</option>
                    @foreach ($qeOptions as $qe)
                        <option value="{{ $qe['id'] }}" {{ old('qe') == $qe['id'] ? 'selected' : '' }}>
                            {{ $qe['label'] }}
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn-make-project" id="btnSubmit">Make Project</button>
            </div>

            <div class="form-group">
                <label for="pekerjaan" class="label-pekerjaan">Pekerjaan:</label>
                <input type="text" id="pekerjaan" name="pekerjaan" required class="input-field"
                    placeholder="Masukkan nama pekerjaan">
            </div>

            <div class="form-group">
                <label for="deskripsi" class="label-deskripsi">Deskripsi:</label>
                <input type="text" id="deskripsi" name="deskripsi" required class="input-field"
                    placeholder="Masukkan deskripsi pekerjaan">
            </div>

            <div class="form-group">
                <label for="khs" class="label-khs">Nomor KHS:</label>
                <input type="text" id="khs" name="khs" required class="input-field"
                    placeholder="Masukkan nomor KHS">
            </div>

            <div class="form-group">
                <label for="pelaksana" class="label-pelaksana">Pelaksana:</label>
                <input type="text" id="pelaksana" name="pelaksana" required class="input-field"
                    placeholder="Masukkan pelaksana pekerjaan">
            </div>

            <div class="form-group">
                <label for="witel" class="label-witel">Witel:</label>
                <input type="text" id="witel" name="witel" required class="input-field"
                    placeholder="Masukkan witel">
            </div>

            <!-- Tabel untuk Menampilkan Data -->
            <div class="table-responsive">
                <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                    style="min-width: 100%">
                    <thead style="text-align: center;">
                        <tr>
                            <th style="width: 30px; border-top-left-radius: 10px;">NO</th>
                            <th style="width: 200px;">DESIGNATOR</th>
                            <th style="width: 300px;">URAIAN</th>
                            <th>SATUAN</th>
                            <th style="width: 200px;">HARGA MATERIAL</th>
                            <th style="width: 200px;">HARGA JASA</th>
                            <th style="width: 60px;">VOLUME</th>
                            <th style="width: 200px;">TOTAL MATERIAL</th>
                            <th style="width: 200px; border-top-right-radius: 10px;">TOTAL JASA</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center;">
                        <tr>
                            <td style="width: 30px;">1</td>
                            <td style="width: 250px;">
                                <div style="position: relative;">
                                    <input type="text" name="designator[]" required class="input-dsg"
                                        placeholder="Masukkan Designator" oninput="filterDesignators(this)">
                                    <div class="suggestions" style="display: none;"></div>
                                </div>
                            </td>
                            <td class="uraian" style="width:300px;">
                                <div class="uraian-overflow" title=""></div>
                            </td>
                            <td class="satuan"></td>
                            <td style="width: 200px;" class="harga_material"></td>
                            <td style="width: 200px;" class="harga_jasa"></td>
                            <td style="width: 60px;"><input type="number" name="volume[]" required class="vol-field"
                                    value="0">
                            </td>
                            <td style="width: 200px;" class="total_material"></td>
                            <td style="width: 200px;" class="total_jasa"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="button-group">
                    <button id="addRow" style="background-color: #133995; color:#ffffff">+</button>
                    <button id="removeRow" style="background-color: #881A14; color:#ffffff"">-</button>
                </div>
                <!-- Tabel Ringkasan -->
                <div class="summary-section" style="margin-top:20px;">
                    <table class="summary-table table-bordered table-striped table-hover table-sm" id="summary-table"
                        style="min-width: 100%; margin-top:20px;">
                        <tr>
                            <td style="width: 1150px;"><strong>Material</strong></td>
                            <td class="summary-material">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>Jasa</strong></td>
                            <td class="summary-jasa">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>Total</strong></td>
                            <td class="summary-total">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>PPN (11%)</strong></td>
                            <td class="summary-ppn">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>Total Setelah PPN</strong></td>
                            <td class="summary-after-ppn">0</td>
                        </tr>
                    </table>
                </div>
            </div>
        </form>


    </div>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .page {
            padding: 10px 20px;
        }

        .flex-container {
            display: flex;
            /* Use flexbox for layout */
            align-items: center;
            /* Center items vertically */
            justify-content: space-between;
            /* Space between items */
            width: 100%;
            /* Full width */
        }

        .btn-make-project {
            background-color: #133995;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 7px 15px;
            cursor: pointer;
            margin-left: auto;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border: 1.5px solid transparent;
            transition: background-color 0.3s;
            margin-left: auto;
        }

        .btn-make-project:hover {
            background-color: white;
            color: #133995;
            border-color: #CFD0D2;
        }

        .input-field {
            width: 300px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            border-color: #133995;
        }

        .select-field {
            width: 320px;
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            appearance: none;
            background: url('/assets/arrow.png') no-repeat right 10px center;
            background-size: 10px;
            border-color: #133995;
        }

        .form-project {
            margin-top: 20px;
        }

        .form-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .form-qe {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .label-qe {
            width: 120px;
            color: #133995;
            font-family: 'Poppins', sans-serif;
            margin-left: 3px;
        }

        .label-pekerjaan {
            margin-right: 42px;
            /* Specific margin for Pekerjaan label */
        }

        .label-deskripsi {
            margin-right: 50px;
            /* Specific margin for Deskripsi label */
        }

        .label-khs {
            margin-right: 32.5px;
            /* Specific margin for KHS label */
        }

        .label-pelaksana {
            margin-right: 38.5px;
            /* Specific margin for Pelaksana label */
        }

        .label-witel {
            margin-right: 81.5px;
            /* Specific margin for Witel label */
        }

        label {
            color: #133995;
            /* Label color */
            font-family: 'Poppins', sans-serif;
            /* Ensure label uses Poppins */
        }

        #data-table {
            border-collapse: collapse;
            width: 100%;
            overflow: visible;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: normal !important;
            /* table-layout: fixed; */
        }

        #data-table th,
        #data-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        #data-table th {
            background-color: #133995;
            color: #ffffff;
            height: 20px;
            /* Tinggi baris header lebih besar */
            font-family: 'Poppins', sans-serif;
            font-weight: 600 !important;
        }

        .input-dsg {
            width: 200px;
            /* Match the width you want for the input */
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            border-color: #133995;
            position: relative;
            /* Position relative for absolute children */
        }

        .vol-field {
            width: 60px;
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #000000;
            background-size: 10px;
            border-color: #133995;
        }

        .uraian-overflow {
            max-width: 300px;
            width: 100%;
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .uraian-overflow::-webkit-scrollbar {
            display: none;
        }

        #summary-table {
            border-collapse: collapse;
            width: 100%;
            overflow: hidden;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: normal !important;
            background-color: #EDF7FF;
        }

        #summary-table td {
            /* border: 1px solid #ccc; */
            padding: 10px;
            text-align: center;
            font-weight: 600 !important;
        }

        .button-group {
            margin-top: 10px;
            display: flex;
            gap: 5px;
        }

        .button-group button {
            width: 30px;
            height: 30px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 100px;
            box-shadow: none;
            outline: none;
            background-color: #f5f5f5;
            cursor: pointer;
        }

        .button-group button:active {
            background-color: #ddd;
        }

        .button-group button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .suggestions {
            border: 1px solid #ccc;
            top: 100%;
            left: 0;
            width: 100%;
            border-radius: 6px;
            background: white;
            position: absolute;
            z-index: 1000;
            max-height: 150px;
            overflow-y: auto;
            display: none;
            box-sizing: border-box;
        }

        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #f0f0f0;
        }

        .select-dsg {
            width: 200px !important;
            /* sesuai dengan lebar cell */
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const dsgData = @json($project_ta_doc);
        const uraianOptions = @json($uraianOptions);

        function toggleDesignatorSelects() {
            const qe = document.getElementById('qe').value;
            const pekerjaan = document.getElementById('pekerjaan').value.trim();
            const deskripsi = document.getElementById('deskripsi').value.trim();
            const khs = document.getElementById('khs').value.trim();
            const pelaksana = document.getElementById('pelaksana').value.trim();
            const witel = document.getElementById('witel').value.trim();

            const allFilled = qe && pekerjaan && deskripsi && khs && pelaksana && witel;

            document.querySelectorAll('.select-dsg').forEach(select => {
                select.disabled = !allFilled;
            });
        }

        // Jalankan saat halaman load
        document.addEventListener('DOMContentLoaded', toggleDesignatorSelects);

        // Jalankan ulang setiap ada perubahan input
        ['qe', 'pekerjaan', 'deskripsi', 'khs', 'pelaksana', 'witel'].forEach(id => {
            document.getElementById(id).addEventListener('input', toggleDesignatorSelects);
            document.getElementById(id).addEventListener('change', toggleDesignatorSelects);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const qeSelect = document.getElementById('qe');

            qeSelect.addEventListener('change', function() {
                if (this.value) {
                    // Tetap gunakan warna abu-abu
                    this.style.color = '#84858C';
                } else {
                    // Warna placeholder
                    this.style.color = '#84858C';
                }
            });

            // Set warna awal
            qeSelect.style.color = '#84858C';
        });

        // Fungsi untuk menghitung total material dan jasa dalam satu baris
        function calculateRow(row) {
            const volume = parseFloat(row.querySelector('.vol-field').value) || 0;
            const hargaMaterial = parseFloat(row.querySelector('.harga_material').textContent.replace(/\./g, '').replace(
                /,/g, '')) || 0;
            const hargaJasa = parseFloat(row.querySelector('.harga_jasa').textContent.replace(/\./g, '').replace(/,/g,
                '')) || 0;

            const totalMaterial = volume * hargaMaterial;
            const totalJasa = volume * hargaJasa;

            row.querySelector('.total_material').textContent = totalMaterial.toLocaleString('id-ID');
            row.querySelector('.total_jasa').textContent = totalJasa.toLocaleString('id-ID');

            // Panggil updateSummary() di luar fungsi ini
        }

        // Fungsi untuk mengaitkan event listener ke field volume
        function attachVolumeListener(row) {
            const volumeInput = row.querySelector('.vol-field');
            volumeInput.addEventListener('input', () => {
                calculateRow(row);
                updateSummary(); // Panggil updateSummary() setelah perhitungan
            });
        }

        // Mengaitkan listener ke setiap baris yang sudah ada di tabel
        document.querySelectorAll('#data-table tbody tr').forEach(row => attachVolumeListener(row));

        // Event listener untuk menambahkan baris baru
        document.getElementById('addRow').addEventListener('click', function() {
            const tableBody = document.querySelector('#data-table tbody');
            const rowCount = tableBody.rows.length + 1;

            const optionsHtml = dsgData
                .map(d => `<option value="${d.id}">${d.designator}</option>`)
                .join('');

            const newRow = `
                <tr>
                    <td>${rowCount}</td>
                    <td style="width: 250px;">
                        <div style="position: relative;">
                            <input type="text" name="designator[]" required class="input-dsg"
                                placeholder="Masukkan Designator" oninput="filterDesignators(this)">
                            <div class="suggestions" style="display: none;"></div>
                        </div>
                    </td>
                    <td class="uraian" style="width:300px;">
                        <div class="uraian-overflow" title=""></div>
                    </td>
                    <td class="satuan"></td>
                    <td class="harga_material"></td>
                    <td class="harga_jasa"></td>
                    <td style="width:60px;"><input type="number" name="volume[]" class="vol-field" value="0"></td>
                    <td class="total_material"></td>
                    <td class="total_jasa"></td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRow);

            // Attach listener ke row baru
            attachVolumeListener(tableBody.lastElementChild);

            // 🔑 Inisialisasi Select2 untuk select di row baru
            $(tableBody.lastElementChild).find('.select-dsg').select2({
                placeholder: "Cari Designator...",
                allowClear: true,
                width: '100%'
            });

            document.getElementById('removeRow').disabled = false;
        });


        // Event listener untuk menghapus baris terakhir
        document.getElementById('removeRow').addEventListener('click', function() {
            const tableBody = document.querySelector('#data-table tbody');
            if (tableBody.rows.length > 1) {
                tableBody.deleteRow(tableBody.rows.length - 1);
            }
            // Jika tersisa 1 row, disable tombol removeRow
            if (tableBody.rows.length === 1) {
                document.getElementById('removeRow').disabled = true;
            }
        });

        // Fungsi untuk mengubah warna font saat memilih designator
        function changeFontColor(selectElement) {
            if (selectElement.value) {
                selectElement.style.color = 'black';
                const selectedId = selectElement.value;
                const row = selectElement.closest('tr');
                const selectedDsg = dsgData.find(dsg => dsg.id == selectedId);

                if (selectedDsg) {
                    // isi uraian
                    const uraianBox = row.querySelector('.uraian-overflow');
                    uraianBox.textContent = selectedDsg.uraian;
                    uraianBox.setAttribute('title', selectedDsg.uraian);

                    // isi satuan
                    row.querySelector('.satuan').textContent = selectedDsg.satuan;

                    // isi harga material & jasa (format angka)
                    row.querySelector('.harga_material').textContent =
                        Number(selectedDsg.harga_material).toLocaleString('id-ID');
                    row.querySelector('.harga_jasa').textContent =
                        Number(selectedDsg.harga_jasa).toLocaleString('id-ID');

                    // hitung ulang total baris & ringkasan
                    calculateRow(row);
                    updateSummary();
                }
            } else {
                selectElement.style.color = '';
            }
        }

        // Fungsi untuk memeriksa apakah semua form terisi
        function checkFormCompletion() {
            let allFilled = true;

            // Cek semua select yang wajib diisi
            document.querySelectorAll('select[required]').forEach(select => {
                if (!select.value) {
                    allFilled = false;
                }
            });

            // Cek semua input yang wajib diisi
            document.querySelectorAll('input[required]').forEach(input => {
                if (!input.value.trim()) {
                    allFilled = false;
                }
            });

            // Enable tombol + dan - jika semua terisi
            document.getElementById('addRow').disabled = !allFilled;
            document.getElementById('removeRow').disabled = !allFilled;

            // Cek apakah field harga bisa diubah
            const hargaMaterials = document.querySelectorAll('.harga_material');
            const hargaJasas = document.querySelectorAll('.harga_jasa');

            hargaMaterials.forEach(field => {
                field.contentEditable = allFilled;
                if (!allFilled) {
                    field.textContent = ''; // Kosongkan jika tidak diizinkan
                }
            });

            hargaJasas.forEach(field => {
                field.contentEditable = allFilled;
                if (!allFilled) {
                    field.textContent = ''; // Kosongkan jika tidak diizinkan
                }
            });
        }

        // Fungsi untuk memperbarui ringkasan total
        function updateSummary() {
            let totalMaterial = 0;
            let totalJasa = 0;

            document.querySelectorAll('#data-table tbody tr').forEach(row => {
                const material = parseFloat(row.querySelector('.total_material').textContent.replace(/\./g, '')
                    .replace(/,/g, '')) || 0;
                const jasa = parseFloat(row.querySelector('.total_jasa').textContent.replace(/\./g, '').replace(
                    /,/g, '')) || 0;
                totalMaterial += material;
                totalJasa += jasa;
            });

            const total = totalMaterial + totalJasa;
            const ppn = total * 0.11;
            const totalAfterPpn = total - ppn;

            document.querySelector('.summary-material').textContent = totalMaterial.toLocaleString('id-ID');
            document.querySelector('.summary-jasa').textContent = totalJasa.toLocaleString('id-ID');
            document.querySelector('.summary-total').textContent = total.toLocaleString('id-ID');
            document.querySelector('.summary-ppn').textContent = ppn.toLocaleString('id-ID');
            document.querySelector('.summary-after-ppn').textContent = totalAfterPpn.toLocaleString('id-ID');
        }

        // Aktifkan Select2 untuk semua dropdown designator
        document.addEventListener('DOMContentLoaded', function() {
            $('.select-dsg').select2({
                placeholder: "Cari Designator...",
                allowClear: true,
                width: '100%'
            });
        });

        function filterDesignators(input) {
            const value = input.value.toLowerCase();
            const suggestionsContainer = input.nextElementSibling;
            suggestionsContainer.innerHTML = '';

            const rect = input.getBoundingClientRect();
            // suggestionsContainer.style.top = rect.bottom + 'px';
            // suggestionsContainer.style.left = rect.left + 'px';
            suggestionsContainer.style.width = rect.width + 'px';

            if (value) {
                const filteredDesignators = dsgData.filter(dsg => dsg.designator.toLowerCase().includes(value));
                if (filteredDesignators.length > 0) {
                    suggestionsContainer.style.display = 'block';
                    filteredDesignators.forEach(dsg => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.textContent = dsg.designator;
                        suggestionItem.classList.add('suggestion-item');
                        suggestionItem.onclick = () => selectDesignator(dsg, input, suggestionsContainer);
                        suggestionsContainer.appendChild(suggestionItem);
                    });
                } else {
                    suggestionsContainer.style.display = 'block';
                    suggestionsContainer.innerHTML = '<div>Designator tidak ditemukan</div>';
                }
            } else {
                suggestionsContainer.style.display = 'none';
            }
        }

        function selectDesignator(dsg, input, suggestionsContainer) {
            input.value = dsg.designator; // Set input value to selected designator
            suggestionsContainer.style.display = 'none'; // Hide suggestions
            const row = input.closest('tr');
            const uraianBox = row.querySelector('.uraian-overflow');
            uraianBox.textContent = dsg.uraian;
            uraianBox.setAttribute('title', dsg.uraian);
            row.querySelector('.satuan').textContent = dsg.satuan;
            row.querySelector('.harga_material').textContent = Number(dsg.harga_material).toLocaleString('id-ID');
            row.querySelector('.harga_jasa').textContent = Number(dsg.harga_jasa).toLocaleString('id-ID');

            // Call calculateRow to update totals
            calculateRow(row);
            updateSummary();
        }

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('input-dsg')) {
                const suggestions = document.querySelectorAll('.suggestions');
                suggestions.forEach(s => s.style.display = 'none');
            }
        });

        // Jalankan pertama kali saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const tableBody = document.querySelector('#data-table tbody');
            if (tableBody.rows.length === 1) {
                document.getElementById('removeRow').disabled = true;
            }
            checkFormCompletion();
        });

        // Jalankan setiap ada perubahan input/select
        document.querySelectorAll('select[required], input[required]').forEach(el => {
            el.addEventListener('input', checkFormCompletion);
            el.addEventListener('change', checkFormCompletion);
        });

        // Sweetalert
        document.addEventListener('DOMContentLoaded', function() {
            const makeBtn = document.getElementById('btnSubmit');
            const form = document.querySelector('.form-project');

            if (makeBtn && form) {
                makeBtn.addEventListener('click', async function(e) {
                    e.preventDefault();

                    const actionUrl = form.action;
                    const formData = new FormData(form);

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Pastikan semua data project sudah benar sebelum dikirim.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Ya, buat project!',
                        reverseButtons: true
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            makeBtn.disabled = true; // cegah klik dobel

                            try {
                                const res = await fetch(actionUrl, {
                                    method: 'POST',
                                    body: formData
                                });

                                const data = await res.json();

                                if (!res.ok || !data.success) {
                                    throw new Error(data.message ||
                                        'Terjadi kesalahan saat menyimpan data.');
                                }

                                // ✅ Jika sukses
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message,
                                    confirmButtonColor: '#133995'
                                }).then(() => {
                                    // Redirect ke halaman Process
                                    window.location.href =
                                        "{{ route('superadmin.process') }}";
                                });

                            } catch (err) {
                                console.error(err);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: err.message ||
                                        'Terjadi kesalahan saat membuat project.'
                                });
                            } finally {
                                makeBtn.disabled = false;
                            }
                        }
                    });
                });
            }
        });
    </script>


@endsection
