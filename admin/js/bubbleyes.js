(function( $ ) {

	'use strict';

	var Synchronizer = function() {

		return {

			getBatchProcess: function(action, offset) {
				// $.getJSON(ajaxurl, {action:action, offset:offset}, function(data) {
				// 	if(data.offset < data.total) {
				// 		Synchronizer.batch(action, data.offset);
				// 	}
				// }).fail(function(response) {
				// 	console.log(response);
				// });
			}
			
		};
	}();

	$(function() {
		// Synchronizer.getBatchProcess();
	});

})( jQuery );
