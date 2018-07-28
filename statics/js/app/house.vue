<script>

    define(["Vue"], function (Vue) {
        Vue.component('item_esf', {
            template: `<a :href="'/house/esf/detail?id='+item.id" class="weui-media-box weui-media-box_appmsg">
    <div class="weui-media-box__hd">
    <img class="weui-media-box__thumb" :src="item.thumb[0].url" alt="">
    </div>
    <div class="weui-media-box__bd">
    <h4 class="weui-media-box__title"> <em v-if="item.old_data.is_top" class="top">顶</em> {{item.title}}


    </h4>
        <p class="weui-media-box__desc">{{item.area_id}}  {{item.size}}㎡ / {{item.zhuangxiu}}     {{item.shi}}</p>
        <p class="weui-media-box__desc">
            <mhui_tag :tagsStr="item.tags"></mhui_tag>
        </p>
    <p class="weui-media-box__desc has-text-right">
            <span class="price has-text-danger" v-if="item.price==0 || item.price=='0'">面议</span>
            <span class="price has-text-danger" v-else>{{item.price}}万</span>
    </p>
</div>
</a>`,
            props: ['item']
        });

        Vue.component('item_loupan', {
            template: `<a data-mha :href="'/house/loupan/detail?id='+item.id" class="weui-media-box weui-media-box_appmsg">
    <div class="weui-media-box__hd">
    <img class="weui-media-box__thumb" :src="item.thumb[0].url" alt="">
    </div>
    <div class="weui-media-box__bd">
    <h4 class="weui-media-box__title">{{item.loupan_name}}</h4>
        <p class="weui-media-box__desc">{{item.address}}  </p>
        <p class="weui-media-box__desc">
            <mhui_tag :tagsStr="item.tags"></mhui_tag>
        </p>
    <p class="weui-media-box__desc has-text-right">
            <span class="price has-text-danger">{{item.price}}元/㎡</span>
    </p>
</div>
</a>`,
            props: ['item']
        });

        Vue.component('item_rent', {
            template: `<a :href="'/house/rent/detail?id='+item.id" class="weui-media-box weui-media-box_appmsg">
    <div class="weui-media-box__hd">
    <img class="weui-media-box__thumb" :src="item.thumb[0].url" alt="">
    </div>
    <div class="weui-media-box__bd">
    <h4 class="weui-media-box__title"> <em v-if="item.old_data.is_top" class="top">顶</em> {{item.title}}</h4>
        <p class="weui-media-box__desc">{{item.area_id}}  {{item.size}}㎡ / {{item.zhuangxiu}}     {{item.shi}}</p>
        <p class="weui-media-box__desc">
            <mhui_tag :tagsStr="item.tags"></mhui_tag>
        </p>
    <p class="weui-media-box__desc has-text-right">
            <span class="price has-text-danger"  v-if="item.price==0 || item.price=='0'">面议</span>
            <span class="price has-text-danger" v-else >{{item.price}} 元/月</span>
    </p>
</div>
</a>`,
            props: ['item']
        });

    });

</script>