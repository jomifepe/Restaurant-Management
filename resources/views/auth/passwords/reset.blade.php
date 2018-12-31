<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restaurant Management - Password Reset</title>
    @yield('extrastyles')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">
</head>
<body>
    <div id="app">
        <PasswordReset></PasswordReset>
    </div>

    <script src="{{ URL::asset('js/email.js') }}"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</body>
</html>