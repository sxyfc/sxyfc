Vue.component('mhui_filter_shadow' , {
    template :`<div class="fitler_shadow" :class="show ? 'show' : 'hidden'" ></div>`,
    props: {
        show : {
            type: Boolean, default: false
        },
        type: {
            type: String,
            default: 'primary'
        },
        btnMsg: {
            type: String,
            default: '页面主操作 Normal'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        plain: {
            type: Boolean,
            default: false
        }
    }
});

Vue.component('mhui_tag' , {
    template :`<div>
<span class="tag" v-for="tag in Arr"  >{{tag}}</span>
</div>` ,
    data (){
        return {
            value : ''
        }
    }
    ,
    props: {
        tagsStr: {
            type: String,
            default: ''
        },
        source_type : {
            type : String ,
            default : 'string'
        }
    },
    methods :{

        bindKeyInput(e){
            this.value = e.target.value;
            this.$emit('applied' , this.field_name , this.value )
        }
    } ,
    computed :{
        Arr (){

            if(this.source_type == 'string'){
                return this.tagsStr.split(" , ");
            }

        }



    }
});

Vue.component('mhui_bottom_loading' , {
    template :  `<div>
    <div class="weui-panel__ft has-text-centered">
    <a :class="is_loading ? '' : 'is-hidden'" class=" button is-light is-mobile-loading is-loading"><img
class="loading_icon" src="/statics/images/logo.png"/></a>
    </div>

    <article class=" is-paddingless has-text-centered" :class="has_more ? 'is-hidden' : ''" style="font-size: 14rem;width:100vw;">
    <div class="">
    <div class="weui-loadmore weui-loadmore_line">
    <span class="weui-loadmore__tips">暂无更多数据</span>
    </div>
    </div>
    </article>
    </div>`,
    props: {
        is_loading: {
            type: Boolean,
            default: false
        },
        has_more: {
            type: Boolean,
            default: false
        }
    }

});


Vue.component('mhcms_modal' , {
    template : `
    <div class="modal">
          <div class="modal-background"></div>
          <div class="modal-card">
            <header class="modal-card-head">
              <p class="modal-card-title">{{modal_title}}</p>
              <button class="delete" aria-label="close" @click="$emit('close')"></button>
            </header>
            <section class="modal-card-body">
              {{modal_content}}
            </section>
            <footer class="modal-card-foot">
              <button class="button is-success" @click="$emit('ok')">Save changes</button>
              <button class="button" @click="$emit('close')">Cancel</button>
            </footer>
          </div>
    </div>
    `,
    props: {
        modal_title: {
            type: String,
            default: "操作提示"
        },
        modal_content: {
            type: String,
            default: ""
        },
    } ,
    methods : {

    }
});

Vue.component('mhcms_modal' , {
    template : `
    <div id="mhcms-dialog">
        <div class="js_dialog" v-if="modal_type=='confirm'" :class="show" id="iosDialog1" style="opacity: 0; display: none;">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">弹窗标题</strong></div>
                <div class="weui-dialog__bd">弹窗内容，告知当前状态、信息和解决方法，描述文字尽量控制在三行内</div>
                <div class="weui-dialog__ft">
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">辅助操作</a>
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">主操作</a>
                </div>
            </div>
        </div>
        
        <div class="js_dialog" v-if="modal_type=='msg'" :class="show_modal ? '' : 'is-hidden'" >
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__bd">{{modal_content}}</div>
                <div class="weui-dialog__ft">
                    <a href="javascript:;" @click="$emit('close')" class="weui-dialog__btn weui-dialog__btn_primary">知道了</a>
                </div>
            </div>
        </div>
        
    
    </div>
    
    ` ,
    props: {

        modal_type: {
            type: String,
            default: "msg"
        },
        modal_title: {
            type: String,
            default: "操作提示"
        },
        modal_content: {
            type: String,
            default: ""
        },
        show_modal : {
            type: Boolean,
            default: false
        }
    } ,
    methods : {

    }
});


Vue.component('mhui_choose_address' , {
    template : `<div class="weui-cell weui-cell_access">
        <div class="weui-cell__hd"><label class="weui-label">选择地址</label></div>
        <div class="weui-cell__bd" @click="choose_address" v-text="text"></div>
        <div class="weui-cell__ft"></div>
    </div>`,
    props: {
        text: {
            type: String,
            default: '使用微信地址快速下单'
        },
    },
    data() {
        return {
            current_time : ''
        }
    },
    methods: {
        apply(res){
            this.$emit('applied' , res)
        } ,
        choose_address (){
            var that = this

            wx.openAddress({
                success: function (res) {
                    var userName = res.userName; // 收货人姓名
                    var postalCode = res.postalCode; // 邮编
                    var provinceName = res.provinceName; // 国标收货地址第一级地址（省）
                    var cityName = res.cityName; // 国标收货地址第二级地址（市）
                    var countryName = res.countryName; // 国标收货地址第三级地址（国家）
                    var detailInfo = res.detailInfo; // 详细收货地址信息
                    var nationalCode = res.nationalCode; // 收货地址国家码
                    var telNumber = res.telNumber; // 收货人手机号码
                    that.apply(res);
                }
            });
        }
    }

});