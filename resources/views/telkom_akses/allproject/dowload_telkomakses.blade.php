<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>All Project Report</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            /* text-align: left; */
        }

        th {
            background-color: #d2d1d1;
            text-align: center;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .total {
            text-align: center;
            font-weight: bold;
            background-color: #eee;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>

    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NAMA PROJECT</th>
                <th>DESKRIPSI PROJECT</th>
                <th>QE</th>
                <th>TANGGAL UPLOAD</th>
                <th>TANGGAL PENGERJAAN</th>
                <th>TANGGAL SELESAI</th>
                <th>STATUS</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project_doc as $index => $project)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $project['nama_project'] }}</td>
                    <td>{{ $project['deskripsi_project'] }}</td>
                    <td>{{ $project['qe'] }}</td>
                    <td>{{ $project['tgl_upload'] }}</td>
                    <td>{{ $project['tgl_pengerjaan'] }}</td>
                    <td>{{ $project['tgl_selesai'] }}</td>
                    <td>{{ $project['status'] }}</td>
                    <td>{{ $project['total_formatted'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="total">TOTAL PROJECT</td>
                <td class="total">{{ $grandTotal }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
