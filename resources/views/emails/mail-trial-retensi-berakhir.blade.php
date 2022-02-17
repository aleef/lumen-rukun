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
                    <span style="font-size:14px;"><strong>Halo {{$nama_warga}}</strong></span>
                    <p>
                        Kami informasikan bahwa akun wilayah Anda sudah melewati masa retensi Trial. <br> Oleh karena itu, terhitung mulai tanggal <tanggal H+1 masa retensi trial> akun wilayah Anda sudah tidak dapat diakses.  
                        <br>Data Anda akan dihapus secara otomatis setelah 30 hari terhitung tanggal tersebut.
                    </p>
                    <p>
                        Jika Anda memerlukan informasi lebih lanjut, silakan menghubungi Customer Service kami melalui WhatsApp/Telp pada nomor: 085158836973 atau melalui online chat di www.rukun.co pada hari Senin‐Jum’at pukul 08:00 – 17:00 WIB.
                    </p>
                    <p style="padding: 30px 0;">
                        <strong>Salam,
                        <br></br>
                        Tim Rukun</strong>
                    </p>
                    <p>
                        Pesan ini dikirim melalui akun Wilayah {{$wil_nama}} pada Aplikasi Rukun.
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
