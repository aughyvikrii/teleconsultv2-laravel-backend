<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dokter</title>
</head>
<body>

<table>
        <tr>
            <td colspan="4" style="font-size: 24pt; font-weight: bold; " > Daftar Dokter </td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;" > Nama </td>
            <td colspan="2" style="font-weight: bold;" > Email </td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['name'] }}</td>
            <td colspan="2">{{ @$filter['email'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Nomor Telepon</td>
            <td colspan="2" style="font-weight: bold;">Spesialis</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['phone_number'] }}</td>
            <td colspan="2">{{ @$filter['specialist'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Halaman</td>
        </tr>
        <tr>
            <td colspan="2">{{ @$filter['page'] }}</td>
        </tr>
    </table>

    <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Nomor Telp</th>
                    <th>Identitas</th>
                    <th>Nomor Identitas</th>
                    <th>Tgl Lahir</th>
                    <th>Tempat Lahir</th>
                    <th>Jenis Kelamin</th>
                    <th>Agama</th>
                    <th>Status</th>
                    <th>Titel</th>
                    <th>Alamat Tinggal</th>
                    <th>Spesialis</th>
                    <th>Tanggal Didaftarkan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->pid }}</td>
                    <td>{{ $item->display_name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->phone_number }}</td>
                    <td>{{ $item->identity_type }}</td>
                    <td>{{ $item->identity_number }}</td>
                    <td>{{ $item->birth_date }}</td>
                    <td>{{ $item->birth_place }}</td>
                    <td>{{ $item->gender }}</td>
                    <td>{{ $item->religion }}</td>
                    <td>{{ $item->married_status }}</td>
                    <td>{{ $item->title_short }}</td>
                    <td>{{ $item->address }}</td>
                    <td>{{ $item->alt_name }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
</body>
</html>