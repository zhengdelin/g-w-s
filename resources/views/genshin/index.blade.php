<!DOCTYPE html>
<html>

<head>
    <title>原神抽卡模擬器</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('css/app.css',true) }}" rel="stylesheet">

    <script>
        var thum_img = JSON.parse("{{ json_encode($thum_img) }}".replace(/&quot;/g, '"')); //縮圖
        var main_img = JSON.parse("{{ json_encode($main_img) }}".replace(/&quot;/g, '"')); //大圖
        var synk_info = JSON.parse("{{ json_encode($synk_info) }}".replace(/&quot;/g, '"')); //數量 & 保底
        var pool_info = JSON.parse("{{ json_encode($pool_info) }}".replace(/&quot;/g, '"')); //池子資訊
        var wp_up_info = JSON.parse("{{ json_encode($wp_up_info) }}".replace(/&quot;/g, '"')); //武器up資訊
        var total_count = synk_info['total_count']; //池子總數
        var five_count = synk_info['five_count']; //五星抽數
        var four_count = synk_info['four_count']; //四星抽數
        var five_b_g = synk_info['five_b_g']; //五星保底
        var four_b_g = synk_info['four_b_g']; //四星保底
        var focus_info = synk_info['focus_info']; //定軌
        var focus_selected = 0;
        if (focus_info[0]) {
            for (var i = 0; i < wp_up_info.length; i++) {
                if (wp_up_info[i]['name'] == focus_info[0])
                    focus_selected = i;
            }
        }
        var inventory = synk_info['inventory']; //歷史紀錄
        var old_inventory=JSON.parse(JSON.stringify(inventory));
        var all_cr_wp = JSON.parse("{{ json_encode($all_cr_wp) }}".replace(/&quot;/g, '"')); //所有角色武器
        var optional_pool = JSON.parse("{{ json_encode($optional_pool) }}".replace(/&quot;/g, '"'));
        var old_optional_pool = JSON.parse(JSON.stringify(optional_pool));
    </script>
</head>

<body>

    <div id="simu">
        {{-- 初始介面 --}}
        {{-- @{{ inventory }} --}}
        {{-- {{ dd(  $synk_info) }} --}}
        {{-- {{ dd($pool_info,$pool_detail,$optional_pool, $pool_all, $main_img, $thum_img, $synk_info, $wp_up_info, session()) }} --}}
        <div id='wish_interface' v-if="view=='index'" class='d-flex align-items-center'>
            {{-- 定軌 --}}
            <div class="settings_container d-flex justify-content-center align-items-center" v-if="set_focus||setting">
                <div class="w-60 row position-relative" v-if="set_focus">
                    <img class="p-0" src="/pictures/定軌圖2.png" alt="">
                    <div class="closebtn" @click="outSetFocus()"></div>
                    <div class="row position-absolute h-100 m-0">
                        <div class="d-flex flex-column align-items-center col-6">
                            <div></div>
                            <div class="focus-img-container d-flex justify-content-center">
                                <img v-for="i in wp_up_info.length" :src="wp_up_info[i-1]['img']"
                                    @click="FocusClick(i-1)">
                            </div>
                            <div class="focus-text">為<span
                                    class='focus-item'>@{{ wp_up_info[focus_clicked]['name'] }}</span>定軌</div>
                            <div class="confirmbtn" @click="SetFocus()"></div>
                        </div>
                        <div class="d-flex flex-column align-items-center col-6" v-if="focus_info[0]">
                            <div></div>
                            <img class="focus-img-container" :src="wp_up_info[focus_selected]['img']">
                            <div class="focus-text">命定值: <span class='focus-item'>@{{ focus_info[1] }}</span>/2
                            </div>
                            <div class="cancelbtn" @click="UnsetFocus()"></div>
                        </div>
                    </div>
                </div>
                <div class="setting text-center p-2 w-50" v-if="setting">
                    <div class="closebtn" @click="outSetting()"></div>
                    <h2 class="text-bold">設置</h2>
                    <form action="/genshin/set_cur_pool" method="POST">
                        @csrf
                        <div class="row container">
                            <div class="col-12">
                                <div class="text-left mb-4">
                                    <label for="cur_pool" class="text-bold mb-2">角色池選擇</label>
                                    <select name="cur_pool" class="form-control">
                                        @foreach ($pool_all as $id => $pool)
                                            <option value="{{ $id }}">
                                                {{ $pool['cr_name'] . ' ' . $pool['version'] . ' ' . $pool['pool_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mb-2 d-flex justify-content-evenly align-items-center">
                                <button type="button" class="btn_ac" @click="ResetCur()">重設本池</button>
                                <button type="button" class="btn_ac" @click="ResetAll()">重設全部</button>

                                <button type="submit" class="btn_ac">選擇卡池</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
            <div class="wish_interface_container d-flex flex-column justify-content-center align-items-center">
                <header id="first" class="d-flex justify-content-center align-items-center flex-wrap">
                    <h1>@{{ pool[selected] }}</h1>
                    <ul class="p-0 m-0 d-flex flex-row align-items-center list-unstyled">
                        <li v-for="i in pool.length" @click="change(i-1)">
                            <img :src="thum_img[i+2]" v-if="(i-1)==selected" :class="{sd:(i-1)==selected}">
                            <img :src="thum_img[i-1]" v-else>
                        </li>
                    </ul>
                </header>

                <section id="center" class="d-flex align-items-center justify-content-center">

                    <div class="focus" v-if="selected==1" @click="toSetFocus()"></div>
                    <div class="container d-flex flex-row overflow-hidden">
                        <div class="totalwish">@{{ total_count[selected] }}</div>
                        <div :style="moveit"></div>
                        <img v-for="i in main_img" :src="i" ref="pr_img">
                        <div class="guaranteed">
                            (@{{ five_count[selected] }}/@{{ selected == 1 ? 80 : 90 }})@{{ five_b_g[selected] ? '大保底' : '小保底' }}
                            <br>(@{{ four_count[selected] }}/10)@{{ four_b_g[selected] ? '大保底' : '小保底' }}
                        </div>
                        <div class="option_pool_btn" v-if="selected==2" @click="toSetOptionalPool()">設定</div>

                        <div class="optional_pool_container h-100 w-100 overflow-auto"
                            v-if="set_optional_pool&&selected==2">
                            <div class="closebtn" @click="outSetOptionalPool()"></div>
                            <div class="optional_pool_table text-bold d-flex flex-column">
                                <div class="d-flex">
                                    <div class="col-2 d-flex align-items-center justify-content-center">控制項</div>
                                    <div class="col d-flex flex-column align-items-center p-2">
                                        <div class="d-flex flex-wrap justify-content-center">

                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['all']}"
                                                @click="OptSelect('select_all')">
                                                全部選取</li>
                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['all_un']}"
                                                @click="OptSelect('unselect_all')">全部取消</li>
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-center">

                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['five_cr']}"
                                                @click="OptSelect('five_cr')">五星角色</li>
                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['five_wp']}"
                                                @click="OptSelect('five_wp')">五星武器</li>
                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['four_cr']}"
                                                @click="OptSelect('four_cr')">四星角色</li>
                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['four_wp']}"
                                                @click="OptSelect('four_wp')">四星武器</li>
                                            <li class="list-unstyled list_option m-1"
                                                :class="{opt_selected:selection['three']}"
                                                @click="OptSelect('three')">三星</li>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="col-2 d-flex align-items-center justify-content-center">五星角色</div>
                                    <div class="col d-flex flex-wrap justify-content-start p-2">
                                        <li v-for="i in all_cr_wp['five_cr']" class="list-unstyled list_option m-1"
                                            :class="{opt_selected:optional_pool['five_cr'].includes(i)}"
                                            @click="OptSelect(i,'five_cr')">
                                            @{{ i }}</li>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="col-2 d-flex align-items-center justify-content-center">五星武器</div>
                                    <div class="col d-flex flex-wrap justify-content-start p-2">
                                        <li v-for="i in all_cr_wp['five_wp']" class="list-unstyled list_option m-1"
                                            :class="{opt_selected:optional_pool['five_wp'].includes(i)}"
                                            @click="OptSelect(i,'five_wp')">@{{ i }}</li>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="col-2 d-flex align-items-center justify-content-center">四星角色</div>
                                    <div class="col d-flex flex-wrap justify-content-start p-2">
                                        <li v-for="i in all_cr_wp['four_cr']" class="list-unstyled list_option m-1"
                                            :class="{opt_selected:optional_pool['four_cr'].includes(i)}"
                                            @click="OptSelect(i,'four_cr')">@{{ i }}</li>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="col-2 d-flex align-items-center justify-content-center">四星武器</div>
                                    <div class="col d-flex flex-wrap justify-content-start p-2">
                                        <li v-for="i in all_cr_wp['four_wp']" class="list-unstyled list_option m-1"
                                            :class="{opt_selected:optional_pool['four_wp'].includes(i)}"
                                            @click="OptSelect(i,'four_wp')">@{{ i }}</li>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="col-2 d-flex align-items-center justify-content-center">三星物品</div>
                                    <div class="col d-flex flex-wrap justify-content-start p-2">
                                        <li v-for="i in all_cr_wp['three']" class="list-unstyled list_option m-1"
                                            :class="{opt_selected:optional_pool['three'].includes(i)}"
                                            @click="OptSelect(i,'three')">@{{ i }}</li>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="col-2 d-flex flex-column align-items-center justify-content-center">
                                        <span class="">
                                            按鈕(請<span class="color_point">儲存</span>)
                                        </span>
                                        <span class="color_point">※將重置自選池</span>
                                    </div>
                                    
                                    <div class="col d-flex flex-wrap justify-content-center align-items-center p-2">
                                        
                                        <button type="button" class="btn_ac" @click="OptSave()">儲存</button>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <footer id="last" class="d-flex justify-content-evenly">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn_ac" @click="toSetting()">設置</button>
                        <button type="button" class="btn_ac" @click="toDetail()">詳情</button>
                        <button type="button" class="btn_ac" @click="toHistory()">歷史紀錄</button>
                    </div>

                    <div class="wish d-flex">
                        {{-- <form action="/genshin/wish" method="POST" name='single'>
                            @csrf
                            <input type="hidden" name="wish_click" value="ten">
                            <input type="hidden" name="selected" value="2">
                        </form>
                        <div class="wish_button" onclick="return document.forms['single'].submit();">
                            祈願
                        </div> --}}
                        <div class="wish_button" @click="single()">
                            祈願
                        </div>
                        <div class="wish_button" @click="ten()">
                            十連祈願
                        </div>
                    </div>


                </footer>
            </div>
        </div>
        {{-- <div id='preview' v-if="dn==0"></div> --}}
        <div v-if="view=='result'">
            <div v-if="wish_click=='single'">
                <div id='wish_scene' v-if="!video_end">
                    <div class='skip_button' @click="VideoEnd()">Skip</div>
                    <video class="wish_video" autoplay muted @ended="VideoEnd()">
                        <source :src="video" type="video/mp4">
                    </video>
                </div>
                <div id="wish_result" v-else class='d-flex flex-column justify-content-center align-items-center'>
                    <div class="wish_result_container">
                        <div class="row">
                            <div class='col-md-12 d-flex justify-content-end align-items-center'>
                                <div class="close_button" @click="toIndex()"></div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="wish_item_container col-12 row align-items-center justify-content-center">
                                <span class="new-badge" v-if="results[0]['new']">New</span>
                                <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                                    {{-- <div style="color:yellow">@{{ Object.values(result)[0]}}</div> --}}
                                    <div>@{{ results[0]['name'] }}</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="star" v-for="i in results[0]['star']">★</div>
                                    </div>
                                </div>
                                <div class='wish_single_img' :style="wish_item_img[0]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div id='wish_scene' v-if="!video_end">
                    <div class='skip_button' @click="VideoEnd()">Skip</div>
                    <video class="wish_video" autoplay muted @ended="VideoEnd()">
                        <source :src="video" type="video/mp4">
                    </video>
                </div>
                <div id="wish_result" v-else class='d-flex flex-column justify-content-center align-items-center'>
                    <div class="wish_result_container ten">
                        <div class="row">
                            <div class='col-md-12 d-flex justify-content-end align-items-center'>
                                <div class="close_button" @click="toIndex()"></div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="wish_item_container_ten col-12 row align-items-center justify-content-center">
                                <div v-for="i in results.length"
                                    class='wish_ten_img d-flex flex-column align-items-center justify-content-end'
                                    :style="wish_item_img[i-1]">
                                    <span class="new-badge" v-if="results[i-1].new">New</span>
                                    <div>@{{ results[i - 1]['name'] }}</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="star" v-for="j in results[i-1]['star']">★</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id='inventory' class="d-flex justify-content-center align-items-center" v-if="view=='history'">
            <div class="container mx-80">
                <div class="closebtn" @click="toIndex()"></div>
                <div class="d-flex flex-wrap">
                    <div class="col-12 col-md me-4">
                        <label for="kind" class="text-bold m-1">祈願類型</label>
                        <select name="kind" class="text-left form-control text-bold" v-model="history_selected">
                            <option v-for="i in pool.length" :value="i-1">@{{ pool[i - 1] }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md me-4">
                        <label for="kind" class="text-bold m-1">排序依據</label>
                        <select name="kind" class="text-left form-control text-bold" v-model="history_order_type" @change="HistoryFilter()">
                            <option value="time">時間</option>
                            <option value="star">星級</option>
                            <option value="name">名稱</option>
                        </select>
                    </div>
                    <div class="col me-md-4">
                        <label for="kind" class="text-bold m-1">排序順序</label>
                        <select name="kind" class="text-left form-control text-bold" v-model="history_order" @change="HistoryFilter()">
                            <option value="desc">降序</option>
                            <option value="asc">升序</option>
                        </select>
                    </div>
                    <div class="col-6 col-md me-4">
                        <label for="kind" class="text-bold m-1">星級</label>
                        <select name="kind" class="text-left form-control text-bold" v-model="history_star" @change="HistoryFilter()">
                            <option value="all">全部</option>
                            <option value="5">五星</option>
                            <option value="4">四星</option>
                            <option value="3">三星</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="kind" class="text-bold m-1">種類</label>
                        <select name="kind" class="text-left form-control text-bold" v-model="history_kind" @change="HistoryFilter()">
                            <option value="all">全部</option>
                            <option value="cr">角色</option>
                            <option value="wp">武器</option>
                        </select>
                    </div>
                </div>
                <div class="row m-1 mt-4 mb-4 bg-white">
                    <div v-if="!inventory[history_selected] || inventory[history_selected].length==0" class="d-flex flex-column align-items-center p-2">
                        <span class="text-bold">༼ಢ_ಢ༽查無歷史紀錄༼ಢ_ಢ༽</span>
                        <img class="w-40" src="/pictures/raiden.png" alt="我的將軍大人，因為查不到歷史紀錄，氣得又哭又鬧嗚嗚嗚嗚好可憐啊.gif">
                    </div>
                    <table v-else class="table m-0 text-center col-12">
                        <tr class="table-active">
                            <th class="col-md-1 p-1">類型</th>
                            <th class="col-md-1 p-1">星級</th>
                            <th class="col-md-4 p-1">名稱</th>
                            <th class="col-md-2 p-1">祈願類型</th>
                            <th class="col-md-4 p-1">時間</th>
                        </tr>
                        <tr class="color_history text-bold"
                            v-for="j in inventory[history_selected][page[history_selected]]">
                            <td class="p-1">@{{ j['kind'] == 'cr' ? '角色' : '武器' }}</td>
                            <td class="p-1"><span v-for="z in j['star']"
                                    :class="{color_fourstar:j['star']==4,color_fivestar:j['star']==5}">★</span></td>
                            <td class="p-1"
                                :class="{color_fourstar:j['star']==4,color_fivestar:j['star']==5} ">
                                @{{ j['name'] }}
                            </td>
                            <td class="p-1">角色祈願</td>
                            <td class="p-1">@{{ j['time'] }}</td>
                        </tr>
                    </table>
                </div>
                <div v-if="inventory[history_selected]" class="d-flex justify-content-center">
                    <input type="button" class="history_control_btn" value="<" @click="PreviousPage()">
                    <select class="hisroty_page_control ms-2 me-2 text-center" v-model="page[history_selected]">
                        <option v-for="i in inventory[history_selected].length" :value="i-1">@{{ i }}
                        </option>
                    </select>
                    <input type="button" class="history_control_btn" value=">" @click="NextPage()">
                </div>
            </div>
        </div>
        <div id='detail' v-if="view=='detail'">
            <div class="container" v-if="selected==0">
                <div class="closebtn" @click="toIndex()"></div>
                <div class="pt-4 pb-3">
                    <div class="detail_title ps-4 py-2">
                        「<span
                            class="color_{{ $pool_detail[$pool_info['five_cr_up'][0]]['attr_eng'] }}">{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 0, 2) }}</span>{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 2) }}」活動祈願
                    </div>
                </div>
                <div class="row my-3">
                    <div class="col-4 d-flex align-items-center h4">以下內容抽取[機率UP!!!]</div>
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <div class="detail_five d-flex align-items-center p-2">
                    <div class="h4 my-0 mx-4 d-flex col-1">
                        <div class="star" v-for="i in 5">★</div>
                    </div>
                    <div>佔5星抽取率的: 50.000%</div>
                </div>
                <div class="row mx-0">
                    <div class="detail_box col-12 col-sm-6 col-md-4 d-flex d-lg-block justify-content-center my-3"
                        style="background-image: url('{{ $pool_detail[$pool_info['five_cr_up'][0]]['detail_box_bg'] }}')">
                        <div class="row align-items-center p-4 mx-0">
                            <div class="col-4">
                                <img class="mw-100"
                                    src="{{ $pool_detail[$pool_info['five_cr_up'][0]]['avatar'] }}"
                                    alt="{{ $pool_info['five_cr_up'][0] }}">
                            </div>
                            <div class="ps-4 col-8">
                                <div class="text-white">{{ $pool_info['five_cr_up'][0] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="detail_four d-flex align-items-center p-2">
                    <div class="h4 my-0 mx-4 d-flex col-1">
                        <div class="star" v-for="i in 4">★</div>
                    </div>

                    <div>佔4星抽取率的: 50.000%</div>
                </div>
                <div class="row mx-0">
                    @foreach ($pool_info['four_cr_up'] as $name)
                        <div class="detail_box col-12 col-sm-6 col-md-4 d-flex d-lg-block justify-content-center my-3"
                            style="background-image: url('{{ $pool_detail[$name]['detail_box_bg'] }}')">
                            <div class="row align-items-center p-4 mx-0">
                                <div class="col-4">
                                    <img class="mw-100" src="{{ $pool_detail[$name]['avatar'] }}"
                                        alt="{{ $name }}">
                                </div>
                                <div class="ps-4 col-8">
                                    <div class="text-white">{{ $name }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="row mt-4 ">
                    <div class="col-2 d-flex align-items-center h4">祈願詳情</div>
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <div class="detail_subtitle d-flex align-items-center p-1 ps-3 text-bold">
                    <div>限時活動</div>
                </div>
                <div class="py-3 detail_text">
                    <div>「<span
                            class="color_{{ $pool_detail[$pool_info['five_cr_up'][0]]['attr_eng'] }}">{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 0, 2) }}</span>{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 2) }}」
                        活動祈願已開放。活動期間內，<span class="color_point">限定</span>5星角色
                        <span
                            class="color_{{ $pool_detail[$pool_info['five_cr_up'][0]]['attr_eng'] }}">「{{ $pool_info['five_cr_up'][0] }}({{ $pool_detail[$pool_info['five_cr_up'][0]]['attribute'] }})」</span>
                        以及4星角色
                        @foreach ($pool_info['four_cr_up'] as $name)
                            <span
                                class="color_{{ $pool_detail[$name]['attr_eng'] }}">「{{ $name }}({{ $pool_detail[$name]['attribute'] }})」</span>
                            @if (!$loop->last)
                                <span>、</span>
                            @endif
                        @endforeach
                        的祈願抽取機率將<span class="color_point">大幅提升</span>！

                        <br>
                        <span class="color_point">※以上角色中，限定角色不會進入「奔行世間」常駐祈願。</span>
                    </div>
                    <span class="my-4 d-block">※一般情況下，所有角色或武器均使用基礎機率。觸發機率UP、保底等以具體規則為準。</span>
                    {{-- 祈願規則 --}}
                    <div>
                        <div class="d-inline-flex align-items-center ">
                            〓祈願規則〓
                        </div>
                        <br>
                        <span>【5星物品】</span>
                        <br>
                        <span>
                            在本期「<span
                                class="color_{{ $pool_detail[$pool_info['five_cr_up'][0]]['attr_eng'] }}">{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 0, 2) }}</span>{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 2) }}」
                            活動祈願中，五星角色祈願的基礎抽取機率為<span class="color_point">0.600%</span>，綜合抽取機率（含保底）為<span
                                class="color_point">1.600%</span>，最多<span
                                class="color_point">90</span>次祈願必定能透過保底抽出五星角色。
                        </span>
                        <br>
                        <span>
                            當祈願抽出5星角色時，有<span class="color_point">50.000%</span>的機率為本期UP角色
                            <span class="color_{{ $pool_detail[$pool_info['five_cr_up'][0]]['attr_eng'] }}">
                                「{{ $pool_info['five_cr_up'][0] }}({{ $pool_detail[$pool_info['five_cr_up'][0]]['attribute'] }})」
                            </span>
                            。如果本次祈願抽出的5星角色非本期UP角色，下次祈願抽出的5星角色<span class="color_point">必定</span>為本期5星UP角色。
                        </span>
                        <br>
                        <span>【4星物品】</span>
                        <br>
                        <span>
                            在本期「<span
                                class="color_{{ $pool_detail[$pool_info['five_cr_up'][0]]['attr_eng'] }}">{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 0, 2) }}</span>{{ mb_substr(substr($pool_all[$cur_pool]['pool_name'], 0, strpos($pool_all[$cur_pool]['pool_name'], '(')), 2) }}」
                            活動祈願中，4星物品祈願的基礎抽取機率為<span class="text-bold-color_point">5.100%</span>，4星角色祈願的基礎抽取機率為<span
                                class="color_point">2.550%</span>，4星武器祈願的基礎抽取機率為<span
                                class="color_point">2.550%</span>，4星物品祈願綜合抽取機率（含保底）為<span
                                class="color_point">13.000%</span>。
                            至多10次祈願必定能透過保底獲得4星以上物品，而透過保底獲得4星物品的機率為<span
                                class="color_point">99.400%</span>，獲得5星物品的機率則為<span
                                class="color_point">0.600%</span>。
                        </span>
                        <br>
                        <span>
                            當祈願抽出4星物品時，有<span class="color_point">50.000%</span>的機率為本期UP角色
                            @foreach ($pool_info['four_cr_up'] as $name)
                                <span
                                    class="color_{{ $pool_detail[$name]['attr_eng'] }}">「{{ $pool_detail[$name]['name'] }}({{ $pool_detail[$name]['attribute'] }})」</span>
                                @if (!$loop->last)
                                    <span>、</span>
                                @endif
                            @endforeach
                            中的一個。如果本次祈願抽出的4星物品非本期UP角色，下次祈願抽出的4星物品<span
                                class="color_point">必定</span>為本期4星UP角色。當祈願抽取到4星UP物品時，每個本期4星UP角色的抽取機率均等。
                        </span>
                    </div>

                    <div class="my-4">
                        <span>
                            獲得4星武器時，會同時獲得2個<span class="color_fivestar">無主的星輝</span>作為副產物；獲得3星武器時，會同時獲得15個<span
                                class="color_fourstar">無主的星塵</span>作為副產物。
                        </span>
                    </div>
                    <div>
                        <div class="d-inline-flex align-items-center ">
                            〓若獲得重複角色〓
                        </div>
                        <br>
                        <span>
                            無論透過何種方式（包含但不限於祈願、商城兌換、系統贈送等）第2~7次獲得相同5星角色時，每次將轉化為1個<span
                                class="color_fourstar">對應角色的命星</span>和10個<span
                                class="color_fivestar">無主的星輝</span>；第8次及之後獲得，將僅轉化為25個<span
                                class="color_fivestar">無主的星輝</span>。
                            <br>
                            無論透過何種方式（包含但不限於祈願、商城兌換、系統贈送等）第2~7次獲得相同4星角色時，每次將轉化為1個<span
                                class="color_fourstar">對應角色的命星</span>和2個<span
                                class="color_fivestar">無主的星輝</span>；第8次及之後獲得，將僅轉化為5個<span
                                class="color_fivestar">無主的星輝</span>。
                        </span>
                    </div>
                    <div class="my-4">
                        <span>
                            ※本祈願屬於「角色活動祈願」，「角色活動祈願」和「角色活動祈願-2」的祈願次數保底完全共用，會一直共同累計在「角色活動祈願」和「角色活動祈願-2」中，與其他祈願的祈願次數保底相互獨立計算，互不影響。
                        </span>
                    </div>
                    <div class="pb-5">
                        <div class="color_list py-3 h4 text-bold m-0">✦ 以下為祈願物品清單：</div>
                        <div class="detail_five d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 5">★</div>
                            </div>
                            <div>5星物品基礎抽取率：0.600%（含保底綜合抽取率：1.600%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                @php
                                    $five = array_merge($pool_info['five_cr_up'], $pool_info['five_cr_std']);
                                @endphp
                                @for ($i = 0; $i < count($five); $i += 2)
                                    <tr class="color_list text-bold">
                                        <td>{{ $pool_detail[$five[$i]]['kind'] }}</td>
                                        <td>{{ $pool_detail[$five[$i]]['name'] }}</td>
                                        @if ($i + 1 != count($five))
                                            <td>{{ $pool_detail[$five[$i + 1]]['kind'] }}</td>
                                            <td>{{ $pool_detail[$five[$i + 1]]['name'] }}</td>
                                        @endif

                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <div class="detail_four d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 4">★</div>
                            </div>
                            <div>4星物品基礎抽取率：5.100%（含保底綜合抽取率：13.000%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                @php
                                    $four = array_merge($pool_info['four_cr_up'], $pool_info['four_cr_std']);
                                @endphp
                                @for ($i = 0; $i < count($four); $i += 2)
                                    <tr class="color_list text-bold">
                                        <td>{{ $pool_detail[$four[$i]]['kind'] }}</td>
                                        <td>{{ $pool_detail[$four[$i]]['name'] }}</td>
                                        @if ($i + 1 != count($four))
                                            <td>{{ $pool_detail[$four[$i + 1]]['kind'] }}</td>
                                            <td>{{ $pool_detail[$four[$i + 1]]['name'] }}</td>

                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <div class="detail_three d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 3">★</div>
                            </div>
                            <div>3星物品基礎抽取率：94.300%（含保底綜合抽取率：85.400%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                @for ($i = 0; $i < count($pool_info['three_std']); $i += 2)
                                    <tr class="color_list text-bold">
                                        <td>{{ $pool_detail[$pool_info['three_std'][$i]]['kind'] }}</td>
                                        <td>{{ $pool_detail[$pool_info['three_std'][$i]]['name'] }}</td>
                                        @if ($i + 1 != count($pool_info['three_std']))
                                            <td>{{ $pool_detail[$pool_info['three_std'][$i + 1]]['kind'] }}</td>
                                            <td>{{ $pool_detail[$pool_info['three_std'][$i + 1]]['name'] }}</td>
                                        @endif

                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="container" v-if="selected==1">
                <div class="closebtn" @click="toIndex()"></div>
                <div class="pt-4 pb-3">
                    <div class="detail_title ps-4 py-2">
                        「<span class="color_orange">神鑄</span>賦形」活動祈願
                    </div>
                </div>
                <div class="row my-3">
                    <div class="col-4 d-flex align-items-center h4">以下內容抽取[機率UP!!!]</div>
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <div class="detail_five d-flex align-items-center p-2">
                    <div class="h4 my-0 mx-4 d-flex col-1">
                        <div class="star" v-for="i in 5">★</div>
                    </div>
                    <div>佔5星抽取率的: 75.000%</div>
                </div>
                <div class="row mx-0">
                    @foreach ($pool_info['five_wp_up'] as $name)
                        <div class="detail_box col-12 col-sm-6 col-md-4 d-flex d-lg-block justify-content-center my-3"
                            style="background-image: url('{{ $pool_detail[$name]['detail_box_bg'] }}')">
                            <div class="row align-items-center p-4 mx-0">
                                <div class="col-4">
                                    <img class="mw-100" src="{{ $pool_detail[$name]['avatar'] }}"
                                        alt="{{ $name }}">
                                </div>
                                <div class="ps-4 col-8">
                                    <div class="text-white">{{ $name }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="detail_four d-flex align-items-center p-2">
                    <div class="h4 my-0 mx-4 d-flex col-1">
                        <div class="star" v-for="i in 4">★</div>
                    </div>

                    <div>佔4星抽取率的: 75.000%</div>
                </div>
                <div class="row mx-0">
                    @foreach ($pool_info['four_wp_up'] as $name)
                        <div class="detail_box col-12 col-sm-6 col-md-4 d-flex d-lg-block justify-content-center my-3"
                            style="background-image: url('{{ $pool_detail[$name]['detail_box_bg'] }}')">
                            <div class="row align-items-center p-4 mx-0">
                                <div class="col-4">
                                    <img class="mw-100" src="{{ $pool_detail[$name]['avatar'] }}"
                                        alt="{{ $name }}">
                                </div>
                                <div class="ps-4 col-8">
                                    <div class="text-white">{{ $name }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="row mt-4 ">
                    <div class="col-2 d-flex align-items-center h4">祈願詳情</div>
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <div class="detail_subtitle d-flex align-items-center p-1 ps-3 text-bold">
                    <div>限時活動</div>
                </div>
                <div class="py-3 detail_text">
                    <div>「<span class="color_orange">神鑄</span>賦形」
                        活動祈願已開放。活動期間內，<span class="color_point">限定</span>5星武器
                        @foreach ($pool_info['five_wp_up'] as $name)
                            <span
                                class="color_fivestar">「{{ $pool_detail[$name]['attribute'] }}·{{ $name }}」</span>
                            @if (!$loop->last)
                                <span>、</span>
                            @endif
                        @endforeach
                        以及4星角色
                        @foreach ($pool_info['four_wp_up'] as $name)
                            <span
                                class="color_fourstar">「{{ $pool_detail[$name]['attribute'] }}·{{ $name }}」</span>
                            @if (!$loop->last)
                                <span>、</span>
                            @endif
                        @endforeach
                        的祈願抽取機率將<span class="color_point">大幅提升</span>！

                        <br>
                        <span class="color_point">※以上角色中，限定武器不會進入「奔行世間」常駐祈願。</span>
                    </div>
                    <span class="my-4 d-block">※一般情況下，所有角色或武器均使用基礎機率。觸發機率UP、保底等以具體規則為準。</span>
                    {{-- 祈願規則 --}}
                    <div>
                        <div class="d-inline-flex align-items-center ">
                            〓祈願規則〓
                        </div>
                        <br>
                        <span>【5星物品】</span>
                        <br>
                        <span>
                            在本期「<span class="color_orange">神鑄</span>賦形」
                            活動祈願中，五星武器祈願的基礎抽取機率為<span class="color_point">0.700%</span>，綜合抽取機率（含保底）為<span
                                class="color_point">1.850%</span>，最多<span
                                class="color_point">80</span>次祈願必定能透過保底抽出五星武器。
                        </span>
                        <br>
                        <span>
                            當祈願抽出5星武器時，有<span class="color_point">75.000%</span>的機率為本期UP武器
                            @foreach ($pool_info['five_wp_up'] as $name)
                                <span
                                    class="color_fivestar">「{{ $pool_detail[$name]['attribute'] }}·{{ $name }}」</span>
                                @if (!$loop->last)
                                    <span>、</span>
                                @endif
                            @endforeach
                            。如果本次祈願抽出的5星武器非本期UP角色，下次祈願抽出的5星武器<span class="color_point">必定</span>為本期5星UP武器。
                            在未透過命定值達到滿值抽取到定軌武器的情況下，當祈願抽取到5星UP物品時，每把本期5星UP武器的抽取機率均等。
                        </span>
                        <br>
                        <span>
                            在本期「<span class="color_orange">神鑄</span>賦形」活動祈願中，可使用「神鑄定軌」對本期5星UP武器進行定軌，定軌武器的選擇僅在本期「<span
                                class="color_orange">神鑄</span>賦形」活動祈願中生效。
                        </span>
                        <br>
                        <span>
                            在本期「<span
                                class="color_orange">神鑄</span>賦形」活動祈願中，使用「神鑄定軌」定軌武器後，當抽取到的5星武器為非目前的定軌武器時，即獲得1點命定值，命定值達到<span
                                class="color_point">滿值</span>後，在本祈願中獲得的下一把5星武器<span
                                class="color_point">必定</span>為目前的定軌武器。抽取到目前的定軌武器時，無論當下命定值是否達到滿值，都將會重置為<span
                                class="color_point">0</span>，重新累計。
                        </span>
                        <br>
                        <span>
                            未使用「神鑄定軌」定軌武器時，將<span class="color_point">不會</span>累積命定值。
                        </span>
                        <br>
                        <span>
                            定軌武器可進行更換或取消。更換或取消目前的定軌武器時，命定值將會重置為<span class="color_point">0</span>，重新累計。
                        </span>
                        <br>
                        <span>
                            ※本祈願中的命定值僅在本期「<span class="color_orange">神鑄</span>賦形」活動祈願中生效，祈願結束後命定值將會重置為<span
                                class="color_point">0</span>，重新累計。
                        </span>
                        <br>
                        <span>【4星物品】</span>
                        <br>
                        <span>
                            在本期「<span class="color_orange">神鑄</span>賦形」
                            活動祈願中，4星物品祈願的基礎抽取機率為<span class="text-bold-color_point">6.000%</span>，4星角色祈願的基礎抽取機率為<span
                                class="color_point">3.000%</span>，4星武器祈願的基礎抽取機率為<span
                                class="color_point">3.000%</span>，4星物品祈願綜合抽取機率（含保底）為<span
                                class="color_point">14.500%</span>。
                            至多10次祈願必定能透過保底獲得4星以上物品，而透過保底獲得4星物品的機率為<span
                                class="color_point">99.300%</span>，獲得5星物品的機率則為<span
                                class="color_point">0.700%</span>。
                        </span>
                        <br>
                        <span>
                            當祈願抽出4星物品時，有<span class="color_point">75.000%</span>的機率為本期UP武器
                            @foreach ($pool_info['four_wp_up'] as $name)
                                <span
                                    class="color_{{ $pool_detail[$name]['attr_eng'] }}">「{{ $pool_detail[$name]['attribute'] }}·{{ $pool_detail[$name]['name'] }}」</span>
                                @if (!$loop->last)
                                    <span>、</span>
                                @endif
                            @endforeach
                            中的一個。如果本次祈願抽出的4星物品非本期UP武器，下次祈願抽出的4星物品<span
                                class="color_point">必定</span>為本期4星UP武器。當祈願抽取到4星UP物品時，每把本期4星UP武器的抽取機率均等。
                        </span>
                    </div>

                    <div class="my-4">
                        <span>
                            祈願獲得5星武器時，會同時獲得10個<span class="color_fivestar">無主的星輝</span>作為副產物；獲得4星武器時，會同時獲得2個<span
                                class="color_fivestar">無主的星輝</span>作為副產物；獲得3星武器時，會同時獲得15個<span
                                class="color_fourstar">無主的星塵</span>作為副產物。
                        </span>
                    </div>
                    <div>
                        <div class="d-inline-flex align-items-center ">
                            〓若獲得重複角色〓
                        </div>
                        <br>
                        <span>
                            <br>
                            無論透過何種方式（包含但不限於祈願、商城兌換、系統贈送等）第2~7次獲得相同4星角色時，每次將轉化為1個<span
                                class="color_fourstar">對應角色的命星</span>和2個<span
                                class="color_fivestar">無主的星輝</span>；第8次及之後獲得，將僅轉化為5個<span
                                class="color_fivestar">無主的星輝</span>。
                        </span>
                    </div>
                    <div class="my-4">
                        <span>
                            ※本祈願屬於「武器活動祈願」，其祈願次數寶底會一直累計在「武器活動祈願」中，與其他祈願的祈願次數保底相互獨立計算，互不影響。
                        </span>
                    </div>
                    <div class="pb-5">
                        <div class="color_list py-3 h4 text-bold m-0">✦ 以下為祈願物品清單：</div>
                        <div class="detail_five d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 5">★</div>
                            </div>
                            <div>5星物品基礎抽取率：0.700%（含保底綜合抽取率：1.850%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                @php
                                    $five = array_merge($pool_info['five_wp_up'], $pool_info['five_wp_std']);
                                @endphp
                                @for ($i = 0; $i < count($five); $i += 2)
                                    <tr class="color_list text-bold">
                                        <td>{{ $pool_detail[$five[$i]]['kind'] }}</td>
                                        <td>{{ $pool_detail[$five[$i]]['name'] }}</td>
                                        @if ($i + 1 != count($five))
                                            <td>{{ $pool_detail[$five[$i + 1]]['kind'] }}</td>
                                            <td>{{ $pool_detail[$five[$i + 1]]['name'] }}</td>
                                        @endif

                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <div class="detail_four d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 4">★</div>
                            </div>
                            <div>4星物品基礎抽取率：6.000%（含保底綜合抽取率：14.500%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                @php
                                    $four = array_merge($pool_info['four_wp_up'], $pool_info['four_wp_std']);
                                @endphp
                                @for ($i = 0; $i < count($four); $i += 2)
                                    <tr class="color_list text-bold">
                                        <td>{{ $pool_detail[$four[$i]]['kind'] }}</td>
                                        <td>{{ $pool_detail[$four[$i]]['name'] }}</td>
                                        @if ($i + 1 != count($four))
                                            <td>{{ $pool_detail[$four[$i + 1]]['kind'] }}</td>
                                            <td>{{ $pool_detail[$four[$i + 1]]['name'] }}</td>

                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <div class="detail_three d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 3">★</div>
                            </div>
                            <div>3星物品基礎抽取率：93.300%（含保底綜合抽取率：83.650%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                @for ($i = 0; $i < count($pool_info['three_std']); $i += 2)
                                    <tr class="color_list text-bold">
                                        <td>{{ $pool_detail[$pool_info['three_std'][$i]]['kind'] }}</td>
                                        <td>{{ $pool_detail[$pool_info['three_std'][$i]]['name'] }}</td>
                                        @if ($i + 1 != count($pool_info['three_std']))
                                            <td>{{ $pool_detail[$pool_info['three_std'][$i + 1]]['kind'] }}</td>
                                            <td>{{ $pool_detail[$pool_info['three_std'][$i + 1]]['name'] }}</td>
                                        @endif

                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="container" v-if="selected==2">
                <div class="closebtn" @click="toIndex()"></div>
                <div class="pt-4 pb-3">
                    <div class="detail_title ps-4 py-2">
                        「奔行<span class="color_optional">世間</span>」<span class="color_point">自選</span>祈願
                    </div>
                </div>
                <div class="row">
                    <div class="col-2 d-flex align-items-center h4">祈願詳情</div>
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <div class="detail_subtitle d-flex align-items-center p-1 ps-3 text-bold">
                    <div>永久</div>
                </div>
                <div class="py-3 detail_text">
                    <div>「奔行<span class="color_optional">世間</span>」
                        <span class="color_point">自選</span>祈願為永久性祈願活動，玩家可自由選擇抽取所有角色及武器。
                        <br>
                        <span class="color_point">※至少需選擇1物品才能進行抽取</span>
                        <br>
                        <span class="color_point">※5星、4星、3星物品若有其一為空，機率仍然正常參照一般情況之機率</span>
                        <br>
                        在本祈願內，每10次祈願<span class="color_point">必會</span>獲得至少1個4星或以上物品。
                    </div>
                    <span class="my-4 d-block">※一般情況下，所有角色或武器均使用基礎機率。觸發機率UP、保底等以具體規則為準。</span>
                    {{-- 祈願規則 --}}
                    <div>
                        <div class="d-inline-flex align-items-center ">
                            〓祈願規則〓
                        </div>
                        <br>
                        <span>
                            5星物品祈願的基礎抽取機率為<span class="color_point">0.600%</span>，5星角色祈願的基礎抽取機率為<span
                                class="color_point">0.300%</span>，5星武器祈願的基礎抽取機率為<span
                                class="color_point">0.300%</span>，5星物品祈願的基礎抽取機率（含保底）為<span
                                class="color_point">1.600%</span>，最多<span
                                class="color_point">90</span>次祈願必定能透過保底抽出5星物品。
                        </span>
                        <br>
                        <span>
                            4星物品祈願的基礎抽取機率為<span class="color_point">5.100%</span>，4星角色祈願的基礎抽取機率為<span
                                class="color_point">2.550%</span>，5星武器祈願的基礎抽取機率為<span
                                class="color_point">2.550%</span>，5星物品祈願的基礎抽取機率（含保底）為<span
                                class="color_point">13.000%</span>，至多<span
                                class="color_point">10</span>次祈願必定能透過保底獲得4星或以上物品，而透過保底獲得4星物品的機率為<span
                                class="color_point">99.400%</span>，獲得5星物品的機率則為<span
                                class="color_point">0.600%</span>。
                        </span>
                        <br>
                        <span>
                            副產物：獲得3星武器時，會同時獲得15個<span class="color_fourstar">無主的星塵</span>作為副產物。
                        </span>

                    </div>


                    <div>
                        <div class="d-inline-flex align-items-center ">
                            〓若獲得重複角色〓
                        </div>
                        <br>
                        <span>
                            無論透過何種方式（包含但不限於祈願、商城兌換、系統贈送等）第2~7次獲得相同5星角色時，每次將轉化為1個<span
                                class="color_fourstar">對應角色的命星</span>和10個<span
                                class="color_fivestar">無主的星輝</span>；第8次及之後獲得，將僅轉化為25個<span
                                class="color_fivestar">無主的星輝</span>。
                            <br>
                            無論透過何種方式（包含但不限於祈願、商城兌換、系統贈送等）第2~7次獲得相同4星角色時，每次將轉化為1個<span
                                class="color_fourstar">對應角色的命星</span>和2個<span
                                class="color_fivestar">無主的星輝</span>；第8次及之後獲得，將僅轉化為5個<span
                                class="color_fivestar">無主的星輝</span>。
                        </span>
                    </div>

                    <div class="pb-5">
                        <div class="color_list py-3 h4 text-bold m-0">✦ 以下為祈願物品清單：</div>
                        <div class="detail_five d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 5">★</div>
                            </div>
                            <div>5星物品基礎抽取率：0.600%（含保底綜合抽取率：1.600%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                <tr class="color_list text-bold"
                                    v-for="i in Math.max(old_optional_pool['five_cr'].length,old_optional_pool['five_wp'].length)">
                                    <td>角色</td>
                                    <td v-if="i <= old_optional_pool['five_cr'].length">
                                        @{{ old_optional_pool['five_cr'][i - 1] }}</td>
                                    <td v-else></td>
                                    <td>武器</td>
                                    <td v-if="i <= old_optional_pool['five_wp'].length">
                                        @{{ old_optional_pool['five_wp'][i - 1] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="detail_four d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 4">★</div>
                            </div>
                            <div>4星物品基礎抽取率：5.100%（含保底綜合抽取率：13.000%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                <tr class="color_list text-bold"
                                    v-for="i in Math.max(old_optional_pool['four_cr'].length,old_optional_pool['four_wp'].length)">
                                    <td>角色</td>
                                    <td v-if="i <= old_optional_pool['four_cr'].length">
                                        @{{ old_optional_pool['four_cr'][i - 1] }}</td>
                                    <td v-else></td>
                                    <td>武器</td>
                                    <td v-if="i <= old_optional_pool['four_wp'].length">
                                        @{{ old_optional_pool['four_wp'][i - 1] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="detail_three d-flex align-items-center p-1">
                            <div class="h4 my-0 mx-4 d-flex col-2">
                                <div class="star" v-for="i in 3">★</div>
                            </div>
                            <div>3星物品基礎抽取率：94.300%（含保底綜合抽取率：85.400%）</div>
                        </div>
                        <table class="table text-center w-100 my-4" style="table-layout: fixed">
                            <tbody>
                                <tr class="table-active">
                                    <th>類型</th>
                                    <th>名稱</th>
                                    <th>類型</th>
                                    <th>名稱</th>
                                </tr>
                                <tr class="color_list text-bold" v-for="i in old_optional_pool['three'].length">
                                    <td v-if="i % 2!==0">武器</td>
                                    <td v-if="i % 2!==0">@{{ old_optional_pool['three'][i - 1] }}</td>
                                    <td v-if="i < optional_pool['three'].length && i%2!==0">武器</td>
                                    <td v-if="i < optional_pool['three'].length && i%2!==0">
                                        @{{ old_optional_pool['three'][i] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript" src="{{ asset('js/app.js',true) }}"></script>
</body>

</html>
