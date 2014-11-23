/**
 * @project ebid
 * @file app.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
requirejs.config({
	baseUrl: 'js',
	paths: {
		jquery : '../vendor/jquery/dist/jquery',
		angular : '../vendor/angular/angular',
		angularplugin : '../vendor/angular',
		bootstrap : '../vendor/bootstrap/dist/js/bootstrap',	
		kendo : ['../vendor/kendo/src/js/kendo.all', '../vendor/kendo/js/kendo.all.min'],
		elevatezoom : '../vendor/elevatezoom/jquery.elevatezoom',
		bootstrapHoverDropdown : '../vendor/bootstrap-hover-dropdown/bootstrap-hover-dropdown',
		fancybox : '../vendor/fancybox/source/jquery.fancybox',
		wow : '../vendor/wow/dist/wow',
	},
	shim:{
		'angular' : {
			deps: ['jquery'],
			exports: 'angular'
		},
		'jquery' : {
			exports: '$'
		},
		'angularplugin/angular-route' :{
			deps: ['jquery', 'angular']
		},
		'angularplugin/angular-animate':{
			deps: ['jquery', 'angular']
		},
		'angularplugin/angular-messages':{
			deps: ['jquery', 'angular']
		},
		'kendo' :{
			deps: ['jquery', 'angular']
		},
		'elevatezoom':{
			deps: ['jquery']
		},
		'bootstrap':{
			deps: ['jquery', 'bootstrapHoverDropdown']
		},
		'bootstrapHoverDropdown':{
			deps: ['jquery']
		},
		'fancybox':{
			deps: ['jquery']
		}
	}
});

require(['angular', 'jquery', 'app', 'wow'], function(angular, $){
	$(document).ready(function(){
		try{
			angular.bootstrap(document, ['ebid']);
		}catch (e) {
            console.error(e.stack || e.message || e);
        }
	});
});