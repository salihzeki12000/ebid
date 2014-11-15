/**
 * @project ebid
 * @file controllers.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
define("controllers", ['angular','kendo','bootstrap'], function(angular){
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
	}
	
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

	ebidController.controller('mainController',['$scope', function($scope){
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
		});
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
	ebidController.controller('loginController',['$scope', function($scope){
		
	}]);	
	ebidController.controller('registerController',['$scope', function($scope){
		
	}]);
	ebidController.controller('homeController',['$scope', function($scope){
		$scope.source = new kendo.data.DataSource({
            transport: {
                read: {
                    url: "http://localhost/demo/products.json",
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
	return ebidController;
});