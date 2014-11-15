/**
 * @project ebid
 * @file app.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
'use strict';

define("app",['angular','controllers', 'directives','angularplugin/angular-route','angularplugin/angular-animate','kendo'], function(angular){
	var app = angular.module('ebid', ['ngRoute','kendo.directives','ebid/controller', 'ebid/directives', 'ngAnimate']);
	app.constant('baseHref', '/ebid/index.html');
	app.config(function($locationProvider) {
		  $locationProvider.html5Mode({enabled: false, requireBase: true});
	})
	app.config(['$routeProvider', '$locationProvider',
		function($routeProvider, $locationProvider){
			$routeProvider.
			when('/',{
				templateUrl: 'partial/index.html',
				controller: 'defaultController'
			})
			.when('/category',{
				templateUrl: 'partial/category.html',
				controller: 'categoryController'				
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
			.otherwise({
				templateUrl: 'partial/404.html',
				controller: 'NotFoundController'	
			});
		}
	]);
	return app;
});