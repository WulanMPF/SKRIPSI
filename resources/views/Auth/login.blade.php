<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            background-image: url('{{ asset('assets/login.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-box {
            position: absolute;
            right: 22%;
            width: 400px;
        }

        .input-field {
            border-radius: 25px;
            padding: 12px 16px;
            margin-top: 12px;
            transition: border-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 52, 176, 0.25);
            position: relative;
        }

        .input-field:focus {
            border-color: #3b82f6;
            outline: none;
        }

        .icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
        }

        .login-button {
            background-color: #133995;
            color: white;
            border-radius: 25px;
            padding: 8px 40px;
            margin-top: 24px;
            transition: background-color 0.3s;
            font-size: 14px;
            font-weight: bold;
            border: 1.5px solid transparent;
        }

        .login-button:hover {
            background-color: white;
            color: #133995;
            border-color: #CFD0D2;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="text-center relative login-box">
        <img src="{{ asset('assets/telkomakses_logo.png') }}" alt="Telkom Akses" class="mx-auto w-40 mb-8">
        <img src="{{ asset('assets/luwina_logo.png') }}" alt="Telkom Akses" class="mx-auto w-56 mb-4">

        <div class="mt-12">
            <form action="{{ route('login-proses') }}" method="POST" class="mt-4">
                @csrf
                <div class="text-left">
                    <div class="relative">
                        <input type="text" name="nik" placeholder="Masukkan username"
                            class="input-field w-full pl-10" required>
                        <span style="position: absolute; left: 10px; top: 60%; transform: translateY(-50%);">
                            <img src="{{ asset('assets/username_logo.png') }}"style="width: 25px; height: 25px;">
                        </span>
                    </div>
                    @error('nik')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="text-left mt-4">
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Masukkan password"
                            class="input-field w-full pl-10" required>
                        <span style="position: absolute; left: 10px; top: 60%; transform: translateY(-50%);">
                            <img src="{{ asset('assets/password_logo.png') }}"style="width: 25px; height: 25px;">
                        </span>
                        <span onclick="togglePassword('password', this)"
                            style="cursor: pointer; position: absolute; right: 10px; top: 60%; transform: translateY(-50%);">
                            <img src="{{ asset('assets/eye-closed.png') }}" alt="Show" id="password_eye"
                                style="width: 20px; height: 20px;">
                        </span>
                    </div>
                    @error('password')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="text-left mt-4 flex justify-end">
                    <button type="submit" class="login-button">
                        Log In
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(id, element) {
            const input = document.getElementById(id);
            const eyeIcon = element.querySelector('img');

            if (input.type === "password") {
                input.type = "text";
                eyeIcon.src = "{{ asset('assets/eye-open.png') }}"; // Change to eye open icon
            } else {
                input.type = "password";
                eyeIcon.src = "{{ asset('assets/eye-closed.png') }}"; // Change to eye closed icon
            }
        }
    </script>

    <!-- jQuery -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($message = Session::get('failed'))
        <script>
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Username atau Password Salah!",
                customClass: {
                    confirmButton: 'my-confirm-button',
                    popup: 'my-swal-popup'
                }
            });
        </script>
    @endif
</body>

</html>
