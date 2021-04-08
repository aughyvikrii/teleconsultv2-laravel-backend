<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" style="font-size: 24pt; font-weight: bold; " > Laporan Keuangan </td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;" > Tanggal </td>
            <td colspan="2" style="font-weight: bold;" > Cabang </td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['date'] }}</td>
            <td colspan="2">{{ @$filter['branch'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Departemen</td>
            <td colspan="2" style="font-weight: bold;">Spesialis</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['department'] }}</td>
            <td colspan="2">{{ @$filter['specialist'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Dokter</td>
            <td colspan="2" style="font-weight: bold;">Pasien</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['doctor'] }}</td>
            <td colspan="2">{{ @$filter['patient'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Status</td>
            <td colspan="2" style="font-weight: bold;">Halaman</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['status'] }}</td>
            <td colspan="2">{{ @$filter['page'] }}</td>
        </tr>
    </table>

    <table style="border: 1px solid black;">
        <thead>
            <tr>
                <th style="font-weight: bold;width: 15px;">ID</th>
                <th style="font-weight: bold;width: 30px;">Pasien</th>
                <th style="font-weight: bold;width: 25px;">Tgl Konsul</th>
                <th style="font-weight: bold;width: 35px;">Dokter</th>
                <th style="font-weight: bold;width: 25px;">Poli</th>
                <th style="font-weight: bold;width: 25px;">Cabang</th>
                <th style="font-weight: bold;width: 15px;">Status</th>
                <th style="font-weight: bold;width: 25px;">Tgl Daftar</th>
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
</body>
</html>