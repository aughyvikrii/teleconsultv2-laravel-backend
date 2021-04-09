<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Spesialis</title>
</head>
<body>

<table>
    <tr>
        <td colspan="4" style="font-size: 24pt; font-weight: bold; " > Daftar Spesialis </td>
    </tr>
</table>
    <table border="1" style="width: 100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Nama</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->sid }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->alt_name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>