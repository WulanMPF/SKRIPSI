@extends('layouts.telkom_akses.template_telkomakses')
@section('title', 'Detail Reject')

@section('header')
    @include('layouts.telkom_akses.header_telkomakses')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <div class="page">
        <!-- Tombol Back + Kerjakan -->
        <div class="action-bar">
            <!-- Tombol Back -->
            <a href="{{ route('telkomakses.reject') }}" class="btn-back">
                <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
            </a>
        </div>

        <!-- Nama Project + Table wrapper -->
        <div class="table-wrapper">
            <!-- Header Nama Project -->
            <div class="project-header">
                <span class="project-title">{{ $reject['nama_project'] ?? 'Nama project belum ada' }}</span>
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
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">MATERIAL</th>
                            <th colspan="3">{{ number_format($totals['material'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">JASA</th>
                            <th colspan="3">{{ number_format($totals['jasa'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">TOTAL</th>
                            <th colspan="3">{{ number_format($totals['total'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">PPN</th>
                            <th colspan="3">{{ number_format($totals['ppn'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">TOTAL SETELAH PPN</th>
                            <th colspan="3">{{ number_format($totals['grand'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Foto Evident -->
        @if (!empty($reject['foto']) && count($reject['foto']) > 0)
            <h3 class="section-title">Foto Evident:</h3>
            <div class="rekap-box">
                <div class="foto-group">
                    <div class="foto-list">
                        @forelse($reject['foto'] as $foto)
                            <img src="{{ $foto }}" class="foto-item" alt="Foto Eviden">
                        @empty
                            <span>-</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
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
