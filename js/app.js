/**
 * @project ebid
 * @file app.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
'use strict';

define("app",['angular','controllers', 'directives','angular_route','angular_animate','kendo', 'angular_messages', 'angular_sanitize', 'angular_slick', 'angularfire', 'angular-moment'], function(angular){
    var app = angular.module('ebid', ['ngRoute','kendo.directives','ebid/controller', 'ebid/directives', 'ngAnimate','ngMessages','ngSanitize', 'slick', 'firebase', 'angularMoment']);
	app.constant('baseHref', '/ebid/index.html');
	app.config(function($locationProvider) {
		  $locationProvider.html5Mode({enabled: false, requireBase: true});
	})
	app.config(['$routeProvider', '$locationProvider',
		function($routeProvider, $locationProvider){
			$routeProvider.
			when('/',{
				templateUrl: 'partial/index.html',
				controller: 'homeController'
			})
			.when('/category',{
				templateUrl: 'partial/category.html',
				controller: 'categoryController'				
			})
            .when('/category/:categoryId', {
                templateUrl: 'partial/category.html',
                controller: 'categoryController'
             })
			.when('/bid/item/:itemId',{
				templateUrl: 'partial/bid/item.html',
				controller: 'itemController'				
			})
            .when('/bid/item/:itemId/edit',{
                templateUrl: 'partial/bid/form.html',
                controller: 'bidEditController'
            })
            .when('/bid/item/:itemId/result',{
                templateUrl: 'partial/bid/result.html',
                controller: 'resultController'
            })
			.when('/bid/add', {
				templateUrl: 'partial/bid/form.html',
				controller: 'bidAddController'
			})
			.when('/user',{
				templateUrl: 'partial/user/index.html',
				controller: 'userController'					
			})
			.when('/auth/login',{
				templateUrl: 'partial/auth/login.html',
				controller: 'loginController'					
			})
			.when('/auth/register',{
				templateUrl: 'partial/auth/register.html',
				controller: 'registerController'					
			})
            .when('/help/rule', {
                templateUrl:'partial/help/rule.html',
                controller: 'helpController'
            })
            .when('/help/dispute', {
                templateUrl:'partial/help/dispute.html',
                controller: 'helpController'
            })
            .when('/help/contact', {
                templateUrl:'partial/help/contact.html',
                controller: 'helpController'
            })
            .when('/bid/thankyou', {
                templateUrl:'partial/bid/thankyou.html',
                controller: 'bidController'
            })
			.otherwise({
				templateUrl: 'partial/404.html',
				controller: 'NotFoundController'	
			});
		}
	]);
	/** clear cache in develop model**/
	app.run(function($rootScope, $templateCache) {
		   $rootScope.$on('$viewContentLoaded', function() {
		      $templateCache.removeAll();
		   });
            $rootScope.$on('$routeChangeStart', function(next, current) {
                $('.zoomContainer').remove();
            });
		});
	return app;
});