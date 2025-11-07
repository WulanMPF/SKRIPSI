@extends('layouts.super_admin.template_superadmin')
@section('title', 'All Project')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="page">
        <!-- Add project -->
        <div class="top-controls">
            <button type="button" class="btn-add-project">+ Add Project</button>

            <div class="controls-right">
                <!-- Input Tanggal -->
                <div class="date-group">
                    <label for="date_range">Tanggal Upload:</label>
                    <input type="text" id="date_range" class="form-control" placeholder="Pilih rentang tanggal">
                </div>
                <!-- Download -->
                {{-- <a href="{{ route('superadmin.allproject_download') }}" class="btn-primary-custom" target="_blank">
                    <i class="fa-solid fa-download"></i> Download All
                </a> --}}
                <a href="{{ route('superadmin.allproject_download', ['start' => request('start'), 'end' => request('end')]) }}"
                    class="btn-primary-custom" target="_blank">
                    <i class="fa-solid fa-download"></i> Download All
                </a>
            </div>
        </div>

        <!-- Menampilkan Chart -->
        <div class="charts-row">
            <div class="card left">
                <div class="card-title">Distribusi Total Project {{ date('Y') }}</div>
                <div class="chart-wrap">
                    <canvas id="chartTotalProject"></canvas>
                </div>
            </div>

            <div class="right-column">
                <div class="card">
                    <div class="card-title">Distribusi Quality Enhancement {{ date('Y') }}</div>
                    <div class="chart-wrap">
                        <canvas id="chartQE"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Distribusi Status All Project {{ date('Y') }}</div>
                    <div class="chart-wrap">
                        <canvas id="chartPie"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel untuk Menampilkan Data -->
        <div class="table-responsive">
            <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                style="min-width: 100%">
                <thead style="text-align: center;">
                    <tr>
                        <th style="min-width: 50px;">NO</th>
                        <th style="width: 300px;">NAMA PROJECT</th>
                        <th style="width: 400px;">DESKRIPSI PROJECT</th>
                        <th style="width: 200px;">QE</th>
                        <th style="width: 200px;">TANGGAL UPLOAD</th>
                        <th style="width: 200px;">TANGGAL PENGERJAAN</th>
                        <th style="width: 200px;">TANGGAL SELESAI</th>
                        <th style="width: 100px;">STATUS</th>
                        <th style="width: 150px;">TOTAL</th>
                        <th style="width: 50px;">DETAIL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project_doc as $index => $project)
                        <tr>
                            <td style="width: 50px;">{{ $index + 1 }}</td>
                            <td style="max-width: 300px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['nama_project'] }}</td>
                            <td style="max-width: 400px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['deskripsi_project'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['qe'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['tgl_upload'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['tgl_pengerjaan'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['tgl_selesai'] }}</td>
                            <td style="max-width: 10px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                @if ($project['status'] === 'ACC')
                                    <span style="color: #28a745; font-weight: 600;">{{ $project['status'] }}</span>
                                @elseif ($project['status'] === 'REJECT')
                                    <span style="color: #dc3545; font-weight: 600;">{{ $project['status'] }}</span>
                                @else
                                    <span>{{ $project['status'] }}</span>
                                @endif
                            </td>
                            <td style="max-width: 150px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['total_formatted'] }}</td>
                            <td>
                                <a href="{{ route('superadmin.allproject_detail', $project['id']) }}" title="Lihat Detail">
                                    <img src="{{ asset('assets/detail.png') }}" alt="Detail"
                                        style="width:20px;height:20px;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-end">TOTAL PROJECT</th>
                        <th colspan="2">{{ $grandTotal }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Pop-Up Add Project -->
    <div id="addProjectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3 class="title">Add Project</h3>

            <form class="addProjectForm" id="addProjectForm" action="{{ route('superadmin.allproject_create') }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Drop Zone Upload File Excel -->
                <div id="dropZoneExcel" class="form-upload"
                    style="border: 2px dashed #ccc; border-radius: 10px; padding: 20px; 
                        text-align: center; margin-bottom: 20px; width: 100%;">

                    <input type="file" name="file" id="excelFile" accept=".xls,.xlsx,.csv" hidden required>

                    <label for="excelFile" style="cursor:pointer; display:block; color:#595961;">
                        <i class="fa fa-cloud-upload-alt" style="font-size:24px; margin-bottom:8px;"></i><br>
                        <span>Upload File Excel Project<br>(.xls / .xlsx / .csv up to 10MB)</span>
                    </label>
                    <br>
                    <button type="button" id="browseExcelBtn" class="btn-browse">Browse</button>

                    <div id="filePreview" class="file-preview"
                        style="margin-top: 10px; color: #133995; font-size: 13px; font-weight: 500;"></div>
                </div>

                <div class="row mb-3">
                    <div class="form-status">
                        <label class="label-status">Status:</label>
                        <select name="status" class="select-field" required>
                            <option value="" disabled selected hidden>Pilih Status Project</option>
                            <option value="PROCESS" style="color:#696A71;">PROCESS</option>
                            <option value="ACC" style="color:#34C759;">ACC</option>
                            <option value="REJECT" style="color:#C8170D;">REJECT</option>
                        </select>
                    </div>

                    <div class="form-qe">
                        <label class="label-qe">QE:</label>
                        <select name="qe" class="select-field" required>
                            <option value="" disabled selected hidden>Pilih Jenis QE</option>
                            @foreach ($qe_doc as $qe)
                                <option value="{{ $qe['qe'] }}">{{ $qe['qe'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-deskripsi">
                        <label class="label-deskripsi">Deskripsi:</label>
                        <input type="text" name="deskripsi" class="input-field"
                            placeholder="Masukkan deskripsi project" required>
                    </div>
                </div>

                <button type="submit" class="btn-save">Save</button>
            </form>
        </div>
    </div>

    <!-- Script Chart.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // === SEMUA EVENT ADA DI SINI ===

            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "id",
                onClose: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        const pad = n => String(n).padStart(2, '0');
                        const start =
                            `${selectedDates[0].getFullYear()}-${pad(selectedDates[0].getMonth() + 1)}-${pad(selectedDates[0].getDate())}`;
                        const end =
                            `${selectedDates[1].getFullYear()}-${pad(selectedDates[1].getMonth() + 1)}-${pad(selectedDates[1].getDate())}`;
                        const params = new URLSearchParams(window.location.search);
                        params.set('start', start);
                        params.set('end', end);
                        window.location.search = params.toString();
                    }
                }
            });

            // 🔗 Update link download PDF sesuai filter
            const downloadBtn = document.querySelector('.btn-primary-custom');
            const params = new URLSearchParams(window.location.search);
            const start = params.get('start');
            const end = params.get('end');
            if (start && end && downloadBtn) {
                downloadBtn.href = "{{ route('superadmin.allproject_download') }}" + `?start=${start}&end=${end}`;
            }

            // === BAGIAN MODAL ADD PROJECT ===
            const addProjectModal = document.getElementById("addProjectModal");
            const addProjectBtn = document.querySelector(".btn-add-project");
            const addProjectForm = document.getElementById("addProjectForm");
            const saveBtn = document.querySelector(".btn-save");

            addProjectBtn.addEventListener("click", function() {
                addProjectModal.style.display = "block";
            });

            window.addEventListener("click", function(event) {
                if (event.target === addProjectModal) {
                    addProjectModal.style.display = "none";
                }
            });

            // === BROWSE FILE HANDLER ===
            document.getElementById("browseExcelBtn").addEventListener("click", function() {
                document.getElementById("excelFile").click();
            });

            document.getElementById("excelFile").addEventListener("change", function(event) {
                const fileInput = event.target;
                const preview = document.getElementById("filePreview");
                if (fileInput.files && fileInput.files.length > 0) {
                    const fileName = fileInput.files[0].name;
                    preview.textContent = `📁 ${fileName}`;
                } else {
                    preview.textContent = "";
                }
            });

            // === SWEET ALERT KONFIRMASI SAAT KLIK SAVE ===
            saveBtn.addEventListener("click", async function(e) {
                e.preventDefault();
                const formData = new FormData(addProjectForm);
                const actionUrl = addProjectForm.getAttribute("action");

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
                        // 1️⃣ Tutup modal add project
                        addProjectModal.style.display = "none";

                        // 2️⃣ Tampilkan loading SweetAlert
                        Swal.fire({
                            title: 'Sedang memproses...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        saveBtn.disabled = true;
                        try {
                            const res = await fetch(actionUrl, {
                                method: "POST",
                                body: formData
                            });
                            const data = await res.json();

                            if (!res.ok || !data.success)
                                throw new Error(data.message ||
                                    "Terjadi kesalahan saat menyimpan data.");

                            // 3️⃣ Tutup loading dan tampilkan alert berhasil
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil!",
                                text: data.message,
                                confirmButtonColor: "#133995"
                            }).then(() => {
                                // 4️⃣ Redirect sesuai status yang dipilih
                                const status = formData.get("status");
                                if (status === "PROCESS") {
                                    window.location.href =
                                        "{{ route('superadmin.process') }}"; // misal halaman default PROCESS
                                } else if (status === "ACC") {
                                    window.location.href =
                                        "{{ route('superadmin.acc') }}";
                                } else if (status === "REJECT") {
                                    window.location.href =
                                        "{{ route('superadmin.reject') }}";
                                } else {
                                    window.location.reload();
                                }
                            });
                        } catch (err) {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: err.message
                            });
                        } finally {
                            saveBtn.disabled = false;
                        }
                    }
                });
            });
        });

        const blue = '#133995';
        const lightBlue = '#4A6AC0';
        const red = '#ff4d4d';

        // Chart Total Project per Bulan
        new Chart(document.getElementById('chartTotalProject'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    data: @json($chartTotalProjectData),
                    backgroundColor: blue
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });

        // Chart QE (Distribusi QE Tahun Ini)
        new Chart(document.getElementById('chartQE'), {
            type: 'bar',
            data: {
                labels: Object.keys(@json($chartQEData)),
                datasets: [{
                    data: Object.values(@json($chartQEData)),
                    backgroundColor: blue
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });


        // Chart Pie (Distribusi All Project)
        new Chart(document.getElementById('chartPie'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(@json($chartPieData)),
                datasets: [{
                    data: Object.values(@json($chartPieData)),
                    backgroundColor: [blue, lightBlue, red]
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <style>
        :root {
            --blue: #133995;
            --bg: white;
            --card-border: #dcdcdc;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .page {
            padding: 10px 20px;
        }

        /* Bagian Top Control */
        .top-controls {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 18px;
        }

        .controls-right {
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        .date-group {
            display: flex;
            flex-direction: column;
        }

        .date-group label {
            margin-bottom: 4px;
            font-weight: 500;
            color: #133995;
        }

        .date-group input {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid #133995;
            color: #ADAEB3;
        }

        .date-group input:hover {
            border-color: #ADAEB3;
        }

        .btn-primary-custom {
            display: inline-block;
            padding: 8px 16px;
            background-color: #133995;
            color: #fff !important;
            border: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: #fff;
            color: #133995 !important;
            border: 1px solid #CFD0D2;
            text-decoration: none;
        }

        .btn-primary-custom i {
            margin-right: 6px;
        }

        .btn-add-project {
            display: inline-block;
            padding: 8px 16px;
            background-color: #133995;
            color: #fff !important;
            border: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-add-project:hover {
            background-color: #fff;
            color: #133995 !important;
            border: 1px solid #CFD0D2;
            text-decoration: none;
        }

        .btn-add-project i {
            margin-right: 6px;
        }

        /* Bagian Chart */
        /* .charts-row {
                                        margin-top: 20px;
                                        display: grid;
                                        grid-template-columns: 2fr 1fr;
                                        gap: 18px;
                                        margin-bottom: 22px;
                                    } */

        .charts-row {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 18px;
            align-items: stretch;
            margin-bottom: 22px;
            min-height: 460px;
            /* Bikin tinggi kedua kolom sejajar */
        }

        .card {
            border: 1px solid #133995;
            background: #F5F5F6;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* .card.left {
                                    height: 420px;
                                } */

        .card.left {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 460px;
        }

        /* .right-column {
                                display: flex;
                                flex-direction: column;
                                gap: 18px;
                                height: 420px;
                            } */

        .right-column {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            gap: 18px;
            min-height: 460px;
        }

        /* .right-column .card {
                            flex: 1;
                            padding: 12px;
                        } */

        .right-column .card {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 12px;
        }

        .card .card-title {
            text-align: center;
            color: var(--blue);
            font-weight: 500;
            margin-bottom: 10px;
        }

        /* .chart-wrap {
                        flex: 1;
                        min-height: 0;
                        display: flex;
                    } */

        .chart-wrap {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 0;
        }

        /* .chart-wrap canvas {
                    width: 100% !important;
                    height: 100% !important;
                    display: block;
                } */

        .chart-wrap canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        #data-table {
            border-collapse: collapse;
            width: 100%;
            overflow: hidden;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: normal !important;
            table-layout: fixed;
        }

        #data-table th,
        #data-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            overflow: hidden;
            white-space: nowrap;
        }

        #data-table th {
            background-color: #133995;
            color: #ffffff;
            height: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600 !important;
        }

        #data-table tfoot th {
            background-color: #EDF7FF;
            color: #133995;
            font-weight: 600;
            text-align: center;
        }

        #data-table td {
            overflow-x: auto;
            overflow-y: hidden;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #data-table td::-webkit-scrollbar {
            display: none;
        }

        #data-table td:first-child,
        #data-table th:first-child {
            width: 50px;
        }

        @media (max-width: 900px) {
            .charts-row {
                grid-template-columns: 1fr;
            }

            .card.left,
            .right-column {
                height: auto;
            }
        }

        /* Style untuk modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            border-radius: 10px;
            margin: 5% auto;
            padding: 20px;
            width: 25%;
        }

        .btn-browse {
            background: #fff;
            color: #133995;
            padding: 10px 24px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            min-width: 120px;
            text-align: center;
            border: 2px solid #CFD0D2;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-browse:hover {
            opacity: 0.9;
        }

        .addProjectForm {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .title {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 45px;
            color: #133995;
        }

        .select-field {
            width: 245px;
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

        .input-field {
            width: 228px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            border-color: #133995;
        }

        /* Custom margins for labels */
        .label-status {
            margin-right: 50px;
            color: #133995;
        }

        .label-qe {
            margin-right: 80px;
            color: #133995;
        }

        .label-deskripsi {
            margin-right: 30px;
            color: #133995;
        }

        .form-status,
        .form-qe,
        .form-deskripsi {
            margin-bottom: 20px;
        }

        .btn-save {
            background-color: #133995;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 7px 20px;
            cursor: pointer;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border: 1.5px solid transparent;
            transition: background-color 0.3s;
        }

        .btn-save:hover {
            background-color: white;
            color: #133995;
            border-color: #CFD0D2;
        }

        .form-upload {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;

            /* Ubah dari max-width ke lebar penuh parent form */
            width: 100%;
            box-sizing: border-box;
        }
    </style>

@endsection
