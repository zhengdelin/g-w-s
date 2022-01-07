<?php

namespace App\Http\Controllers;

use App\Models\CrWpInfo;
use App\Models\DetailBoxPicture;
use App\Models\FiveStd;
use App\Models\FiveWpUp;
use App\Models\FourCrUp;
use App\Models\FourStd;
use App\Models\FourWpUp;
use App\Models\PoolImg;
use App\Models\PoolInfo;
use App\Models\ThreeStd;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DataController extends BaseController
{
    public function index(Request $request)
    {
        foreach (PoolInfo::select('id', 'version', 'pool_name', 'cr_name')->get() as $i)
            $pool_all[$i->id] = ['cr_name' => $i->cr_name, 'version' => $i->version, 'pool_name' => $i->pool_name];
        if (!$request->session()->has('cur_pool'))
            session(['cur_pool' => count($pool_all)]);
        // dd($request->session()->all());
        $cur_pool = session('cur_pool');
        $img = PoolImg::where('pool_id', $cur_pool)->first();
        $main_img = [$img->cr_main_img, $img->wp_main_img];
        $thum_img = [
            $img->cr_thum_off_img, $img->wp_thum_off_img,
            $img->cr_thum_on_img, $img->wp_thum_on_img,
        ];
        return view(
            'genshin.data.index',
            [
                'pool_all' => $pool_all,
                'cur_pool' => $cur_pool,
                'main_img' => $main_img,
                'thum_img' => $thum_img,
            ]
        );
    }
    public function add_std()
    {
        $std = [
            '凱亞', '麗莎', '安柏',  '北斗',   '芭芭拉', '行秋', '砂糖',
            '重雲', '香菱', '班尼特', '菲謝爾',  '雷澤', '凝光', '諾艾爾',
        ];
        $wp = [
            '西風大劍', '西風長槍', '西風秘典', '西風劍', '西風獵弓',
            '匣裡滅辰', '匣裡龍吟', '雨裁',
            '祭禮大劍', '祭禮弓', '祭禮殘章', '祭禮劍',
            '笛劍', '絕弦', '鐘劍', '弓藏', '昭心', '流浪樂章',
        ];
        $add = ['迪奧娜', '辛焱', '蘿莎莉亞', '煙緋', '早柚', '九條裟羅', '托馬', '五郎'];

        foreach ($std as $i) {
            $f = new FourStd();
            $f->four_std_id = 1;
            $f->name = $i;
            $f->kind = 'cr';
            // $f->save();
            // dd($f);
        }
        foreach ($wp as $i) {
            $f = new FourStd();
            $f->four_std_id = 1;
            $f->name = $i;
            $f->kind = 'wp';
            // $f->save();
            // dd($f);
        }
        $c = 2;
        foreach ($add as $i) {
            $f = new FourStd();
            $f->four_std_id = $c;
            $c++;
            $f->name = $i;
            $f->kind = 'cr';
            // $f->save();
        }
        $fivecr = ['迪盧克', '莫娜', '琴', '刻晴', '七七'];
        $fivewp = [
            '天空之翼', '天空之卷', '天空之脊', '天空之傲', '天空之刃', '風鷹劍', '阿莫斯之弓', '四風原典', '和璞鳶', '狼的末路'
        ];
        foreach ($fivecr as $i) {
            $f = new FiveStd();
            $f->five_std_id = 1;
            $f->name = $i;
            $f->kind = 'cr';
            // $f->save();
            // dd($f);
        }
        foreach ($fivewp as $i) {
            $f = new FiveStd();
            $f->five_std_id = 1;
            $f->name = $i;
            $f->kind = 'wp';
            // $f->save();
            // dd($f);
        }
        $datas = FourStd::get();
        echo 'four_stds<br>';
        foreach ($datas as $i) {
            echo $i->four_std_id . ' ' . $i->name . ' ' . $i->kind . '<br>';
        }
        $datas = FiveStd::get();
        echo 'five_std<br>';
        foreach ($datas as $i) {
            echo $i->five_std_id . ' ' . $i->name . ' ' . $i->kind . '<br>';
        }
        $three=[
            '以理服人', '冷刃', '沐浴龍血的劍', '飛天御劍', '神射手之誓', '討龍英傑譚', '黑纓槍', '翡玉法球', '彈弓', '黎明神劍', '鐵影闊劍', '魔導緒論'
        ];
        foreach ($three as $i) {
            $f = new ThreeStd();
            $f->name = $i;
            $f->kind = 'wp';
            // $f->save();
            // dd($f);
        }
    }
    public function add_crwpimg()
    {
        $cr=[
            '風'=>['早柚','楓原萬葉','魈','溫迪','琴','砂糖'],
            '火'=>['托馬','宵宮','煙緋','胡桃','辛焱','可莉','迪盧克','香菱','安柏','班尼特'],
            '雷'=>['九條裟羅','雷電將軍','雷澤','菲謝爾','麗莎','刻晴','北斗'],
            '岩'=>['阿貝多','鍾離','凝光','	諾艾爾'],
            '水'=>['珊瑚宮心海','達達利亞','行秋','莫娜','芭芭拉'],
            '冰'=>['神里綾華','優菈','蘿莎莉亞','甘雨','迪奧娜','七七','凱亞','重雲'],

        ];
        foreach(glob('pictures/avatars/*.*') as $i){
            $name=substr($i,strrpos($i,'/')+1);
            $name=substr($name,0,strpos($name,'.'));
    
            $x[$name]='/'.$i;
        }
        // dd($x);
        foreach (glob('pictures/wish_img/*.png') as $i) {
            $s = substr(strrchr($i, '/'), 1);
            $star = $s[0];
            $kind = $s[1] == 'w' ? 'wp' : 'cr';
            $s = substr($s, 2);
            $name = '';
            foreach (str_split($s) as $j) {
                if ($j == 'w')
                    break;
                else
                    $name .= $j;
            }
            $img = "/$i";

            $info = new CrWpInfo();
            $info->star = $star;
            $info->name = $name;
            $info->img = $img;
            $info->kind = $kind;
            if($info->kind=='cr'){
                $info->avatar=array_key_exists($info->name,$x)?$x[$info->name]:null;
                $info->inv_avatar=array_key_exists('inv'.$info->name,$x)?$x['inv'.$info->name]:null;
            }else{
                $info->avatar=array_key_exists($info->name,$x)?$x[$info->name]:null;
                $info->inv_avatar=array_key_exists($info->name,$x)?$x[$info->name]:null;
            }

            // dd($cr)
            foreach(array_keys($cr) as $i){
                foreach($cr[$i] as $j){
                    if($j==$info->name){
                        $info->attribute=$i;
                    }
                }
            }
            foreach(glob('pictures/detail_box_picture/*.*') as $i){
                $name=substr($i,strrpos($i,'/')+1);
                $name=substr($name,0,strpos($name,'.'));
                $y[$name]='/'.$i;
                $d=new DetailBoxPicture();
                $d->attribute=$name;
                $d->img=$y[$name];
                // $d->save();
            }
            dd($y);
            // dd($info);
            // $info->save();
            // dd($info);
        }
        // dd($x);
    }  
    public function add_pool(Request $r)
    {
        $r->validate([
            'version'=>'required',
            'pool_name'=>'required',
            'cr_name'=>'required',
            'five_std_id'=>'required',
            'four_std_id'=>'required',
            'five_wp_up'=>'required',
            'four_cr_up'=>'required',
            'four_wp_up'=>'required',
        ]);
        $img = [];
        foreach (glob('pictures/pool_thum/*.png') as $i) {
            $s = substr(strrchr($i, '/'), 1);
            $y = mb_str_split($s);
            $t = '';
            $z = '';
            foreach ($y as $j) {
                if ($j == "武" || $j == "池")
                    break;
                else
                    $t .= $j;
            }
            foreach ($y as $j) {
                if (is_numeric($j)) {
                    $z .= $j;
                }
            }
            $t = $t . $z;
            if (mb_strpos($s, '武器')) { //武器
                $q = '';
                foreach ($y as $j) {
                    if (!is_numeric($j) && $j !== '武' && $j !== '器') {
                        $q .= $j;
                    }
                }
                if (mb_strpos($s, 'on')) {
                    $img[$t]['wp_thum_on_img'] = '/' . $i;
                    if ($z) {
                        $img[$t]['cr_thum_on_img'] = '/pictures/pool_thum/' . $q;
                    }
                } else if (mb_strpos($s, 'off')) {
                    $img[$t]['wp_thum_off_img'] = '/' . $i;
                    if ($z) {
                        $img[$t]['cr_thum_off_img'] = '/pictures/pool_thum/' . $q;
                    }
                } else {
                    echo 'error';
                }
            } else { //角色

                if (mb_strpos($s, 'on')) {
                    $img[$t]['cr_thum_on_img'] = '/' . $i;
                } else if (mb_strpos($s, 'off')) {
                    $img[$t]['cr_thum_off_img'] = '/' . $i;
                } else {
                    echo 'error';
                }
            }
        }
        foreach (glob('pictures/pool_main/*.*') as $i) {
            $s = substr(strrchr($i, '/'), 1);
            $y = mb_str_split($s);
            $t = '';
            $z = '';
            foreach ($y as $j) {
                if ($j == "武" || $j == "池")
                    break;
                else
                    $t .= $j;
            }
            foreach ($y as $j) {
                if (is_numeric($j))
                    $z .= $j;
            }
            $t = $t . $z;
            if (mb_strpos($s, '武器')) { //武器
                $img[$t]['wp_main_img'] = '/' . $i;
            } else { //角色
                $img[$t]['cr_main_img'] = '/' . $i;
            }
        }
        
        // dd(PoolInfo::where('id','3')->get());
        $poolinfo = new PoolInfo();
        $poolinfo->version = $r['version'];
        $poolinfo->pool_name = $r['pool_name'];
        $poolinfo->cr_name = $r['cr_name'];
        $poolinfo->five_std_id = $r['five_std_id'];
        $poolinfo->four_std_id = $r['four_std_id'];
        $poolinfo->save();
        foreach ($r['five_wp_up'] as $i) {
            $fivewpup = new FiveWpUp();
            $fivewpup->pool_id = $poolinfo->id;
            $fivewpup->name = $i;
            $fivewpup->save();
        }
        foreach ($r['four_cr_up'] as $i) {
            $fourcrup = new FourCrUp();
            $fourcrup->pool_id = $poolinfo->id;
            $fourcrup->name = $i;
            $fourcrup->save();
        }
        foreach ($r['four_wp_up'] as $i) {
            $fourwpup = new FourWpUp();
            $fourwpup->pool_id = $poolinfo->id;
            $fourwpup->name = $i;
            $fourwpup->save();
        }
        $count= PoolInfo::where('cr_name',$poolinfo->cr_name)->count();
        $name=$count=='1'?$poolinfo->cr_name:$poolinfo->cr_name.$count;
        $poolimg = new PoolImg();
        $poolimg->pool_id = $poolinfo->id;
        $poolimg->cr_main_img = $img[$name]['cr_main_img'];
        $poolimg->cr_thum_on_img = $img[$name]['cr_thum_on_img'];
        $poolimg->cr_thum_off_img = $img[$name]['cr_thum_off_img'];
        $poolimg->wp_main_img = $img[$name]['wp_main_img'];
        $poolimg->wp_thum_on_img = $img[$name]['wp_thum_on_img'];
        $poolimg->wp_thum_off_img = $img[$name]['wp_thum_off_img'];
        $poolimg->save();
        // dd($poolinfo,$fivewpup,$fourcrup,$fourwpup);
        return redirect()->to('genshin/data/index')->with('success', true);
    }
    public function video()
    {
        $video = [
            '3_single' => '/videos/3starwish-single.mp4',
            '4_single' => '/videos/4starwish-single.mp4',
            '5_single' => '/videos/5starwish-single.mp4',
            '4_ten' => '/videos/4starwish.mp4',
            '5_ten' => '/videos/5starwish.mp4'
        ];
        foreach(array_keys($video) as $i){
            $v=new Video;
            $v->kind=$i;
            $v->video=$video[$i];
            // $v->save();
        }
    }
    public function search(Request $r)
    {
        $datas=session('datas',[]);
        return view('genshin.data.search',['datas'=>$datas]);
    }
    public function search_crwp(Request $r)
    {
        $name=$r['name'];
        $datas=CrWpInfo::where('name','like',"%$name%")->orderby('star','desc')->get()->toArray();

        return redirect()->to('genshin/data/search')->with('datas',$datas);
    }
    public function set_cur_pool(Request $request)
    {
        $cur_pool = $request['cur_pool'];
        session(['cur_pool' => $cur_pool]);
        return redirect()->to('genshin/data/index');
    }
}
