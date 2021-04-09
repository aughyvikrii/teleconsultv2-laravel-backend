<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daftar Cabang</title>
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
        <h1 style="margin-bottom: 0px;">Daftar Departemen</h1>
        <small style="font-size: 10pt;">UID: {{ $id }}</small>
        <hr>
        <table border="1" style="width: 100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->department_id }}</td>
                    <td>{{ $item->thumbnail }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</body>
</html>