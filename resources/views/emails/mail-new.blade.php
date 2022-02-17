<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rukun</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size:12px;
        }

        .main {
            border: 1px solid #000000;
            margin: 0px auto;
            width: 100%;
            background-color: #f2f2f2;
        }

        .sub-main {
            width: 80%;
            margin:0px auto;
            background-color: #ffffff;
            padding: 20px;
            margin-top:10px;
            margin-bottom:10px;
        }

        .footer {
            width: 80%;
            margin:0px auto;
            margin-top:10px;
            margin-bottom:10px;
            text-align: center;
        }

        .green-line {
            height: 3px;
            border-radius: 1.5em;
            overflow: hidden;
            background-color: #1bae95;
            clear: both;
            width: 100%;
        }

        .grey-line {
            height: 1px;
            border-radius: 1.5em;
            overflow: hidden;
            background-color: #c1c1c1;
            clear: both;
        }

        p.grey {
            color:#c1c1c1;
            font-size:14px;
        }

        .image {
            text-align: center;
            margin-bottom:10px;
        }

        .paragraph {
            text-align: center;
            width: 100%;
            margin-top: 20px;
        }

        .registrasi {
            display: block;
            background-color: #41D0B6;
            color: #ffffff;
            font-size: 14px;
            padding: 10px;
            width:100px;
            margin:0px auto;
            text-decoration : none;
            border-radius: 0.5em;
            overflow: hidden;
        }

        .divider20 {
            height: 20px;
            clear: both;
        }

        .divider50 {
            height: 50px;
            clear: both;
        }

        a.registrasi {
            color:#ffffff;
            font-weight: bold;
        }

        a:link {
            text-decoration: none;
        }

        /* visited link */
        a:visited {
            text-decoration: none;
        }

        /* mouse over link */
        a:hover {
            text-decoration: none;
        }

        /* selected link */
        a:active {
            text-decoration: none;
        }


        a.registrasi:link {
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
        }

        /* visited link */
        a.registrasi:visited {
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
        }

        /* mouse over link */
        a.registrasi:hover {
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
        }

        /* selected link */
        a.registrasi:active {
            text-decoration: none;
            color: #ffffff;
        }

        .filter-green {
            filter: invert(47%) sepia(73%) saturate(481%) hue-rotate(120deg) brightness(102%) contrast(88%);
        }

    </style>
</head>
<body>

    <div class="main">
        <div class="sub-main">
            <div class="image"><img src="{{ url('') }}/public/img/logo.png" width="200px"></div>
            <div class="green-line"></div>

            <div class="paragraph">
                <span style="font-size:14px;"><strong>Halo Warga {{$nama_wilayah}}</strong></span>
                <p>Anda telah diundang oleh <strong>{{$nama_pengurus}}</strong> untuk bergabung pada Aplikasi Rukun</p>

                <p>
                    Silakan klik tombol Registrasi berikut untuk melakukan registrasi Aplikasi,<br>
                    kemudian install Aplikasi Rukun pada perangkat telepon pintar Anda.<br>
                    <br><br><strong>Undangan ini hanya berlaku bagi Anda dan tidak berlaku bagi orang lain.<strong>
                </p>

                <a href="{{$url_registrasi}}" class="registrasi">REGISTRASI</a>

                <p>Aplikasi Rukun dapat Anda install melalui tautan berikut</p>
                <div class="image">
                    <a href="{{$url_aplikasi}}">
                        <img src="{{ url('public/img/download/android-app.png') }}" width="150px">
                    </a>
                </div>

                <p>
                    Pesan ini dikirim melalui akun Wilayah {{$nama_wilayah}} pada Aplikasi Rukun.
                </p>

            </div>
        </div>
        <div class="footer">
            <div class="divider20"></div>
            <div>
                <a href="#" style="padding:10px;margin-right:10px;"><img src="{{ url('') }}/public/img/facebook.png" width="28"></a>
                <a href="#" style="padding:10px;margin-right:10px;"><img src="{{ url('') }}/public/img/twitter.png" width="28"></a>
                <a href="#" style="padding:10px;margin-right:10px;"><img src="{{ url('') }}/public/img/instagram.png" width="28"></a>
                <a href="#" style="padding:10px;margin-right:10px;"><img src="{{ url('') }}/public/img/youtube.png" width="28"></a>
            </div>

            <div class="divider20"></div>
            <div class="grey-line"></div>
            <div class="divider20"></div>
            <p class="grey">PT. Jaringmas Solusi Bergerak</p>
            <p class="grey">Gedung Gajah Unit V <br> Jl. DR Saharjo No.111 Tebet<br> Jakarta Selatan</p>
            <div class="divider20"></div>
        </div>
    </div>

</body>
</html>
