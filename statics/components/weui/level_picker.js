

+ function($) {
    "use strict";

    var defaults;

    var format = function(data) {
        var result = [];
        for(var i=0;i<data.length;i++) {
            var d = data[i];
            if(/^请选择|请选择/.test(d.name)) continue;
            result.push(d);
        }
        if(result.length) return result;
        return [];
    };

    var sub = function(data) {
        console.log(data);
        if(!data.children) return [{ name: '', id: data.id }];  // 有可能某些县级市没有区
        return format(data.children);
    };

    var getLevel2 = function(d , raw) {
        console.log(d)
        for(var i=0;i< raw.length;i++) {
            if(raw[i].id === d || raw[i].name === d) return sub(raw[i]);
        }
        return [];
    };

    var getLevel3 = function(p, c , raw) {
        for(var i=0;i< raw.length;i++) {
            if(raw[i].id === p || raw[i].name === p) {
                for(var j=0;j< raw[i].children.length;j++) {
                    if(raw[i].children[j].id === c || raw[i].children[j].name === c) {
                        return sub(raw[i].children[j]);
                    }
                }
            }
        }
    };

    var parseInitValue = function (val , raw) {

        var p = raw[0], c, d;
        var tokens = val.split(' ');
        raw.map(function (t) {
            if (t.name === tokens[0]) p = t;
        });

        p.children.map(function (t) {
            if (t.name === tokens[1]) c = t;
        })
        console.log(raw);
        if (tokens[2]) {
            c.children.map(function (t) {
                if (t.name === tokens[2]) d = t;
            })
        }
        if(!c){
            c = {'name' : '', id : 0};
        }

        if (d) return [p.id, c.id, d.id];
        return [p.id, c.id];
    }

    $.fn.mhcms_level_picker = function(params , raw) {
        params = $.extend({}, defaults, params);
        return this.each(function() {
            var self = this;

            var Level_1_datas = raw.map(function(d) {
                return d.name;
            });
            var Level_1_ids = raw.map(function(d) {
                return d.id;
            });
            var Level_2_datas = sub(raw[0]);
            var Level_2_names = Level_2_datas.map(function (c) {
                return c.name;
            });
            var Level_2_ids = Level_2_datas.map(function (c) {
                return c.id;
            });
            var Level_3_datas = sub(raw[0].children[0]);

            var Level_3_names = Level_3_datas.map(function (c) {
                return c.name;
            });
            var level_3_ids = Level_3_datas.map(function (c) {
                return c.id;
            });

            var current_level_1 = Level_1_datas[0];
            var current_level_2 = Level_2_names[0];
            var current_level_3 = Level_3_names[0];

            var cols = [
                {
                    displayValues: Level_1_datas,
                    values: Level_1_ids,
                    cssClass: "col-province"
                },
                {
                    displayValues: Level_2_names,
                    values: Level_2_ids,
                    cssClass: "col-city"
                }
            ];

            if(params.showDistrict) cols.push({
                values: level_3_ids,
                displayValues: Level_3_names,
                cssClass: "col-district"
            });

            var config = {

                cssClass: "city-picker",
                rotateEffect: false,  //为了性能
                formatValue: function (p, values, displayValues) {
                    return displayValues.join(' ');
                },
                onChange: function (picker, values, displayValues) {
                    var newProvince = picker.cols[0].displayValue;
                    var newCity;
                    if(newProvince !== current_level_1) {
                        var newCities = getLevel2(newProvince , raw);
                        console.log(picker.cols[1]);
                        if(newCities.length > 0){

                            newCity = newCities[0].name;
                        }else{
                            newCity = '';
                            newCities = [{id:0 , name : ''}];
                        }
                        picker.cols[1].replaceValues(newCities.map(function (c) {
                            return c.id;
                        }), newCities.map(function (c) {
                            return c.name;
                        }));
                        if(params.showDistrict){
                            var newDistricts = getLevel3(newProvince, newCity, raw);
                            picker.cols[2].replaceValues(newDistricts.map(function (d) {
                                return d.id;
                            }), newDistricts.map(function (d) {
                                return d.name;
                            }));
                        }
                        current_level_1 = newProvince;
                        current_level_2 = newCity;
                        picker.updateValue();

                        return false; // 因为数据未更新完，所以这里不进行后序的值的处理
                    } else {
                        if(params.showDistrict) {
                            newCity = picker.cols[1].displayValue;
                            if(newCity !== current_level_2) {
                                var districts = getLevel3(newProvince, newCity, raw);
                                picker.cols[2].replaceValues(districts.map(function (d) {
                                    return d.id;
                                }), districts.map(function (d) {
                                    return d.name;
                                }));
                                current_level_2 = newCity;
                                picker.updateValue();
                                return false; // 因为数据未更新完，所以这里不进行后序的值的处理
                            }
                        }
                    }
                    //如果最后一列是空的，那么取倒数第二列
                    var len = (values[values.length-1] ? values.length - 1 : values.length - 2)
                    $(self).attr('data-code', values[len]);
                    $(self).attr('data-codes', values.join(','));

                    $('#' +  params.field_name).val(values[len]);

                    if (params.onChange) {
                        params.onChange.call(self, picker, values, displayValues);
                    }
                },

                cols: cols
            };

            if(!this) return;
            var p = $.extend({}, params, config);
            //计算value
            var val = $(this).data('val');
            if (!val) val = '';
            current_level_1 = val.split(" ")[0];
            current_level_2 = val.split(" ")[1];
            current_level_3= val.split(" ")[2];

            console.log(val);
            if(val) {
                p.value = parseInitValue(val , raw);
                if(p.value[0]) {
                    var cities = getLevel2(p.value[0] , raw);
                    p.cols[1].values = cities.map(function (c) {
                        return c.id;
                    });
                    p.cols[1].displayValues = cities.map(function (c) {
                        return c.name;
                    });
                }

                if(p.value[1]) {
                    if (params.showDistrict) {
                        var dis = getLevel3(p.value[0], p.value[1], raw);
                        p.cols[2].values = dis.map(function (d) {
                            return d.id;
                        });
                        p.cols[2].displayValues = dis.map(function (d) {
                            return d.name;
                        });
                    }
                } else {
                    if (params.showDistrict) {
                        var dis = getLevel3(p.value[0], p.cols[1].values[0], raw);
                        p.cols[2].values = dis.map(function (d) {
                            return d.id;
                        });
                        p.cols[2].displayValues = dis.map(function (d) {
                            return d.name;
                        });
                    }
                }
            }
            $(this).picker(p);
        });
    };

    defaults = $.fn.mhcms_level_picker.prototype.defaults = {
        showDistrict: true //是否显示地区选择
    };

}($);