
<style>
    .layui_mutil_upload, .layui_single_upload_thumb {
        padding: 0;
        height: 90px;
        width: 90px;
        float: left;
    }

    .layui-upload-list {
        margin-left: 100px;
    }

    .layui-form-label {
        width: 110px;
        text-align: left;
        padding: 10px 0;
    }

    .node_field_tips {
        padding: 10px 0;
    }
    .hidden_tr{
        display: none;
    }
</style>


<div class="" id="app_mhcms">
    {if !$_W['module_config']['close_agent'] && !$_W['module_config']['noreg_agent']}

    <div class="mhcms-panel">
        <div class="mhcms-panel-header">
            申请合伙人
        </div>
        <div class="mhcms-panel-body" style="padding:0 10px">

            <form class="layui-form form-inline" target="mhcms" method="post" action="{$_W.current_url}">

                {volist name="field_list" id="item"}
                <div class="layui-form-item {$item.node_field_mode}_tr" style="border-top: dotted 1px #ccc;margin-bottom: 0px">

                    <label class="layui-form-label"><strong style="font-size: 16px"> {$item['slug']}</strong></label>
                    <div class="layui-input-block node_field_tips">
                        {$item['form_str']}
                    </div>
                </div>
                {/volist}
                <div class="layui-form-item" style="border-top: dotted 1px #ccc">
                </div>
                <div class="layui-form-item " style="display: none">
                    <label class="layui-form-label">安全码</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input pull-left" id="code_input" name="code" placeholder="验证码">
                    </div>
                    <div class="layui-input-inline" style="width: auto">
                        <img id="code" src="{:captcha_src()}" onclick="this.src = this.src +'?i=' + Math.random()"
                             alt="captcha"/>
                    </div>
                </div>


                <div class="weui-cells__title has-text-danger" v-if="is_top">请选择VIP套餐</div>
                <div class="weui-cells weui-cells_checkbox needsclick" id="vip-reg" >
                    <label class="weui-cell weui-check__label needsclick"   v-for="(set,index) in agent_reg_set" :for="set.unit_type + '_' + set.days">
                        <div class="weui-cell__hd needsclick">
                            <input type="radio" class="weui-check needsclick" required name="set_index" :value="index" :id="set.unit_type + '_' + set.days" lay-ignore >
                            <i class="weui-icon-checked"></i>
                        </div>
                        <div class="weui-cell__bd needsclick" :money="set.money">
                            <p class="needsclick" v-if="set.money === '0'"> {{set.days}}天  免费试用</p>
                            <p  class="needsclick" v-else> {{set.days}}天VIP {{set.money}} <span :class="set.unit_type"> {{units_text[set.unit_type]}}</span></p>
                        </div>
                    </label>
                </div>
                <div class="layui-form-item" style="border-top: dotted 1px #ccc">
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block needsclick">
                        <input type="hidden" value="{:intval($_GPC.grid_id)}" id="grid_id" name="data[grid_id]">
                        {:token()}
                        <input class="layui-btn needsclick" type="submit" lay-submit lay-filter="*" value="立即提交">
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        require(['Vue' , 'layui']  ,function (Vue , layui) {
            new Vue({
                el : "#vip-reg" ,
                data : {
                    units_text : {
                        balance : "{$_W['site']['config']['trade']['balance_text']}" ,
                        point : "{$_W['site']['config']['trade']['point_text']}" ,
                    },
                    agent_reg_set : {:json_encode($_W['module_config']['agent_reg_set'])}
                }
            });

            layui.use(['form'], function () {
                var form = layui.form;

                form.on('submit(*)', function(data){
                    console.log(data.elem) //被执行事件的元素DOM对象，一般为button对象
                    console.log(data.form) //被执行提交的form对象，一般在存在form标签时才会返回
                    console.log(data.field) //当前容器的全部表单字段，名值对形式：{name: value}
                    if( (typeof data.field.set_index) === "undefined" && {:count($_W['module_config']['agent_reg_set'])} > 0){
                        console.log(typeof data.field.set_index);
                        alert("请选择套餐");
                        return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
                    }

                });
            });
        });



    </script>

    {else}

    <div class="weui-msg">
        <div class="weui-msg__icon-area"><i class="weui-icon-info weui-icon_msg"></i></div>
        <div class="weui-msg__text-area">
            <p class="weui-msg__desc">
                对不起 经纪人系统当前不允许新申请！
            </p>
        </div>
        <div class="weui-msg__opr-area">
            <p class="weui-btn-area">
                <a href="javascript:history.back();" class="weui-btn weui-btn_primary">返回</a>
            </p>
        </div>
    </div>

    {/if}
</div>