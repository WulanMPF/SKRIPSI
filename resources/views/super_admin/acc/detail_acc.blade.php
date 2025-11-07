    @extends('layouts.super_admin.template_superadmin')
    @section('title', 'Detail ACC')

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
                <a href="{{ route('superadmin.acc') }}" class="btn-back">
                    <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
                </a>

                <!-- Tombol Kerjakan -->
                <div class="action-buttons">
                    @if ($acc['tgl_pengerjaan'] == '-' || empty($acc['tgl_pengerjaan']))
                        <form id="formKerjakan" action="{{ route('superadmin.acc.kerjakan', $acc['id']) }}" method="POST"
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
                    <a href="{{ route('superadmin.acc_edit', $acc['id']) }}" class="edit-project">Edit Project</a>
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
                                        <form
                                            action="{{ route('superadmin.acc_destroy', ['id' => $acc['id'], 'detailId' => $item->id]) }}"
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

            <!-- Tombol Delete Data Project -->
            <div id="deleteProject" style="margin-top: 20px; text-align: left;">
                <form action="{{ route('superadmin.acc_destroy_project', $acc['id']) }}" method="POST"
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

        <!-- Pop Up Done Upload Foto -->
        <div id="doneModal" class="modal" style="display:none;">
            <div class="modal-content">
                <h3 id="modalTitle" style="text-align:center; color:#133995;">Upload Foto Evident Sebelum Pengerjaan</h3>
                <p id="modalDesc" style="text-align:center;">Silahkan unggah foto evident <b>sebelum</b> pengerjaan</p>

                <form method="POST" action="{{ route('superadmin.acc.storeFoto', $acc['id']) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Drop Zone / Upload -->
                    <div id="dropZone"
                        style="border:2px dashed #ccc; border-radius:10px; padding:20px; text-align:center; margin-bottom:20px;">
                        <input type="file" name="foto_sebelum[]" id="imagesSebelum" multiple accept="image/*" hidden>
                        <input type="file" name="foto_proses[]" id="imagesProses" multiple accept="image/*" hidden>
                        <input type="file" name="foto_sesudah[]" id="imagesSesudah" multiple accept="image/*" hidden>
                        <label for="imagesSebelum" style="cursor:pointer; display:block; color:#595961;">
                            <i class="fa fa-cloud-upload-alt" style="font-size:24px; margin-bottom:8px;"></i><br>
                            <span>Upload Foto Sebelum Pengerjaan<br>JPEG/PNG up to 10MB</span>
                            <br><br>
                            <button type="button" id="browseBtn" class="btn-browse">Browse</button>
                        </label>
                    </div>

                    <!-- Preview Gambar per Step -->
                    <div id="previewSebelum" class="previewImages"
                        style="display:flex; flex-wrap:wrap; gap:10px; justify-content:center;"></div>
                    <div id="previewProses" class="previewImages"
                        style="display:none; flex-wrap:wrap; gap:10px; justify-content:center;"></div>
                    <div id="previewSesudah" class="previewImages"
                        style="display:none; flex-wrap:wrap; gap:10px; justify-content:center;"></div>

                    <!-- Tombol Navigasi -->
                    <div style="display:flex; justify-content:space-between; margin-top:20px;">
                        <!-- Container kiri -->
                        <div id="leftBtns">
                            <button type="button" id="cancelBtn" class="modal-btn cancel">Cancel</button>
                            <button type="button" id="prevBtn" class="modal-btn prev"
                                style="display:none;">Previous</button>
                        </div>

                        <!-- Container kanan -->
                        <div id="rightBtns">
                            <button type="button" id="nextBtn" class="modal-btn next">Next</button>
                            <button type="submit" id="uploadBtn" class="modal-btn upload"
                                style="display:none;">Upload</button>
                        </div>
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

                <form action="{{ route('superadmin.acc.pending', $acc['id']) }}" method="POST">
                    @csrf

                    <!-- Container untuk input group -->
                    <div id="keteranganContainer">
                        <div class="input-group horizontal">
                            <div class="tgl-wrapper">
                                <input type="text" name="tgl_pending[]" class="form-control tgl-pending"
                                    placeholder="Tanggal" readonly>
                                <i class="fa fa-calendar calendar-icon"></i>
                            </div>
                            <input type="text" name="keterangan[]" class="form-control"
                                placeholder="Tulis keterangan">
                        </div>
                    </div>

                    <!-- Tombol Add / Remove -->
                    <div class="action-btns">
                        <button type="button" class="btn-add">+</button>
                        <button type="button" class="btn-remove">-</button>
                    </div>

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
        @if (!empty($acc['tgl_pengerjaan']) && $acc['tgl_pengerjaan'] != '-')
            <div class="rekap-section mt-6">
                <h3 class="section-title">Foto Evident:</h3>
                <div class="rekap-box">
                    <div class="foto-group">
                        <div class="foto-title">Sebelum</div>
                        <div class="foto-list">
                            @forelse($acc['foto']['sebelum'] ?? [] as $foto)
                                <img src="{{ $foto }}" class="foto-item">
                            @empty
                                <span>-</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="foto-group">
                        <div class="foto-title">Proses</div>
                        <div class="foto-list">
                            @forelse($acc['foto']['proses'] ?? [] as $foto)
                                <img src="{{ $foto }}" class="foto-item">
                            @empty
                                <span>-</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="foto-group">
                        <div class="foto-title">Sesudah</div>
                        <div class="foto-list">
                            @forelse($acc['foto']['sesudah'] ?? [] as $foto)
                                <img src="{{ $foto }}" class="foto-item">
                            @empty
                                <span>-</span>
                            @endforelse
                        </div>
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

            .btn-add,
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
            }

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

            .foto-item {
                width: 150px;
                height: 150px;
                object-fit: cover;
                border-radius: 8px;
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

            /* Tabel ACC Footer (Material, Jasa, Total, PPN, Grand) */
            #data-table tfoot th {
                background: #EDF7FF;
                font-weight: 600;
                text-align: center;
                border: 1px solid #ddd !important;
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const doneModal = document.getElementById("doneModal");
                const btnDone = document.getElementById("btnDone");
                const browseBtn = document.getElementById("browseBtn");
                const dropZone = document.getElementById("dropZone");

                // Input terpisah untuk setiap step
                const inputSebelum = document.getElementById("imagesSebelum");
                const inputProses = document.getElementById("imagesProses");
                const inputSesudah = document.getElementById("imagesSesudah");

                const prevBtn = document.getElementById("prevBtn");
                const nextBtn = document.getElementById("nextBtn");
                const uploadBtn = document.getElementById("uploadBtn");

                const title = document.getElementById("modalTitle");
                const desc = document.getElementById("modalDesc");
                const cancelBtn = document.getElementById("cancelBtn");

                const previewSebelum = document.getElementById("previewSebelum");
                const previewProses = document.getElementById("previewProses");
                const previewSesudah = document.getElementById("previewSesudah");

                const btnKerjakan = document.getElementById('btnKerjakan');
                const formKerjakan = document.getElementById('formKerjakan');

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
                            cancelButtonText: 'Cancel',
                            confirmButtonText: 'Ya, kerjakan!',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                formKerjakan.submit();
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

                let step = 1;
                let imagesSebelum = [];
                let imagesProses = [];
                let imagesSesudah = [];

                // Fungsi render dengan tombol hapus
                function renderPreview(container, imagesArray) {
                    container.innerHTML = "";
                    imagesArray.forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = e => {
                            const wrapper = document.createElement("div");
                            wrapper.classList.add("preview-item");

                            const img = document.createElement("img");
                            img.src = e.target.result;

                            const removeBtn = document.createElement("button");
                            removeBtn.innerHTML = "×";
                            removeBtn.classList.add("remove-btn");
                            removeBtn.addEventListener("click", () => {
                                imagesArray.splice(index, 1);
                                renderPreview(container, imagesArray);
                            });

                            wrapper.appendChild(img);
                            wrapper.appendChild(removeBtn);
                            container.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    });
                }

                // Open modal Done
                btnDone?.addEventListener('click', () => {
                    step = 1;
                    updateModal();
                    doneModal.style.display = "block";
                });

                // Tutup modal Done
                cancelBtn.addEventListener('click', () => {
                    doneModal.style.display = "none";
                });

                // Klik luar modal untuk tutup
                window.addEventListener("click", (e) => {
                    if (e.target === doneModal) doneModal.style.display = "none";
                });

                // Browse file
                browseBtn.addEventListener('click', () => {
                    if (step === 1) inputSebelum.click();
                    else if (step === 2) inputProses.click();
                    else inputSesudah.click();
                });

                // Handle input Sebelum
                inputSebelum.addEventListener('change', () => {
                    imagesSebelum.push(...Array.from(inputSebelum.files));
                    renderPreview(previewSebelum, imagesSebelum);
                    inputSebelum.value = "";
                });

                // Handle input Proses
                inputProses.addEventListener('change', () => {
                    imagesProses.push(...Array.from(inputProses.files));
                    renderPreview(previewProses, imagesProses);
                    inputProses.value = "";
                });

                // Handle input Sesudah
                inputSesudah.addEventListener('change', () => {
                    imagesSesudah.push(...Array.from(inputSesudah.files));
                    renderPreview(previewSesudah, imagesSesudah);
                    inputSesudah.value = "";
                });

                // Drag & Drop
                dropZone.addEventListener("dragover", (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = "#133995";
                });

                dropZone.addEventListener("dragleave", () => {
                    dropZone.style.borderColor = "#ccc";
                });

                dropZone.addEventListener("drop", (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = "#ccc";
                    const files = Array.from(e.dataTransfer.files);

                    if (files.length > 0) {
                        if (step === 1) {
                            imagesSebelum.push(...files);
                            renderPreview(previewSebelum, imagesSebelum);
                        } else if (step === 2) {
                            imagesProses.push(...files);
                            renderPreview(previewProses, imagesProses);
                        } else {
                            imagesSesudah.push(...files);
                            renderPreview(previewSesudah, imagesSesudah);
                        }
                    }
                });

                // Navigation buttons
                nextBtn.addEventListener('click', () => {
                    if (step < 3) step++;
                    updateModal();
                });
                prevBtn.addEventListener('click', () => {
                    if (step > 1) step--;
                    updateModal();
                });

                // Update tampilan modal sesuai step
                function updateModal() {
                    previewSebelum.style.display = "none";
                    previewProses.style.display = "none";
                    previewSesudah.style.display = "none";

                    cancelBtn.style.display = "none";
                    prevBtn.style.display = "none";
                    nextBtn.style.display = "none";
                    uploadBtn.style.display = "none";

                    if (step === 1) {
                        title.textContent = "Upload Foto Evident Sebelum Pengerjaan";
                        desc.innerHTML = "Silahkan unggah foto evident <b>sebelum</b> pengerjaan";
                        inputImages = inputSebelum;
                        previewSebelum.style.display = "flex";
                        renderPreview(previewSebelum, imagesSebelum);
                        cancelBtn.style.display = "inline-block";
                        nextBtn.style.display = "inline-block";
                    } else if (step === 2) {
                        title.textContent = "Upload Foto Evident Proses Pengerjaan";
                        desc.innerHTML = "Silahkan unggah foto evident <b>proses</b> pengerjaan";
                        inputImages = inputProses;
                        previewProses.style.display = "flex";
                        renderPreview(previewProses, imagesProses);
                        prevBtn.style.display = "inline-block";
                        nextBtn.style.display = "inline-block";
                    } else if (step === 3) {
                        title.textContent = "Upload Foto Evident Sesudah Pengerjaan";
                        desc.innerHTML = "Silahkan unggah foto evident <b>setelah</b> pengerjaan";
                        inputImages = inputSesudah;
                        previewSesudah.style.display = "flex";
                        renderPreview(previewSesudah, imagesSesudah);
                        prevBtn.style.display = "inline-block";
                        uploadBtn.style.display = "inline-block";
                    }
                }

                // Upload
                uploadBtn.addEventListener('click', async function(e) {
                    e.preventDefault();

                    const form = uploadBtn.closest('form');
                    const actionUrl = form.action;
                    const csrfToken = form.querySelector('input[name="_token"]').value;

                    const formData = new FormData();
                    formData.append('_token', csrfToken);

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Setelah upload foto evident, data tidak dapat diubah lagi.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#133995',
                        cancelButtonColor: '#C8170D',
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Ya, upload!',
                        reverseButtons: true
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const form = uploadBtn.closest('form');
                            const actionUrl = form.action;
                            const csrfToken = form.querySelector('input[name="_token"]').value;

                            // Tambahkan semua file dari masing-masing array
                            imagesSebelum.forEach(file => formData.append('foto_sebelum[]',
                                file));
                            imagesProses.forEach(file => formData.append('foto_proses[]',
                                file));
                            imagesSesudah.forEach(file => formData.append('foto_sesudah[]',
                                file));

                            uploadBtn.disabled = true; // biar ga double klik

                            try {
                                const res = await fetch(actionUrl, {
                                    method: 'POST',
                                    body: formData,
                                });

                                if (!res.ok) throw new Error('Upload gagal');

                                // Tutup modal DONE otomatis
                                doneModal.style.display = "none";

                                // Ambil ulang isi foto evident tanpa reload seluruh halaman
                                const parser = new DOMParser();
                                const html = await (await fetch(window.location.href)).text();
                                const newDoc = parser.parseFromString(html, 'text/html');

                                const oldSection = document.querySelector(
                                    '.rekap-section.mt-6'); // bagian foto evident lama
                                const newSection = newDoc.querySelector('.rekap-section.mt-6');

                                if (oldSection && newSection) {
                                    oldSection.innerHTML = newSection.innerHTML;
                                }

                                // Reset file array
                                imagesSebelum = [];
                                imagesProses = [];
                                imagesSesudah = [];
                                previewSebelum.innerHTML = "";
                                previewProses.innerHTML = "";
                                previewSesudah.innerHTML = "";

                                // Notifikasi sukses tanpa reload
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Foto evident berhasil diupload dan disimpan!',
                                    confirmButtonColor: '#133995'
                                });

                            } catch (err) {
                                console.error(err);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Upload!',
                                    text: 'Terjadi kesalahan saat upload foto.'
                                });
                            } finally {
                                uploadBtn.disabled = false;
                            }
                        }
                    });

                    // Sembunyikan tombol Pending & Done setelah upload berhasil
                    const btnPending = document.getElementById('btnPending');
                    const btnDone = document.getElementById('btnDone');
                    if (btnPending) btnPending.style.display = 'none';
                    if (btnDone) btnDone.style.display = 'none';
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
            });
        </script>

    @endsection
