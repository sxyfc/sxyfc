<div id="app_mhcms">
    <div class="weui-panel__hd">
        <form class="submit-form" action="/house/weituo/index" method="post">
            <select name="type" class="weui-select">
                <option class="weui-cell_select" selected value="0">发布类型</option>
                <option class="weui-cell_select" value="1">出租</option>
                <option class="weui-cell_select" value="2">出售</option>
                <option class="weui-cell_select" value="3">求租</option>
                <option class="weui-cell_select" value="4">求购</option>
            </select>
            <input type="submit" class="weui-btn weui-btn_primary search_btn" value="搜索">
        </form>
    </div>

    <div class="ui mhcms-panel">
        {if $type==1 || $type==2}
        {foreach $list as $item}
        <div class="ui column mhcms-panel-body" style="    margin-bottom: 10px;padding-top: 0;">
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">小区名称： {$item['xiaoqu_name']}</div>
                </div>
                <div class="column">
                    <div class="text">地址信息： {$item['address']}</div>
                </div>
            </div>
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">联系手机： {$item['mobile']}
                    </div>
                </div>
                <div class="column">
                    <div class="text">委托类型： {if $item['type']==1}出售{else}出租{/if}
                    </div>
                </div>
            </div>
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">期望价格： {$item['price']} 万元</div>
                </div>
                <div class="column">
                    <div class="text">期望面积： {$item['size']} 平米</div>
                </div>
                <div class="column">
                    <div class="text">户型描述： {$item['huxing']}
                    </div>
                </div>
                <div class="column">
                    <div class="text">审核状态： {if $item['status']==0}审核中{else}通过{/if}
                    </div>
                </div>
            </div>
        </div>
        {/foreach}
        {else}
        {foreach $list as $item}
        <div class="ui column mhcms-panel-body" style="    margin-bottom: 10px;padding-top: 0;">
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">标题： {$item['title']}</div>
                </div>
            </div>
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">内容： {$item['content']}</div>
                </div>
            </div>
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">联系手机： {$item['mobile']}
                    </div>
                </div>
                <div class="column">
                    <div class="text">委托类型： {if $item['type']==1}求购{else}求租{/if}
                    </div>
                </div>
            </div>
            <div class=" cells columns is-mobile is-marginless">
                <div class="column">
                    <div class="text">意向价格： {$item['price']} 万元</div>
                </div>
                <div class="column">
                    <div class="text">意向面积： {$item['size']} 平米</div>
                </div>
                <div class="column">
                    <div class="text">意向户型： {$item['huxing']}
                    </div>
                </div>
                <div class="column">
                    <div class="text">审核状态： {if $item['status']==0}审核中{else}通过{/if}
                    </div>
                </div>
            </div>
        </div>
        {/foreach}
        {/if}
    </div>
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
                active: 1,
                customer_type: 1,
                weituo_model: 'offer',
                items: [],
                params: {}
            },

            methods: {
                load_list(init) {
                    let that = this;
                    if (init === 1) {
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


                    let promise = that.$http.get(api, {
                        params: {
                            site_id: "{$_W.site.id}",
                            query: this.params
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
                    }, (error) => {

                        console.log(error);

                        that.is_loading = false;
                    });

                },
                change_options(tab_id, customer_type, weituo_model) {
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