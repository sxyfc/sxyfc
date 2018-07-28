<script>
    define(["Vue"], function (Vue) {

        Vue.component('mhui_filter_shadow', {
            template: `<div class="fitler_shadow" :class="show ? 'show' : 'hidden'" ></div>`,
            props: {
                show: {
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

        Vue.component('mhui_tag', {
            template: `<div>
<span class="tag" v-for="tag in Arr"  >{{tag}}</span>
</div>`,
            data() {
                return {
                    value: ''
                }
            }
            ,
            props: {
                tagsStr: {
                    type: String,
                    default: ''
                },
                source_type: {
                    type: String,
                    default: 'string'
                }
            },
            methods: {

                bindKeyInput(e) {
                    this.value = e.target.value;
                    this.$emit('applied', this.field_name, this.value)
                }
            },
            computed: {
                Arr() {

                    if (this.source_type == 'string') {
                        return this.tagsStr.split(" , ");
                    }

                }


            }
        });

        Vue.component('mhui_bottom_loading', {
            template: `<div>
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
        Vue.component('mhcms_modal', {
            template: `
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
            },
            methods: {}
        });

        Vue.component('mhcms_confirm', {
            template: `
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

    `,
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
                show_modal: {
                    type: Boolean,
                    default: false
                }
            },
            methods: {}
        });


        Vue.component('mhui_choose_address', {
            template: `<div class="weui-cell weui-cell_access">
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
                    current_time: ''
                }
            },
            methods: {
                apply(res) {
                    this.$emit('applied', res)
                },
                choose_address() {
                    var that = this

                    global_wx.openAddress({
                        success: function (res) {
                            that.apply(res);
                        }
                    });

                }
            }

        });

        Vue.component('mhui_input_text', {
            template: `
            <div class="weui-cell" :class="unit!='unit' ? 'weui-cell_vcode':' '">
        <div class="weui-cell__hd"><label class="weui-label" v-text="text"></label></div>
        <div class="weui-cell__bd">
            <input @input="bindKeyInput" :value="value" class="weui-input" :type="type" :placeholder="placeholder">
        </div>
        <div class="weui-cell__ft" v-if="unit!='unit'"><span :class="unit!='unit' ? '':'is-hidden'"  class="weui-vcode-btn" v-text="unit" ></span></div>
    </div>
        `,
            props: {
                text: {
                    type: String,
                    default: '输入框'
                },
                placeholder: {
                    type: String,
                    default: '输入提示'
                },
                disabled: {
                    type: Boolean,
                    default: false
                },
                unit: {
                    type: String,
                    default: 'unit'
                },
                field_name: {
                    type: String,
                    default: ''
                },
                type: {
                    type: String,
                    default: 'text'
                },
                value: {
                    type: String,
                    default: ''
                }
            },
            methods: {

                bindKeyInput(e) {
                    this.value = e.target.value;
                    this.$emit('applied', this.field_name, this.value)
                }

            }
        });
        Vue.component('mhui_toast', {
            template: `
            <div>
        <div v-if="type=='toast'" id="toast" :class="show ===true ? 'show' :''">
            <div class="weui-mask_transparent"></div>
            <div class="weui-toast">
                <i :class="icon" class=" weui-icon_toast"></i>
                <p class="weui-toast__content" v-text="text"></p>
            </div>
        </div>


        <div v-if="type=='loadingToast'" id="loadingToast" :class="show ===true ? 'show' :''">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast">
            <a  class="button  is-mobile-loading is-loading" style="background:transparent;"><img
                    class="loading_icon" src="/statics/images/logo.png"/></a>
            <p class="weui-toast__content" v-text="text"></p>
        </div>
    </div>
</div>
            `, props: {
                type: {
                    type: String,
                    default: 'toast'
                },
                text: {
                    type: String,
                    default: '数据加载中'
                },
                placeholder: {
                    type: String,
                    default: '输入提示'
                },
                show: {
                    type: Boolean,
                    default: false
                },
                icon :{
                    type : String,
                    default : "weui-icon-warn weui-icon_msg-primary"
                }
            },
            watch : {

                show (new_show){
                    console.log(new_show);
                    var that = this;
                    if(new_show===true  && this.type == "toast") {
                        setTimeout(function () {
                            that.show = false;
                            that.$emit('hide_toast');

                        } , 2000)
                    }

                }
            }
        });
    });
</script>