@extends('layouts.mitra.template_mitra')
@section('title', 'Detail All Project')

@section('header')
    @include('layouts.mitra.header_mitra')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <div class="page">
        <!-- Tombol Back + Kerjakan -->
        <div class="action-bar">
            <!-- Tombol Back -->
            <a href="{{ route('mitra.allproject') }}" class="btn-back">
                <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
            </a>

            <!-- Tombol Kerjakan -->
            <div class="action-buttons">
                @if ($acc['tgl_pengerjaan'] == '-' || empty($acc['tgl_pengerjaan']))
                    <form id="formKerjakan" action="{{ route('mitra.allproject_acc.kerjakan', $acc['id']) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <button type="button" id="btnKerjakan" class="btn-action btn-kerjakan">
                            Kerjakan
                        </button>
                    </form>
                @elseif($acc['tgl_pengerjaan'] != '-' && ($acc['tgl_selesai'] == '-' || empty($acc['tgl_selesai'])))
                    <button type="button" class="btn-action btn-pending" id="btnPending">Pending</button>
                    <button type="button" class="btn-action btn-done" id="btnDone">Done</button>
                @endif
            </div>
        </div>

        <!-- Nama Project + Table wrapper -->
        <div class="table-wrapper">
            <!-- Header Nama Project -->
            <div class="project-header">
                <span class="project-title">{{ $acc['nama_project'] ?? 'Nama project belum ada' }}</span>
                <a href="{{ route('mitra.allproject_acc_edit', $acc['id']) }}" class="edit-project">Edit Project</a>
            </div>

            {{-- <div style="margin: 10px 0; padding: 0 16px;">
                    <p><strong>Tanggal Pengerjaan:</strong> {{ !empty($acc['tgl_pengerjaan']) ? $acc['tgl_pengerjaan'] : '-' }}</p>
                    <p><strong>Tanggal Selesai:</strong> {{ !empty($acc['tgl_selesai']) ? $acc['tgl_selesai'] : '-' }}</p>
                </div> --}}

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
                            <th>FOTO SEBELUM</th>
                            <th>FOTO SESUDAH</th>
                            <th style="width: 50px;">DELETE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($acc['detail'] ?? [] as $index => $item)
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
                                    @if (isset($acc['foto']['sebelum'][$item->designator]))
                                        @foreach ($acc['foto']['sebelum'][$item->designator] as $foto)
                                            <img src="{{ $foto }}" class="foto-item zoomable">
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if (isset($acc['foto']['sesudah'][$item->designator]))
                                        @foreach ($acc['foto']['sesudah'][$item->designator] as $foto)
                                            <img src="{{ $foto }}" class="foto-item zoomable">
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <form
                                        action="{{ route('mitra.allproject_acc_destroy', ['id' => $acc['id'], 'detailId' => $item->id]) }}"
                                        method="POST" class="form-delete-material">
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
                            <th colspan="8" class="text-end">MATERIAL</th>
                            <th colspan="4">{{ number_format($totals['material'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="8" class="text-end">JASA</th>
                            <th colspan="4">{{ number_format($totals['jasa'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="8" class="text-end">TOTAL</th>
                            <th colspan="4">{{ number_format($totals['total'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="8" class="text-end">PPN</th>
                            <th colspan="4">{{ number_format($totals['ppn'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="8" class="text-end">TOTAL SETELAH PPN</th>
                            <th colspan="4">{{ number_format($totals['grand'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tombol Delete Data Project -->
        <div id="deleteProject" style="margin-top: 20px; text-align: left;">
            <form action="{{ route('mitra.allproject_acc_destroy_project', $acc['id']) }}" method="POST"
                class="form-delete-project">
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

    <!-- Pop Up Done Upload Foto -->
    <div id="doneModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:1500px;">
            <h3 style="text-align:center;color:#133995;">
                Upload Foto Evident
            </h3>

            <form id="formUploadFoto" action="{{ route('mitra.acc.storeFoto', $acc['id']) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div style="max-height:400px; overflow:auto; margin-top:50px;">
                    {{-- <table class="data-table table-bordered upload-table" style="width:100%;"> --}}
                    <table class="data-table table-bordered" style="width:100%;">
                        <thead style="text-align: center; vertical-align: middle;">
                            <tr>
                                <th>No</th>
                                <th>Designator</th>
                                <th>Uraian</th>
                                <th>Foto Sebelum</th>
                                <th>Foto Sesudah</th>
                            </tr>
                        </thead>
                        <tbody style="vertical-align: middle;">
                            @foreach ($acc['detail'] as $i => $item)
                                <tr>
                                    <td style="text-align: center;">{{ $i + 1 }}</td>
                                    <td>{{ $item->designator }}</td>
                                    <td>{{ $item->uraian }}</td>
                                    <td style="text-align: center;">
                                        <div class="file-center">
                                            <input type="file" name="foto_sebelum[{{ $item->designator }}][]"
                                                accept="image/*" onchange="previewImage(this)" multiple>
                                            <img class="img-preview" style="display:none;">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="file-center">
                                            <input type="file" name="foto_sesudah[{{ $item->designator }}][]"
                                                accept="image/*" onchange="previewImage(this)">
                                            <img class="img-preview" style="display:none;" multiple>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex;justify-content:space-between;margin-top:20px;">
                    <button type="button" class="modal-btn cancel" id="cancelBtn">Cancel</button>

                    <button type="submit" class="modal-btn upload">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pop Up Pending -->
    <div id="pendingModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3 style="text-align:center; color:#133995; font-weight:600;">Input Worklog</h3>
            <p style="text-align:center; font-size:14px; color:#595961; margin-bottom:20px;">
                Silahkan tulis keterangan mengapa project pending
            </p>

            <form action="{{ route('mitra.allproject_acc.pending', $acc['id']) }}" method="POST">
                @csrf

                <!-- Container untuk input group -->
                <div id="keteranganContainer">
                    <div class="input-group horizontal">
                        <div class="tgl-wrapper">
                            <input type="text" name="tgl_pending[]" class="form-control tgl-pending"
                                placeholder="Tanggal" readonly>
                            <i class="fa fa-calendar calendar-icon"></i>
                        </div>
                        <input type="text" name="keterangan[]" class="form-control" placeholder="Tulis keterangan">
                    </div>
                </div>

                <!-- Tombol Add / Remove -->
                {{-- <div class="action-btns">
                        <button type="button" class="btn-add">+</button>
                        <button type="button" class="btn-remove">-</button>
                    </div> --}}

                <!-- Footer Buttons -->
                <div style="display:flex; justify-content:space-between; margin-top:20px;">
                    <!-- Tombol kiri -->
                    <div>
                        <button type="button" id="cancelPendingBtn" class="modal-btn cancel">Cancel</button>
                    </div>

                    <!-- Tombol kanan -->
                    <div>
                        <button type="submit" class="modal-btn next">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Foto Evident -->
    @if (!empty($acc['foto_project']) && is_array($acc['foto_project']))
        <div class="rekap-section mt-6">
            <h3 class="section-title">Foto Evident:</h3>
            <div class="rekap-box">
                <div class="foto-list">
                    @foreach ($acc['foto_project'] as $foto)
                        <img src="{{ $foto }}" class="foto-item" alt="Foto Eviden">
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Keterangan Pending -->
    @if (!empty($acc['tgl_pengerjaan']) && $acc['tgl_pengerjaan'] != '-')
        <div class="rekap-section mt-6">
            <h3 class="section-title">Keterangan Pending:</h3>
            <div class="rekap-box">
                <table class="pending-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th style="width: 200px;">Waktu Pending</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($acc['pending'] ?? [] as $index => $pending)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $pending['tgl_pending'] ?? '-' }}</td>
                                <td>{{ $pending['keterangan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                        @if (empty($acc['pending']))
                            <tr>
                                <td colspan="3" class="text-center">Belum ada data pending</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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

        .btn-back:hover {
            background-color: #fff;
            color: #133995 !important;
            border: 1px solid #CFD0D2;
            text-decoration: none;
        }

        .btn-kerjakan {
            background: #E5E5E8;
            color: #133995;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            cursor: pointer;
            border: none;
            font-family: 'Poppins', sans-serif;
        }

        .btn-kerjakan:hover {
            background: #133995;
            color: #fff;
        }

        .btn-pending {
            background: var(--blue);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn-pending:hover {
            background-color: #fff;
            color: #133995 !important;
            border: 1px solid #CFD0D2;
            text-decoration: none;
        }

        .btn-done {
            background: #E5E5E8;
            color: #133995;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn-done:hover {
            background: #133995;
            color: #fff;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background: #fff;
            border-radius: 10px;
            margin: 5% auto;
            padding: 20px;
            width: 50%;
            max-width: 600px;
        }

        .previewImages img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .modal-btn {
            padding: 10px 24px;
            font-size: 14px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            min-width: 120px;
            text-align: center;
            border: none;
            transition: 0.2s;
        }

        .modal-btn.cancel {
            background: #E5E5E8;
            color: #133995;
        }

        .modal-btn.prev {
            background: #E5E5E8;
            color: #133995;
        }

        .modal-btn.next {
            background: #133995;
            color: #fff;
        }

        .modal-btn.upload {
            background: #133995;
            color: #fff;
        }

        .modal-btn:hover {
            opacity: 0.9;
            cursor: pointer;
        }

        #dropZone label .btn-browse {
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

        #dropZone label .btn-browse:hover {
            opacity: 0.9;
        }

        .preview-item {
            position: relative;
            display: inline-block;
        }

        .preview-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .preview-item .remove-btn {
            position: absolute;
            top: -6px;
            right: -6px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Pending */
        .input-group.horizontal {
            display: flex;
            border: 1px solid #133995;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .input-group.horizontal input {
            flex: 1;
            border: none;
            padding: 10px 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }

        .tgl-wrapper {
            position: relative;
            flex: 0 0 140px;
            border-right: 1px solid #133995;
            display: flex;
            align-items: center;
        }

        .tgl-wrapper input {
            width: 100%;
            padding-right: 30px;
            font-size: 13px;
        }

        .tgl-wrapper .calendar-icon {
            position: absolute;
            right: 10px;
            color: #133995;
            cursor: pointer;
        }

        .action-btns {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        /* .btn-add,
                                                                                                    .btn-remove {
                                                                                                        display: flex;
                                                                                                        justify-content: center;
                                                                                                        align-items: center;
                                                                                                        font-weight: bold;
                                                                                                        font-size: 16px;
                                                                                                        border-radius: 50%;
                                                                                                        width: 36px;
                                                                                                        height: 36px;
                                                                                                        cursor: pointer;
                                                                                                        border: none;
                                                                                                        transition: 0.2s;
                                                                                                    } */

        .btn-add {
            background: #133995;
            color: #fff;
        }

        .btn-add:hover {
            background: #0f2f78;
        }

        .btn-remove {
            background: #A30000;
            color: #fff;
        }

        .btn-remove:hover {
            background: #7a0000;
        }

        /* Rekap */
        .section-title {
            color: #133995;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .rekap-section {
            margin-left: 1.5rem;
            margin-right: 1.5rem;
        }

        .rekap-box {
            background: #F9F9F9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
        }

        /* Foto Evident */
        .foto-group {
            margin-bottom: 20px;
        }

        .foto-title {
            font-weight: 600;
            color: #133995;
            margin-bottom: 10px;
        }

        .foto-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .foto-container {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            background: #F9F9F9;
        }

        .foto-item {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        /* Pending Table */
        .pending-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .pending-table thead {
            background: #133995;
            color: #fff;
            text-align: center;
        }

        .pending-table th,
        .pending-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        .pending-table tbody td {
            text-align: center;
        }

        .pending-table tbody td:last-child {
            text-align: left !important;
        }

        #data-table tfoot tr+tr th,
        #data-table tfoot tr+tr td {
            border-top: none !important;
        }

        /* Tambahan fix border footer hilang */
        #data-table.table-bordered>tfoot>tr>th,
        #data-table.table-bordered>tfoot>tr>td {
            border: none !important;
            border-top: none !important;
        }

        /* PUSATKAN KONTEN DI DALAM MODAL DONE */
        #doneModal .modal-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* FORM DI TENGAH */
        #doneModal form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* WRAPPER TABEL DI TENGAH */
        #doneModal form>div {
            width: 95%;
            margin: 0 auto;
        }

        /* TABEL DI TENGAH */
        #doneModal table {
            margin: 0 auto;
        }

        .file-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .zoom-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .zoom-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            /* box-shadow: 0 0 25px rgba(255, 255, 255, 0.3); */
        }

        .zoom-close {
            position: absolute;
            top: 25px;
            right: 35px;
            font-size: 40px;
            color: #fff;
            cursor: pointer;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doneModal = document.getElementById("doneModal");
            const btnDone = document.getElementById("btnDone");

            const cancelBtn = document.getElementById("cancelBtn");

            const btnKerjakan = document.getElementById('btnKerjakan');
            const formKerjakan = document.getElementById('formKerjakan');

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // SWEETALERT UNTUK DELETE MATERIAL
            document.querySelectorAll('.form-delete-material').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Material ini akan dihapus dari project dan tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Ya, hapus material!',
                        reverseButtons: true
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang menghapus material...',
                                text: 'Mohon tunggu sebentar.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: new FormData(form)
                                });

                                const data = await res.json().catch(() => ({}));
                                if (!res.ok || data.success === false)
                                    throw new Error(data.message ||
                                        'Terjadi kesalahan saat menghapus material.'
                                    );

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message ||
                                        'Material berhasil dihapus.',
                                    confirmButtonColor: '#133995'
                                }).then(() => window.location.reload());

                            } catch (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: err.message ||
                                        'Terjadi kesalahan saat menghapus material.',
                                    confirmButtonColor: '#C8170D'
                                });
                            }
                        }
                    });
                });
            });

            // SWEETALERT UNTUK DELETE PROJECT
            const deleteProjectForm = document.querySelector('.form-delete-project');
            if (deleteProjectForm) {
                deleteProjectForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Seluruh data project beserta material akan dihapus secara permanen.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Ya, hapus project!',
                        reverseButtons: true
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang menghapus project...',
                                text: 'Mohon tunggu sebentar.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(deleteProjectForm.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: new FormData(deleteProjectForm)
                                });

                                const data = await res.json().catch(() => ({}));
                                if (!res.ok || data.success === false)
                                    throw new Error(data.message ||
                                        'Terjadi kesalahan saat menghapus project.');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message ||
                                        'Seluruh data project berhasil dihapus.',
                                    confirmButtonColor: '#133995'
                                }).then(() => {
                                    window.location.href =
                                        "{{ route('mitra.allproject') }}";
                                });

                            } catch (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: err.message ||
                                        'Terjadi kesalahan saat menghapus project.',
                                    confirmButtonColor: '#C8170D'
                                });
                            }
                        }
                    });
                });
            }

            // ALERT KERJAKAN
            if (btnKerjakan && formKerjakan) {
                btnKerjakan.addEventListener('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Anda akan mulai mengerjakan project ini.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, kerjakan!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang menyimpan perubahan...',
                                text: 'Mohon tunggu sebentar.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // 🔹 jalankan submit setelah sedikit jeda
                            setTimeout(() => {
                                formKerjakan.submit();
                            }, 700);
                        }
                    });
                });
            }

            // buka popup Done
            btnDone.addEventListener('click', function() {
                doneModal.style.display = "block";
            });

            // tutup popup Done
            cancelBtn.addEventListener('click', function() {
                doneModal.style.display = "none";
            });

            const formUpload = document.getElementById('formUploadFoto');

            if (formUpload) {
                formUpload.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Setelah upload, foto evident tidak dapat diubah.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        confirmButtonText: 'Ya, upload!'
                        // reverseButtons: true
                    }).then((result) => {

                        if (!result.isConfirmed) return;

                        Swal.fire({
                            title: 'Sedang mengunggah foto...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        fetch(formUpload.action, {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(res => {

                                if (res.status === 'success') {

                                    document.getElementById('btnDone')?.remove();
                                    document.getElementById('btnPending')?.remove();

                                    // disable semua input file
                                    document.querySelectorAll('input[type="file"]').forEach(
                                        el => {
                                            el.disabled = true;
                                        });

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: res.message,
                                        confirmButtonColor: '#133995'
                                    }).then(() => {
                                        location.reload();
                                    });

                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: res.message,
                                        confirmButtonColor: '#C8170D'
                                    });
                                }
                            })
                    });
                });
            }
        });

        function previewImage(input) {
            const file = input.files[0];
            if (!file) return;

            const img = input.parentElement.querySelector('.img-preview');
            const reader = new FileReader();

            reader.onload = function(e) {
                img.src = e.target.result;
                img.style.display = "block";
                img.style.width = "80px";
                img.style.height = "80px";
                img.style.objectFit = "cover";
                img.style.borderRadius = "6px";
                img.style.border = "1px solid #ccc";
                img.style.marginTop = "6px";
            };

            reader.readAsDataURL(file);
        }

        // Klik luar modal untuk tutup
        window.addEventListener("click", (e) => {
            if (e.target === doneModal) doneModal.style.display = "none";
        });

        // Pending
        const pendingModal = document.getElementById("pendingModal");
        const btnPending = document.getElementById("btnPending");
        const cancelPendingBtn = document.getElementById("cancelPendingBtn");

        // Buka modal Pending
        btnPending?.addEventListener("click", () => {
            pendingModal.style.display = "block";
        });

        // Tutup modal Pending
        cancelPendingBtn?.addEventListener("click", () => {
            pendingModal.style.display = "none";
        });

        // Klik luar modal Pending untuk close
        window.addEventListener("click", (e) => {
            if (e.target === pendingModal) {
                pendingModal.style.display = "none";
            }
        });

        const container = document.getElementById("keteranganContainer");
        const btnAdd = document.querySelector(".btn-add");
        const btnRemove = document.querySelector(".btn-remove");

        // Fungsi isi tanggal hari ini
        function setToday(input) {
            const today = new Date().toISOString().split("T")[0];
            input.value = today;
        }

        // Set default tanggal di field pertama
        const firstDateInput = container.querySelector(".tgl-pending");
        if (firstDateInput) setToday(firstDateInput);

        // Klik icon kalender -> set hari ini
        container.addEventListener("click", function(e) {
            if (e.target.classList.contains("calendar-icon")) {
                const input = e.target.closest(".tgl-wrapper").querySelector(".tgl-pending");
                setToday(input);
            }
        });

        // Tambah field baru
        btnAdd?.addEventListener("click", () => {
            const newGroup = document.createElement("div");
            newGroup.classList.add("input-group", "horizontal");
            newGroup.innerHTML = `
                        <div class="tgl-wrapper">
                            <input type="text" name="tgl_pending[]" class="form-control tgl-pending" readonly>
                            <i class="fa fa-calendar calendar-icon"></i>
                        </div>
                        <input type="text" name="keterangan[]" class="form-control" placeholder="Tulis keterangan">
                    `;
            container.appendChild(newGroup);

            // langsung isi tanggal hari ini
            setToday(newGroup.querySelector(".tgl-pending"));
        });

        // Hapus field terakhir (minimal 1 field tersisa)
        btnRemove?.addEventListener("click", () => {
            const groups = container.querySelectorAll(".input-group");
            if (groups.length > 1) {
                groups[groups.length - 1].remove();
            }
        });

        function previewImage(input) {
            const file = input.files[0];
            if (!file) return;

            const img = input.parentElement.querySelector('.img-preview');
            const reader = new FileReader();

            reader.onload = function(e) {
                img.src = e.target.result;
                img.style.display = "block";
                img.style.width = "80px";
                img.style.height = "80px";
                img.style.objectFit = "cover";
                img.style.borderRadius = "6px";
                img.style.border = "1px solid #ccc";
                img.style.marginTop = "6px";
                img.style.cursor = "zoom-in";

                // 👉 klik untuk zoom
                img.onclick = () => openZoom(e.target.result);
            };

            reader.readAsDataURL(file);
        }

        function openZoom(src) {
            const modal = document.getElementById("imageZoomModal");
            const zoomImg = document.getElementById("zoomedImage");

            zoomImg.src = src;
            modal.style.display = "flex";
        }

        // tutup modal
        document.querySelector(".zoom-close").onclick = () => {
            document.getElementById("imageZoomModal").style.display = "none";
        };

        document.getElementById("imageZoomModal").onclick = (e) => {
            if (e.target.id === "imageZoomModal") {
                e.target.style.display = "none";
            }
        };

        document.querySelectorAll('.zoomable').forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', () => {
                openZoom(img.src);
            });
        });
    </script>

@endsection
