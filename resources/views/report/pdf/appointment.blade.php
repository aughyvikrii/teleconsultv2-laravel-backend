<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Perjanjian</title>
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
        <h1 style="margin-bottom: 0px;">Laporan Perjanjian</h1>
        <small style="font-size: 10pt;">UID: {{ $id }}</small>
        <hr>
        <table>
            <tr>
                <td class="label left">Tanggal</td>
                <td class="label">Cabang</td>
            </tr>
            <tr>
                <td>{{ @$filter['date'] ? @$filter['date'] : 'Semua' }}</td>
                <td>{{ @$filter['branch'] ? @$filter['branch'] : 'Semua' }}</td>
            </tr>
            <tr>
                <td class="label">Departemen</td>
                <td class="label">Spesialis</td>
            </tr>
            <tr>
                <td>{{ @$filter['department'] ? @$filter['department'] : 'Semua' }}</td>
                <td>{{ @$filter['specialist'] ? @$filter['specialist'] : 'Semua' }}</td>
            </tr>
            <tr>
                <td class="label">Dokter</td>
                <td class="label">Pasien</td>
            </tr>
            <tr>
                <td>{{ @$filter['doctor'] ? @$filter['doctor'] : 'Semua' }}</td>
                <td>{{ @$filter['patient'] ? @$filter['patient'] : 'Semua' }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="label">Halaman</td>
            </tr>
            <tr>
                <td>{{ @$filter['status'] ? @$filter['status'] : 'Semua' }}</td>
                <td>{{ @$filter['page'] }}</td>
            </tr>
        </table>
        <br>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pasien</th>
                    <th>Tgl Konsul</th>
                    <th>Dokter</th>
                    <th>Poli</th>
                    <th>Cabang</th>
                    <th>Status</th>
                    <th>Tgl Daftar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->aid }}</td>
                    <td>{{ $item->patient_name }}</td>
                    <td>{{ $item->consul_date }} {{ $item->consul_time }}</td>
                    <td>{{ $item->doctor_name }}</td>
                    <td>{{ $item->department }}</td>
                    <td>{{ $item->branch }}</td>
                    <td>{{ appointment_status($item->status) }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</body>
</html>