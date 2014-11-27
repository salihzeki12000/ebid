/**
 * @project ebid
 * @file directives.js
 * @author Wensheng Yan
 * @date Nov 4, 2014
 * (c) 2007 - 2014 Wensheng Yan
 */
define("directives", ['angular','jquery','elevatezoom','fancybox'], function(angular, $){
	var app = angular.module('ebid/directives', []);
	app.directive('ngElevateZoom', function() {
		  return {
		    restrict: 'A',
		    link: function(scope, element, attrs) {

		      //Will watch for changes on the attribute
		      attrs.$observe('zoomImage',function(){
		        linkElevateZoom();
		      });

                var isDuplicate = false;

		      function linkElevateZoom(){
		        //Check if its not empty
		        if (!attrs.zoomImage) return;
                  if(isDuplicate) return;
                  var options = scope[attrs.kOption];
                  if(options){
                      if(options.gallery){
                          var img = $("#" + options.gallery).find("img");
                          if(img.length == 0){
                              return;
                          }
                      }
                  }
                  isDuplicate = true;
		          element.attr('data-zoom-image',attrs.zoomImage);
                  if(!options){
                      options = {};
                  }

		        $(element).elevateZoom(options);
		        $(element).bind("click", function(e){
		        	var ez = $(element).data('elevateZoom');
		        	$.fancybox(ez.getGalleryList());
		        	return false;
		        });
		      }

		      linkElevateZoom();

		    }
		  };
		});

	app.directive("compareTo", function(){
	    return {
	        require: "ngModel",
	        scope: {
	            otherModelValue: "=compareTo"
	        },
	        link: function(scope, element, attributes, ngModel) {
	             
	            ngModel.$validators.compareTo = function(modelValue) {
	                return modelValue == scope.otherModelValue;
	            };
	 
	            scope.$watch("otherModelValue", function() {
	                ngModel.$validate();
	            });
	        }
	    };
	});
	return app;
});