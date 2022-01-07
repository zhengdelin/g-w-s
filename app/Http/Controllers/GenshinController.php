<?php

namespace App\Http\Controllers;

use App\Models\FiveStd;
use App\Models\FourStd;
use App\Models\ThreeStd;

use App\Models\FiveWpUp;
use App\Models\FourCrUp;
use App\Models\FourWpUp;

use App\Models\PoolInfo;
use App\Models\CrWpInfo;
use App\Models\DetailBoxPicture;
use App\Models\PoolImg;
use App\Models\Video;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use DateTime;
use DateTimeZone;

class GenshinController extends BaseController
{
    public function index(Request $request)
    {
        // dd((new DateTime("now", new DateTimeZone('Asia/Taipei')))->format('Y-m-d H:i:s'));
        foreach (PoolInfo::select('id', 'version', 'pool_name', 'cr_name')->get() as $i)
            $pool_all[$i->id] = ['cr_name' => $i->cr_name, 'version' => $i->version, 'pool_name' => $i->pool_name];
        if (!$request->session()->has('cur_pool'))
            session(['cur_pool' => count($pool_all)]);
        // dd($request->session()->all());
        $cur_pool = session('cur_pool');
        $img = PoolImg::where('pool_id', $cur_pool)->first();
        $std = PoolImg::where('pool_id', '0')->first();
        $main_img = [$img->cr_main_img, $img->wp_main_img, $std->cr_main_img];
        $thum_img = [
            $img->cr_thum_off_img, $img->wp_thum_off_img, $std->cr_thum_off_img,
            $img->cr_thum_on_img, $img->wp_thum_on_img, $std->cr_thum_on_img
        ];
        $synk_info = $this->get_synk_info($cur_pool);
        $pool_info = $this->get_pool_info($cur_pool);
        $pool_detail = $this->get_details($pool_info);
        foreach ($pool_info['five_wp_up'] as $i) {
            $wp_up_info[] = ['name' => $i, 'img' => CrWpInfo::select('img')->where('name', $i)->first()->img];
        }
        $pool_detail = $this->get_details($pool_info);
        $all_cr_wp = $this->get_all_cr_wp();
        if (!$request->session()->has('optional_pool'))
            session(['optional_pool' => $all_cr_wp]);
        $optional_pool = session('optional_pool');
        return view(
            'genshin.index',
            [
                'pool_all' => $pool_all,
                'cur_pool' => $cur_pool,
                'main_img' => $main_img,
                'thum_img' => $thum_img,
                'synk_info' => $synk_info,
                'pool_info' => $pool_info,
                'wp_up_info' => $wp_up_info,
                'pool_detail' => $pool_detail,
                'all_cr_wp' => $all_cr_wp,
                'optional_pool' => $optional_pool
            ]
        );
    }
    public function wish(Request $request)
    {
        $results = [];
        $selected = $request['selected'];
        $cur_pool = session('cur_pool', PoolInfo::select('id')->orderby('id', 'desc')->first()->id);

        // dd($request->session()->all());
        if ($selected == 0) {
            $pool_info = $this->get_pool_info($cur_pool, 'cr');
        } else if ($selected == 1) {
            $pool_info = $this->get_pool_info($cur_pool, 'wp');
            // dd($pool_info);
        } else {
            $pool_info = session('optional_pool');
        }
        switch ($request['wish_click']) {
            case 'single':
                if ($selected == 0) {
                    $results[] = $this->cr_single($pool_info);
                    // dd($request->session()->all());
                    // dd($results, $request->session()->all());
                } elseif ($selected == 1) {

                    $results[] = $this->wp_single($pool_info);
                } else {
                    $results[] = $this->opt_pool_single($pool_info);
                }
                break;
            case 'ten':
                if ($selected == 0) {
                    $results = $this->cr_ten($pool_info);
                } elseif ($selected == 1) {
                    $results = $this->wp_ten($pool_info);
                } else {
                    $results = $this->opt_pool_ten($pool_info);
                }
                break;
            default:
                break;
        }
        // dd($results);
        foreach (array_keys($results) as $i) {
            $name = $results[$i];
            if ($request->session()->has($name)) {
                $results[$i] = session($name);
                $results[$i]['new'] = false;
            } else {
                $results[$i] = CrWpInfo::select('name', 'star', 'img', 'kind')->where('name', $name)->first()->toArray();
                session([$name => $results[$i]]);
                $results[$i]['new'] = true;
            }


            // $cur_page = count(session("inventory.$selected", [])) == 0 ? 0 : count(session("inventory.$selected", [])) - 1;
            // $page = count(session("inventory.$selected.$cur_page", [])) == 10 ? $cur_page + 1 : $cur_page;
            // session(["inventory.$selected.$page." . count(session("inventory.$selected.$page", [])) => [
            //     'name' => $results[$i]['name'],
            //     'star' => $results[$i]['star'],
            //     'kind' => $results[$i]['kind'],
            //     'time' => (new DateTime("now", new DateTimeZone('Asia/Taipei')))->format('Y-m-d H:i:s')
            // ]]);
            session(["inventory.$selected." . count(session("inventory.$selected", [])) => [
                    'name' => $results[$i]['name'],
                    'star' => $results[$i]['star'],
                    'kind' => $results[$i]['kind'],
                    'time' => (new DateTime("now", new DateTimeZone('Asia/Taipei')))->format('Y-m-d H:i:s')
                ]]);
        }
        // dd(max(array_column($results, 'star')));
        $max = max(array_column($results, 'star'));
        if ($max == 3) {
            $video = Video::select('video')->where('kind', '3_single')->first()->video;
        } else {
            $video = Video::select('video')->where('kind', $max . '_' . $request['wish_click'])->first()->video;
        }

        // dd($results,$video, $request->session()->all());
        $synk_info = $this->get_synk_info($cur_pool);
        return response()->json(['results' => $results, 'video' => $video, 'synk_info' => $synk_info]);
    }
    public function wish_test(Request $request)
    {
        $pool_info = ['five' => ['琴'], 'four' => ['琴'], 'three' => ['琴']];

        $pool = 'optional_pool';
        $count['four'] = session("$pool.count.four", 0);
        $count['five'] = session("$pool.count.five", 0);
        //總數+1
        session(["$pool.count.total" => session("$pool.count.total", 0) + 1]);
        //五星機率
        $p5 = $this->cr_chance_5($count['five'] + 1);
        //四星機率
        $p4 = $this->cr_chance_4($count['four'] + 1);
        //機率
        $rand = $this->randFloat();

        $result = '';

        if (empty($pool_info['four']) && empty($pool_info['three'])) {
            $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
        } else if (empty($pool_info['five']) && empty($pool_info['three'])) {
            $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
        } else if (empty($pool_info['five']) && empty($pool_info['three'])) {
            $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
        } else if (empty($pool['three'])) {
            if ($rand <= $p5) {
                //設定五星抽數為0
                session(["$pool.count.five" => 0]);
                //四星抽數=9時不動，否則+1
                $count['four'] == 9 ?: session(["$pool.count.four" => $count['four'] + 1]);
                $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
            } else {
                //五星抽數+1,四星重製
                session(["$pool.count.five" => $count['five'] + 1]);
                session(["$pool.count.four" => 0]);
                $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
            }
        } else if (empty($pool['four'])) {
            if ($rand <= $p5) {
                //設定五星抽數為0
                session(["$pool.count.five" => 0]);
                $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
            } else {
                //五星抽數+1
                session(["$pool.count.five" => $count['five'] + 1]);
                $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
            }
        } else if (empty($pool['five'])) {
            if ($rand <= $p4) {
                //設定四星抽數為0
                session(["$pool.count.four" => 0]);
                $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
            } else {
                //四星抽數+1
                session(["$pool.count.four" => $count['four'] + 1]);
                $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
            }
        } else {
            if ($rand <= $p5) {
                //設定五星抽數為0
                session(["$pool.count.five" => 0]);
                //四星抽數=9時不動，否則+1
                $count['four'] == 9 ?: session(["$pool.count.four" => $count['four'] + 1]);
                $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
            } elseif ($rand <= ($p5 + $p4)) {
                //五星抽數+1,四星重製
                session(["$pool.count.five" => $count['five'] + 1]);
                session(["$pool.count.four" => 0]);
                $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
            } else {
                //三星
                $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
                session(["$pool.count.five" => $count['five'] + 1]);
                session(["$pool.count.four" => $count['four'] + 1]);
            }
        }
    }
    public function set_focus(Request $request)
    {
        // dd($request->all());
        $action = $request['action'];
        $cur_pool = session("cur_pool");
        if ($action == 'set') {
            $focus_item = $request['focus_item'];
            session(["pool$cur_pool.focus.item" => $focus_item]);
            session(["pool$cur_pool.focus.value" => 0]);
        } elseif ($action == 'unset') {
            session(["pool$cur_pool.focus.item" => '']);
            session(["pool$cur_pool.focus.value" => 0]);
        }
        $focus_info = $this->get_focus($cur_pool);
        return  response()->json(['focus_info' => $focus_info]);
    }
    public function set_cur_pool(Request $request)
    {
        $cur_pool = $request['cur_pool'];
        session(['cur_pool' => $cur_pool]);
        return redirect()->to('genshin');
    }
    public function reset(Request $request)
    {
        $action = $request['action'];
        if ($action == 'reset_all') {
            $request->session()->flush();
        } elseif ($action == 'reset_cur') {
            $cur_pool = session('cur_pool');
            $request->session()->forget("pool$cur_pool");
        }
        return redirect()->to('genshin');
    }
    public function set_optional_pool(Request $request)
    {
        $optional_pool = $request['optional_pool'];
        session(['optional_pool' => $optional_pool]);
        // session(["optional_pool.count.five" => 0]);
        // session(["optional_pool.count.four" => 0]);
        $request->session()->forget("inventory.2");
        return response()->json(['synk_info' => $this->get_synk_info(session('cur_pool'))]);
    }
    public function cr_single($pool_info)
    {

        //抽數
        $pool = 'pool' . session('cur_pool');
        $count['four'] = session("$pool.count.cr.four", 0);
        $count['five'] = session("$pool.count.cr.five", 0);
        //總數+1
        session(["$pool.count.cr.total" => session("$pool.count.cr.total", 0) + 1]);
        //大保底
        $b_g['four'] = session("$pool.b_g.cr.four", 0);
        $b_g['five'] = session("$pool.b_g.cr.five", 0);
        //五星機率
        $p5 = $this->cr_chance_5($count['five'] + 1);
        //四星機率
        $p4 = $this->cr_chance_4($count['four'] + 1);
        //機率
        $rand = $this->randFloat();

        $result = '';

        if ($rand <= $p5) {
            //設定五星抽數為0
            session(["$pool.count.cr.five" => 0]);
            //四星抽數=9時不動，否則+1
            $count['four'] == 9 ?: session(["$pool.count.cr.four" => $count['four'] + 1]);
            if ($b_g['five']) {
                //大保底，重製session五星大保底
                $result = $pool_info['five_cr_up'][0];
                session(["$pool.b_g.cr.five" => 0]);
            } else {
                //非大保底
                $r = mt_rand(0, 1);
                //0表示抽中當期up
                if ($r == 0) {
                    $result = $pool_info['five_cr_up'][0];
                } else {
                    $result = $pool_info['five_cr_std'][mt_rand(0, count($pool_info['five_cr_std']) - 1)];
                    session(["$pool.b_g.cr.five" => 1]);
                }
            }
        } elseif ($rand <= ($p5 + $p4)) {
            //五星抽數+1,四星重製
            session(["$pool.count.cr.five" => $count['five'] + 1]);
            session(["$pool.count.cr.four" => 0]);
            if ($b_g['four']) {
                //四星大保底，重製session四星大保底
                $result = $pool_info['four_cr_up'][mt_rand(0, count($pool_info['four_cr_up']) - 1)];
                session(["$pool.b_g.cr.four" => 0]);
            } else {
                // 小保底
                $r = mt_rand(0, 1);
                //0表示抽中當期up
                if ($r == 0) {
                    $result = $pool_info['four_cr_up'][mt_rand(0, count($pool_info['four_cr_up']) - 1)];
                } else {
                    $result = $pool_info['four_cr_std'][mt_rand(0, count($pool_info['four_cr_std']) - 1)];
                    session(["$pool.b_g.cr.four" => 1]);
                }
            }
        } else {
            //三星
            $result = $pool_info['three_std'][mt_rand(0, count($pool_info['three_std']) - 1)];
            session(["$pool.count.cr.five" => $count['five'] + 1]);
            session(["$pool.count.cr.four" => $count['four'] + 1]);
        }

        return $result;
    }
    public function cr_ten($pool_info)
    {
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $this->cr_single($pool_info);
        }
        return $results;
    }
    public function wp_single($pool_info)
    {
        $pool = 'pool' . session('cur_pool');

        $count['four'] = session("$pool.count.wp.four", 0);
        $count['five'] = session("$pool.count.wp.five", 0);
        //總數+1
        session(["$pool.count.wp.total" => session("$pool.count.wp.total", 0) + 1]);
        //大保底
        $b_g['four'] = session("$pool.b_g.wp.four", 0);
        $b_g['five'] = session("$pool.b_g.wp.five", 0);
        //定軌
        $focus = $this->get_focus(session('cur_pool'));
        $focus_item = $focus[0];
        //定軌次數
        $focus_value = $focus[1];
        //五星機率
        $p5 = $this->wp_chance_5($count['five'] + 1);
        //四星機率
        $p4 = $this->wp_chance_4($count['four'] + 1);
        //機率
        $rand = $this->randFloat();

        $result = '';

        if ($rand <= $p5) {
            //設定五星抽數為0
            session(["$pool.count.wp.five" => 0]);
            //四星抽數=9時不動，否則+1
            $count['four'] == 9 ?: session(["$pool.count.wp.four" => $count['four'] + 1]);
            if ($focus_value == 2) {
                $result = $focus_item;
                session(["$pool.b_g.wp.five" => 0]);
                session(["$pool.focus.value" => 0]);
            } else {
                if ($b_g['five']) {
                    $result = $pool_info['five_wp_up'][mt_rand(0, count($pool_info['five_wp_up']) - 1)];
                    if ($result == $focus_item)
                        session(["$pool.focus.value" => 0]);
                    elseif ($focus_item != '')
                        session(["$pool.focus.value" => session("$pool.focus.value", 0) + 1]);
                    session(["$pool.b_g.wp.five" => 0]);
                } else {
                    $r = mt_rand(0, 3);
                    if ($r < 3) {
                        $result = $pool_info['five_wp_up'][mt_rand(0, count($pool_info['five_wp_up']) - 1)];
                        if ($result == $focus_item)
                            session(["$pool.focus.value" => 0]);
                        elseif ($focus_item != '')
                            session(["$pool.focus.value" => session("$pool.focus.value", 0) + 1]);
                    } else {
                        $result = $pool_info['five_wp_std'][mt_rand(0, count($pool_info['five_wp_std']) - 1)];
                        session(["$pool.b_g.wp.five" => 1]);
                        if ($focus_item != '')
                            session(["$pool.focus.value" => session("$pool.focus.value", 0) + 1]);
                    }
                }
            }
        } elseif ($rand <= ($p5 + $p4)) {
            session(["$pool.count.wp.five" => $count['five'] + 1]);
            session(["$pool.count.wp.four" => 0]);
            if ($b_g['four']) {
                $result = $pool_info['four_wp_up'][mt_rand(0, count($pool_info['four_wp_up']) - 1)];
                session(["$pool.b_g.wp.four" => 0]);
            } else {
                $r = mt_rand(0, 3);
                if ($r < 3) {
                    $result = $pool_info['four_wp_up'][mt_rand(0, count($pool_info['four_wp_up']) - 1)];
                } else {
                    $result = $pool_info['four_wp_up'][mt_rand(0, count($pool_info['four_wp_up']) - 1)];
                    session(["$pool.b_g.wp.four" => 1]);
                }
            }
        } else {
            $result = $pool_info['three_std'][mt_rand(0, count($pool_info['three_std']) - 1)];
            session(["$pool.count.wp.five" => $count['five'] + 1]);
            session(["$pool.count.wp.four" => $count['four'] + 1]);
        }

        return $result;
    }
    public function wp_ten($pool_info)
    {
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $this->wp_single($pool_info);
        }
        return $results;
    }
    public function opt_pool_single($pool_info)
    {
        //合併四星、五星
        $pool_info['five'] = array_merge($pool_info['five_cr'], $pool_info['five_wp']);
        $pool_info['four'] = array_merge($pool_info['four_cr'], $pool_info['four_wp']);

        $pool = 'optional_pool';
        $count['four'] = session("$pool.count.four", 0);
        $count['five'] = session("$pool.count.five", 0);
        //總數+1
        session(["$pool.count.total" => session("$pool.count.total", 0) + 1]);
        //五星機率
        $p5 = $this->cr_chance_5($count['five'] + 1);
        //四星機率
        $p4 = $this->cr_chance_4($count['four'] + 1);
        //機率
        $rand = $this->randFloat();

        $result = '';

        if (empty($pool_info['four']) && empty($pool_info['three'])) {
            $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
        } else if (empty($pool_info['five']) && empty($pool_info['three'])) {
            $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
        } else if (empty($pool_info['five']) && empty($pool_info['four'])) {
            $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
        } else if (empty($pool_info['three'])) {
            if ($rand <= $p5) {
                //設定五星抽數為0
                session(["$pool.count.five" => 0]);
                //四星抽數=9時不動，否則+1
                $count['four'] == 9 ?: session(["$pool.count.four" => $count['four'] + 1]);
                $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
            } else {
                //五星抽數+1,四星重製
                session(["$pool.count.five" => $count['five'] + 1]);
                session(["$pool.count.four" => 0]);
                $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
            }
        } else if (empty($pool_info['four'])) {
            if ($rand <= $p5) {
                //設定五星抽數為0
                session(["$pool.count.five" => 0]);
                $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
            } else {
                //五星抽數+1
                session(["$pool.count.five" => $count['five'] + 1]);
                $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
            }
        } else if (empty($pool_info['five'])) {
            if ($rand <= $p4) {
                //設定四星抽數為0
                session(["$pool.count.four" => 0]);
                $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
            } else {
                //四星抽數+1
                session(["$pool.count.four" => $count['four'] + 1]);
                $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
            }
        } else {
            if ($rand <= $p5) {
                //設定五星抽數為0
                session(["$pool.count.five" => 0]);
                //四星抽數=9時不動，否則+1
                $count['four'] == 9 ?: session(["$pool.count.four" => $count['four'] + 1]);
                $result = $pool_info['five'][mt_rand(0, count($pool_info['five']) - 1)];
            } elseif ($rand <= ($p5 + $p4)) {
                //五星抽數+1,四星重製
                session(["$pool.count.five" => $count['five'] + 1]);
                session(["$pool.count.four" => 0]);
                $result = $pool_info['four'][mt_rand(0, count($pool_info['four']) - 1)];
            } else {
                //三星
                $result = $pool_info['three'][mt_rand(0, count($pool_info['three']) - 1)];
                session(["$pool.count.five" => $count['five'] + 1]);
                session(["$pool.count.four" => $count['four'] + 1]);
            }
        }
        return $result;
    }
    public function opt_pool_ten($pool_info)
    {
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $this->opt_pool_single($pool_info);
        }
        return $results;
    }
    public function cr_chance_5($count)
    {
        if ($count <= 73) {
            return 0.006;
        } elseif ($count <= 89) {
            return 0.006 + 0.06 * ($count - 73);
        } elseif ($count == 90) {
            return 1;
        } else {
            return 0;
        }
    }
    public function cr_chance_4($count)
    {
        if ($count <= 8) {
            return 0.051;
        } elseif ($count == 9) {
            return 0.562;
        } elseif ($count == 10) {
            return 1;
        } else {
            return 0;
        }
    }
    public function wp_chance_5($count)
    {
        if ($count <= 62) {
            return 0.007;
        } elseif ($count <= 73) {
            return 0.007 + 0.07 * ($count - 62);
        } elseif ($count <= 80) {
            return 0.007 + 0.35 * ($count - 73);;
        } else {
            return 0;
        }
    }
    public function wp_chance_4($count)
    {
        if ($count <= 7) {
            return 0.06;
        } elseif ($count == 8) {
            return 0.66;
        } elseif ($count == 9) {
            return 0.96;
        } elseif ($count == 10) {
            return 1;
        } else {
            return 0;
        }
    }
    public function randFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
    public function get_synk_info($cur_pool)
    {
        $total_count = [
            session("pool$cur_pool.count.cr.total", 0),
            session("pool$cur_pool.count.wp.total", 0),
            session("optional_pool.count.total", 0)
        ];
        $five_count = [
            session("pool$cur_pool.count.cr.five", 0),
            session("pool$cur_pool.count.wp.five", 0),
            session("optional_pool.count.five", 0)
        ];
        $four_count = [
            session("pool$cur_pool.count.cr.four", 0),
            session("pool$cur_pool.count.wp.four", 0),
            session("optional_pool.count.four", 0)
        ];
        $five_b_g = [
            session("pool$cur_pool.b_g.cr.five", 0),
            session("pool$cur_pool.b_g.wp.five", 0)
        ];
        $four_b_g = [
            session("pool$cur_pool.b_g.cr.four", 0),
            session("pool$cur_pool.b_g.wp.four", 0)
        ];
        $focus_info = $this->get_focus($cur_pool);
        $inventory = session("inventory", []);
        // dd($inventory);
        // if (!empty($inventory)) {
        //     foreach (array_keys($inventory) as $i) {
        //         foreach (array_keys($inventory[$i]) as $j) {
        //             $inventory[$i][$j] = array_reverse($inventory[$i][$j]);
        //         }
        //     }
        //     foreach (array_keys($inventory) as $i) {
        //         $inventory[$i] = array_reverse($inventory[$i]);
        //     }
        // }

        return [
            'total_count' => $total_count,
            'five_count' => $five_count,
            'four_count' => $four_count,
            'five_b_g' => $five_b_g,
            'four_b_g' => $four_b_g,
            'focus_info' => $focus_info,
            'inventory' => $inventory
        ];
    }
    public function get_pool_info($cur_pool, $kind = 'all')
    {
        $pool = PoolInfo::where('id', $cur_pool)->first();
        $pool_info['three_std'] = ThreeStd::pluck('name')->all();
        switch ($kind) {
            case 'all':
                $pool_info['five_cr_up'][] = $pool->cr_name;
                $pool_info['five_cr_std'] = FiveStd::where("five_std_id", $pool->five_std_id)->where('kind', 'cr')->pluck('name')->all();
                $pool_info['five_cr_std'] = array_diff($pool_info['five_cr_std'], $pool_info['five_cr_up']);
                $pool_info['four_cr_up'] = FourCrUp::where("pool_id", $pool->id)->pluck('name')->all();
                $pool_info['four_cr_std'] = FourStd::where("four_std_id", "<", $pool->four_std_id)->orderby('kind', 'asc')->pluck('name')->all();
                $pool_info['four_cr_std'] = array_diff($pool_info['four_cr_std'], $pool_info['four_cr_up']);
                $pool_info['five_wp_up'] = FiveWpUp::where('pool_id', $pool->id)->pluck('name')->all();
                $pool_info['five_wp_std'] = FiveStd::where("five_std_id", $pool->five_std_id)->where('kind', 'wp')->pluck('name')->all();
                $pool_info['five_wp_std'] = array_diff($pool_info['five_wp_std'], $pool_info['five_wp_up']);
                $pool_info['four_wp_up'] = FourWpUp::where("pool_id", $pool->id)->pluck('name')->all();
                $pool_info['four_wp_std'] = FourStd::where("four_std_id", "<", $pool->four_std_id)->orderby('kind', 'desc')->pluck('name')->all();
                $pool_info['four_wp_std'] = array_diff($pool_info['four_wp_std'], $pool_info['four_wp_up']);
                break;
            case 'cr':
                //五星角色Up
                $pool_info['five_cr_up'][] = $pool->cr_name;
                //五星角色常駐
                $pool_info['five_cr_std'] = FiveStd::where("five_std_id", $pool->five_std_id)->where('kind', 'cr')->pluck('name')->all();
                //常駐扣掉up，避免up為常駐
                $pool_info['five_cr_std'] = array_diff($pool_info['five_cr_std'], $pool_info['five_cr_up']);
                //四星角色up
                $pool_info['four_cr_up'] = FourCrUp::where("pool_id", $pool->id)->pluck('name')->all();
                //四星角色常駐
                $pool_info['four_cr_std'] = FourStd::where("four_std_id", "<", $pool->four_std_id)->orderby('kind', 'asc')->pluck('name')->all();
                //常駐扣掉up，避免up為常駐
                $pool_info['four_cr_std'] = array_diff($pool_info['four_cr_std'], $pool_info['four_cr_up']);
                break;
            case 'wp':
                //五星武器Up
                $pool_info['five_wp_up'] = FiveWpUp::where('pool_id', $pool->id)->pluck('name')->all();
                //五星武器常駐
                $pool_info['five_wp_std'] = FiveStd::where("five_std_id", $pool->five_std_id)->where('kind', 'wp')->pluck('name')->all();
                //常駐扣掉up，避免up為常駐
                $pool_info['five_wp_std'] = array_diff($pool_info['five_wp_std'], $pool_info['five_wp_up']);
                //四星武器up
                $pool_info['four_wp_up'] = FourWpUp::where("pool_id", $pool->id)->pluck('name')->all();
                //四星武器常駐
                $pool_info['four_wp_std'] = FourStd::where("four_std_id", "<", $pool->four_std_id)->orderby('kind', 'desc')->pluck('name')->all();
                //常駐扣掉up，避免up為常駐
                $pool_info['four_wp_std'] = array_diff($pool_info['four_wp_std'], $pool_info['four_wp_up']);
                break;
        }
        foreach (array_keys($pool_info) as $i) {
            if (substr($i, -3) == 'std') {
                $pool_info[$i] = array_values($pool_info[$i]);
            }
        }
        return $pool_info;
    }
    public function get_focus($cur_pool)
    {
        return [session("pool$cur_pool.focus.item", ''), session("pool$cur_pool.focus.value", 0)];
    }
    public function get_details($pool_info)
    {

        foreach ($pool_info as $i) {
            foreach ($i as $j) {
                $all[] = $j;
            }
        }
        $all = array_unique($all);
        foreach ($all as $i) {
            $pool_detail[$i] = CrWpInfo::select('name', 'star', 'kind', 'attribute', 'avatar', 'inv_avatar')->where('name', $i)->first()->toArray();
            switch ($pool_detail[$i]['attribute']) {
                case "火":
                    $attr_eng = 'fire';
                    break;
                case "水":
                    $attr_eng = 'water';
                    break;
                case "冰":
                    $attr_eng = 'ice';
                    break;
                case "風":
                    $attr_eng = 'wind';
                    break;
                case "岩":
                    $attr_eng = 'rock';
                    break;
                case "雷":
                    $attr_eng = 'thunder';
                    break;
                default:
                    $attr_eng = 'weapon';
                    break;
            }
            $pool_detail[$i]['attr_eng'] = $attr_eng;
            $pool_detail[$i]['kind'] = $pool_detail[$i]['kind'] == 'wp' ? '武器' : '角色';
            $pool_detail[$i]['detail_box_bg'] = DetailBoxPicture::select('img')->where('attribute', $pool_detail[$i]['attribute'])->first()->img;
        }
        // dd($pool_detail);

        return $pool_detail;
    }
    public function get_all_cr_wp()
    {
        $five_cr = CrWpInfo::where('star', 5)->where('kind', 'cr')->orderByRaw('CHAR_LENGTH(name)')->pluck('name')->toArray();
        $five_wp = CrWpInfo::where('star', 5)->where('kind', 'wp')->orderByRaw('CHAR_LENGTH(name)')->pluck('name')->toArray();
        $four_cr = CrWpInfo::where('star', 4)->where('kind', 'cr')->orderByRaw('CHAR_LENGTH(name)')->pluck('name')->toArray();
        $four_wp = CrWpInfo::where('star', 4)->where('kind', 'wp')->orderByRaw('CHAR_LENGTH(name)')->pluck('name')->toArray();
        $three = CrWpInfo::where('star', 3)->orderByRaw('CHAR_LENGTH(name)')->pluck('name')->toArray();
        $all_cr_wp = [
            'five_cr' => $five_cr,
            'five_wp' => $five_wp,
            'four_cr' => $four_cr,
            'four_wp' => $four_wp,
            'three' => $three
        ];
        return $all_cr_wp;
    }
}
