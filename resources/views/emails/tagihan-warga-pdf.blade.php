<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Warga</title>
    <style>

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size:10px;
        }

        .main {
            border: 1px solid #000000;
            margin: 0px auto;
            width: 100%;
        }

        .footer {
            background-color: #41D0B6;
            width: 100%;
            text-align: center;
            color: #ffffff;
            font-weight: bold;
        }

        .sub-main {
            width: 95%;
            margin:0px auto;
        }

        .grey-container {
            background-color: #e9e9e9;
            width: 90%;
            padding:5px;
            border-radius: 0.8em;
            overflow: hidden;
            margin:0px auto;
        }

        .logo {
            float: left;
            width: 50%;
            text-align: center;
            margin-top:40px;
        }

        .clear{
            clear: both;
        }

        .divider50 {
            height: 50px;
            clear: both;
        }

        .divider20 {
            height: 20px;
            clear:both;
        }

        .divider10 {
            height: 10px;
            clear:both;
        }

        .italic {
            font-style: italic;
        }

        .underline {
            border-bottom: 1px dotted #000000;
            width:75%;
        }

        .header-deskripsi {
            background-color: #2c2a2a5d;
            color: #ffffff;
            font-weight: bold;
            font-size: 16;
            padding:10px;
            float:left;
            width: 70%;
            text-align: center;
        }

        .header-jumlah {
            margin-left:10px;
            background-color: #2c2a2a5d;
            color: #ffffff;
            font-weight: bold;
            font-size: 16;
            padding:10px;
            float:left;
            width: 23%;
            text-align: center;
        }


        .grey-black-header {
            background-color: #968f8f5d;
            color: #000000;
            font-weight: bold;
            padding:10px;
        }

        .grey-white-header {
            background-color: #3a3a3a5d;
            color: #ffffff;
            font-weight: bold;
            padding:10px;
        }

        .center-header {
            text-align: center;
        }

        .white-header {
            background-color: #ffffff;
            color: #000000;
        }

        table {
            border-radius: 1.2em;
            overflow: hidden;
        }

        .page-break {
            page-break-after: always;
        }

    </style>
</head>

<body>
@php
    $i = 1;
    $totalData = count($data);
@endphp

@foreach($data as $item)
<div class="main">
	<table border="0" width="100%">
		<tr>
			<td width="50%" align="center"><img src="{{$item['wil_logo']}}" width="64" height="64"></td>
			<td width="50%">
				<h1 class="italic underline">TAGIHAN BULANAN</h1>
				<h2>{{$item['wil_nama']}}</h2>
				<span>{{$item['wil_alamat']}}</span>
			</td>
		</tr>
	</table>
    <div class="divider20"></div>
    <div class="sub-main">
        <div class="grey-container">
            <table border="0" width="100%">
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        <table width="100%">
                            <tr>
                                <th class="grey-black-header center-header">Identitas Warga </th>
                            </tr>
                            <tr class="white-header">
                                <td>
                                    <div style="margin-left:15px;margin-bottom:15px;">
                                        <h3>{{$item['warga_nama']}}</h3>
                                        {{$item['warga_alamat']}}<br>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="50%" style="vertical-align: top;">
                        <table width="100%" style="margin-bottom: 5px;">
                            <tr>
                                <td class="grey-black-header" width="45%">No Tagihan</td>
                                <td class="white-header" width="55%">&nbsp;<strong>{{$item['order_no']}}</strong></td>
                            </tr>
                        </table>
                        <table width="100%" style="margin-bottom: 5px;">
                            <tr>
                                <td class="grey-black-header" width="45%">Periode Tagihan</td>
                                <td class="white-header" width="55%">&nbsp;<strong>{{$item['periode_tagihan']}}</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>

        <div class="divider20"></div>
        <div class="grey-container">
            <table border="0" width="100%">
                <tr>
                    <td width="30%" style="padding:5px;">
                        <table width="100%">
                            <tr>
                                <th class="grey-black-header">Tanggal Tagihan</th>
                            </tr>
                            <tr>
                                <td class="white-header" align="center">
                                    <h3>{{$item['tgl_tagihan']}}</h3>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="30%" align="center" style="padding:5px;">
                        <table width="100%">
                            <tr>
                                <th class="grey-black-header">Tanggal Jatuh Tempo</th>
                            </tr>
                            <tr>
                                <td class="white-header" align="center">
                                    <h3>{{$item['tgl_jatuh_tempo']}}</h3>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="30%" align="right" style="padding:5px;">
                        <table width="100%">
                            <tr>
                                <th class="grey-black-header">Jumlah</th>
                            </tr>
                            <tr>
                                <td class="white-header" align="center">
                                    <h3>Rp.{{number_format($item['tag_total'],0)}}</h3>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="divider20"></div>
        <div class="grey-container" style="background-image:url('')">
            <table  border="0" width="100%" style="margin:2px;">
                <tr>
                    <th class="grey-white-header" width="70%" style="font-size:14px;">Deskripsi</th>
                    <th class="grey-white-header" width="30%" style="font-size:14px;">Jumlah</th>
                </tr>
            </table>
            <div class="divider10"></div>
            <table cellpadding="10" width="100%" class="white-header" style="margin-left:2px;margin-right:2px;margin-bottom:2px;">
                <tr>
                    <td width="70%" style="font-size:12px;" valign="top"><strong>Iuran Pengelolaan Lingkungan</strong></td>
                    <td width="30%" style="font-size:12px;" align="right" valign="top"><strong>Rp.{{number_format($item['tag_ipl'],0)}}</strong></td>
                </tr>
                <tr>
                    <td width="70%" style="font-size:12px;" valign="top"><strong>Tagihan Listrik</strong>
                    <br>
                    {{$item['tag_listrik_kwh']}} Kwh x Rp.{{number_format($item['tag_listrik_per_kwh'],0)}} = Rp. {{number_format($item['tag_listrik_total_kwh'],0)}}
                    <br>
                    Abodemen Listrik = Rp.{{number_format($item['tag_listrik_abo'],0)}}
                    </td>
                    <td width="30%" style="font-size:12px;" align="right" valign="top"><strong>Rp.{{number_format($item['tag_listrik_total'],0)}}</strong></td>
                </tr>
				<tr>
                    <td width="70%" style="font-size:12px;" valign="top"><strong>Tagihan Air</strong>
                    <br>
                    {{$item['tag_air_m3']}} Kwh x Rp.{{number_format($item['tag_air_per_m3'],0)}} = Rp. {{number_format($item['tag_air_total_m3'],0)}}
                    <br>
                    Abodemen Air = Rp.{{number_format($item['tag_air_abo'],0)}}
                    </td>
                    <td width="30%" style="font-size:12px;" align="right" valign="top"><strong>Rp.{{number_format($item['tag_air_total'],0)}}</strong></td>
                </tr>
				<tr>
                    <td width="70%" style="font-size:12px;" valign="top"><strong>Tagihan Lain-lain</strong></td>
                    <td width="30%" style="font-size:12px;" align="right" valign="top"><strong>Rp.{{number_format($item['tag_lain'],0)}}</strong></td>
                </tr>
				<tr>
                    <td width="70%" style="font-size:12px;" valign="top"><strong>Denda</strong></td>
                    <td width="30%" style="font-size:12px;" align="right" valign="top"><strong>Rp.{{number_format($item['tag_denda'],0)}}</strong></td>
                </tr>
				<tr>
                    <td width="70%" style="font-size:12px;" valign="top"><strong>Total</strong></td>
                    <td width="30%" style="font-size:12px;" align="right" valign="top"><strong>Rp.{{number_format($item['tag_total'],0)}}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <img  style="opacity: 0.2;" src="{{ url('') }}/public/img/logo.png" width="200px">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
            </table>
        </div>
        <div class="divider50"></div>
    </div>

    <div class="clear"></div>
    <div class="divider20"></div>
    <div class="footer">
        <div class="divider10"></div>
        <span style="font-size:16px;">www.rukun.co | email: care@rukun.co</span>
        <div class="divider10"></div>
    </div>
    <div class="clear"></div>
</div>

@if(count($data) != $i)
@php $i = $i + 1; @endphp
<div class="page-break"></div>
@endif

@endforeach

</body>
</html>
