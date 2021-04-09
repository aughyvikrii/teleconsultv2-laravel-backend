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
            <td colspan="2">{{ @$filter['date'] ? @$filter['date'] : 'Semua' }}</td>
            <td colspan="2">{{ @$filter['branch'] ? @$filter['branch'] : 'Semua' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Departemen</td>
            <td colspan="2" style="font-weight: bold;">Spesialis</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['department'] ? @$filter['department'] : 'Semua' }}</td>
            <td colspan="2">{{ @$filter['specialist'] ? @$filter['specialist'] : 'Semua' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Dokter</td>
            <td colspan="2" style="font-weight: bold;">Pasien</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['doctor'] ? @$filter['doctor'] : 'Semua' }}</td>
            <td colspan="2">{{ @$filter['patient'] ? @$filter['patient'] : 'Semua' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Status</td>
            <td colspan="2" style="font-weight: bold;">Halaman</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['status'] ? @$filter['status'] : 'Semua' }}</td>
            <td colspan="2">{{ @$filter['page'] }}</td>
        </tr>
    </table>

    <table style="border: 1px solid black;">
        <thead>
            <tr>
                <th style="font-weight: bold;width: 15px;">Invoice</th>
                <th style="font-weight: bold;width: 10px;">Perjanjian</th>
                <th style="font-weight: bold;width: 30px;">Tgl Bayar</th>
                <th style="font-weight: bold;width: 30px;">Pasien</th>
                <th style="font-weight: bold;width: 25px;">Tgl Konsul</th>
                <th style="font-weight: bold;width: 35px;">Dokter</th>
                <th style="font-weight: bold;width: 25px;">Poli</th>
                <th style="font-weight: bold;width: 25px;">Cabang</th>
                <th style="font-weight: bold;width: 15px;">Status</th>
                <th style="font-weight: bold;width: 25px;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->bill_uniq }}</td>
                <td>{{ $item->appointment_id }}</td>
                <td>{{ $item->id_paid_on }}</td>
                <td>{{ $item->patient_name }}</td>
                <td>{{ $item->consul_date }} {{ $item->consul_time }}</td>
                <td>{{ $item->doctor_name }}</td>
                <td>{{ $item->department }}</td>
                <td>{{ $item->branch }}</td>
                <td>{{ payment_status($item->status) }}</td>
                <td>{{ format_rupiah($item->amount, true) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>