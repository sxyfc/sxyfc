<div id="app_mhcms">
    <div class="tabs is-toggle is-toggle-rounded">
        <ul>
            <li :class="{'is-active' : target=='loupan'}" @click="target='loupan'">
                <a>
                    <span class="icon is-small"><i class="fas fa-image"></i></span>
                    <span>新房</span>
                </a>
            </li>
            <li :class="{'is-active' : target=='esf'}" @click="target='esf'">
                <a>
                    <span class="icon is-small"><i class="fas fa-music"></i></span>
                    <span>二手房</span>
                </a>
            </li>
            <li :class="{'is-active' : target=='rent'}" @click="target='rent'">
                <a>
                    <span class="icon is-small"><i class="fas fa-film"></i></span>
                    <span>出租房</span>
                </a>
            </li>

            <li  >
                <a :href="'/house/map/' + target">
                    <span class="icon is-small"><i class="fas fa-film"></i></span>
                    <span>地图找房</span>
                </a>
            </li>
        </ul>
    </div>
    <div style="padding: 0 15px; margin-bottom: 15px">
        <div class="field has-addons">
            <p class="control">
    <span class="select" style="height: auto">
      <select v-model="search_type">

        <option value="normal">普通搜索</option>
      </select>
    </span>
            </p>

            <div class="control is-expanded">
                <input class="input" type="text" v-model="keyword" placeholder="请输入楼盘名字">
            </div>
            <div class="control">
                <a class="button is-info" @click="do_search">
                    Search
                </a>
            </div>
        </div>
    </div>

    <section id="content_list" class="mhcms-panel">
        <h2 class="mhcms-panel-header">搜索结果 <span>{{items.length}}</span> 条</h2>
        <div class="mhcms-panel-body">

                <a  v-for="item in items"  :href="'/house/'+ target +'/detail?id=' + item.id" class="weui-media-box weui-media-box_appmsg">
                    <div class="weui-media-box__hd is-clipped" style="width: 120px;height: 90px;">
                        <img :src="item.thumb[0].url" alt="" class="weui-media-box__thumb">
                    </div>
                    <div class="weui-media-box__bd" style="align-self: flex-start;">
                        <h4 class="weui-media-box__title"> {{item.loupan_name}}</h4>
                        <p class="weui-media-box__desc" style="  font-size: 12px">{{item.address}} </p>
                        <p class="weui-media-box__desc" style="padding-top: 5px">
                        </p>
                        <div class="mtags">
                            <span v-for="(tag , i ) in item.tags.split(' , ')" :class="'mtag tag_' + i">{{tag}} </span>
                        </div>
                        <p></p>

                        <p class="weui-media-box__desc has-text-right">
                            <span class="price has-text-danger">{{item.price}} 元/平方</span>
                        </p>

                    </div>
                </a>
        </div>
    </section>
</div>
<script>

    require(["Vue", "jquery", "layui"], function (Vue, $, layui) {


        new Vue({
            el: "#app_mhcms",
            data: {
                target: 'loupan',
                search_type: 'normal',
                keyword: '',
                api_url: "{:url('core/common_service/search')}",
                items: []
            },
            methods: {
                do_search: function () {
                    var that = this;
                    $.post(this.api_url, {
                        site_id: "{$_W['site']['id']}",
                        query: {
                            keyword: this.keyword ,
                            module : 'house',
                            model_id : this.target,
                        }

                    }, function (data) {
                        that.items = data.data.data;

                        if (that.items.length === 0) {
                            layui.use(['layer'], function () {
                                layui.layer.msg("对不起,没有任何结果!");

                            });
                        }
                    }, 'json');
                }
            },
            created: function () {

            }
        });
    });
</script>

{block name="footer"}
{include file="public/footer" /}
{/block}
