
<div id="app_mhcms">
    <div class="weui-navbar" style="position: static;top: 0;left: 0">
        <a @click="change_options(1 , 1 , 'offer')" class="weui-navbar__item make_call " :class="active ===1 ? 'weui-bar__item_on' : '' ">
            出售
        </a>
        <a @click="change_options(2 , 2 , 'offer')" class="weui-navbar__item make_call" :class="active ===2 ? 'weui-bar__item_on' : '' ">
            出租
        </a>

        <a @click="change_options(3 , 1 , 'request')" class="weui-navbar__item make_call" :class="active ===3 ? 'weui-bar__item_on' : '' ">
            求购
        </a>

        <a @click="change_options(4 , 2 , 'request')" class="weui-navbar__item make_call" :class="active ===4 ? 'weui-bar__item_on' : '' ">
            求租
        </a>
    </div>

    <div class="weui-cells">

        <div v-if="weituo_model=='request'">
            <div  class="weui-cell weui-cell_swiped" v-for="(item ,index_1) in items">
                <div class="weui-cell__bd ">
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>委托编号：{{item.id}} /  {{item.create_at}} / {{item.status}}</p>
                        </div>
                        <div class="weui-cell__ft">{{item.shangquan}}</div>
                    </div>
                </div>


                <div class="weui-cell__ft">
                    <a class="weui-swiped-btn weui-swiped-btn_warn" href="javascript:">删除</a>
                </div>
            </div>
        </div>
        <div v-if="weituo_model=='offer'">
            <div  class="weui-cell weui-cell_swiped" v-for="(item,index_2) in items" >
                <div class="weui-cell__bd ">
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>{{item.loupan_name}}</p>
                        </div>
                        <div class="weui-cell__ft">{{item.address}}</div>
                    </div>
                </div>

                <div class="weui-cell__ft">
                    <a class="weui-swiped-btn weui-swiped-btn_warn" href="javascript:">删除</a>
                </div>
            </div></div>
    </div>


    <mhui_bottom_loading :has_more="has_more" :is_loading="is_loading"></mhui_bottom_loading>
</div>
<script>
    require(['Vue', 'axios', 'vue!mhcms_ui', 'vue!mhcms_filters', 'vue!house'], function (Vue, axios) {
        Vue.prototype.$http = axios;
        new Vue({
            el: "#app_mhcms",
            data: {
                page: 1,
                has_more: true,
                is_loading: false,
                active : 1 ,
                customer_type: 1,
                weituo_model: 'offer',
                items : [] ,
                params : {

                }
            },

            methods: {
                load_list(init) {
                    let that = this;
                    if(init === 1){
                        that.items = [];
                        that.page = 1;
                    }
                    this.params.customer_type = this.customer_type

                    let api = "";
                    if (this.weituo_model === "offer") {
                        api = api_host + 'house/service/get_user_weituo';
                    }


                    if (this.weituo_model === "request") {
                        api = api_host + 'house/service/get_user_appointment';
                    }


                    that.is_loading = true;
                    that.has_more = true;


                    let promise = that.$http.get(api , {
                        params :{
                            site_id : "{$_W.site.id}",
                            query : this.params
                        }
                    }).then((ret) => {
                        ret = ret.data;
                        console.log(ret);
                        if (ret.code === 1) {
                            for (let i = 0; i <= ret.data.data.length - 1; i++) {
                                that.items.push(ret.data.data[i])
                            }

                            if (ret.data.has_more === true) {
                                that.page++;
                                that.has_more = true;
                            } else {
                                that.has_more = false;
                            }
                            that.is_loading = false;
                        }
                    } , (error)=>{

                        console.log(error);

                        that.is_loading = false;
                    });

                },
                change_options(tab_id , customer_type, weituo_model) {
                    this.active = tab_id;
                    this.customer_type = customer_type;
                    this.weituo_model = weituo_model;

                    this.load_list(1);
                }
            },
            created() {
                this.load_list(1)
            }
        });
    });
</script>