<!DOCTYPE html>
<html>
<head>
    <title>Debug Upload Cloudinary</title>
</head>
<body>
    <h2>Test Upload Cloudinary</h2>

    <form action="/debug-upload" method="POST" enctype="multipart/form-data">
        @csrf
        <label>Pilih Foto:</label><br><br>
        <input type="file" name="foto" required><br><br>

        <button type="submit">Upload Debug</button>
    </form>
</body>
</html>
