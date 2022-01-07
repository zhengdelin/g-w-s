import axios from 'axios';
import { createApp } from "vue";
const genshin = {
    data() {
        return {

            pool: ["角色祈願", "武器祈願", "自選祈願"],
            main_img: main_img, //現在池子大圖
            thum_img: thum_img, //現在池子縮圖(角色關,五器關,,角色開,武器開,
            pool_info: pool_info,
            selected: 0, //角色池:0, 武器池:1, 常駐池:2=
            history_selected: 0,
            history_order: 'desc',
            history_order_type: 'time',
            history_star: 'all',
            history_kind: 'all',
            view: 'index',
            video_end: false,
            video: '',
            wish_click: '',
            total_count: total_count,
            five_count: five_count,
            four_count: four_count,
            five_b_g: five_b_g,
            four_b_g: four_b_g,
            inventory: inventory,
            old_inventory: old_inventory,
            results: [],
            wish_item_img: [{}],
            wp_up_info: wp_up_info,
            set_focus: false,
            set_optional_pool: false,
            focus_clicked: 0,
            focus_selected: focus_selected,
            focus_info: focus_info,
            setting: false,
            page: [0, 0, 0],
            all_cr_wp: all_cr_wp,
            optional_pool: optional_pool,
            old_optional_pool: old_optional_pool,
            selection: {},
            moveit: { //slider
                'margin-left': '0',
                'width': '0px',
                'transition': '0.3s linear'
            },
            // bg_pos_x: {
            //     '諾艾爾': '59%',
            //     '行秋': '58%',
            //     '北斗': '53%',
            //     '心海': '63%',
            //     '刻晴': '58%',
            //     '琴': '57%',
            //     '莫娜': '58%',
            //     '七七': '64%',
            //     '迪盧克': '57%',
            //     '凱亞': '61%',
            //     '迪奧娜': '53%',
            //     '麗莎': '61%',
            //     '芭芭拉': '58%',
            //     '煙緋': '56%',
            //     '重雲': '57%',
            //     '香菱': '56%',
            //     '砂糖': '60%',
            //     '安柏': '63%',
            //     '班尼特': '59%',
            //     '凝光': '59%',
            //     '雷澤': '44%',
            //     '九條裟羅': '62%',
            //     '菲謝爾': "61%",
            //     '辛焱': '62%'
            // },
        }
    },
    methods: {
        toResultView() {
            this.view = 'result';
        },
        VideoEnd() {
            this.video_end = true;
        },
        toIndex() {
            this.view = 'index';
        },
        toSetFocus() {
            this.set_focus = true;
        },
        outSetFocus() {
            this.set_focus = false;
        },
        toSetOptionalPool() {
            this.set_optional_pool = true;
        },
        outSetOptionalPool() {
            this.set_optional_pool = false;
            this.optional_pool = JSON.parse(JSON.stringify(this.old_optional_pool));
            this.OptSelect();
        },
        toSetting() {
            this.setting = true;
        },
        outSetting() {
            this.setting = false;
        },
        toHistory() {
            this.history_selected = this.selected;
            this.view = 'history';
        },
        toDetail() {
            this.view = 'detail';
        },
        change(val) {
            this.moveit['margin-left'] = (0 - val) * this.$refs.pr_img[0].clientWidth + "px";
            this.selected = val;
        },
        single() {
            this.wish_click = 'single';
            axios.post('genshin/wish', { 'wish_click': this.wish_click, 'selected': this.selected }).then(response => {
                this.view = 'result';//result介面
                this.video_end = false;//播放影片
                var synk_info = response['data']['synk_info'];
                this.Synk(synk_info);//同步
                this.results = response['data']['results'];
                this.wish_item_img[0] = {//style設定
                    "background-image": "url(" + this.results[0]['img'] + ")",
                    "background-size": this.results[0]["kind"] == "wp" ? "inherit" : "contain"
                };
                this.video = response['data']['video'];//video
            }, () => {
                alert('失敗');
            })
        },
        ten() {
            this.wish_click = 'ten';
            axios.post('genshin/wish', { 'wish_click': this.wish_click, 'selected': this.selected }).then(response => {
                this.view = 'result';
                this.video_end = false;
                var synk_info = response['data']['synk_info'];
                this.Synk(synk_info);
                this.video = response['data']['video'];
                var r = [[], [], [], [], [], [], [], [], [], [],];
                var j = 0;
                var done = {};
                var results_info = response['data']['results'];
                this.results = [];
                for (var i = 0; i < 10; i++) {//依照名字分群
                    var cur = results_info[i]['name'];
                    if (!Object.keys(done).includes(cur)) {//當key不含cur，就在done[cur]處新增j，表示cur是第幾個
                        done[cur] = j;
                        j += 1;
                    }
                    r[done[cur]].push(results_info[i]);//在r的cur的位置的陣列新增現在的result
                };
                r = r.sort((a, b) => {//依照分好群的做排列，數量多的排前面
                    return a.length < b.length ? 1 : -1;
                });
                for (var i = 0; i < j; i++) {//遍歷分群內的所有陣列做排序，new的排前面
                    r[i].sort((a, b) => {
                        return b.new == true ? 1 : -1;
                    });
                };
                for (var i = 0; i < j; i++) {//將排好順序的結果加入results
                    r[i].forEach(a => {
                        this.results.push(a);
                        this.results[this.results.length - 1]['count'] = r[i].length;//在當前results處新增count(數量)
                    });
                };

                this.results = this.results.sort(this.WishSort);//排序
                for (var i = 0; i < 10; i++) {
                    this.wish_item_img[i] = {
                        'background-image': 'url(' + this.results[i]['img'] + ')',
                        'background-size': this.results[i]['kind'] == 'cr' ? 'cover' : 'contain',
                        'background-position-x': '57%'
                    };
                };
                // document.write(JSON.stringify(this.results, null, 4));
            }, () => {
                alert('失敗');
            })
        },
        Synk(synk_info) {//同步抽數、保底
            this.total_count = synk_info['total_count'];//池子總數
            this.five_count = synk_info['five_count'];//五星抽數
            this.four_count = synk_info['four_count'];//四星抽數
            this.five_b_g = synk_info['five_b_g'];//五星保底
            this.four_b_g = synk_info['four_b_g'];//四星保底
            this.focus_info = synk_info['focus_info'];
            this.inventory = synk_info['inventory'];
            this.old_inventory = JSON.parse(JSON.stringify(this.inventory));
            this.HistoryFilter();
        },
        WishSort(a, b) {
            if (a.star > b.star)
                return -1;
            else if (a.star < b.star)
                return 1;
            else {
                if (this.selected == 0 || this.selected == 1) {
                    var isup = false;
                    Object.keys(this.pool_info).forEach(cato => {
                        if (cato.includes('up')) {
                            if (this.pool_info[cato].includes(b.name))
                                isup = true;
                        }
                    });
                    if (isup)
                        return 1;
                    else {
                        if (a.kind > b.kind)
                            return 1;
                        else if (a.kind < b.kind)
                            return -1;

                        else
                            return 0;
                    }
                } else {
                    if (a.kind > b.kind)
                        return 1;
                    else if (a.kind < b.kind)
                        return -1;

                    else
                        return 0;
                }

            }
        },
        FocusClick(n) {
            this.focus_clicked = n;
        },
        SetFocus() {
            axios.post('genshin/set_focus', { 'focus_item': this.wp_up_info[this.focus_clicked]['name'], 'action': 'set' }).then(response => {
                this.focus_info = response['data']['focus_info'];
                this.focus_selected = this.focus_clicked;
            }, () => {
                alert('失敗');
            })
        },
        UnsetFocus() {
            axios.post('genshin/set_focus', { 'action': 'unset' }).then(response => {
                this.focus_info = response['data']['focus_info'];
            }, () => {
                alert('失敗');
            })
        },
        ResetCur() {
            axios.post('genshin/reset', { 'action': 'reset_cur' }).then(response => {
                window.location.reload();
            }, () => {
                alert('失敗');
            })
        },
        ResetAll() {
            axios.post('genshin/reset', { 'action': 'reset_all' }).then(response => {
                window.location.reload();
            }, () => {
                alert('失敗');
            })
        },
        OptSelect(name, group) {
            switch (name) {
                case undefined:
                    break;
                case 'select_all':
                    (Object.keys(this.optional_pool)).forEach(i => {
                        this.optional_pool[i] = JSON.parse(JSON.stringify(this.all_cr_wp[i]));
                    });
                    break;
                case 'unselect_all':
                    (Object.keys(this.optional_pool)).forEach(i => {
                        this.optional_pool[i] = [];
                    });
                    break;
                case 'five_cr':
                    if (this.optional_pool['five_cr'].length < this.all_cr_wp['five_cr'].length) {
                        this.optional_pool['five_cr'] = JSON.parse(JSON.stringify(this.all_cr_wp['five_cr']));
                    } else {
                        this.optional_pool['five_cr'] = [];
                    }
                    break;
                case 'five_wp':
                    if (this.optional_pool['five_wp'].length < this.all_cr_wp['five_wp'].length) {
                        this.optional_pool['five_wp'] = JSON.parse(JSON.stringify(this.all_cr_wp['five_wp']));
                    } else {
                        this.optional_pool['five_wp'] = [];
                    }
                    break;
                case 'four_cr':
                    if (this.optional_pool['four_cr'].length < this.all_cr_wp['four_cr'].length) {
                        this.optional_pool['four_cr'] = JSON.parse(JSON.stringify(this.all_cr_wp['four_cr']));
                    } else {
                        this.optional_pool['four_cr'] = [];
                    }
                    break;
                case 'four_wp':
                    if (this.optional_pool['four_wp'].length < this.all_cr_wp['four_wp'].length) {
                        this.optional_pool['four_wp'] = JSON.parse(JSON.stringify(this.all_cr_wp['four_wp']));
                    } else {
                        this.optional_pool['four_wp'] = [];
                    }
                    break;
                case 'three':
                    if (this.optional_pool['three'].length < this.all_cr_wp['three'].length) {
                        this.optional_pool['three'] = JSON.parse(JSON.stringify(this.all_cr_wp['three']));
                    } else {
                        this.optional_pool['three'] = [];
                    }
                    break;
                default:
                    if (this.optional_pool[group].includes(name))
                        this.optional_pool[group].splice(this.optional_pool[group].indexOf(name), 1);
                    else
                        this.optional_pool[group].push(name);
                    break;
            }
            Object.keys(this.all_cr_wp).forEach(i => {
                this.selection[i] = true;
                for (let a = 0; a < this.all_cr_wp[i].length; a++) {
                    if (!(this.optional_pool[i]).includes(this.all_cr_wp[i][a])) {
                        this.selection[i] = false;
                    }
                }
            });
            this.selection['all'] = true;
            this.selection['all_un'] = true;
            if (Object.values(this.selection).includes(false)) {
                this.selection['all'] = false;
            };
            Object.values(this.optional_pool).forEach(i => {
                if (i.length != 0)
                    this.selection['all_un'] = false;
            })
        },
        OptSave() {
            if (this.selection['all_un']) {
                alert('請至少選擇一項！！！');
            } else {
                axios.post('genshin/set_optional_pool', { 'optional_pool': this.optional_pool }).then(response => {
                    this.old_optional_pool = JSON.parse(JSON.stringify(this.optional_pool));
                    this.Synk(response['data']['synk_info']);
                    this.outSetOptionalPool();
                }, () => {
                    alert('失敗');
                });
            }
        },
        HistoryFilter() {
            let inv = JSON.parse(JSON.stringify(this.old_inventory));
            if (this.history_star != 'all') {
                (Object.keys(inv)).forEach(i => {
                    inv[i] = inv[i].filter(object => object['star'] == this.history_star);
                });
            }
            if (this.history_kind != 'all') {
                (Object.keys(inv)).forEach(i => {
                    inv[i] = inv[i].filter(object => object['kind'] == this.history_kind);
                });
            }
            let count = 0, per = [], temp = {};
            if (this.history_order == 'desc') {
                (Object.keys(inv)).forEach(i => {
                    (Object.keys(inv[i])).forEach(j => {
                        inv[i] = inv[i].sort((a, b) => {
                            if (a[this.history_order_type] > b[this.history_order_type])
                                return -1;
                            else if (a[this.history_order_type] < b[this.history_order_type])
                                return 1;
                            else
                                return 0;
                        })
                    })
                });
            } else {
                (Object.keys(inv)).forEach(i => {
                    (Object.keys(inv[i])).forEach(j => {
                        inv[i] = inv[i].sort((a, b) => {
                            if (a[this.history_order_type] > b[this.history_order_type])
                                return 1;
                            else if (a[this.history_order_type] < b[this.history_order_type])
                                return -1;
                            else
                                return 0;
                        })
                    })
                });
            }
            (Object.keys(inv)).forEach(i => {
                temp[i] = [];
                while(inv[i].length>10){
                    // document.write(JSON.stringify(inv[i])+'<br>');
                    temp[i].splice(0,0,inv[i].splice(-10));
                }
                if(inv[i].length>0){
                    // document.write(JSON.stringify(inv[i])+'<br>');
                    temp[i].splice(0,0,inv[i]);
                }
                
            });
            this.inventory = temp;
        },
        NextPage() {
            if ((this.page[this.history_selected] + 1) == this.inventory[this.history_selected].length) {

            } else {
                this.page[this.history_selected]++;
            }

        },
        PreviousPage() {
            if (this.page[this.history_selected] != 0) {
                this.page[this.history_selected]--;
            }

        },
    },
    mounted() {
        this.OptSelect();
        this.HistoryFilter();
    }
}
createApp(genshin).mount('#simu');