@extends('layouts.super_admin.template_superadmin')
@section('title', 'User')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="page">
        <div class="top-bar">
            <button type="button" class="btn-add-user">+ Add User</button>

            <div class="search-container">
                <label>Search:</label>
                <input type="text" id="searchInput" placeholder="NIK / Nama" class="search-input">
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                style="min-width: 100%">
                <thead style="text-align: center;">
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th style="width: 200px;">NIK</th>
                        <th style="width: 300px;">NAMA</th>
                        <th style="width: 300px;">UNIT KERJA</th>
                        <th style="width: 200px;">ROLE</th>
                        <th style="width: 200px;">PASSWORD</th>
                        <th style="width: 50px;">EDIT</th>
                        <th style="width: 50px;">DELETE</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @foreach ($usr_doc as $index => $user)
                        <tr>
                            <td style="width: 50px;">{{ $index + 1 }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $user['nik'] }}</td>
                            <td style="max-width: 300px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $user['nama'] }}</td>
                            <td style="max-width: 300px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $user['uker'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $user['role'] }}</td>
                            <td>********</td> <!-- Password ditampilkan sebagai asteris -->
                            <td>
                                <a href="#" class="btn-edit-user" data-id="{{ $user['id'] }}"
                                    data-nik="{{ $user['nik'] }}" data-nama="{{ $user['nama'] }}"
                                    data-uker="{{ $user['uker'] }}" data-role="{{ $user['role_id'] }}"
                                    data-password="{{ $user['password'] }}">
                                    <img src="{{ asset('assets/edit.png') }}" alt="Edit"
                                        style="width:20px;height:20px;">
                                </a>
                            </td>
                            <td>
                                <form action="{{ route('superadmin.user_destroy', $user['id']) }}" method="POST"
                                    class="form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;cursor:pointer;">
                                        <img src="{{ asset('assets/delete.png') }}" alt="Delete"
                                            style="width:20px;height:20px;">
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pop-Up Add User -->
    <div id="addUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3 class="title">Add User</h3>
            <form class="addUserForm" id="addUserForm" method="POST" action="{{ route('superadmin.user_store') }}">
                @csrf
                <div class="edit-nik">
                    <label for="nik" class="label-nik">NIK:</label>
                    <input type="text" id="nik" name="nik" class="input-field" placeholder="Masukkan NIK User"
                        required>
                </div>

                <div class="edit-nama">
                    <label for="nama" class="label-nama">Nama:</label>
                    <input type="text" id="nama" name="nama" class="input-field" placeholder="Masukkan Nama User"
                        required>
                </div>

                <div class="edit-uker">
                    <label for="uker" class="label-uker">Unit Kerja:</label>
                    <input type="text" id="uker" name="uker" class="input-field"
                        placeholder="Masukkan Unit Kerja User" required>
                </div>

                <div class="edit-role">
                    <label for="role" class="label-role">Role:</label>
                    <select id="role" name="role" class="select-field" required>
                        <option value="" disabled selected hidden>Pilih Role User</option>
                        @foreach ($role_doc as $rl)
                            <option value="{{ $rl['id'] }}">{{ $rl['role'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="edit-password">
                    <label for="password" class="label-password">Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="input-field"
                            placeholder="Masukkan Password User" required>
                        <span class="toggle-password" onclick="togglePassword('password', this)" style="top: 55%;">
                            <img src="{{ asset('assets/eye-closed.png') }}" alt="Show" id="password_eye">
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-save">Save</button>
            </form>
        </div>
    </div>

    <!-- Pop-Up Edit User -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3 class="title">Edit User</h3>
            <form class="editUserForm" id="editUserForm" method="POST">
                @csrf
                <input type="hidden" id="edit_id" name="id">

                <div class="edit-nik">
                    <label for="edit_nik" class="label-nik">NIK:</label>
                    <input type="text" id="edit_nik" name="nik" class="input-field">
                </div>

                <div class="edit-nama">
                    <label for="edit_nama" class="label-nama">Nama:</label>
                    <input type="text" id="edit_nama" name="nama" class="input-field">
                </div>

                <div class="edit-uker">
                    <label for="edit_uker" class="label-uker">Unit Kerja:</label>
                    <input type="text" id="edit_uker" name="uker" class="input-field">
                </div>

                <div class="edit-role">
                    <label for="edit_role" class="label-role">Role:</label>
                    <select id="edit_role" name="role" class="select-field">
                        @foreach ($role_doc as $rl)
                            <option value="{{ $rl['id'] }}">{{ $rl['role'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="edit-password">
                    <label for="edit_password" class="label-password">Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="edit_password" name="password" class="input-field">
                        <span class="toggle-password" onclick="togglePassword('edit_password', this)" style="top: 55%;">
                            <img src="{{ asset('assets/eye-closed.png') }}" alt="Show">
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-save">Save</button>
            </form>
        </div>
    </div>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .page {
            padding: 10px 20px;
        }

        .btn-add-user {
            background-color: #133995;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 7px 20px;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border: 1.5px solid transparent;
            transition: background-color 0.3s;
        }

        .btn-add-user:hover {
            background-color: white;
            color: #133995;
            border-color: #CFD0D2;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .search-container label {
            margin-bottom: 5px;
            color: #133995;
            font-weight: 500;
        }


        .search-input {
            padding: 7px 12px;
            border: 1px solid #133995;
            border-radius: 7px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #333;
            width: 220px;
            outline: none;
        }

        .search-input:focus {
            border-color: #0d2a6d;
            box-shadow: 0 0 5px rgba(19, 57, 149, 0.3);
        }

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
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #data-table td::-webkit-scrollbar {
            display: none;
        }

        #data-table th {
            background-color: #133995;
            color: #ffffff;
            height: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600 !important;
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

        .editUserForm {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .addUserForm {
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

        .input-field {
            width: 228px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            border-color: #133995;
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

        /* Custom margins for labels */
        .label-nik {
            margin-right: 75px;
            color: #133995;
        }

        .label-nama {
            margin-right: 50px;
            color: #133995;
        }

        .label-uker {
            margin-right: 24px;
            color: #133995;
        }

        .label-role {
            margin-right: 66px;
            color: #133995;
        }

        .label-password {
            margin-right: 22.5px;
            color: #133995;
        }

        .edit-nik,
        .edit-nama,
        .edit-uker,
        .edit-role,
        .edit-password {
            margin-bottom: 20px;
        }

        .password-wrapper {
            position: relative;
            display: inline-block;
        }

        .password-wrapper .input-field {
            padding-right: 35px;
            /* kasih ruang untuk icon */
            width: 200px;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .password-wrapper .toggle-password img {
            width: 20px;
            height: 20px;
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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll("#data-table tbody tr");

            rows.forEach(row => {
                let nik = row.cells[1].textContent.toLowerCase();
                let nama = row.cells[2].textContent.toLowerCase();

                if (nik.includes(filter) || nama.includes(filter)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });

        // Ambil modal Edit User
        const editUserModal = document.getElementById("editUserModal");

        document.querySelectorAll('.btn-edit-user').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();

                const id = this.getAttribute('data-id');
                const nik = this.getAttribute('data-nik');
                const nama = this.getAttribute('data-nama');
                const uker = this.getAttribute('data-uker');
                const role = this.getAttribute('data-role');
                const password = this.getAttribute('data-password');

                // Isi form
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_nik').value = nik;
                document.getElementById('edit_nama').value = nama;
                document.getElementById('edit_uker').value = uker;
                document.getElementById('edit_role').value = role;
                document.getElementById('edit_password').value = password;

                // Set action form ke route update
                document.getElementById('editUserForm').action = `/superadmin/user/update/${id}`;

                editUserModal.style.display = "block";
            });
        });

        // Tutup modal edit jika klik di luar
        window.addEventListener("click", function(event) {
            if (event.target === editUserModal) {
                editUserModal.style.display = "none";
            }
        });

        // Menangani pengiriman form edit
        // document.getElementById('editUserForm').addEventListener('submit', function() {
        //     editUserModal.style.display = "none"; // Tutup modal setelah submit
        // });


        // Fungsi untuk toggle password
        function togglePassword(id, element) {
            const input = document.getElementById(id);
            const eyeIcon = element.querySelector('img');

            if (input.type === "password") {
                input.type = "text";
                eyeIcon.src = "{{ asset('assets/eye-open.png') }}"; // Ganti dengan ikon mata terbuka
            } else {
                input.type = "password";
                eyeIcon.src = "{{ asset('assets/eye-closed.png') }}"; // Ganti dengan ikon mata tertutup
            }
        }

        // Ambil elemen modal Add User
        const addUserModal = document.getElementById("addUserModal");

        // Tombol Add User
        const addUserBtn = document.querySelector(".btn-add-user");

        // Tampilkan modal saat tombol Add User diklik
        addUserBtn.addEventListener("click", function() {
            addUserModal.style.display = "block";
        });

        // Tutup modal jika klik di luar area modal
        window.addEventListener("click", function(event) {
            if (event.target === addUserModal) {
                addUserModal.style.display = "none";
            }
        });

        // SWEETALERT UNTUK ADD USER
        document.getElementById("addUserForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const addUserModal = document.getElementById("addUserModal");
            const saveBtn = this.querySelector(".btn-save");
            const actionUrl = this.action;
            const formData = new FormData(this);

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Pastikan semua data user sudah benar sebelum disimpan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#133995',
                cancelButtonColor: '#C8170D',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Ya, tambahkan user!',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // 1️⃣ Tutup modal Add User
                    addUserModal.style.display = "none";

                    // 2️⃣ Tampilkan loading SweetAlert
                    Swal.fire({
                        title: 'Sedang menambahkan user...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    saveBtn.disabled = true;

                    try {
                        const res = await fetch(actionUrl, { method: "POST", body: formData });
                        const data = await res.json().catch(() => ({})); // antisipasi jika response bukan JSON

                        if (!res.ok || (data.success === false)) {
                            throw new Error(data.message || "Terjadi kesalahan saat menambahkan user.");
                        }

                        // 3️⃣ Tutup loading dan tampilkan alert berhasil
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: data.message || "User berhasil ditambahkan.",
                            confirmButtonColor: "#133995"
                        }).then(() => {
                            window.location.reload();
                        });

                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: err.message || "Terjadi kesalahan saat menambahkan user.",
                            confirmButtonColor: "#C8170D"
                        });
                    } finally {
                        saveBtn.disabled = false;
                    }
                }
            });
        });

        // SWEETALERT UNTUK DELETE USER
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'User ini akan dihapus secara permanen dan tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#133995',
                    cancelButtonColor: '#C8170D',
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Ya, hapus user!',
                    reverseButtons: true
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        // 1️⃣ Tampilkan loading 
                        Swal.fire({
                            title: 'Sedang menghapus user...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        try {
                            const actionUrl = form.action;
                            const formData = new FormData(form);
                            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                            const res = await fetch(actionUrl, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': token },
                                body: formData
                            });

                            const data = await res.json().catch(() => ({}));

                            if (!res.ok || data.success === false)
                                throw new Error(data.message || 'Terjadi kesalahan saat menghapus user.');

                            // 2️⃣ Tutup loading & tampilkan alert sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'User berhasil dihapus.',
                                confirmButtonColor: '#133995'
                            }).then(() => {
                                window.location.reload();
                            });

                        } catch (err) {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.message || 'Terjadi kesalahan saat menghapus user.',
                                confirmButtonColor: '#C8170D'
                            });
                        }
                    }
                });
            });
        });

        // SWEETALERT UNTUK EDIT USER
        document.getElementById("editUserForm").addEventListener("submit", async function (e) {
            e.preventDefault();

            const editUserModal = document.getElementById("editUserModal");
            const saveBtn = this.querySelector(".btn-save");
            const actionUrl = this.action;
            const formData = new FormData(this);

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Perubahan data user akan disimpan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#133995',
                cancelButtonColor: '#C8170D',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Ya, simpan perubahan!',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // 1️⃣ Tutup modal edit user
                    editUserModal.style.display = "none";

                    // 2️⃣ Tampilkan loading
                    Swal.fire({
                        title: 'Sedang menyimpan perubahan...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    saveBtn.disabled = true;

                    try {
                        const res = await fetch(actionUrl, { method: "POST", body: formData });
                        const data = await res.json().catch(() => ({})); // antisipasi jika bukan JSON

                        if (!res.ok || data.success === false)
                            throw new Error(data.message || "Terjadi kesalahan saat menyimpan perubahan user.");

                        // 3️⃣ Tutup loading & tampilkan alert sukses
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: data.message || "Perubahan data user berhasil disimpan.",
                            confirmButtonColor: "#133995"
                        }).then(() => {
                            window.location.reload();
                        });

                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: err.message || "Terjadi kesalahan saat menyimpan perubahan user.",
                            confirmButtonColor: "#C8170D"
                        });
                    } finally {
                        saveBtn.disabled = false;
                    }
                }
            });
        });
    </script>

@endsection
