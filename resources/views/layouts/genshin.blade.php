<!DOCTYPE html>
<html>

<head>
    <title>原神抽卡模擬器</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>

    <div id="simu_data">
        <div id='wish_interface' class='d-flex justify-content-center align-items-center'>
            @section('content')
                
            @show
        </div>
    </div>
    <script src="{{ asset('js/data.js') }}"></script>
</body>

</html>
