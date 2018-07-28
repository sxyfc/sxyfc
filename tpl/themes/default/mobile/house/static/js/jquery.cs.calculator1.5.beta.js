/*
@author   		CaiShuai (caishuai@58.com,58caishuai@gmail.com)
@date    		2012-11-10
@edition	 	1.5.beta
@dependencies  	jQuery
*/
;(function ($) {
    if (typeof jQuery === 'undefined') {
        alert("please import jquery.js file");
        return;
    }
    $CS = $.$CS || {};
    $CS.utils = {
        calculator: {
            loanCalc: function (settings) {
                var options = {};
                var defaults = {
                    repaymentMode: "1",
                    loanType: "1",
                    commercialLoan: "280",
                    housingFundLoan: "280",
                    computeMode: "1",
                    housingPrice: "280",
                    mortgageProportion: "7",
                    loanPrice: "280",
                    mortgageYears: "20"
                };
                var options = $.extend(options, defaults, settings);
                options.appraisalPrice = options.housingPrice * 0.8;
                options.initialPayment = options.housingPrice - options.appraisalPrice * (options.mortgageProportion / 10);
                options.loanPayment = options.housingPrice - options.initialPayment;
                options.loanMonths = options.mortgageYears * 12;
                options.totalLoan = (options.loanType == "3" ? parseFloat(options.commercialLoan) + parseFloat(options.housingFundLoan) : (options.computeMode == "1" ? options.loanPayment : options.loanPrice));
                options.getLoanInterestRate = function (years) {
                    switch (true) {
                        case years <= 0.5:
                            return 5.6;
                            break;
                        case 0.5 < years && years <= 1:
                            return 5.6;
                            break;
                        case 1 < years && years <= 3:
                            return 6;
                            break;
                        case 3 < years && years <= 5:
                            return 6;
                            break;
                        case years > 5:
                            return 6.15;
                            break;
                        default:
                            return 6.15;
                            break;
                    }
                };
                options.getFundInterestRate = function (years) {
                    switch (true) {
                        case years <= 5:
                            return 3.75;
                            break;
                        case years > 5:
                            return 4.25;
                            break;
                        default:
                            return 3.75;
                    }
                };
                options.setRepayment = function (repaymentMode) {
                    if (repaymentMode == "1") {
                        if (options.loanType == "1") {
                            options.monthlyRepayment = (function () {
                                return (options.totalLoan * (options.monthlyLoanInterestRate / 100) * Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths) - 1);
                            })();
                            options.totalRepayment = options.monthlyRepayment * options.loanMonths;
                            options.totalInterest = options.totalRepayment - options.totalLoan;
                        } else if (options.loanType == "2") {
                            options.monthlyRepayment = (function () {
                                return (options.totalLoan * (options.monthlyFundInterestRate / 100) * Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths) - 1);
                            })();
                            options.totalRepayment = options.monthlyRepayment * options.loanMonths;
                            options.totalInterest = options.totalRepayment - options.totalLoan;
                        } else {
                            options.monthlyRepayment = (function () {
                                return ((options.commercialLoan * (options.monthlyLoanInterestRate / 100) * Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths) - 1)) + ((options.housingFundLoan * (options.monthlyFundInterestRate / 100) * Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths) - 1));
                            })();
                            options.totalRepayment = options.monthlyRepayment * options.loanMonths;
                            options.totalInterest = options.totalRepayment - options.totalLoan;
                        }

                    } else if (repaymentMode == "2") {
                        if (options.loanType == "1") {
                            options.totalInterest = options.totalLoan * (options.monthlyLoanInterestRate / 100) * (options.loanMonths + 1) / 2;
                            options.totalRepayment = parseFloat(options.totalLoan) + parseFloat(options.totalInterest);
                            options.monthlyPayback = options.totalLoan / options.loanMonths;
                            options.monthlyRepaymentDetails = (function () {
                                var objArray = {};
                                for (var i = 1; i <= options.loanMonths; i++) {
                                    objArray["第 " + i + " 期"] = options.monthlyPayback + (options.totalLoan - options.monthlyPayback * (i - 1)) * (options.monthlyLoanInterestRate / 100);
                                }
                                return objArray;
                            })();
                        } else if (options.loanType == "2") {
                            options.totalInterest = options.totalLoan * (options.monthlyFundInterestRate / 100) * (options.loanMonths + 1) / 2;
                            options.totalRepayment = parseFloat(options.totalLoan) + parseFloat(options.totalInterest);
                            options.monthlyPayback = options.totalLoan / options.loanMonths;
                            options.monthlyRepaymentDetails = (function () {
                                var objArray = {};
                                for (var i = 1; i <= options.loanMonths; i++) {
                                    objArray["第 " + i + " 期"] = options.monthlyPayback + (options.totalLoan - options.monthlyPayback * (i - 1)) * (options.monthlyFundInterestRate / 100);
                                }
                                return objArray;
                            })();
                        } else {
                            options.totalInterest = (options.commercialLoan * (options.monthlyLoanInterestRate / 100) * (options.loanMonths + 1) / 2) + (options.housingFundLoan * (options.monthlyFundInterestRate / 100) * (options.loanMonths + 1) / 2);
                            options.totalRepayment = parseFloat(options.totalLoan) + parseFloat(options.totalInterest);
                            options.monthlyLoanPayback = options.commercialLoan / options.loanMonths;
                            options.monthlyFundPayback = options.housingFundLoan / options.loanMonths;
                            options.monthlyRepaymentDetails = (function () {
                                var objArray = {};
                                for (var i = 1; i <= options.loanMonths; i++) {
                                    objArray["第 " + i + " 期"] = (options.monthlyLoanPayback + (options.commercialLoan - options.monthlyLoanPayback * (i - 1)) * (options.monthlyLoanInterestRate / 100)) + (options.monthlyFundPayback + (options.housingFundLoan - options.monthlyFundPayback * (i - 1)) * (options.monthlyFundInterestRate / 100));
                                }
                                return objArray;
                            })();
                        }
                    }
                };

                if (options.loanType == "3" || options.computeMode == "2") {
                    options.housingPrice = 0;
                    options.appraisalPrice = 0;
                    options.initialPayment = 0;
                }
                if (options.loanType == "1") {
                    options.loanInterestRate = options.getLoanInterestRate(options.mortgageYears);
                    options.fundInterestRate = "_";
                } else if (options.loanType == "2") {
                    options.fundInterestRate = options.getFundInterestRate(options.mortgageYears);
                    options.loanInterestRate = "_";
                } else {
                    options.loanInterestRate = options.getLoanInterestRate(options.mortgageYears);
                    options.fundInterestRate = options.getFundInterestRate(options.mortgageYears);
                }

                options.monthlyLoanInterestRate = (options.loanInterestRate / 12);
                options.monthlyFundInterestRate = (options.fundInterestRate / 12);
                options.setRepayment(options.repaymentMode);
                return options;
            },
            taxCalc: function (settings) {
                var options = {};
                var defaults = {
                    unitPrice: "0",
                    totalArea: "0"
                };
                var options = $.extend(options, defaults, settings);
                options.totalPrice = options.unitPrice * options.totalArea / 10000;
                options.getContractTaxRate = function (totalArea) {
                    switch (true) {
                        case totalArea < 90:
                            return 0.01;
                            break;
                        case 90 <= totalArea && totalArea < 140:
                            return 0.015;
                            break;
                        case totalArea >= 140:
                            return 0.03;
                            break;
                        default:
                            return 0.03;
                            break;
                    }
                }
                options.contractTaxRate = options.getContractTaxRate(options.totalArea);
                options.contractTax = options.totalPrice * options.contractTaxRate;
                options.assessmentTax = (options.totalPrice < 50 && 300) || (50 <= options.totalPrice && options.totalPrice < 100 && 500) || (options.totalPrice >= 100 && options.totalPrice * 10);
                options.notariaFee = (options.totalPrice * 10000 <= 500000 && (function () {
                    return options.totalPrice * 10000 * 0.003 < 200 ? 200 : options.totalPrice * 10000 * 0.003
                })()) || (500000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 5000000 && 1500 + (options.totalPrice - 50) * 10000 * 0.0025) || (5000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 10000000 && 12750 + (options.totalPrice - 500) * 10000 * 0.002) || (10000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 20000000 && 22750 + (options.totalPrice - 1000) * 10000 * 0.0015) || (20000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 50000000 && 37750 + (options.totalPrice - 2000) * 10000 * 0.001) || (50000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 100000000 && 67750 + (options.totalPrice - 5000) * 10000 * 0.0005) || (100000000 < options.totalPrice * 10000 && 92750 + (options.totalPrice - 10000) * 10000 * 0.0001);

                options.otherFee = 2.5 * options.totalArea + 340;
                options.totalTax = options.contractTax * 10000 + options.assessmentTax + options.notariaFee + options.otherFee;
                return options;

            },
            appraisalCalc: function (settings) {
                var options = {};
                var defaults = {
                    currentPurchaseCapital: "0",
                    householdSize: "0",
                    monthlyHouseholdIncome: "0",
                    monthlyPurchaseExpenditure: "0",
                    desiredFloorSpace: "0",
                    desiredResidentialZone: "aaaaaa",
                    loanType: "1",
                    housingFundLoan: "0",
                    repaymentMode: "1",
                    mortgageYears: "30"
                };
                var options = $.extend(options, defaults, settings);
                options.loanMonths = options.mortgageYears * 12;
                var A = (options.currentPurchaseCapital / 0.44) + 800000;
                var B = options.currentPurchaseCapital - 0 + options.monthlyPurchaseExpenditure * 240;
                options.totalPrice = Math.min(A, B) / 10000;
                options.getContractTaxRate = function (totalArea) {
                    switch (true) {
                        case totalArea < 90:
                            return 0.01;
                            break;
                        case 90 <= totalArea && totalArea < 140:
                            return 0.015;
                            break;
                        case totalArea >= 140:
                            return 0.03;
                            break;
                        default:
                            return 0.03;
                            break;
                    }
                }
                options.contractTaxRate = options.getContractTaxRate(options.desiredFloorSpace);
                options.contractTax = options.totalPrice * options.contractTaxRate;
                options.assessmentTax = (options.totalPrice < 50 && 300) || (50 <= options.totalPrice && options.totalPrice < 100 && 500) || (options.totalPrice >= 100 && options.totalPrice * 10);
                options.notariaFee = (options.totalPrice * 10000 <= 500000 && (function () {
                    return options.totalPrice * 10000 * 0.003 < 200 ? 200 : options.totalPrice * 10000 * 0.003
                })()) || (500000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 5000000 && 1500 + (options.totalPrice - 50) * 10000 * 0.0025) || (5000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 10000000 && 12750 + (options.totalPrice - 500) * 10000 * 0.002) || (10000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 20000000 && 22750 + (options.totalPrice - 1000) * 10000 * 0.0015) || (20000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 50000000 && 37750 + (options.totalPrice - 2000) * 10000 * 0.001) || (50000000 < options.totalPrice * 10000 && options.totalPrice * 10000 <= 100000000 && 67750 + (options.totalPrice - 5000) * 10000 * 0.0005) || (100000000 < options.totalPrice * 10000 && 92750 + (options.totalPrice - 10000) * 10000 * 0.0001);
                options.otherFee = 2.5 * options.desiredFloorSpace + 340;

                options.totalTax = options.contractTax * 10000 + options.assessmentTax + options.notariaFee + options.otherFee;

                options.totalLoan = (options.loanType == "1" ? (options.totalPrice - options.currentPurchaseCapital / 10000) : options.housingFundLoan);
                options.getLoanInterestRate = function (years) {
                    switch (true) {
                        case years <= 0.5:
                            return 5.6;
                            break;
                        case 0.5 < years && years <= 1:
                            return 5.6;
                            break;
                        case 1 < years && years <= 3:
                            return 6;
                            break;
                        case 3 < years && years <= 5:
                            return 6;
                            break;
                        case years > 5:
                            return 6.15;
                            break;
                        default:
                            return 6.15;
                            break;
                    }
                };
                options.getFundInterestRate = function (years) {
                    switch (true) {
                        case years <= 5:
                            return 3.75;
                            break;
                        case years > 5:
                            return 4.25;
                            break;
                        default:
                            return 3.75;
                    }
                };

                options.setRepayment = function (repaymentMode) {
                    if (repaymentMode == "1") {
                        if (options.loanType == "1") {
                            options.monthlyRepayment = (function () {
                                return (options.totalLoan * (options.monthlyLoanInterestRate / 100) * Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths) - 1);
                            })();
                        } else if (options.loanType == "2") {
                            options.monthlyRepayment = (function () {
                                return (options.totalLoan * (options.monthlyFundInterestRate / 100) * Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths) - 1);
                            })();
                        }
                    } else if (repaymentMode == "2") {
                        if (options.loanType == "1") {
                            options.monthlyPayback = options.totalLoan / options.loanMonths;
                            options.monthlyRepaymentDetails = (function () {
                                var objArray = {};
                                for (var i = 1; i <= options.loanMonths; i++) {
                                    objArray["第 " + i + " 期"] = options.monthlyPayback + (options.totalLoan - options.monthlyPayback * (i - 1)) * (options.monthlyLoanInterestRate / 100);
                                }
                                return objArray;
                            })();
                        } else if (options.loanType == "2") {
                            options.monthlyPayback = options.totalLoan / options.loanMonths;
                            options.monthlyRepaymentDetails = (function () {
                                var objArray = {};
                                for (var i = 1; i <= options.loanMonths; i++) {
                                    objArray["第 " + i + " 期"] = options.monthlyPayback + (options.totalLoan - options.monthlyPayback * (i - 1)) * (options.monthlyFundInterestRate / 100);
                                }
                                return objArray;
                            })();
                        }
                    }
                };
                if (options.loanType == "1") {
                    options.loanInterestRate = options.getLoanInterestRate(options.mortgageYears);
                } else if (options.loanType == "2") {
                    options.fundInterestRate = options.getFundInterestRate(options.mortgageYears);
                }
                options.monthlyLoanInterestRate = (options.loanInterestRate / 12);
                options.monthlyFundInterestRate = (options.fundInterestRate / 12);
                options.setRepayment(options.repaymentMode);
                return options;
            },
            fundCalc: function (settings) {
                var options = {};
                var defaults = {
                    personalMonthlyFundDeposit: "0",
                    personalDepositRate: "0.12",
                    spouseMonthlyFundDeposit: "0",
                    spouseDepositRate: "0.12",
                    mortgageYears: "20",
                    personalCreditRating: "3",
                    totalLoan: "0"
                };
                var options = $.extend(options, defaults, settings);
                options.loanMonths = options.mortgageYears * 12;
                options.getFundInterestRate = function (years) {
                    switch (true) {
                        case years <= 5:
                            return 3.75;
                            break;
                        case years > 5:
                            return 4.25;
                            break;
                        default:
                            return 3.75;
                    }
                };
                options.fundLoanUpperLimit = ((options.personalMonthlyFundDeposit / options.personalDepositRate) + (options.spouseMonthlyFundDeposit / options.spouseDepositRate)) * 0.45 * options.loanMonths / 10000;
                options.theoreticalLoanUpperLimit = options.personalCreditRating == "3" ? 80 : (options.personalCreditRating == "2" ? 92 : 104);
                options.maxLoanPrice = Math.min(options.fundLoanUpperLimit, options.theoreticalLoanUpperLimit);
                options.fundInterestRate = options.getFundInterestRate(options.mortgageYears);
                options.monthlyFundInterestRate = (options.fundInterestRate / 12);
                options.monthlyRepaymentA = (function () {
                    return (options.totalLoan * 10000 * (options.monthlyFundInterestRate / 100) * Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths) - 1);
                })();
                options.totalPrincipalInterestA = options.monthlyRepaymentA * options.loanMonths;
                options.monthlyRepaymentB = (options.totalLoan * 10000 / options.loanMonths) + (options.totalLoan * 10000 * options.monthlyFundInterestRate / 100);
                options.totalInterest = options.totalLoan * 10000 * options.monthlyFundInterestRate / 100 * (options.loanMonths + 1) / 2;
                options.totalPrincipalInterestB = options.totalLoan * 10000 + options.totalInterest;
                return options;
            },
            prepayCalc: function (settings) {
                var options = {};
                var defaults = {
                    repaymentType: "1",
                    totalLoan: "0",
                    mortgageYears: "20",
                    originalLoanRate: "6.5",
                    firstRepayTimeY: "2012",
                    firstRepayTimeM: "8",
                    projectedRepayTimeY: "2030",
                    projectedRepayTimeM: "8",
                    repaymentMode: "1",
                    repayMount: "0",
                    processMode: "1"
                };
                var options = $.extend(options, defaults, settings);
                options.loanMonths = options.mortgageYears * 12;
                options.getMonthlyInterestRepayed = function (i, rate) {
                    var A = (options.totalLoan * (rate / 100) * Math.pow((1 + (rate / 100)), options.loanMonths)) / (Math.pow((1 + (rate / 100)), options.loanMonths) - 1);
                    var B = (options.totalLoan * (rate / 100) * Math.pow((1 + (rate / 100)), (i - 1))) / (Math.pow((1 + (rate / 100)), options.loanMonths) - 1);
                    return A - B;
                };
                options.getRepayMonths = function (r, monthlyRepayment, surplusCapital) {
                    var A = monthlyRepayment / (monthlyRepayment - r * surplusCapital);
                    var B = 1 + r;
                    return Math.ceil(Math.log(A) / Math.log(B));
                };
                if (options.mortgageYears == "0.5") {
                    if (((options.firstRepayTimeM - 0) + 6) - 1 <= 12) {
                        options.oldLastRepayDateY = parseFloat(options.firstRepayTimeY) - 0;
                        options.oldLastRepayDateM = parseFloat(options.firstRepayTimeM) - 1 + 6;
                    } else {
                        options.oldLastRepayDateY = parseFloat(options.firstRepayTimeY) + 1;
                        options.oldLastRepayDateM = parseFloat(options.firstRepayTimeM) - 13 + 6;
                    }
                } else if (options.firstRepayTimeM - 1 == 0) {
                    options.oldLastRepayDateY = parseFloat(options.firstRepayTimeY) + parseFloat(options.mortgageYears) - 1;
                    options.oldLastRepayDateM = 12;
                } else {
                    options.oldLastRepayDateY = parseFloat(options.firstRepayTimeY) + parseFloat(options.mortgageYears);
                    options.oldLastRepayDateM = parseFloat(options.firstRepayTimeM) - 1;
                }
                options.monthsRepayed = (options.projectedRepayTimeY - options.firstRepayTimeY) * 12 + parseFloat(options.projectedRepayTimeM) - options.firstRepayTimeM;

                if (options.repaymentType == "1") {
                    options.monthlyLoanInterestRate = (options.originalLoanRate / 12);
                    options.monthlyRepayment = (function () {
                        return (options.totalLoan * (options.monthlyLoanInterestRate / 100) * Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyLoanInterestRate / 100)), options.loanMonths) - 1);
                    })();
                    options.totalRepayment = options.monthlyRepayment * options.loanMonths;
                    options.totalInterest = options.totalRepayment - options.totalLoan;
                    options.interestRepayed = (function () {
                        var temp = options.monthsRepayed;
                        var sum = 0;
                        for (var i = 1; i <= temp; i++) {
                            sum += options.getMonthlyInterestRepayed(i, options.monthlyLoanInterestRate);
                        }
                        return sum;
                    })();
                } else {
                    options.monthlyFundInterestRate = (options.originalLoanRate / 12);
                    options.monthlyRepayment = (function () {
                        return (options.totalLoan * (options.monthlyFundInterestRate / 100) * Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths)) / (Math.pow((1 + (options.monthlyFundInterestRate / 100)), options.loanMonths) - 1);
                    })();
                    options.totalRepayment = options.monthlyRepayment * options.loanMonths;
                    options.totalInterest = options.totalRepayment - options.totalLoan;
                    options.interestRepayed = (function () {
                        var temp = options.monthsRepayed;
                        var sum = 0;
                        for (var i = 1; i <= temp; i++) {
                            sum += options.getMonthlyInterestRepayed(i, options.monthlyFundInterestRate);
                        }
                        return sum;
                    })();
                }
                if ((options.projectedRepayTimeY > options.oldLastRepayDateY) || ((options.projectedRepayTimeY == options.oldLastRepayDateY) && (options.projectedRepayTimeM > options.oldLastRepayDateM))) {
                    options.totalRepayed = options.totalRepayment;
                    options.monthlyRepaymentFromNext = 0.00;
                    options.onceRepayment = 0.00;
                    options.saveInterestPayments = 0.00;
                    options.newLastRepayDateY = options.oldLastRepayDateY;
                    options.newLastRepayDateM = options.oldLastRepayDateM;
                } else if ((options.projectedRepayTimeY == options.oldLastRepayDateY) && (options.projectedRepayTimeM == options.oldLastRepayDateM)) {
                    options.totalRepayed = options.monthlyRepayment * options.monthsRepayed;
                    options.monthlyRepaymentFromNext = 0.00;
                    options.onceRepayment = options.monthlyRepayment;
                    options.saveInterestPayments = 0.00;
                    options.newLastRepayDateY = options.oldLastRepayDateY;
                    options.newLastRepayDateM = options.oldLastRepayDateM;
                    if ((options.repaymentMode != "1") && (options.repayMount != 0)) {
                        options.result = "您的提前还款额已足够还清所欠贷款";
                    }
                } else {
                    options.totalRepayed = options.monthlyRepayment * options.monthsRepayed;
                    options.surplusCapital = options.totalLoan - (options.totalRepayed - options.interestRepayed);
                    if (options.repaymentMode == "1") {
                        options.monthlyRepaymentFromNext = 0.00;
                        options.onceRepayment = options.surplusCapital - 0 + options.getMonthlyInterestRepayed(options.monthsRepayed - 0 + 1, options.originalLoanRate / 12);
                        options.saveInterestPayments = options.totalRepayment - options.totalRepayed - options.onceRepayment;
                        options.newLastRepayDateY = options.projectedRepayTimeY;
                        options.newLastRepayDateM = options.projectedRepayTimeM;
                    } else {
                        if (options.repayMount == 0) {
                            options.onceRepayment = options.monthlyRepayment;
                            options.monthlyRepaymentFromNext = options.monthlyRepayment;
                            options.saveInterestPayments = 0;
                            options.newLastRepayDateY = options.oldLastRepayDateY;
                            options.newLastRepayDateM = options.oldLastRepayDateM;
                            options.saveInterestPayments = 0;
                        } else {
                            options.onceRepayment = Math.min(options.repayMount - 0 + options.monthlyRepayment - 0, options.surplusCapital - 0 + options.getMonthlyInterestRepayed(options.monthsRepayed - 0 + 1, options.originalLoanRate / 12));
                            var rate = options.originalLoanRate / 1200;
                            options.result = ((options.repayMount - 0 + options.monthlyRepayment - 0 - options.getMonthlyInterestRepayed(options.monthsRepayed - 0 + 1, options.originalLoanRate / 12)) > options.surplusCapital ? "您的提前还款额已足够还清所欠贷款" : "");
                            if (options.result != "您的提前还款额已足够还清所欠贷款") {
                                if (options.processMode == "1") {
                                    options.repayMonths = options.getRepayMonths(rate, options.monthlyRepayment, options.surplusCapital - options.repayMount) - 1;
                                    options.monthlyRepaymentFromNext = (function () {
                                        return ((options.surplusCapital - options.onceRepayment + options.getMonthlyInterestRepayed(options.monthsRepayed - 0 + 1, options.originalLoanRate / 12)) * rate * Math.pow((1 + rate), options.repayMonths)) / (Math.pow((1 + rate), options.repayMonths) - 1);
                                    })();
                                    options.saveInterestPayments = options.totalRepayment - options.totalRepayed - options.monthlyRepaymentFromNext * options.repayMonths - options.onceRepayment;
                                    var Y = Math.floor(options.repayMonths / 12) - 0;
                                    var M = (options.repayMonths % 12) - 0;
                                    if (M + parseFloat(options.projectedRepayTimeM) <= 12) {
                                        options.newLastRepayDateY = parseFloat(options.projectedRepayTimeY) + Y;
                                        options.newLastRepayDateM = parseFloat(options.projectedRepayTimeM) + M;
                                    } else {
                                        options.newLastRepayDateY = parseFloat(options.projectedRepayTimeY) + Y + 1;
                                        options.newLastRepayDateM = parseFloat(options.projectedRepayTimeM) + M - 12;
                                    }
                                } else {
                                    options.newLastRepayDateY = options.oldLastRepayDateY;
                                    options.newLastRepayDateM = options.oldLastRepayDateM;
                                    options.repayMonths = options.loanMonths - options.monthsRepayed - 1;
                                    options.monthlyRepaymentFromNext = (function () {
                                        return ((options.surplusCapital - options.onceRepayment + options.getMonthlyInterestRepayed(options.monthsRepayed - 0 + 1, options.originalLoanRate / 12)) * rate * Math.pow((1 + rate), options.repayMonths)) / (Math.pow((1 + rate), options.repayMonths) - 1);
                                    })();
                                    options.saveInterestPayments = options.totalRepayment - options.totalRepayed - options.monthlyRepaymentFromNext * options.repayMonths - options.onceRepayment;

                                }
                            } else {
                                options.monthlyRepaymentFromNext = 0;
                                options.onceRepayment = options.surplusCapital - 0 + options.getMonthlyInterestRepayed(options.monthsRepayed - 0 + 1, options.originalLoanRate / 12);
                                options.saveInterestPayments = options.totalRepayment - options.totalRepayed - options.onceRepayment;
                                options.newLastRepayDateY = options.projectedRepayTimeY;
                                options.newLastRepayDateM = options.projectedRepayTimeM;
                            }
                        }
                    }
                }
                return options;
            }

        }

    };
})(jQuery);