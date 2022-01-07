import axios from 'axios';
import { createApp } from "vue";
const g = {
    data() {
        return {
            cur_pool : 1,
        }
    },
    methods: {
        set_cur_pool() {
            axios.post('./set_cur_pool', {
                'cur_pool': this.cur_pool
            }).then(response => {
                window.location.reload();
            }, () => {
                alert('失敗');
            })
        }
    }
}
createApp(g).mount('#simu_data');