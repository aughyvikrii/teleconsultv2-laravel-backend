<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Departemen</title>
</head>
<body>

<table>
    <tr>
        <td colspan="4" style="font-size: 24pt; font-weight: bold; " > Daftar Departemen </td>
    </tr>
</table>

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
</body>
</html>