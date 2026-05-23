@extends('layouts.telkom_akses.template_telkomakses')
@section('title', 'Detail All Project')

@section('header')
    @include('layouts.telkom_akses.header_telkomakses')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <div class="page">
        <!-- Tombol Back -->
        <div class="action-bar">
            <a href="{{ route('telkomakses.allproject') }}" class="btn-back">
                <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
            </a>
        </div>

        <!-- Nama Project + Table wrapper -->
        <div class="table-wrapper">
            <!-- Header Nama Project -->
            <div class="project-header">
                <span class="project-title">{{ $acc['nama_project'] ?? 'Nama project belum ada' }}</span>
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
                                    {{-- @forelse($acc['foto']['sebelum'] ?? [] as $foto)
                                        <img src="{{ $foto }}" class="foto-item">
                                    @empty
                                        <span>-</span>
                                    @endforelse --}}
                                    @if (isset($acc['foto']['sebelum'][$item->designator]))
                                        @foreach ($acc['foto']['sebelum'][$item->designator] as $foto)
                                            <img src="{{ $foto }}" class="foto-item zoomable">
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{-- @forelse($acc['foto']['sesudah'] ?? [] as $foto)
                                        <img src="{{ $foto }}" class="foto-item">
                                    @empty
                                        <span>-</span>
                                    @endforelse --}}
                                    @if (isset($acc['foto']['sesudah'][$item->designator]))
                                        @foreach ($acc['foto']['sesudah'][$item->designator] as $foto)
                                            <img src="{{ $foto }}" class="foto-item zoomable">
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">MATERIAL</th>
                            <th colspan="4">{{ number_format($totals['material'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">JASA</th>
                            <th colspan="4">{{ number_format($totals['jasa'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">TOTAL</th>
                            <th colspan="4">{{ number_format($totals['total'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">PPN</th>
                            <th colspan="4">{{ number_format($totals['ppn'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">TOTAL SETELAH PPN</th>
                            <th colspan="4">{{ number_format($totals['grand'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
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

    <!-- Modal Zoom Foto -->
    <div id="imageZoomModal" class="zoom-modal">
        <span class="zoom-close">&times;</span>
        <img class="zoom-content" id="zoomedImage">
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
