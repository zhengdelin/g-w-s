<html>

<head>
    <title>新增卡池資訊</title>
    <meta charset="UTF-8">
    <style>
        * {
            margin-bottom: 10px;
            font-family: 'dfkai-sb';
        }

    </style>
</head>

<body>
    <form action="{{ url('datas/search_crwp') }}" method="POST">
        @csrf
        <label for="id">搜尋武器或角色：</label>
        <input type="text" name="name">
        <br>
        <button type="submit">送出</button>
    </form>
    @foreach ($datas as $data)
        <p>{{ $data['name'] }}</p>
    @endforeach

</body>

</html>
