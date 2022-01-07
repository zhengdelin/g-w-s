<html>
<head>
    <title>新增卡池資訊</title>
    <meta charset="UTF-8">
    <style>
        *{
            margin-bottom: 10px;
            font-family: 'dfkai-sb';
        }
    </style>
</head>

<body>
    <form action="{{ url('datas/add_pool') }}" method="POST">
        @csrf
        <label for="version">版本：</label>
        <select name="version" id="version">
            <option value="1.0">1.0</option>
            <option value="1.1">1.1</option>
            <option value="1.2">1.2</option>
            <option value="1.3">1.3</option>
            <option value="1.4">1.4</option>
            <option value="1.5">1.5</option>
            <option value="1.6">1.6</option>
            <option value="2.0">2.0</option>
            <option value="2.1">2.1</option>
            <option value="2.2">2.2</option>
            <option value="2.3">2.3</option>
        </select>
        <br>

        <label for="pool_name">卡池名稱：</label>
        <input type="text" name="pool_name" id="pool_name" size="150" value="{{ old('pool_name') }}">
        <br>

        <label for="cr_name">角色up：</label>
        @foreach (App\Models\CrWpInfo::where('kind', 'cr')->where('star','5')->get() as $i)
            <input type="radio" name="cr_name" id="{{ $i->name }}" value="{{ $i->name }}">
            <label for="{{ $i->name }}">{{ $i->name }}</label>
        @endforeach
        <br>

        <label for="five_std_id">五星常駐Id：</label>
        <select name="five_std_id" id="five_std_id">
            <option value="1">初版</option>
        </select>
        <br>

        <label for="four_std_id">四星常駐Id：</label>
        <input type="radio" name="four_std_id" id="origin" value="1">
            <label for="origin">初始</label>
        @foreach (App\Models\FourStd::where('four_std_id','>', '1')->get() as $i)
            <input type="radio" name="four_std_id" id="{{ $i->name }}" value="{{ $i->four_std_id }}">
            <label for="{{ $i->name }}">{{ $i->name }}</label>
        @endforeach
        <br>

        <label for="five_wp_up">五星武器up：</label>
        @foreach (App\Models\CrWpInfo::where('kind', 'wp')->where('star','5')->get() as $i)
            <input type="checkbox" name="five_wp_up[]" id="{{ $i->name }}" value="{{ $i->name }}">
            <label for="{{ $i->name }}">{{ $i->name }}</label>
        @endforeach
        <br>

        <label for="four_cr_up">四星角色up：</label>
        @foreach (App\Models\CrWpInfo::where('kind', 'cr')->where('star','4')->get() as $i)
            <input type="checkbox" name="four_cr_up[]" id="{{ $i->name }}" value="{{ $i->name }}">
            <label for="{{ $i->name }}">{{ $i->name }}</label>
        @endforeach
        <br>
        <label for="four_wp_up">四星武器up：</label>
        @foreach (App\Models\CrWpInfo::where('kind', 'wp')->where('star','4')->get() as $i)
            <input type="checkbox" name="four_wp_up[]" id="{{ $i->name }}" value="{{ $i->name }}">
            <label for="{{ $i->name }}">{{ $i->name }}</label>
        @endforeach
        <br>

        <button type="submit">送出</button>
        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            <p>新增成功！！</p>
                {{ App\Models\PoolInfo::orderby('id','desc')->first()->version }}
                {{ App\Models\PoolInfo::orderby('id','desc')->first()->pool_name }} 
                {{ App\Models\PoolInfo::orderby('id','desc')->first()->cr_name }}  
            <br>
            @foreach (App\Models\FiveWpUp::where('pool_id',App\Models\PoolInfo::orderby('id','desc')->first()->id)->get() as $i)
               {{ $i->name }} 
            @endforeach
            <br>
            @foreach (App\Models\FourCrUp::where('pool_id',App\Models\PoolInfo::orderby('id','desc')->first()->id)->get() as $i)
                {{ $i->name }} 
            @endforeach
            <br>
            @foreach (App\Models\FourWpUp::where('pool_id',App\Models\PoolInfo::orderby('id','desc')->first()->id)->get() as $i)
                {{ $i->name }} 
            @endforeach
            <br>
            @foreach (App\Models\PoolImg::where('pool_id',App\Models\PoolInfo::orderby('id','desc')->first()->id)->get() as $i)
                <img src="{{ $i->cr_main_img }}" alt="">
            @endforeach
        @endif
    </form>
    
</body>

</html>
