/**
 * @project ebid
 * @file controllers.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
define("controllers", ['angular','kendo','bootstrap'], function(angular){
	var BASEURL = 'index.php';
    var FIREBASEURL = 'https://ebid-release.firebaseio.com';

	var SUCCESS = 0;
	var FAILURE = 1;
	var LOGIN_REQUIRE = 2;
    var EXPIRE = 5;

	var ebidController = angular.module('ebid/controller', []);
    ebidController.constant('GlobalEnum', {
        EnumBidType : [
            { listas: "Auction", listId: 1 },
            { listas: "Fixed price", listId: 2 }
        ],
        EnumShippingType : [
            { vendors: "USPS", listId: 1 },
            { vendors: "Fedex", listId: 2 },
            { vendors: "UPS", listId: 3 }
        ],

        EnumConditionType : [
            {conditionId: 1, conditionName: "New"},
            {conditionId: 2, conditionName: "Used"}
        ]
    });

    function EnumBidTypeName (GlobalEnum, key) {
        return GlobalEnum.EnumBidType.filter(function(enumItem) {
            return enumItem.listId === parseInt(key);
        })[0].listas;
    };

    function EnumConditionTypeName (GlobalEnum, key) {
        return GlobalEnum.EnumConditionType.filter(function(enumItem) {
            return enumItem.conditionId === parseInt(key);
        })[0].conditionName;
    };

    function EnumShippingTypeName(GlobalEnum, key){
        return GlobalEnum.EnumShippingType.filter(function(enumItem) {
            return enumItem.listId === parseInt(key);
        })[0].vendors;
    };
    ebidController.filter('reverse', function() {
        return function(items) {
            return items.slice().reverse();
        };
    });
    String.prototype.startWith = function(str) {
        if(str==null||str==""||this.length==0||str.length>this.length)
            return false;

        if(this.substr(0,str.length)==str)
            return true;
        else
            return false;

        return true;
    };
	var animate = function($element, $animateName,callback){
		$element.addClass($animateName);
		$element.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
			$element.removeClass($animateName);
			if(callback){
				(callback)();
			}
		});
	};
	var repeatText = function() {
		animate($('.bannerText'),'animated bounceIn');
		setTimeout(repeatText, 5000);
	};
    var getCookie = function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
        }
        return "";
    };
	var isLogin = function(logincallback, notlogincallback){
		$.ajax({
			url: BASEURL + '/auth/islogin',
			dataType: "json",
			type: "POST"
		}).done(function(data){
			if(data.type == 0 && data.data.islogin){
				if(logincallback){
					logincallback();
				}
			}else{
				if(notlogincallback){
					notlogincallback();
				}
			}
		});
	};
	var renderLoginState = function($scope){
        var id = getCookie('id');
        var name = getCookie('username');
        $scope.username = name;
        $scope.id = id;
        if(name && id){
            $("#unlogin").hide();
            $("#alreadylogin").show();
        }
	};

	function adjustModalMaxHeightAndPosition(){
		$('.modal').each(function(){
			if($(this).hasClass('in') == false){
				$(this).show();
			};
			var contentHeight = $(window).height() - 60;
			var headerHeight = $(this).find('.modal-header').outerHeight() || 2;
			var footerHeight = $(this).find('.modal-footer').outerHeight() || 2;

			$(this).find('.modal-content').css({
				'max-height': function () {
					return contentHeight;
				}
			});

			$(this).find('.modal-body').css({
				'max-height': function () {
					return (contentHeight - (headerHeight + footerHeight));
				}
			});

			$(this).find('.modal-dialog').css({
				'margin-top': function () {
					return -($(this).outerHeight() / 2);
				},
				'margin-left': function () {
					return -($(this).outerWidth() / 2);
				}
			});
			if($(this).hasClass('in') == false){
				$(this).hide();
			};
		});
	};

	$(window).resize(adjustModalMaxHeightAndPosition).trigger("resize");

	ebidController.controller('mainController',['$scope', '$location', function($scope, $location){
		animate($('.logo'), 'animated rotateIn',function(){
			animate($('.logo'), 'animated bounceIn');
		});

		$('.logo').mouseover(function(){
			animate($('.logo'), 'animated bounceIn');
		});
		$('.bannerText').hide();
		animate($('.banner'), 'animated bounceInRight', function(){
			$('.bannerText').show();
			animate($('.bannerText'), 'animated bounceInRight', function(){
				animate($('.bannerText'),'animated bounceIn');
				setTimeout(repeatText, 5000);
			});
		});
		$('#loginModal').on('show.bs.modal', function (event) {
			adjustModalMaxHeightAndPosition();
			$("#loginInfoPanel").hide();
		});
		$scope.$on('login', function(event, args) {
			login(args.username, args.password);
		});
		var login = function(username, password){
			$("#loginInfoPanel").hide();

			$.ajax({
				url: BASEURL + '/auth/login',
				dataType: "json",
				type: "POST",
				data: {
					_username : username,
					_password : password
				}
			}).done(function(data){
				if(data.type == SUCCESS){
					$scope.InfoNotification.show(data.message, "success");
                    var data = data.data;
					$scope.username = data.username;
                    $scope.id = data.id;
					$("#unlogin").hide();
					$("#alreadylogin").show();
					$('#loginModal').modal('hide');
					var path = $location.path();
					if(/^\/auth\/login/.test(path)){
						setTimeout(function(){
							$location.path('/').replace();
							$scope.$apply();
						},2000);
					}
				}else{
					$scope.InfoNotification.show(data.message, "error");
					$("#loginInfoPanel .panel-body").html(data.message);
					$("#loginInfoPanel").show();
				}
				$scope.$apply();
			});
		};
        $scope.getClass = function(path) {
            var currentPath = $location.path();
            if (currentPath == path) {
                return "active"
            } else {
                return ""
            }
        };
		$scope.login = function(){
			var username = $("#username").val();
			var passwd = $("#password").val();
			login(username, passwd);
		};
        $scope.autologin = function($event){
            if($event.keyCode == 13){
                $scope.login();
            }
        };
		$scope.logout = function(){
			$.ajax({
				url: BASEURL + '/auth/logout',
				dataType: "json",
				type: "POST"
			}).done(function(data){
				$("#unlogin").show();
				$("#alreadylogin").hide();
                $scope.username = "unknown";
                $scope.id = -1;
				$scope.InfoNotification.show(data.message, "success");
                var currentPath = $location.path();
                if(currentPath.startWith('/user') || currentPath == '/bid/add'){
                    $location.path('/').replace();
                }
				$scope.$apply();
			});
		};
		$scope.username = "unknown";
		$('.fb').mouseover(function(){
			animate($('.fb'), 'animated bounceIn');
		});
		$('.tw').mouseover(function(){
			animate($('.tw'), 'animated bounceIn');
		});
		$('.google').mouseover(function(){
			animate($('.google'), 'animated bounceIn');
		});
		$('.youtube').mouseover(function(){
			animate($('.youtube'), 'animated bounceIn');
		});
		$scope.onShow =	function (e) {
			if (!$("." + e.sender._guid)[1]) {
				var element = e.element.parent(),
				eWidth = element.width(),
				eHeight = element.height(),
				wWidth = $(window).width(),
				wHeight = $(window).height(),
				newTop, newLeft;
	
				newLeft = Math.floor(wWidth / 2 - eWidth / 2);
				newTop = Math.floor(wHeight / 2 - eHeight / 2);
	
				e.element.parent().css({top: newTop, left: newLeft});
			}
		};
		renderLoginState($scope);
		new WOW().init();
	}]);

	ebidController.controller('itemController',['$scope', '$route','$http', '$location', '$routeParams', '$compile', '$sce', '$firebase', '$interval','GlobalEnum',function($scope, $route, $http,$location, $routeParams, $compile, $sce, $firebase, $interval, GlobalEnum){
        kendo.culture("en-US");
        $scope.itemId = $routeParams.itemId;

        var ref = new Firebase(FIREBASEURL + "/bid/item/" +$scope.itemId );
        var sync = $firebase(ref);
        var syncObject = sync.$asObject();
        syncObject.$bindTo($scope, "price");

        var hisref = new Firebase(FIREBASEURL + "/bid/history/" +$scope.itemId );
        var hissync = $firebase(hisref);
        $scope.histories = hissync.$asArray();

        var comref = new Firebase(FIREBASEURL + "/bid/comment/" +$scope.itemId );
        var comsync = $firebase(comref);
        $scope.comments = comsync.$asArray();

        $http({
            method: 'GET',
            url: BASEURL + '/product/item/' + $scope.itemId
        })
        .success(function(data, status, headers, config) {
                if(data.type == FAILURE){
                    $location.path('/nopage').replace();
                    $route.reload();
                }
                if(data.type == EXPIRE){
                    $location.path('/bid/item/' + $scope.itemId + '/result').replace();
                    $route.reload();
                }
                if(data.type == SUCCESS){
                    $scope.product = data.data;
                    document.title = " " + $scope.product.pname +" | eBid";
                    $scope.product.auction = EnumBidTypeName(GlobalEnum, $scope.product.auction);
                    $scope.product.condition = EnumConditionTypeName(GlobalEnum, $scope.product.condition);
                    $scope.product.shippingType = EnumShippingTypeName(GlobalEnum, $scope.product.shippingType);
                    if($scope.product.shippingCost == 0) {
                        $scope.product.shippingCost = "Free";
                    }

                    //$('#descriptionContent').document.body.innerHTML = $scope.product.description;
                    $scope.descriptionURL = BASEURL + "/product/item/"+ $scope.itemId + "/description" ;

                    $scope.userId = $scope.$parent.id;
                    var end = moment($scope.product.endTime);
                    var now = moment($scope.product.serverTime);
                    var diff = moment.duration(now.diff(moment()));
                    var duration = moment.duration(end.diff(now));
                    $scope.remaindays = duration.days();
                    $scope.remainhours = duration.hours();
                    if($scope.remaindays != 0){
                        $scope.product.remaining = $scope.remaindays + " d " + $scope.remainhours + " h";
                    }else{
                        $scope.remainminutes = duration.minutes();
                        if($scope.remainhours != 0){
                            $scope.product.remaining = $scope.remainhours + " h " + $scope.remainminutes + " m";
                        }else{
                            $scope.remainseconds = duration.seconds();
                            if($scope.remainminutes > 0){
                                $scope.product.remaining = $scope.remainminutes + " m " + $scope.remainseconds + " s";
                            }else{
                                $scope.product.remaining = $scope.remainseconds + " s ";
                            }
                            $('#timeReminder').addClass("hurry");
                            $scope.clock = $interval(function(){
                                var now = moment().add(diff);
                                var duration = moment.duration(end.diff(now));
                                if(duration < 0){
                                    $scope.stopClock();
                                    $location.path('/bid/item/' + $scope.itemId + '/result').replace();
                                    $route.reload();
                                }
                                $scope.remainminutes = duration.minutes();
                                $scope.remainseconds = duration.seconds();
                                if($scope.remainminutes != 0){
                                    $scope.product.remaining = $scope.remainminutes + " m " + $scope.remainseconds + " s";
                                }else{
                                    $scope.product.remaining = $scope.remainseconds + " s ";
                                }
                            }, 1000);

                        }
                    }

                    //sync.$set($scope.product);
                    //$scope.product.description = $sce.trustAsHtml($scope.product.description);
                }
        });

        $scope.stopClock = function() {
            if (angular.isDefined($scope.clock)) {
                $interval.cancel($scope.clock);
                $scope.clock = undefined;
            }
        };

        $scope.$on('$destroy', function () {
            $interval.cancel($scope.clock);
        });

        $('#commentModal').on('show.bs.modal', function (event) {
            adjustModalMaxHeightAndPosition();
        });

        $scope.toBigImg = function(image){
            if(image){
                if(image.match(/\$_[\d]+\.JPG/))
                return image.replace(/\$_[\d]+\.JPG/, '$_57.JPG');
            }
        };
        var marginPic = function(e){
            var width = e.width();
            var height = e.height();
            if(e.width() <= 300 ){
                e.css('margin-left', '100px');
            }else{
                e.css('margin-left', '0px');
            }
            if(e.height() <= 240){
                e.css('margin-top', '50px');
            }
            else if(e.height() <= 300){
                e.css('margin-top', '25px');
            }
            else{
                e.css('margin-top', '0px');
            }
        };
        $scope.ImageZoomConfig = {
            gallery: "productGallery",
            imageCrossfade : "true",
            galleryActiveClass: "active",
            onZoomedImageLoaded : marginPic,
            onImageSwapComplete : marginPic
        };
        $scope.getIncPrice = function (price){
            if(price < 10){
                return 0.5;
            }else if(price < 50){
                return 1;
            }else if(price < 100){
                return 2;
            }else if(price < 300){
                return 5;
            }else if(price < 1000){
                return 10;
            }else if(price < 10000){
                return 50;
            }else if(price < 50000){
                return 500;
            }else{
                return 1000;
            }
        };
        //recompile productImg
        $scope.$watch(
            function () { return $('#productGallery').html() },
            function(newval, oldval){
                if(oldval.trim() != newval.trim())
                    $compile(angular.element("#productImg"))($scope);
            }, true);

        $scope.$watch(
            'price.currentPrice',
            function(newval, oldval){
                //current price change
                if(newval && oldval){
                    if($scope.product.userMinPrice <= newval){
                        $scope.setUserMinPrice(newval);
                    }
                }
            }, true);

        $scope.$watch(
            'price.bidNumber',
            function(newval, oldval){
                //current price change
                if(!$scope.product) return;
                if(newval == 1 && $scope.product.userMinPrice == $scope.price.currentPrice){
                    $scope.setUserMinPrice($scope.product.userMinPrice);
                }
            }, true);

        $scope.setUserMinPrice = function(currentPrice){
            var inc = $scope.getIncPrice(currentPrice);
            $scope.product.userMinPrice = currentPrice + inc;
            $(".pricecurrency").css("opacity", 0);
            $(".pricecurrency").fadeTo('slow', 1);
        };

        $scope.$watch(
            'product.userMinPrice',
            function(newval, oldval){
                if(newval){
                    $scope.product.bidPrice = newval;
                    var textBox = $("#bidTextbox").data("kendoNumericTextBox");
                    if(textBox){
                        textBox.value(newval);
                        textBox.min(newval);
                        textBox.step($scope.getIncPrice(newval));
                    }

                }
            }, true);

/*
        $scope.$watch(
            'price.increase',
            function(newval, oldval){
                if(newval){
                    $("#bidTextbox").data("kendoNumericTextBox").step($scope.price.increase);
                }
            }, true);
*/
        $scope.getPriceCurrency = function(price){
            return kendo.toString(price, "c");
        };

		$scope.placebid = function(){
            if(!$scope.userId){
                if($scope.$parent.id){
                    $scope.userId = $scope.$parent.id;
                }else{
                    $('#loginModal').modal();
                    return;
                }
            }
            if($scope.userId == $scope.product.seller.uid){
                $scope.InfoNotification.show("You can't bid your product.", "error");
                return;
            }
            $scope.product.userMinPrice = $scope.product.bidPrice + $scope.getIncPrice($scope.product.bidPrice);
            $http({
                method: 'GET',
                url: BASEURL + '/product/item/' + $scope.itemId + '/bid/' + $scope.product.bidPrice
            })
            .success(function(data, status, headers, config) {
                    if(data.type == SUCCESS){
                        $scope.product.isBid = true;
                        $scope.InfoNotification.show(data.messages, "success");
                    }else if(data.type == EXPIRE){
                        $scope.stopClock();
                        $location.path('/bid/item/' + $scope.itemId + '/result').replace();
                        $route.reload();
                    }
                    else{
                        $scope.InfoNotification.show(data.message, "error");
                    }
            })
            .error(function(data, status, headers, config) {
                    $scope.InfoNotification.show(data.message, "error");
            });
		};

        $scope.save_comment = function(){
            var title = $scope.user.commentTitle;
            var content = $scope.user.commentContent;
            var uname = $scope.$parent.username;
            if(!uname || uname == 'unknown'){
                uname = 'guest';
            }
            var now = moment().format('MMMM Do YYYY, h:mm:ss a');

            comref.push({name: uname, time: now, title: title, content: content});
            $scope.user.commentTitle = "";
            $scope.user.commentContent = "";
            $('#commentModal').modal('hide');
        }
	}]);
    ebidController.controller('resultController',['$scope','$http', '$location', '$routeParams','GlobalEnum', function($scope, $http, $location, $routeParams, GlobalEnum){
        kendo.culture("en-US");
        $scope.itemId = $routeParams.itemId;
        $http({
            method: 'GET',
            url: BASEURL + '/product/item/' + $scope.itemId +'/result'
        })
        .success(function(data, status, headers, config) {
                if(data.type == FAILURE){
                    $location.path('/nopage').replace();
                }
                if(data.type == SUCCESS){
                    $scope.product = data.data;
                    document.title = " " + $scope.product.pname +" | eBid";
                    $scope.product.auction = EnumBidTypeName(GlobalEnum, $scope.product.auction);
                    $scope.product.condition = EnumConditionTypeName(GlobalEnum, $scope.product.condition);
                    $scope.product.shippingType = EnumShippingTypeName(GlobalEnum, $scope.product.shippingType);
                    if($scope.product.shippingCost == 0){
                        $scope.product.shippingCost = "Free";
                    }
                    $scope.userId = $scope.$parent.id;
                    if($scope.product.isEnd == true && $scope.product.hasWinner == true){
                        $scope.product.productStatusImg = "images/imgSold.png";
                    }else{
                        $scope.product.productStatusImg = "images/imgEnded.png";
                    }

                    if($scope.product.isEnd == true && $scope.product.WinnerId == $scope.userId){
                        $scope.product.userStatusImg = "images/winner.jpg";
                    }else{
                        $scope.product.userStatusImg = "images/fail.gif";
                    }
                }else{
                    $scope.InfoNotification.show(data.message, "error");
                    $scope.$apply();
                }
        });

        $scope.getPriceCurrency = function(price){
            return kendo.toString(price, "c");
        };

        $scope.pathRedirect = function(path){
            $location.path(path);
        }

    }]);
    ebidController.controller('categoryController',['$scope', '$location','$routeParams', '$http', function($scope, $location, $routeParams, $http){
        document.title = " Category Home | eBid";
        $scope.categoryId = typeof($routeParams.categoryId) == "undefined" ? "all" : $routeParams.categoryId;
        $scope.Categorylist = new kendo.data.HierarchicalDataSource({
            transport: {
                read: {
                    url: BASEURL + "/ajax/getCategoryHierarchy" ,
                    dataType: "json",
                    cache: true
                }
            },
            schema: {
                data: 'data',
                model:{
                    children:'items'
                }
            }
        });
        $scope.CategoryTemplate = $('#CategoryTemplate').html();
        $scope.categoryOptions = {
        };

        $scope.pageSize = 12;

        $scope.productlist = new kendo.data.DataSource({
            transport: {
                read: {
                    url: BASEURL + "/ajax/getProductByCategory/" + $scope.categoryId ,
                    dataType: "json"
                }
            },
            pageSize: $scope.pageSize,
            serverPaging: true,
            schema: {
                data: "data",
                total: "total"
            }
        });
        $scope.productlistTemplate = $("#productlisttemplate").html();
        $scope.pathRedirect = function(path){
            $location.path(path);
        };
        $scope.linkItem = function(itemId){
            $location.path('/bid/item/' + itemId);
        };
	}]);
	ebidController.controller('userController',['$scope', '$location', '$http','GlobalEnum',function($scope, $location, $http, GlobalEnum){
        document.title = " User Home | eBid"
        isLogin(null, function(){
            $location.path('/auth/login').replace();
            if(!$scope.$$phase) $scope.$apply();
        });
        $http({
            method: 'GET',
            url: BASEURL + '/user/index/5'
        })
        .success(function(data, status, headers, config) {
                if(data.type == FAILURE){
                    $location.path('/').replace();
                }
                if(data.type == SUCCESS){
                    $scope.userIndex = data.data;
                }
        });
        $scope.goToEdit = function(itemId){
            $location.path('bid/item/' + itemId +'/edit');
        };
        $scope.Actionlist = new kendo.data.HierarchicalDataSource({data: [
            {name: 'Sell', path: '#/user/sell', imgIcon: 'glyphicon glyphicon-usd'},
            {name: 'bid', path: '#/user/bid', imgIcon: 'glyphicon glyphicon-fire'}
        ]});
        $scope.actionTemplate = $('#actionTemplate').html();
        $scope.getPriceCurrency = function(price){
            return kendo.toString(price, "c");
        };
        $scope.getConditionName = function(number){
            return EnumConditionTypeName(GlobalEnum, number);
        }

	}]);
	ebidController.controller('loginController',['$scope', '$location', function($scope, $location){
        document.title = " Login | eBid"
		$('#login_Info_Panel').hide();
		isLogin(function(){
			$location.path('/').replace();
			if(!$scope.$$phase) $scope.$apply();
		},null);
		$scope.submit = function(){
			if(!$scope.login_Form.$valid){
				return;
			}
			var username = $("#login_username").val();
			var passwd = $("#login_password").val();
			var args = {
					'username': username,
					'password': passwd
			};
			$scope.$emit('login', args);
		};
	}]);	
	ebidController.controller('registerController',['$scope', '$http', '$location', function($scope, $http, $location){
        document.title = " Registration | eBid"
		$scope.submit = function(){
			if(!$scope.registrationForm.$valid){
				return;
			}
			$http({
				method: 'POST',
				url: BASEURL + '/user/register',
				data: $scope.user
			})
			.success(function(data, status, headers, config) {
				if(data.type == SUCCESS){
					$scope.InfoNotification.show(data.message + " You will redirect to login page within 2 seconds.", "success");
					setTimeout(function(){
						$location.path('/auth/login');
						$scope.$apply();
					},2000);
				}else{
					$scope.InfoNotification.show(data.message, "error");
				}
				
			 })
			 .error(function(data, status, headers, config) {
				 $scope.InfoNotification.show("please contact system administrator.", "error");
			  });
		};
	}]);
	ebidController.controller('homeController',['$scope', '$location',function($scope, $location){
        document.title = " Electronics, Books, Coupons and More | eBid";
		$scope.endingsoonlist = new kendo.data.DataSource({
			transport: {
				read: {
					url: BASEURL + "/ajax/commingsoonProductList/10" ,
					dataType: "json",
					cache: true
				}
			},
			pageSize: 10,
            schema: {
                data: "data"
            }
		});

        $scope.hotbiddinglist = new kendo.data.DataSource({
            transport: {
                read: {
                    url: BASEURL + "/ajax/hotBiddingProductList/10" ,
                    dataType: "json",
                    cache: true
                }
            },
            pageSize: 10,
            schema: {
                data: "data"
            }
        });
        $scope.linkItem = function(itemId){
            $location.path('/bid/item/' + itemId);
        }
		$scope.listViewTemplate = $("#template").html();
	}]);
	ebidController.controller('NotFoundController',['$scope', '$location', function($scope, $location){
        document.title = " Page Not Found | eBid";
		$scope.homeURL = '#';
	}]);
    ebidController.controller('helpController',['$scope', '$location', function($scope, $location){
        document.title = " Customer Center | eBid";
        var path = $location.path();
    }]);
    var initialBidForm = function($scope, $location, $http, GlobalEnum){
        isLogin(null, function(){
            $location.path('/auth/login').replace();
            if(!$scope.$$phase) $scope.$apply();
        });
        $scope.bidType = GlobalEnum.EnumBidType;
        $scope.shippingType = GlobalEnum.EnumShippingType;

        $scope.conditionType = GlobalEnum.EnumConditionType;

        $scope.categoryType = new kendo.data.DataSource ({
            transport: {
                read: {
                    url: BASEURL + "/ajax/getCategory",
                    dataType: "json"
                }
            },
            dataTextField: "categoryName",
            dataValueField: "categoryId"

        });
        $scope.ProductNameAutoComplete = new kendo.data.DataSource({
            transport: {
                read: {
                    //url: BASEURL + "/ajax/findProducts",
                    url: BASEURL + "/ajax/autoCompleteProducts",
                    dataType: "json",
                    data: {
                        maxFetch: 3,
                        searchTerm: function() {
                            return $('#ProductName').val();
                        }
                    }
                }
            },
            serverFiltering: true,
            total: 3,
            schema: {
                data: "productFamilies"
            }
        });
        $scope.imagelistViewTemplate = $('#imagepreviewtemplate').html();
        $scope.Imageslistsource = new kendo.data.DataSource({
            data: []
        });
        $scope.ProductNameAutoCompleteOptions = {
            dataSource: $scope.ProductNameAutoComplete,
            dataTextField: "title",
            valuePrimitive: true,
            filter: 'contains',
            placeholder : "Enter item name here",
            // using  templates:
            template: kendo.template($("#ProductNameAutoCompleteTemplate").html()),
            animation: {
                close: {
                    effects: "fadeOut zoom:out",
                    duration: 300
                },
                open: {
                    effects: "fadeIn zoom:in",
                    duration: 300
                }
            },
            change: function(){
                this.dataSource.read();
            },
            select: function(e) {
                var val = e.item.text().trim();
                var data = $scope.ProductNameAutoComplete.data().slice(0);
                var obj;
                $.each(data, function(i, item){
                    var name = item.title.trim();
                    if(val === name){
                        obj = item;
                        return false;
                    }
                });
                var itemId = obj.itemId;
                //remove all pictures
                $scope.Imageslistsource.data([]);
                //clear default image
                $scope.product.defaultImage = null;
                $http.get(BASEURL + "/ajax/getProductById/" + itemId)
                    .success(function(data, status, headers, config) {
                        $scope.product.description = data.Description;
                        $.each(data.PictureURL, function(i, item){
                            var image = {
                                ImageName: i,
                                ImageURL: item,
                                targetUid: null
                            };
                            $scope.Imageslistsource.add(image);
                        });
                        if(!$scope.$$phase) {
                            $scope.$apply();
                        }
                    });
                /*
                 var detailurl = obj.detailURL;
                 var item = {
                 ImageName: 'original',
                 ImageURL: obj.galleryURL,
                 targetUid: null
                 };
                 $scope.Imageslistsource.add(item);
                 $http({
                 method: 'GET',
                 url: BASEURL + "/ajax/getDetail?url=" + encodeURIComponent(detailurl)
                 }).success(function(data, status, headers, config) {
                 $scope.product.description = data;
                 if(!$scope.$$phase) {
                 $scope.$apply();
                 }
                 })
                 */
            },
            height: 500
        };
        $scope.uploadPicturesOptions = {
            async: {
                saveUrl: BASEURL + "/ajax/upload",
                removeUrl: BASEURL + "/ajax/upload/remove",
                autoUpload: true
            },
            success: function (e) {
                var files = e.files;
                if (e.operation == "upload") {
                    var data = e.response;
                    if(data.type == SUCCESS){
                        $.each(data.data, function(i, item){
                            $.each(files, function(j, file){
                                if(file.name == item.ImageName){
                                    item['targetUid'] = file.uid;
                                }
                            });
                            $scope.Imageslistsource.add(item);
                        });
                        $scope.$apply();
                    }
                }else{
                    var data = $scope.Imageslistsource.data().slice(0);
                    $.each(data, function(i, item){
                        var name = item.ImageName;
                        $.each(files, function(j, file){
                            if(file.name == name){
                                if(item.ImageURL == $scope.product.defaultImage){
                                    $scope.product.defaultImage = null;
                                }
                                $scope.Imageslistsource.remove(item);
                            }
                        });
                    });
                }
            }
        };
        $scope.ImageDefault = function(name, url){
            $scope.product.defaultImage = url;

        };
        $scope.ImageDelete = function(name, uid){
            if(uid){
                var lists = $('.k-file-success');
                $.each(lists, function(i, list){
                    var test = $(list).attr('data-uid');
                    if($(list).attr('data-uid') == uid){
                        var button = $(list).find('button');
                        button.click();
                    }
                });
            }else{
                var data = $scope.Imageslistsource.data().slice(0);
                $.each(data, function(i, item){
                    if(item.ImageName == name){
                        if(item.ImageURL == $scope.product.defaultImage){
                            $scope.product.defaultImage = null;
                        }
                        $scope.Imageslistsource.remove(item);
                    }
                });
            }
        };
        $scope.product = {};

        $scope.dateTimeOptions = {
            format: "yyyy/MM/dd HH:mm"
        };
    };
    ebidController.controller('bidEditController',['$scope', '$location', '$http', '$routeParams', 'GlobalEnum', function($scope, $location, $http, $routeParams, GlobalEnum){
        document.title = " Edit Product | eBid";
        $scope.itemId = $routeParams.itemId;
        initialBidForm($scope, $location, $http, GlobalEnum);

        $http({
            method: 'GET',
            url: BASEURL + '/product/' + $scope.itemId +'/edit'
        })
        .success(function(data, status, headers, config) {
            if(data.type == SUCCESS){
                $scope.product = data.data;
                $.each($scope.product.imageLists, function(i, item){
                    $scope.Imageslistsource.add({ImageURL: item, ImageName: ''});
                });
                $scope.product.endTime = moment($scope.product.endTime).format('YYYY/MM/DD HH:mm');
            }else{
                setTimeout(function(){
                    $location.path('/user');
                    $scope.$apply();
                },2000);
            }
        })
        .error(function(data, status, headers, config) {
                $scope.InfoNotification.show("please contact system administrator.", "error");
                setTimeout(function(){
                    $location.path('/user');
                    $scope.$apply();
                },2000);
        });

        $('#startPriceForm').hide();
        $('#endDayForm').hide();

        $scope.submit = function(){
            if(!$scope.bidForm.$valid){
                return;
            }
            $scope.product.imageLists = [];
            $.each($scope.Imageslistsource.data(), function(i, item){
                $scope.product.imageLists.push(item.ImageURL);
            });
            $http({
                method: 'POST',
                url: BASEURL + '/product/' + $scope.itemId +'/edit',
                data: $scope.product
            })
                .success(function(data, status, headers, config) {
                    if(data.type == SUCCESS){
                        $scope.InfoNotification.show(data.message + " You will redirect to user home page within 2 seconds.", "success");
                        setTimeout(function(){
                            $location.path('/user');
                            $scope.$apply();
                        },2000);
                    }else{
                        $scope.InfoNotification.show(data.message, "error");
                    }
                })
                .error(function(data, status, headers, config) {
                    $scope.InfoNotification.show("please contact system administrator.", "error");
                });
        };
    }]);
	ebidController.controller('bidAddController',['$scope', '$location', '$http', 'GlobalEnum', function($scope, $location, $http, GlobalEnum){
        document.title = " Add Product | eBid";
        initialBidForm($scope, $location, $http, GlobalEnum);

        $scope.submit = function(){
            if(!$scope.bidForm.$valid){
                return;
            }
            $scope.product.imageLists = [];
            $.each($scope.Imageslistsource.data(), function(i, item){
                $scope.product.imageLists.push(item.ImageURL);
            });
            $http({
                method: 'POST',
                url: BASEURL + '/product/add',
                data: $scope.product
            })
                .success(function(data, status, headers, config) {
                    if(data.type == SUCCESS){
                        $scope.InfoNotification.show(data.message + " You will redirect to user home page within 2 seconds.", "success");
                        setTimeout(function(){
                            $location.path('/user');
                            $scope.$apply();
                        },2000);
                    }else{
                        $scope.InfoNotification.show(data.message, "error");
                    }
                })
                .error(function(data, status, headers, config) {
                    $scope.InfoNotification.show("please contact system administrator.", "error");
                });
        };
    }]);
	return ebidController;
});