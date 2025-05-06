{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\layouts\empty.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Puedes incluir aquí otros recursos CSS si lo necesitas --}}
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>