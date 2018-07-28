<script>
    define(["Vue"], function (Vue) {
        Vue.component('item_product', {
            template: `




             <label class="weui-cell weui-check__label" :for="'s11' + item.id">

                <div class="weui-cell__hd" >
                    <input type="radio" @click="select_product(item)" :id="'s11' + item.id" v-model="selected_id"   class="weui-check" :value="item.id" >
                    <i class="weui-icon-checked"></i>
                </div>

                <div class="thumb_wrap weui-media-box__hd">
                    <img class="weui-media-box__thumb" :src="item.thumb[0].url" alt="">
                </div>
                <a :href1="'/jiazheng/product/detail?id='+item.id" class="weui-media-box__bd">
                    <h4 class="weui-media-box__title">{{item.title}}</h4>
                    <p class="weui-media-box__desc">{{item.description}}</p>
                </a>
                </label>
`,
            data: function () {
                return {
                    selected_id: 0
                }

            },
            props: ['item', 'current_product_id'],
            methods: {
                select_product: function (item) {
                    this.selected_id = item.id
                    this.$emit('checked', item)
                }
            },
            created: function () {
                this.selected_id = this.current_product_id
            },
            watch: {
                current_product_id: function (new_val) {
                    console.log(new_val);
                    this.selected_id = new_val
                }
            }
        });
    });

</script>