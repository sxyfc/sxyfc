<div id="app_mhcms">
<div class="ui five  grid  padded  mhcms-panel" id="mhcms-app">


    <div class="ui mhcms-panel-header wide column mhcms-header-column"  >
        客户购房订单
        <a class="floated right" style="float: right"> 推荐客户 </a>
    </div>


</div>
<div class="  columns is-mobile is-marginless has-text-centered " id="mhcms-app">


    {foreach $status_options as $state}
    <div class="ui column" :class="{active:status==1}">
        <a class="nav_item"  href="/house/user_orders/index/status/{$state.id}"> {$state.name}</a>
    </div>
    {/foreach}

</div>
</div>
<script>
    require(['Vue'] , function (Vue) {
        var app = new Vue({
            el: '#app_mhcms',
            data: {
                status:  {$status} ,

            } ,
            methods : {

            }
        })
    });
</script>
<div class="ui two column stackable grid container" style="margin: 0">
    <div class="ui twelve wide  column stackable  mhcms_mobile_column" >

        <div class="ui top attached mhcms-panel">
            <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">
                <ul class="ui mhcms-list unstackable items">
                    {foreach $appointments as $appointment}
                    <?php

                    $_item = \app\common\model\Models::get_item($appointment['id'] , "house_appointment")
                    ?>
                    <div class="ui item mhcms-list-item ">
                        <div class="content">
                            <a class="header mhcms-item-header" href1="{:url('house/loupan/detail' , ['id'=>$item['id'] ])}">客户{$appointment['id']}</a>
                            <div class="meta">
                                {$appointment.note}
                                {if $appointment.kanfang_date}
                                <p>
                                    看房中
                                </p>
                                {/if}
                            </div>
                            <div class="extra">
                                {$_item.status}
                            </div>
                        </div>

                    </div>

                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>