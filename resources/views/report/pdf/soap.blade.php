<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Soap</title>
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
            vertical-align: top;
        }

        .left {
            width: 450px !important;
        }
    </style>
</head>
<body class="A4">
    <section class="sheet padding-10mm">
        <h1 style="margin-bottom: 0px;">Laporan Soap</h1>
        <small style="font-size: 10pt;">UID: {{ $id }}</small>
        <hr>
        <table>
            <tr>
                <td class="label left">Nomor Pendaftaran</td>
                <td class="label">Tanggal Pendaftaran</td>
            </tr>
            <tr>
                <td>#{{ @$register->appointment_json['aid'] }}</td>
                <td>{{ \Carbon\Carbon::parse(@$register->appointment_json['created_at'])->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Nama Pasien</td>
                <td class="label">Nama Dokter</td>
            </tr>
            <tr>
                <td>{{ @$register->patient_json['full_name'] }}</td>
                <td>{{ @$register->doctor_json['display_name'] }}</td>
            </tr>
            <tr>
                <td class="label" colspan="2">Tanggal Konsultasi</td>
            </tr>
            <tr>
                <td>{{ \Carbon\Carbon::parse(@$register->appointment_json['consul_date'].' '. @$register->appointment_json['consul_time'])->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
        </table>

        <br>
        <h1>Informasi Soap</h1> <hr>
        <table>
            <tr>
                <td class="label" style="width: 150px;">Subjective</td>
                <td>: {{ nl2br(@$soap->soap_json['subjective']) }}</td>
            </tr>
            <tr>
                <td class="label">Objective</td>
                <td>: {{ nl2br(@$soap->soap_json['objective']) }}</td>
            </tr>
            <tr>
                <td class="label">Assesment</td>
                <td>: {{ nl2br(@$soap->soap_json['assesment']) }}</td>
            </tr>
            
            <tr>
                <td class="label">Plan</td>
                <td>: {{ nl2br(@$soap->soap_json['plan']) }}</td>
            </tr>
        </table>

        <br>
        <h1>Informasi Farmasi</h1> <hr>
        <table>
            <tr>
                <td class="label" style="width: 150px;">Diagnosa</td>
                <td>: {{ nl2br(@$soap->pharmacy_json['diagnosis']) }}</td>
            </tr>
            <tr>
                <td class="label">Rekomendasi</td>
                <td>: {{ nl2br(@$soap->pharmacy_json['recommendation']) }}</td>
            </tr>
            <tr>
                <td class="label">Alergi</td>
                <td>: {{ nl2br(@$soap->pharmacy_json['allergy']) }}</td>
            </tr>
        </table>

        <br>
        <h1>Informasi Radiologi</h1> <hr>
        <table>
            <tr>
                <td class="label" style="width: 150px;">Diagnosa</td>
                <td>: {{ nl2br(@$soap->radiology_json['diagnosis']) }}</td>
            </tr>
            <tr>
                <td class="label">Rekomendasi</td>
                <td>: {{ nl2br(@$soap->radiology_json['recommendation']) }}</td>
            </tr>
            <tr>
                <td class="label">Alergi</td>
                <td>: {{ nl2br(@$soap->radiology_json['allergy']) }}</td>
            </tr>
        </table>

        <br>
        <h1>Informasi Laboratorium</h1> <hr>
        <table>
            <tr>
                <td class="label" style="width: 150px;">Diagnosa</td>
                <td>: {{ nl2br(@$soap->laboratory_json['diagnosis']) }}</td>
            </tr>
            <tr>
                <td class="label">Rekomendasi</td>
                <td>: {{ nl2br(@$soap->laboratory_json['recommendation']) }}</td>
            </tr>
            <tr>
                <td class="label">Alergi</td>
                <td>: {{ nl2br(@$soap->laboratory_json['allergy']) }}</td>
            </tr>
        </table>
    </section>
</body>
</html>