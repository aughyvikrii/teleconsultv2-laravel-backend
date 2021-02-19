<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Akun Telekonsultasi</title>
</head>
<body>
    Hallo {{$name}},<br><br>
    terimakasih telah mendaftar di aplikasi {{config('app.name')}} <br>
    Berikut adalah link konfirmasi akun anda: <br><br>
    {{ $link }} <br><br>
    Jika anda tidak melakukan pendaftaran, abaikan pesan ini. <br>
    Terimakasih.
</body>
</html>