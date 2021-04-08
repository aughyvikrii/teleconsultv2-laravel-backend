<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daftar Dokter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">
    <style>
        @page { size: A4 }
    
        h1 {
            font-weight: bold;
            font-size: 20pt;
            /* text-align: center; */
        }
    
        .table th {
            padding: 8px 8px;
            border:1px solid #000000;
            text-align: center;
        }
    
        .table td {
            padding: 3px 3px;
            border:1px solid #000000;
        }
    
        .text-center {
            text-align: center;
        }

        tr td {
            padding: 3px;
        }

        .label  {
            font-weight: bold;
            vertical-align: top;
        }

        .left {
            width: 450px !important;
        }
    </style>
</head>
<body class="A4">
    <section class="sheet padding-10mm">
        <h1 style="margin-bottom: 0px;">Daftar Dokter</h1>
        <small style="font-size: 10pt;">UID: {{ $id }}</small>
        <hr>
        <table>
            <tr>
                <td class="label left">Nama</td>
                <td class="label">Email</td>
            </tr>
            <tr>
                <td>{{ @$filter['name'] }}</td>
                <td>{{ @$filter['email'] }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Telepon</td>
                <td class="label">Spesialis</td>
            </tr>
            <tr>
                <td>{{ @$filter['phone_number'] }}</td>
                <td>{{ @$filter['specialist'] }}</td>
            </tr>
            <tr>
                <td class="label">Halaman</td>
            </tr>
            <tr>
                <td>{{ @$filter['page'] }}</td>
            </tr>
        </table>
        <br>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Nomor Telp</th>
                    <th>Spesialis</th>
                    <th>Tanggal Didaftarkan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->doctor_id }}</td>
                    <td>{{ $item->display_name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->phone_number }}</td>
                    <td>{{ $item->alt_name }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</body>
</html>