

<style>
    .main {
        max-width: 768px;
        margin: 36px  auto;
    }

    .header {
        line-height: 30px;
        text-align: center;font-size: 18px;
        text-shadow: 1px 1px #ccc;
    }

    #logo {
        width: 30px;
        height: 30px;
        line-height: 30px;
        vertical-align: bottom;
        border-radius: 50%;
        background: #fff;
    }

    .nav {
        overflow: hidden;
        clear: both;
    }

    .nav .item {
        float: left;
        width: 24.8%;
        margin-top: 50px;
        text-align: center;
        text-shadow: 1px 1px #ccc;
    }

    .column i{
        display: block;
        color: #fff;
        font-size: 75px;
    }

    .column a{
        display: block;
    }
    .column a div{
        font-size: 18px;
        line-height: 40px;
        display: block;
    }
    .box{
        position: relative;
        perspective: 1000px;
    }
    .box .box-img{
        transform: rotateY(0);
        transition: all 0.50s ease-in-out 0s;
    }
    .box:hover .box-img{
        transform: rotateY(-90deg);
    }
    .box .box-img img{
        width: 100%;
        height: auto;
    }
    .box .box-content{
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        text-align: center;
        background: rgba(0,0,0,0.7);
        transform: rotateY(90deg);
        transition: all 0.50s ease-in-out 0s;
    }
    .box:hover .box-content{
        transform: rotateY(0);
    }
</style>

<div class="main">
    <h1 class="header"><img id="logo" src="/statics/images/logo.png"> {$site.site_name} {$_W.global_config.system_name}</h1>

</div>
<div class="container">

    <div class="columns is-multiline is-mobile">


        {foreach $modules as $module}
        <div class="box is-radiusless column is-paddingless  is-4  is-marginless ">
            <a   href="{:url($module.web_route)}" class="box-img   has-text-centered" style=" background-color: {$module.bgcolor}">
                <i class="iconfont  {$module.icon}"></i>
                <div  class="has-text-white">{$module.module_name}</div>
            </a>

            <a  href="{:url($module.web_route)}" class="box-content  has-text-centered" style=" background-color: {$module.bgcolor}">
                <i class="iconfont  {$module.icon}"></i>
                <div  class="has-text-white">{$module.module_name}</div>
            </a>

        </div>
        {/foreach}

    </div>
</div>