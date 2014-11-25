/**
 * @project ebid
 * @file controllers.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
define("controllers", ['angular','kendo','bootstrap'], function(angular){
	var BASEURL = 'index.php';
	var SUCCESS = 0;
	var FAILURE = 1;
	var LOGIN_REQUIRE = 2;
	var ebidController = angular.module('ebid/controller', []);
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
	}
	var renderLoginState = function($scope){
		$.ajax({
			url: BASEURL + '/auth/islogin',
			dataType: "json",
			type: "POST"
		}).done(function(data){
			if(data.type == 0 && data.data.islogin){
				$("#unlogin").hide();
				$("#alreadylogin").show();
				$scope.username = data.data.username;
				$scope.$apply();
			}
		});
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
					$scope.username = username;
					$("#unlogin").hide();
					$("#alreadylogin").show();
					$('#loginModal').modal('hide');
					var path = $location.path();
					if(/^\/auth\/login/.test(path)){
						setTimeout(function(){
							$location.path('/');
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
		}
		$scope.login = function(){
			var username = $("#username").val();
			var passwd = $("#password").val();
			login(username, passwd);
		};
		$scope.logout = function(){
			$.ajax({
				url: BASEURL + '/auth/logout',
				dataType: "json",
				type: "POST"
			}).done(function(data){
				$("#unlogin").show();
				$("#alreadylogin").hide();
				$scope.InfoNotification.show(data.message, "success");
				$scope.$apply();
			});
		}
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

	ebidController.controller('itemController',['$scope', function($scope){
		kendo.culture("en-US"); 
		$scope.price = 29999.99;
		$scope.bidPrice = $scope.price + 500;
		$scope.priceCurrency = kendo.toString($scope.price, "c");
		$scope.bidPriceCurrency = kendo.toString($scope.bidPrice, "c");
		$scope.bidnumber = 0;
		$scope.placebid = function(){
			$scope.bidnumber++;
			$scope.price = $scope.bidPrice;
			$scope.bidPrice = $scope.price + 500;
			$scope.priceCurrency = kendo.toString($scope.price, "c");
			$scope.bidPriceCurrency = kendo.toString($scope.bidPrice, "c");
			$(".pricecurrency").css("opacity", 0);
			$(".pricecurrency").fadeTo('slow', 1);
			$("#bidTextbox").data("kendoNumericTextBox").min($scope.bidPrice);
		};
		$("#bidTextbox").width(100);
		var timer;
		$('#detail_tab  > li > a').hover(function () {
			var current = $(this);
			clearTimeout(timer);
			timer = setTimeout(function () {
				current.tab('show');
			}, 200);

		});

		//var productImg = $("#productImg").data('elevateZoom');
		//productImg.options.gallery = "productGallery";
		//productImg.options.imageCrossfade = true;


	}]);
	ebidController.controller('categoryController',['$scope', function($scope){

	}]);
	ebidController.controller('userController',['$scope', function($scope){

	}]);	
	ebidController.controller('loginController',['$scope', '$location', function($scope, $location){
		$('#login_Info_Panel').hide();
		isLogin(function(){
			$location.path('/');
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
	ebidController.controller('homeController',['$scope', function($scope){
		$scope.source = new kendo.data.DataSource({
			transport: {
				read: {
					url: "test/products.json",
					dataType: "json",
					cache: true
				}
			},
			pageSize: 10
		});

		$scope.listViewTemplate = $("#template").html();
	}]);
	ebidController.controller('NotFoundController',['$scope', '$location', function($scope, $location){
		$scope.homeURL = '#';
	}]);	
	ebidController.controller('bidAddController',['$scope', '$location', function($scope, $location){
		isLogin(null, function(){
			$location.path('/auth/login');
			if(!$scope.$$phase) $scope.$apply();
		});
		$scope.bidType = [
							{ listas: "Auction", listId: 1 },
							{ listas: "Fixed price", listId: 2 }
						];
		$scope.shippingType = [
								{ vendors: "USPS", listId: 1 },
								{ vendors: "Fedex", listId: 2 },
								{ vendors: "UPS", listId: 3 }
							];

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
            data: [{'ImageName': '404.jpg', 'ImageURL':'upload/images/wensheng/404.jpg'}]
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
                                    $scope.Imageslistsource.remove(item);
                                }
                            });
                        });
                    }
                }
		};
        $scope.ImageDefault = function(name, url){
            var a = name;
        }
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
            }
        }
    }]);
	return ebidController;
});