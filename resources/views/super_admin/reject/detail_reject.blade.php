@extends('layouts.super_admin.template_superadmin')
@section('title', 'Detail Reject')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <div class="page">
        <!-- Tombol Back + Kerjakan -->
        <div class="action-bar">
            <!-- Tombol Back -->
            <a href="{{ route('superadmin.reject') }}" class="btn-back">
                <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
            </a>

            <!-- Tombol Kerjakan -->
            <div class="action-buttons">
                <button type="button" class="btn-add-revisi">
                    Upload File Revisi
                </button>
            </div>
        </div>

        <!-- Nama Project + Table wrapper -->
        <div class="table-wrapper">
            <!-- Header Nama Project -->
            <div class="project-header">
                <span class="project-title">{{ $reject['nama_project'] ?? 'Nama project belum ada' }}</span>
                <a href="{{ route('superadmin.reject_edit', $reject['id']) }}" class="edit-project disabled">Edit
                    Project</a>
            </div>

            <!-- Tabel Detail -->
            <div class="table-responsive">
                <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                    style="min-width: 100%">
                    <thead style="text-align: center;">
                        <tr>
                            <th style="min-width: 50px;">NO</th>
                            <th style="width: 150px;">DESIGNATOR</th>
                            <th style="width: 300px;"> URAIAN</th>
                            <th style="width: 100px;">SATUAN</th>
                            <th>HARGA MATERIAL</th>
                            <th>HARGA JASA</th>
                            <th style="width: 100px;">VOLUME</th>
                            <th>TOTAL MATERIAL</th>
                            <th>TOTAL JASA</th>
                            <th style="width: 50px;">DELETE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reject['detail'] ?? [] as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->designator }}</td>
                                <td>{{ $item->uraian }}</td>
                                <td>{{ $item->satuan }}</td>
                                <td>{{ number_format($item->harga_material, 0, ',', '.') }}</td>
                                <td>{{ number_format($item->harga_jasa, 0, ',', '.') }}</td>
                                <td>{{ $item->volume }}</td>
                                <td>{{ number_format($item->total_material, 0, ',', '.') }}</td>
                                <td>{{ number_format($item->total_jasa, 0, ',', '.') }}</td>
                                <td>
                                    <form
                                        action="{{ route('superadmin.reject_destroy', ['id' => $reject['id'], 'detailId' => $item->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus material ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            style="border:none;background:none;padding:0;cursor:pointer;">
                                            <img src="{{ asset('assets/delete.png') }}" alt="Delete"
                                                style="width:20px;height:20px;">
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">MATERIAL</th>
                            <th colspan="3">{{ number_format($totals['material'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">JASA</th>
                            <th colspan="3">{{ number_format($totals['jasa'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">TOTAL</th>
                            <th colspan="3">{{ number_format($totals['total'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">PPN</th>
                            <th colspan="3">{{ number_format($totals['ppn'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">TOTAL SETELAH PPN</th>
                            <th colspan="3">{{ number_format($totals['grand'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Pop-Up Upload Revisi -->
        <div id="addRevisiModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3 class="title">Upload File Revisi</h3>

                <form class="addRevisiForm" id="addRevisiForm"
                    action="{{ route('superadmin.reject_upload_revisi', $reject['id']) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Drop Zone Upload File Excel -->
                    <div id="dropZoneExcel" class="form-upload"
                        style="border: 2px dashed #ccc; border-radius: 10px; padding: 20px;
                        text-align: center; margin-bottom: 20px; width: 90%;">

                        <input type="file" name="file" id="excelFile" accept=".xls,.xlsx,.csv" hidden required>

                        <label for="excelFile" style="cursor:pointer; display:block; color:#595961;">
                            <i class="fa fa-cloud-upload-alt" style="font-size:24px; margin-bottom:8px;"></i><br>
                            <span>Upload File Excel Project<br>(.xls / .xlsx / .csv up to 10MB)</span>
                        </label>
                        <br>
                        <button type="button" id="browseExcelBtn" class="btn-browse">Browse</button>

                        {{-- <div id="filePreview" class="file-preview"
                            style="margin-top: 10px; color: #133995; font-size: 13px; font-weight: 500;"></div>

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
                        </div> --}}
                    </div>

                    <button type="submit" class="btn-save">Save</button>
                </form>
            </div>
        </div>

        <!-- Tombol Delete Data Project -->
        <div id="deleteProject" style="margin-top: 20px; text-align: left;">
            <form action="{{ route('superadmin.reject_destroy_project', $reject['id']) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh data project ini beserta detail materialnya?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    style="background-color:#C8170D; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer; font-family: 'Poppins', sans-serif;
                font-weight: 500;">
                    <i class="fa fa-trash" style="margin-right:8px;"></i> Hapus Data Project
                </button>
            </form>
        </div>
    </div>

    <style>
        :root {
            --blue: #133995;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        .page {
            padding: 20px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-back {
            background: var(--blue);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .btn-add-revisi {
            background: #fff;
            color: #133995;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            cursor: pointer;
            border: 2px solid #133995;
            font-family: 'Poppins', sans-serif;
        }

        .btn-add-revisi:hover {
            background: #133995;
            color: #fff;
            border: 2px solid #F5F5F6;
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
            margin: 9% auto;
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

        .addRevisiForm {
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

        .table-wrapper {
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 16px;
        }

        .project-header {
            background: #EBEBEB;
            padding: 12px 16px;
            font-size: 18px;
            font-weight: 500;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .project-title {
            color: #595961;
        }

        .edit-project {
            font-size: 14px;
            font-weight: 500;
            color: #133995;
            text-decoration: none;
            cursor: pointer;
        }

        .edit-project:hover {
            text-decoration: underline;
            color: #133995;
        }

        .table-responsive {
            overflow-x: auto;
        }

        #data-table {
            border-collapse: collapse;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            border-radius: 0;
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
            background-color: var(--blue);
            color: white;
            height: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600 !important;
        }

        #data-table tfoot th {
            background-color: #EDF7FF;
            color: #000;
            font-weight: 700 !important;
            text-align: center;
            border: none !important;
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
    </style>

    <!-- Script Chart.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
        // Ambil elemen modal Add Revisi
        const addRevisiModal = document.getElementById("addRevisiModal");

        // Tombol Add Revisi
        const addRevisiBtn = document.querySelector(".btn-add-revisi");

        // Tampilkan modal saat tombol Add Project diklik
        addRevisiBtn.addEventListener("click", function() {
            addRevisiModal.style.display = "block";
        });

        // Tutup modal jika klik di luar area modal
        window.addEventListener("click", function(event) {
            if (event.target === addRevisiModal) {
                addRevisiModal.style.display = "none";
            }
        });

        document.getElementById("browseExcelBtn").addEventListener("click", function() {
            document.getElementById("excelFile").click();
        });
    </script>

@endsection
