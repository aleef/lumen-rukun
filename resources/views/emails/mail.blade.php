<!DOCTYPE html>
<html>
<head>
    <title>Rukun</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300&display=swap" rel="stylesheet">
</head>
<body>
	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td style="text-align: center;">
				<h1><img src="http://rukun.co/images/logo-small.png"></h1>
			</td>
		</tr>
	</table>

	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td  style="text-align: center;">
				<h1 style="font-family: 'Nunito', sans-serif; font-size: 22px; font-weight: bolder;">Undangan Rukun</h1>
			</td>
		</tr>
	</table>

	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 50px;">
		<tr>
			<td style="text-align: left; padding: 0 30px; font-family: 'Nunito', sans-serif;">
				<h2>Hi, {{ $name }}</h2>
				<p>{{ $message_1 }}, <br> {{ $message_2 }} </p><br><br>
				<p><span><a href='{{ url('') }}/warga/register-m/{{ $warga_undang_id }}/{{ $wil_id }}'> >> Register Warga </a></span></p><br><br>
				<p>Terima Kasih <br> rukun.co</p>
			</td>
		</tr>
	</table>

	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td style="text-align: center; padding: 40px 30px; font-family: 'Nunito', sans-serif; background-color: #333; color: #fff;">
				<p>Copyright 2021 Rukun.co. All Rights Reserved</p>
			</td>
		</tr>
	</table>

</body>
</html>
