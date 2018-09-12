<div id="app_mhcms">
    <?php
    $is_a = \app\house\controller\Check::check_admin();
    ?>
    {if $detail.old_data.status == 99 || $is_a}
    <div class="swiper-container swiper-container-horizontal new-better-swiper-container"
         id="detail_ad">
        <div class="swiper-wrapper">
            {foreach $detail.thumbs as $image}
            <div class="swiper-slide swiper-slide-prev">
                <img src="{$image.url}" class="image ui" style="max-height: 350px;margin: auto">
            </div>
            {/foreach}
        </div><!-- Add Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Add Pagination -->
        <div class="swiper-button-next swiper-button-white"></div>
        <div class="swiper-button-prev swiper-button-white"></div>
    </div>


    <div class="ui top attached mhcms-panel" id="intro">
        <div class="ui  mhcms-panel-header"><h2>{$detail['title']} {if $detail['vr_link']}
                <a href="{:url('house/loupan/vr_link' , ['id'=>$detail['id']])}"
                   class="weui-btn weui-btn_mini weui-btn_warn is-pulled-right  label"><i
                            class="icon street view"></i> 全景看房</a>
                {/if}</h2></div>
        <div class="ui column mhcms-panel-body" style="    margin-bottom: 10px;padding-top: 0;">

            <div class=" cells columns is-mobile is-marginless">

                <div class="column">
                    <div class="text">均价</div>
                    <div class="em"><?php echo round($detail['price']/$detail['size'],4)*10000;?>元</div>
                </div>

                <div class="column">
                    <div class="text">户型</div>
                    <div class="em">
                        {$detail['old_data']['shi']}室{$detail['old_data']['ting']}厅{$detail['old_data']['chu']}厨{$detail['old_data']['wei']}卫
                    </div>
                </div>

                <div class="column">
                    <div class="text">建筑面积</div>
                    <div class="em">{$detail['size']}平米</div>
                </div>
            </div>


            <div class="columns is-mobile is-marginless is-multiline">
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">地区 :</div>
                        <div class="column">{$detail['area_id']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">装修 :</div>
                        <div class="column has-text-danger">{$detail['zhuangxiu']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">朝向 :</div>
                        <div class="column has-text-danger">{$detail['direction']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">房屋用途 :</div>
                        <div class="column ">{$detail['yongtu']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">楼层 :</div>
                        <div class="column ">{$detail['floor']}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">电梯 :</div>
                        <div class="column ">{$detail['lift']}</div>
                    </div>
                </div>
                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">特色 :</div>
                        <div class="column">{$detail['tags']}</div>
                    </div>
                </div>

                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">地址 :</div>
                        <div class="column">{$detail['address']}</div>
                    </div>
                </div>
                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">更新日期 :</div>
                        <div class="column">{:date('Y-m-d' , strtotime($detail['update_at']))}</div>
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">浏览量 :</div>
                        <div class="column">{$detail['hits']['base'] + $detail['hits']['views']}
                        </div>
                    </div>
                </div>

                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">房源描述 :</div>
                        <div class="column">{$detail['description']}</div>
                    </div>
                </div>
                {if $show_power}
                {if $pay_result}
                <div class="column is-full">
                    <div class="columns option is-mobile is-marginless">
                        <div class="column is-narrow">经纪人电话 :</div>
                        {if $power_result}
                        <div class="column">{$detail['mobile']}</div>
                        {else}
                        <div class="column">无权查看</div>
                        {/if}
                    </div>
                </div>
                {/if}
                {/if}
            </div>
        </div>

        <div class="ui mhcms-item">
            <label class="column is-narrow">户型图</label>
            <div class="column">
                <img src="{$detail['huxing_link']}" class="image ui" style="max-height: 350px;margin: auto">
            </div>
        </div>
        {foreach $field_list as $k=>$field}
        {if $field['field_name'] == 'baidu_map'}
        <div class="ui mhcms-item">
            <label class="column is-narrow">{$field['slug']}</label>
            <div class="column">
                {$field['form_str']}
            </div>
        </div>
        {/if}
        {/foreach}
        <div class="column is-full">
            <div class="columns option is-mobile is-marginless">
                <div class="column is-narrow">经纪人电话:</div>
                <div class="column"><a href="tel:{$mobile}">{$mobile}</a></div>
            </div>
        </div>
    </div>
    <?php
    $agent = \app\common\model\Models::get_item_by(['user_id' => $detail['user_id']], 'house_agent');
    $agent_user = \app\common\model\Users::get(['id' => $detail['user_id']]);
    ?>
    {if $agent && $agent_user}

    <!--<div class="ui top attached mhcms-panel">-->
    <!--    <div class="ui column mhcms-panel-body" style="margin-bottom: 10px">-->
    <!--        <a class="weui-cell weui-cell_access" href="{:url('house/agent/detail' , ['user_id'=>$detail['user_id']])}">-->
    <!--            <div class="weui-cell__hd" style="position: relative;margin-right: 10px;">-->
    <!--                <img class=" is-rounded" src="{$agent['avatar'][0]['url']}" style="width: 50px;height:50px;display: block;border-radius: 50%">-->
    <!--            </div>-->
    <!--            <div class="weui-cell__bd">-->
    <!--                <p>{$agent.person_name}-->
    <!---->
    <!--                    {if $user_verify.personal_verify==99}<i class="has-text-success iconfont icon-renzheng"></i>{/if}-->
    <!---->
    <!--                    {if $user_verify.company_verify==99}<i class="has-text-success iconfont icon-renzhengpeizhi"></i>{/if}-->
    <!--                </p>-->
    <!--                <p style="font-size: 13px;color: #888888;">{$agent.mobile}</p>-->
    <!--            </div><div class="weui-cell__ft"></div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--</div>-->

    {/if}

    <style>
        .column {
            color: #0C0C0C;
            font-size: 1.4rem;
            position: relative;
            line-height: 2.3rem;
        }

        .column.is-narrow {
            color: #999;
        }

        .column.is-full, .column.is-half {
            padding: 0;

        }

        .cells.columns {
            border-top: solid 1px #ebebeb;
            border-bottom: solid 1px #ebebeb;
            padding: 15px 10px;
            line-height: 20px;
        }

        .column .em {
            color: orangered;
            font-size: 17px;
        }

        .column .text {
            font-size: 12px;
        }

    </style>
    <script>
        require(['jquery', 'swiper4'], function ($, Swiper) {
            $(document).ready(function () {
                var swiper = new Swiper('#detail_ad', {
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    autoplay: {
                        delay: 3000,
                        stopOnLastSlide: false,
                        disableOnInteraction: false,
                    }
                });
            });
        });
    </script>
    <div class="mhcms-navbar weui-navbar" style="">
        <a class="weui-navbar__item is-narrow" href="/house">
            <i class="icon home"></i> <span>首页</span>
        </a>

        <!-- 这里改为 支付查看信息，支付后展示 一键导入和查看房东电话-->
        {if $show_power}
        {if $pay_result}
        <a class="weui-navbar__item" href="tel:{$detail['mobile']}">
            一键拨号
        </a>

        <a class="weui-navbar__item" href="/house/esf/autoAdd/id/{$detail['id']}">
            一键导入
        </a>

        {else}
        <a class="weui-navbar__item" href="/house/user_orders/pay_for_see/id/{$detail['id']}/type/2" onclick="if(confirm('确认支付房宝获取？')==false)return false;">
            获取房东电话
        </a>
        </form>
        {/if}
        {/if}
    </div>

    {else}

    <div class="weui-msg">
        <div class="weui-msg__icon-area"><i class="weui-icon-waiting weui-icon_msg"></i></div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title">提示信息</h2>
            <p class="weui-msg__desc">该内容尚未通过审核请等待管理审核，或与我们联系尽快审核！</p>
        </div>
        <div class="weui-msg__opr-area">
            <p class="weui-btn-area">
                <a href="javascript:;" class="weui-btn weui-btn_primary"
                   onclick="history.back()">返回</a>
            </p>
        </div>
        <div class="weui-msg__extra-area">
            <div class="weui-footer">
                <p class="weui-footer__links">
                    <a href="javascript:void(0);" class="weui-footer__link">
                        {$_W.global_config.system_name}
                    </a>
                </p>
                <p class="weui-footer__text">Copyright © 2008-2016 {$_W['siteroot']}</p>
            </div>
        </div>
    </div>

    {/if}


</div>
