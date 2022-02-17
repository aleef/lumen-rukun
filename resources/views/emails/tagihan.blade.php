<!DOCTYPE html>
<html>
<head>
    <title>Rukun</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300&display=swap" rel="stylesheet">
</head>
<body>
	<p>
        Bapak/Ibu <strong>{{$warga_nama}}</strong> yang terhormat,
    </p>

    <p>
    Berikut adalah tagihan bulanan Anda untuk periode <strong>{{$periode_tagihan}}</strong>. Total Tagihan yang harus dibayarkan adalah sebesar <strong>Rp.{{number_format($total_tagihan,0)}}</strong> Tagihan ini mohon dibayarkan sebelum tanggal <strong>{{$tgl_jatuh_tempo}}</strong>.
    </p>

    <p>Untuk melakukan pembayaran secara online, silakan melalui Modul Tagihan Saya pada Aplikasi Rukun.</p>

    <p>Jika Anda memerlukan informasi lebih lanjut, silakan hubungi Customer Care Rukun melalui email: <a href="mailto:customercare@rukun.co">customercare@rukun.co</a>. Terima kasih.</p>

    <br>
    Hormat kami,
    <br><br><br>
    Pengurus Wilayah
    <br>
    {{$wil_nama}}

    <br><br>
    <i><strong>*Tagihan ini dicetak secara otomatis oleh Aplikasi Rukun</strong></i>

</body>
</html>
