Vue.component('area_filter' , {
    template :`
        <div class="weui-flex__item" >
        <div class="placeholder" @click="toggle_panel" ><span v-text="area_name ? area_name : text"></span> <i class="iconfont icon-dropdown"></i> 
        </div>

        <div :class="show_filter ? 'active' : 'is-hidden'" style="" class="is-marginless columns filter_panel is-mobile">

            <div class="column is-marginless is-paddingless " style="background-color: #fff">
                <div scroll-y style="height: 50vh;" @scrolltoupper="true" @scrolltolower="true" @scroll="true"  >
                    <ul class="menu-list  weui-cells weui-cells_checkbox is-marginless">
                        <li><a @click="load_area(0 , '地区')">不限</a></li>
                        <li v-for="(top_area , index) in top_areas" @click="load_area(top_area.id ,top_area.area_name )" :key="index" data-cate_id="top_cate.id">
                            <a  :class="current_parent_id==top_area.id || current_area_id == top_area.id ? 'is-active' : ''">{{top_area.area_name}}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div  style="height: 50vh;" class="column is-marginless is-paddingless">
                <ul class="menu-list  weui-cells weui-cells_checkbox is-marginless">
                    <li><a  @click="apply_sub(0)">不限</a></li>
                    <li v-for="(sub_area , index) in sub_areas" @click="apply_sub(sub_area.id, sub_area.area_name)" :key="index">
                        <a  :class="current_area_id==sub_area.id || area_id == sub_area.id ? 'is-active' : ''">{{sub_area.area_name}}</a>
                    </li>
                </ul>
            </div>
        </div>
        <mhui_filter_shadow :show="show_filter" ></mhui_filter_shadow>
    </div>
    `
    ,
    data (){
        return {
            current_area_id : 0 ,
            current_parent_id : 0 ,
            area_name : "" ,
            parent_area_name : '',
            top_areas : [

            ] ,
            sub_areas : []
        }
    },
    props: ['area_id' , 'text' , 'show_filter' , 'field_name'] ,
    methods : {
        apply(){
            if(this.current_area_id === 0){
                var c_area_id = this.current_parent_id
            }else{
                var c_area_id = this.current_area_id
            }
            this.$emit('applied' , this.field_name , c_area_id);
            this.$emit('show_filter' , this.field_name , false);
            this.$emit('post_area_name' , this.field_name , this.area_name);
        } ,
        apply_sub($area_id , area_name){

            this.area_name = area_name;
            if($area_id === 0){
                this.area_name = this.parent_area_name;
                this.current_area_id = this.current_parent_id;
            }else{
                this.current_area_id = $area_id;
            }
            this.apply();
        } ,
        toggle_panel (){
            console.log("toggle_panel");
            this.$emit('show_filter' , this.field_name , !this.show_filter);
        },
        load_area( $parent_id  , area_name){

            if(this.current_parent_id !== $parent_id){
                this.current_parent_id = $parent_id;
            }
            this.parent_area_name = area_name
            let that = this;
            let api_url =api_host  +  "house/service/area_list";

            this.$http.get(api_url , {
                params: {
                    site_id: site_id ,
                    query : {
                        parent_id : $parent_id
                    }
                }
            }).then(function(ret){
                var data =  ret.data;

                if(data.code===1){
                    if($parent_id == 0){
                        that.top_areas = data.data
                        that.current_area_id = 0;
                    }else{
                        that.sub_areas = data.data
                    }
                }
                console.log(data);
            }, function(error) {
                // failure
                console.log(error);
            });


        }
    },
    created () {
        this.load_area(0 , '地区');
    },
    computed : {

    }
});

Vue.component('comp_filter_option' , {

    template: `
    <div class="weui-flex__item" >
        <div class="placeholder weui-flex" @click="toggle_panel" >
            <span class="weui-flex__item text" v-text="option_name ? option_name : text"></span>
            <span class="weui-flex__item dropdown"><i class="iconfont icon-dropdown"></i></span>
        </div>

        <div :class="show_filter ? 'active' : 'is-hidden'" class="is-marginless columns filter_panel is-mobile">

            <div class="column is-marginless is-paddingless " style="background-color: #fff">
                <div  bindscrolltoupper="upper" bindscrolltolower="lower" bindscroll="scroll"  >
                    <template v-if="multi ==false" >
                    <ul class="menu-list weui-cells weui-cells_checkbox is-marginless">
                        <li>
                            <a @click="remove_option" class="has-text-left" style="display: block">
                                <label style="display: block">不限</label>
                            </a>
                        </li>
                        <li v-for="(option , index) in options" :key="index" >
                            <a class="has-text-left" style="display: block">
                            <label style="display: block"> {{option.name}}
                                <input type="radio" :data-option_name="option.name" :checked="checked_id==option.id" v-model="checked_id"   class="is-pulled-right" :value="option.id" >
                            </label>
</a>
                            <div class="is-clearfix"></div>
                        </li>
                    </ul>
                    </template>
                    <template v-else>
                        <ul class="menu-list weui-cells weui-cells_checkbox is-marginless">
                            <li><a  @click="remove_option" class="has-text-left" style="display: block">不限</a></li>
                            <li v-for="(option , index) in options" :key="index" >
                                <a class="has-text-left" style="display: block">
                                    <label style="display: block"> {{option.name}}
                                        <input type="checkbox" v-model="checked_ids" :checked="option.checked" class="is-pulled-right" :value="option.id" >
                                    </label>
                                </a>
                                <div class="is-clearfix"></div>
                            </li>
                        </ul>
                    </template>
                </div>
                <a style="margin: 20rpx">
                    <span href="javascript:;" @click="apply" class="weui-btn weui-btn_primary">确定</span>
                </a>
            </div>
        </div>

    </div>
    
    ` ,
    data  () {
        return {

            checked_ids : [] ,
            option_name : "" ,
            options : [] ,
            checked_id : 0
        }
    },
    props: [ 'text' , 'current_option_id' , 'ids' , 'show_filter' , 'model_id' , 'field_name' , 'multi'] ,

    methods : {
        apply(){
            console.log(this.ids);
            console.log(this.current_option_id);
            //todo set option name
            if(this.multi === true){
                this.$emit('applied' , this.field_name , this.checked_ids);
            }else{
                this.$emit('applied' , this.field_name , this.checked_id);
            }
            this.option_name ='';
            if(this.multi === true){


                for(let key in this.options){
                    if(this.checked_ids.indexOf(this.options[key].id) >=0){
                        this.options[key].checked = true;
                        this.option_name += this.options[key].name
                    }else{
                        this.options[key].checked = false;
                    }
                }
            }else{
                for(let key in this.options){
                    if(this.options[key].id === this.checked_id){
                        this.option_name = this.options[key].name
                    }
                }
            }
            this.$emit('show_filter' , this.field_name , false);
            this.$emit('post_name' , this.field_name , this.option_name);
        } ,
        radioChange (e) {
            console.log('radio发生change事件，携带value值为：', e.target  , e.target.dataset)
            this.checked_id =  e.target.value;
        },
        remove_option(){
            this.checked_id = 0;
            this.checked_ids = [];
            console.log("remove_option");
            for(let key in this.options){
                this.options[key].checked = false;
            }
            this.option_name = this.text
            this.apply();
        },
        toggle_panel (){
            this.$emit('show_filter' , this.field_name , !this.show_filter);
        },
        load_option(){
            var that = this
            var api_url = api_host + 'house/service/load_options';
            this.$http.get(api_url , {
                params: {
                    site_id: site_id ,
                    query : {
                        model_id : this.model_id ,
                        field_name : this.field_name
                    }
                }
            }).then(function(data){
                console.log(data);
                that.options = data.data;
            });
        }
    },
    created (){
        this.checked_ids = this.ids || [];
        this.checked_id = this.current_option_id || 0
        this.load_option();
    },
    computed: {

    }


});
