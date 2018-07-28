/*
@author   		CaiShuai (caishuai@58.com,58caishuai@gmail.com)
@date    		2012-11-19
@edition	 	1.5.beta
@dependencies  	jQuery
*/
;(function($){
    $(function(){
        //获取各个E对象
        var $repayMode = $("#repayMode"),
            $loanType  = $("#loanType"),
            $cLoan = $("#cLoan"),
            $fLoan = $("#fLoan"),
            $computeMode = $("#computeMode"),
            $housePrice = $("#housePrice"),
            $mP = $("#mP"),
            $mY = $("#mY"),
            $totalLoanA = $("#totalLoanA"),
            $hideDiv = $("#hideDiv"),
            $switchDiv = $("#switchDiv"),
            $switchDiv_hP = $("#switchDiv_hP"),
            $switchDiv_tL = $("#switchDiv_tL"),
            $cal_reset_b = $("#cal_reset_b"),
            $cal_button = $("#cal_button"),
            $rs_show = $("#rs_show"),
            $monthlyRepay = $(".monthlyRepay"),
            $monthlyRepayDetails = $(".monthlyRepayDetails"),
            $cRate = $("#cRate"),
            $fRate = $("#fRate");
        $cs_tuijian = $("#cs_tuijian"),//推荐区域
            $showList = $("#showList");
        //获取计算结果对象集
        var $housingPrice = $("#housingPrice"),
            $appraisalPrice = $("#appraisalPrice"),
            $totalLoan = $("#totalLoan"),
            $initialPayment = $("#initialPayment"),
            $totalRepayment = $("#totalRepayment"),
            $totalInterest = $("#totalInterest"),
            $loanMonths = $("#loanMonths"),
            $monthlyRepayment = $("#monthlyRepayment"),
            $monthlyRepaymentDetails = $("#monthlyRepaymentDetails"),
            $loanInterestRate = $("#loanInterestRate"),
            $fundInterestRate = $("#fundInterestRate"),
            computeStatus = false;//计算状态

        //配置
        var cookieReserved = $LS.methods.getCookie("city") || "bj";
        $cs_tuijian.hide();
        $hideDiv.hide();
        $switchDiv_tL.hide();
        $rs_show.hide();
        var urlValue = $LS.methods.getUrl("fj");
        if(/\d+/.test(urlValue)){
            $housePrice.val(urlValue);
        }
        //组合模式下，无计算方式和房屋总价
        $loanType.change(function(){
            if($(this).val() == "3"){
                $switchDiv.hide();
                $hideDiv.show();
            }else{
                $switchDiv.show();
                $hideDiv.hide();
            }
            //设置auto_calc
            $LS.methods.setAutoCalc(computeStatus,$cal_button);
        });
        //计算方式为贷款总额时，无房屋总价设置
        $computeMode.change(function(){
            if($(this).val() == "2"){
                $switchDiv_hP.hide();
                $switchDiv_tL.show();

            }else{
                $switchDiv_hP.show();
                $switchDiv_tL.hide();
            }
            //设置auto_calc
            $LS.methods.setAutoCalc(computeStatus,$cal_button);
        });
        //根据还款方式，确定结果集显示
        $repayMode.change(function(){
            if($(this).val() == "1"){
                $monthlyRepay.show();
                $monthlyRepayDetails.hide();
            }else{
                $monthlyRepay.hide();
                $monthlyRepayDetails.show();
            }
            //设置auto_calc
            $LS.methods.setAutoCalc(computeStatus,$cal_button);
        });
        //根据贷款类型，确定结果集显示
        $loanType.change(function(){
            if($(this).val() == "1"){
                $cRate.show();
                $fRate.hide();
            }else if($(this).val() == "2"){
                $cRate.hide();
                $fRate.show();
                $fRate.next().hide();
            }else{
                $cRate.show();
                $fRate.show();
            }
            //设置auto_calc
            $LS.methods.setAutoCalc(computeStatus,$cal_button);
        });
        $mP.change(function(){
            //设置auto_calc
            $LS.methods.setAutoCalc(computeStatus,$cal_button);
        });
        $mY.change(function(){
            //设置auto_calc
            $LS.methods.setAutoCalc(computeStatus,$cal_button);
        });
        //恢复默认值
        $cal_reset_b.bind("click",function(){
            $repayMode.get(0).selectedIndex = 0;
            $loanType.get(0).selectedIndex = 0;
            $computeMode.get(0).selectedIndex = 0;
            $housePrice.val("");
            $cLoan.val("");
            $fLoan.val("");
            $totalLoan.val("");
            $mP.get(0).selectedIndex = 6;
            $mY.get(0).selectedIndex = 20;
            $switchDiv.show();
            $hideDiv.hide();
            $switchDiv_hP.show();
            $switchDiv_tL.hide();
            $rs_show.fadeOut("slow");
            computeStatus = false;
            return false;
        });

        //验证输入项为数字且设置默认值
        var objElement = {
            "totalLoanA" : $totalLoanA,
            "housePrice" : $housePrice,
            "cLoan"      : $cLoan,
            "fLoan"      : $fLoan
        }
        for(var prop in objElement){
            objElement[prop].bind({
                "keyup" : function(){
                    return $LS.methods.checkNum(this.value,this);
                },
                "blur" : function(){
                    if(this.value == ""){
                        this.value = "0.00";
                    }
                    //设置auto_calc
                    $LS.methods.setAutoCalc(computeStatus,$cal_button);
                    return $LS.methods.checkNum(this.value,this);
                },
                "click" : function(){
                    if(this.value == "0.00"){
                        this.value = "";
                    }
                }
            });
        }
        //开始计算
        $cal_button.bind("click",function(){
            computeStatus = true;
            $rs_show.fadeIn("slow");
            //根据还款方式，确定结果集显示
            if($repayMode.val() == "1"){
                $monthlyRepay.show();
                $monthlyRepayDetails.hide();
            }else{
                $monthlyRepay.hide();
                $monthlyRepayDetails.show();
            }
            //根据贷款类型，确定结果集显示
            if($loanType.val() == "1"){
                $cRate.show();
                $fRate.hide();
            }else if($loanType.val() == "2"){
                $cRate.hide();
                $fRate.show();
            }else{
                $cRate.show();
                $fRate.show();
            }
            //设置计算参数
            var rs = $CS.utils.calculator.loanCalc({
                repaymentMode : $repayMode.val() || "1",
                loanType : $loanType.val() || "1",
                commercialLoan : $cLoan.val() || "0",
                housingFundLoan : $fLoan.val() || "0",
                computeMode : $computeMode.val() || "1",
                housingPrice : $housePrice.val() || "0",
                mortgageProportion : $mP.val() || "7",
                loanPrice : $totalLoanA.val() || "0",
                mortgageYears : $mY.val() || "20"
            });
            //获取计算结果
            $housingPrice.empty().html((rs.housingPrice - 0).toFixed(2));
            $appraisalPrice.empty().html(rs.appraisalPrice.toFixed(2));
            $totalLoan.empty().html((rs.totalLoan - 0).toFixed(2));
            $initialPayment.empty().html(rs.initialPayment.toFixed(2));
            $totalRepayment.empty().html((rs.totalRepayment - 0).toFixed(2));
            $totalInterest.empty().html(rs.totalInterest.toFixed(2));
            $loanMonths.empty().html(rs.loanMonths);
            $monthlyRepayment.empty().html((rs.monthlyRepayment * 10000).toFixed(2));
            $loanInterestRate.empty().html(rs.loanInterestRate + "%");
            $fundInterestRate.empty().html(rs.fundInterestRate + "%");
            if(rs.housingPrice == 0){
                $housePrice.val("0.00");
            }
            if(rs.commercialLoan == 0){
                $cLoan.val("0.00");
            }
            if(rs.housingFundLoan == 0){
                $fLoan.val("0.00");
            }
            if(rs.loanPrice == 0){
                $totalLoanA.val("0.00");
            }
            //每次计算更新list option
            $monthlyRepaymentDetails.find("option").remove();
            //获取列表数据
            for(var prop in rs.monthlyRepaymentDetails){
                var _A = (rs.monthlyRepaymentDetails[prop] * 10000).toFixed(2);
                var _B = "<option value=" + "'" +prop + "'" + ">" + prop + "  " + _A + " 元"+"</option>";
                $monthlyRepaymentDetails.append(_B);
            }
            return false;
        });
        function queryString(key,val){
            var  uri = window.location.search, re = new RegExp("(&?)" + key + "=([^&?]*)", "ig"),
                search=uri.replace(re,(val?'$1'+key+'='+val:'')); search=search.replace(/^\?&/g,'?');
            if(val===undefined)return ((uri.match(re)) ? RegExp.$2 : null);
            return (uri.match(re)) ? (search==='?'?'':search):(uri?uri+'&':'?')+key+'='+val;
        }
        var houseprice=(queryString('price')||0)-0;
        if(houseprice){
            $('#housePrice').val(houseprice);
            $('#cal_button').click();
        }
    });
})(jQuery);