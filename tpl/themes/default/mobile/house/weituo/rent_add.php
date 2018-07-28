
<div id="app_mhcms">
    <div class="weui-cells weui-cells_form">
        <mhui_input_text :field_name="'area'" :text="'区域'" :placeholder="'您想住哪个区域'" @applied="apply"></mhui_input_text>
        <mhui_input_text :field_name="'shangquan'" :text="'商圈'" :placeholder="'您想住哪个商圈'" @applied="apply"></mhui_input_text>

        <mhui_select :field_name="'shi'" :model_id="'house_appointment'" :text="'户型'" @applied="apply"></mhui_select>

        <mhui_input_text :field_name="'size'" :type="'number'" :text="'面积'" :placeholder="'您想住的面积'" :unit="'平米'" @applied="apply"></mhui_input_text>
        <mhui_input_text :field_name="'price'"  :type="'number'" :text="'月租'" :placeholder="'你的期望价格'" :unit="'元/月'" @applied="apply"></mhui_input_text>

    </div>
    <div class="weui-cells weui-cells_form">
        <mhui_input_text :field_name="'real_name'" :text="'称呼'" :placeholder="'如何称呼您'" @applied="apply"></mhui_input_text>
        <mhui_input_text :field_name="'mobile'" :type="'number'" :text="'联系方式'" :placeholder="'您的联系方式'" @applied="apply"></mhui_input_text>
    </div>
    <div class="weui-btn-area">
        <a class="weui-btn weui-btn_primary" @click="save()"  id="showTooltips">确定</a>
    </div>
    <mhui_toast @hide_toast="loading_toast.show=false" :show="loading_toast.show" :type="'loadingToast'"></mhui_toast>

    <mhui_toast @hide_toast="message_toast.show=false" :show="message_toast.show" :icon="message_toast.icon" :type="'toast'" :text="message_toast.toast_text"></mhui_toast>
</div>


<script>
    require(['Vue', 'axios', 'vue!mhcms_ui', 'vue!mhcms_filters', 'vue!house'], function (Vue, axios) {
        Vue.prototype.$http = axios;
        new Vue({
            el: "#app_mhcms",
            data: {
                loading_toast: {
                    show: false
                },
                message_toast: {
                    show: false
                    , icon: "",
                    toast_text: ''
                },
                params: {},
                toast_text: '',
                type: "{$type}",
                msg_toast: {
                    icon: "",
                    toast_text: ''
                },
                custom_type : 2,
                disabled : false ,
            }
            ,
            methods: {
                apply($field_name, $field_value) {
                    this.params[$field_name] = $field_value
                },
                save() {
                    let that = this;
                    that.params.type = this.type;
                    that.loading_toast.show = true;
                    that.message_toast.show = false;
                    that.params.custom_type = that.custom_type;
                    setTimeout(function () {
                        let api = api_host + 'house/service/add_appointment';
                        that.$http.get(api, {
                            params: {
                                site_id : "{$_W.site.id}" ,
                                query: that.params
                            }
                        }).then(function (ret) {
                            ret = ret.data
                            if(ret.code !==1){
                                console.log("error");
                                that.params = {};
                                that.message_toast.icon = "weui-icon-warn weui-icon_msg";
                                that.message_toast.toast_text = ret.msg;
                                that.loading_toast.show = false;
                                that.message_toast.show = true;

                            }else{
                                that.params = {};

                                that.message_toast.icon = "weui-icon-success weui-icon_msg";
                                that.message_toast.toast_text = "感谢您的关注 我们会尽快处理 您会将会到一个微信消息提示！";
                                that.loading_toast.show = false;
                                that.message_toast.show = true;
                            }
                        });
                    }, 300);

                }
            }
        });
    });
</script>