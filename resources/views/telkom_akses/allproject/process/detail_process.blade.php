@extends('layouts.telkom_akses.template_telkomakses')
@section('title', 'Detail All Project')

@section('header')
    @include('layouts.telkom_akses.header_telkomakses')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <div class="page">
        <!-- Tombol Back + ACC / Reject -->
        <div class="action-bar">
            <!-- Tombol Back -->
            <a href="{{ route('telkomakses.allproject') }}" class="btn-back">
                <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
            </a>

            <!-- Tombol ACC / Reject -->
            <div class="action-buttons">
                <form id="formAcc" action="{{ route('telkomakses.allproject_process.acc', $process['id']) }}"
                    method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-acc">
                        <i class="fa fa-check" style="margin-right: 8px;"></i> ACC
                    </button>
                </form>

                <!-- Reject -->
                <form id="formReject" action="{{ route('telkomakses.allproject_process.reject', $process['id']) }}"
                    method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-reject">
                        <i class="fa fa-times" style="margin-right: 8px;"></i> Reject
                    </button>
                </form>
            </div>
        </div>

        <!-- Nama Project + Table wrapper -->
        <div class="table-wrapper">
            <!-- Header Nama Project -->
            <div class="project-header">
                <span class="project-title">{{ $process['nama_project'] ?? 'Nama project belum ada' }}</span>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($process['detail'] ?? [] as $index => $item)
                            {{-- @dd($item) --}}
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
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Material</th>
                            <th colspan="3">{{ number_format($totals['material'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">Jasa</th>
                            <th colspan="3">{{ number_format($totals['jasa'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">Total</th>
                            <th colspan="3">{{ number_format($totals['total'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">PPN (11%)</th>
                            <th colspan="3">{{ number_format($totals['ppn'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">Total Setelah PPN</th>
                            <th colspan="3">{{ number_format($totals['grand'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Foto Evident -->
        @if (!empty($process['foto']) && count($process['foto']) > 0)
            <h3 class="section-title">Foto Evident:</h3>
            <div class="rekap-box">
                <div class="foto-group">
                    <div class="foto-list">
                        @forelse($process['foto'] as $foto)
                            <img src="{{ $foto }}" class="foto-item" alt="Foto Eviden">
                        @empty
                            <span>-</span>
                        @endforelse
                    </div>
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

            .btn-action {
                border: none;
                padding: 10px 16px;
                border-radius: 8px;
                color: white;
                font-size: 14px;
                font-weight: 500;
                display: flex;
                align-items: center;
            }

            .btn-acc {
                background: #22973F;
            }

            .btn-acc:hover {
                background-color: #fff;
                color: #22973F !important;
                border: 1px solid #CFD0D2;
                text-decoration: none;
                cursor: pointer;
            }

            .btn-reject {
                background: #C8170D;
            }

            .btn-reject:hover {
                background-color: #fff;
                color: #C8170D !important;
                border: 1px solid #CFD0D2;
                text-decoration: none;
                cursor: pointer;
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
        </style>

    @endsection

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // SWEETALERT UNTUK ACC
            const accForm = document.getElementById('formAcc');
            if (accForm) {
                accForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Anda akan ACC project ini.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        confirmButtonText: 'Ya, ACC!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then(result => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses ACC...',
                                text: 'Mohon tunggu sebentar.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            fetch(accForm.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: new FormData(accForm)
                                })
                                .then(res => res.json().catch(() => ({})))
                                .then(data => {
                                    if (!data || data.success === false) throw new Error(data
                                        .message ||
                                        'Terjadi kesalahan saat ACC project.');

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message ||
                                            'Project berhasil di-ACC.',
                                        confirmButtonColor: '#133995'
                                    }).then(() => {
                                        document.getElementById('formAcc').submit();
                                    });
                                })
                                .catch(err => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: err.message ||
                                            'Terjadi kesalahan saat ACC project.',
                                        confirmButtonColor: '#C8170D'
                                    });
                                });
                        }
                    });
                });
            }

            // SWEETALERT UNTUK REJECT
            const rejectForm = document.getElementById('formReject');
            if (rejectForm) {
                rejectForm.addEventListener("submit", async function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Anda akan menolak (Reject) project ini.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        confirmButtonText: 'Ya, Reject!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then(result => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses Reject...',
                                text: 'Mohon tunggu sebentar.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            fetch(rejectForm.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: new FormData(rejectForm)
                                })
                                .then(res => res.json().catch(() => ({})))
                                .then(data => {
                                    if (!data || data.success === false) throw new Error(
                                        data.message ||
                                        'Terjadi kesalahan saat menolak project.');

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message ||
                                            'Project berhasil di-reject.',
                                        confirmButtonColor: '#133995'
                                    }).then(() => {
                                        document.getElementById('formReject')
                                            .submit();
                                    });
                                })
                                .catch(err => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: err.message ||
                                            'Terjadi kesalahan saat menolak project.',
                                        confirmButtonColor: '#C8170D'
                                    });
                                });
                        }
                    });
                });
            }

        }); // end DOMContentLoaded
    </script>
