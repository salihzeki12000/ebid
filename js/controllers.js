/**
 * @project ebid
 * @file controllers.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
define("controllers", ['angular','kendo','bootstrap'], function(angular){
	var ebidController = angular.module('ebid/controller', []);
	ebidController.controller('mainController',['$scope', function($scope){
		
	}]);
	
	ebidController.controller('defaultController',['$scope', function($scope){
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
	return ebidController;
});