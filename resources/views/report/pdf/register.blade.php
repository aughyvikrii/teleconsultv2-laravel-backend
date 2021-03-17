@php
    $start_consul = \Carbon\Carbon::parse(@$data->appointment_json['start_consul']);
    $end_consul = \Carbon\Carbon::parse(@$data->appointment_json['end_consul']);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pendaftaran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">
    <style>
        @page { size: A4 }
    
        h1 {
            font-weight: bold;
            font-size: 20pt;
            /* text-align: center; */
        }
    
        table {
            border-collapse: collapse;
            width: 100%;
            /* border: 1px solid black; */
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
        }

        .left {
            width: 450px !important;
        }
    </style>
</head>
<body class="A4">
    <section class="sheet padding-10mm">
        <h1 style="margin-bottom: 0px;">Laporan Pendaftaran</h1>
        <small style="font-size: 10pt;">UID: {{ $id }}</small>
        <hr>
        <table>
            <tr>
                <td class="label left">Nomor Pendaftaran</td>
                <td class="label">Tanggal Pendaftaran</td>
            </tr>
            <tr>
                <td>#{{ @$data->appointment_json['aid'] }}</td>
                <td>{{ \Carbon\Carbon::parse(@$data->appointment_json['created_at'])->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Tagihan</td>
                <td class="label">Tanggal Pelunasan</td>
            </tr>
            <tr>
                <td>#{{ @$data->bill_json['uniq'] }}</td>
                <td>{{ \Carbon\Carbon::parse(@$data->bill_json['paid_on'])->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
        </table>
        <br>
        <h1>Informasi Pasien</h1> <hr>
        <table>
            <tr>
                <td class="label" style="width: 150px;">Nama Depan</td>
                <td>: {{ @$data->patient_json['first_name'] }}</td>
                <td class="label" style="width: 120px;">Nama Belakang</td>
                <td>: {{ @$data->patient_json['last_name'] }}</td>
            </tr>
            <tr>
                <td class="label">Tempat Lahir</td>
                <td>: {{ @$data->patient_json['birth_place'] }}</td>
                <td class="label">Tanggal Lahir</td>
                <td>: {{ @$data->id_birth_date }}</td>
            </tr>
            <tr>
                <td class="label">Umur</td>
                <td colspan="3">: {{ @$data->age }}</td>
            </tr>
            <tr>
                <td class="label">Keluhan Utama</td>
                <td colspan="3">: {{ @$data->appointment_json['main_complaint'] }}</td>
            </tr>
            <tr>
                <td class="label">Riwayat Penyakit</td>
                <td colspan="3">: {{ @$data->appointment_json['disease_history'] }}</td>
            </tr>
            <tr>
                <td class="label">Alergi</td>
                <td colspan="3">: {{ @$data->appointment_json['allergy'] }}</td>
            </tr>
            <tr>
                <td class="label">Suhu Badan</td>
                <td colspan="3">: {{ @$data->appointment_json['body_temperature'] }} C</td>
            </tr>
            <tr>
                <td class="label">Tekanan Darah</td>
                <td colspan="3">: {{ @$data->appointment_json['blood_pressure'] }} mmHg</td>
            </tr>
            <tr>
                <td class="label">Berat Badan</td>
                <td colspan="3">: {{ @$data->appointment_json['wight'] }} Kg</td>
            </tr>
            <tr>
                <td class="label">Tinggi Badan</td>
                <td colspan="3">: {{ @$data->appointment_json['height'] }} Cm</td>
            </tr>
        </table>

        <br>
        <h1>Informasi Perjanjian</h1> <hr>
        <table>
            <tr>
                <td class="label" style="width: 150px;">Dokter</td>
                <td colspan="3">: {{ @$data->doctor_json['display_name'] }}</td>
            </tr>
            <tr>
                <td class="label">Poli</td>
                <td>: {{ @$data->department_json['name'] }}</td>
            </tr>
            <tr>
                <td class="label">Cabang</td>
                <td>: {{ @$data->branch_json['name'] }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Konsultasi</td>
                <td>: {{ \Carbon\Carbon::parse(@$data->appointment_json['date_consul'].' '. @$data->appointment_json['time_consul'])->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Durasi Jadwal</td>
                <td>: {{ @$data->schedule_json['duration'] }} Menit</td>
            </tr>
            <tr>
                <td class="label">Jam Dimulai</td>
                <td>: {{ $start_consul->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Jam Selesai</td>
                <td>: {{ $end_consul->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Durasi Konsultasi</td>
                <td>: {{ $start_consul->diff($end_consul)->format('%H Jam %I Menit %S Detik') }}</td>
            </tr>
        </table>
    </section>
</body>
</html>