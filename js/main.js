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
        angular_route: '../vendor/angular-route/angular-route',
        angular_animate: '../vendor/angular-animate/angular-animate',
        angular_sanitize: '../vendor/angular-sanitize/angular-sanitize',
		angular_messages : '../vendor/angular-messages/angular-messages',
		bootstrap : '../vendor/bootstrap/dist/js/bootstrap',	
		kendo : ['../vendor/kendo/src/src/kendo.all', '../vendor/kendo/js/kendo.all.min'],
		elevatezoom : '../vendor/elevatezoom/jquery.elevatezoom',
		bootstrapHoverDropdown : '../vendor/bootstrap-hover-dropdown/bootstrap-hover-dropdown',
		fancybox : '../vendor/fancybox/source/jquery.fancybox',
		wow : '../vendor/wow/dist/wow',
        slickcarousel : '../vendor/slick-carousel/slick/slick',
        angular_slick : '../vendor/angular-slick/dist/slick',
        firebase : '../vendor/firebase/firebase',
        angularfire : '../vendor/angularfire/dist/angularfire',
        momentjs : '../vendor/moment/moment',
        "angular-moment" : '../vendor/angular-moment/angular-moment'
	},
	shim:{
		'angular' : {
			deps: ['jquery'],
			exports: 'angular'
		},
		'jquery' : {
			exports: '$'
		},
		'angular_route' :{
			deps: ['jquery', 'angular']
		},
		'angular_animate':{
			deps: ['jquery', 'angular']
		},
		'angular_messages':{
			deps: ['jquery', 'angular']
		},
        'angular_sanitize':{
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
		},
        'slickcarousel':{
            deps: ['jquery']
        },
        'angular_slick':{
            deps: ['jquery', 'angular', 'slickcarousel']
        },
        'angularfire':{
            deps: ['angular', 'firebase']
        },
        'angular-moment':{
            deps: ['jquery', 'angular', 'momentjs']
        },
        'momentjs':{
            deps: ["jquery"],
            exports: 'moment'
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