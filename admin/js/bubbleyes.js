(function( $ ) {

	'use strict';

	$(function() {

		var $form     = $('#bubbleyes-options');
		var $overlay  = $('#bubbleyes-overlay');
		var $progress = $overlay.find('.bubbleyes-progress-bar');
		var $status   = $overlay.find('.bubbleyes-progress-status');
		var $errors   = $overlay.find('.bubbleyes-errors');

		var Synchronizer = function() {

			var stopped = false;
			var errors  = 0;
			var startAt = 0;
			var chunkSize = 25;

			return {

				startBatchSync: function() {

					stopped = false;

					$status.html('Synchronizing...');
					$errors.empty();

					batch(startAt, 'bubbleyes_batch_start');

					function batch(startAt, action) {

						if(stopped) return;

						$.getJSON(ajaxurl, {
							action: action || 'bubbleyes_batch_sync',
							chunk_size: chunkSize,
							start_at: startAt
						}, onSuccess).fail(onFail);

					}

					function onSuccess(data) {

						if(!data) return onFail();

						if(data.error) {
							errors++;
							$errors.append(data.errorMessage + '<br>');
						}

						if(data.done) {
							if(data.failed.length) {
								$status.html('Done, but we had some problems with the products below.');
								for (var i = 0; i < data.failed.length; i++) {
									$errors.append('<strong>' + data.failed[i].name + '</strong> ');
									$errors.append('<i>SKU: ' + data.failed[i].sku + '</i><br>');
									$errors.append('<i>' + data.failed[i].message + '</i><br>');
								}
							} else if( ! $errors.is(':empty')) {
								$status.html('Done, but we had some problems.');
							} else {
								$status.html('All products synchronized successfully!');
								setTimeout(function() {
									$overlay.removeAttr('style');
									location.reload();
								}, 3000);
							}
							return;
						}

						var currentProgress = data.startAt > data.total ? data.total : data.startAt;
						$progress.css('width', (currentProgress / data.total) * 100 + '%');
						$status.html('Processed ' + currentProgress + ' of ' + data.total + ' products');
						batch(data.startAt);

					}

					function onFail(response) {
						$status.html('We had some problems synchronizing your products.');
						if(response) $errors.html(response.responseText);
						if(response && response.response) $errors.html(response.response.Message);
					}
				},

				stopBatchSync: function() {
					$overlay.removeAttr('style');
					stopped = true;
				}

			};
		}();

		$('#bubbleyes-resync, #bubbleyes-submit-sync').on('click', function(event) {

			event.preventDefault();

			$overlay.show();
			$progress.removeAttr('style');
			$status.html('Initializing...');
			$errors.empty();

			$form.ajaxSubmit({
				success: function(data) {
					if(data === 'error') {
						$status.html('Please enter a valid API key.');
						return;
					}
					Synchronizer.startBatchSync();
				}
			});

		});

		$('.bubbleyes-overlay-close').on('click', function(event) {
			Synchronizer.stopBatchSync();
		});

	});

})( jQuery );
