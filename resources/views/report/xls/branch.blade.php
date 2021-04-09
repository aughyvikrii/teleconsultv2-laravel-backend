<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Cabang</title>
</head>
<body>

<table>
    <tr>
        <td colspan="4" style="font-size: 24pt; font-weight: bold; " > Daftar Cabang </td>
    </tr>
</table>

    <table border="1" style="width: 100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Perusahaan</th>
                <th>NPWP</th>
                <th>Bank</th>
                <th>Nomor Rekening</th>
                <th>Nomor Telp</th>
                <th>No Whatsapp</th>
                <th>Midtrans ID</th>
                <th>Midtrans Server Key</th>
                <th>Midtrans Client key</th>
                <th>Tanggal Didaftarkan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->branch_id }}</td>
                <td>{{ $item->thumbnail }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->company }}</td>
                <td>{{ $item->npwp }}</td>
                <td>{{ $item->bank_name }}</td>
                <td>{{ $item->account_number }}</td>
                <td>{{ $item->phone_number }}</td>
                <td>{{ $item->whatsapp_number }}</td>
                <td>{{ $item->midtrans_id_merchant }}</td>
                <td>{{ $item->midtrans_server_key }}</td>
                <td>{{ $item->midtrans_client_key }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>