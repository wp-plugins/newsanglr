(
	function($){
		$('#get_api_key').click(
			function(){
				var link = this;
				var text = $(link).html();
				$(link).html('requesting api key ...');
				
				var data = {
					action: 'anglr_get_api_key_request'
				};
				
				$.get(anglr_params.ajaxurl, data, function(data){
					$(link).html(text);
					data = JSON.parse(data);
					if('error_code' in data){
						if(data.error_code == '001'){
							$('#api_key').val('');
							$('#api_key_error').text('Your site is not accessible from the outside world. Check to make sure the base-url it NOT configured as localhost. You can still test the plugin without registering, but we cannot index your articles and they will not show up on third-party blogs until this is fixed.');
						} else if (data.error_code == '002'){
							$('#api_key').val('');
							$('#api_key_error').text('Your site is not accessible from the outside world. Are you on an intranet? Please contact support@newsanglr.com for additional support.');
						}
					} else {
						$('#api_key').val(data.api_key);
						$('#api_key_error').text('');	
					}
				});
				
				//prevent default behaviour of the link
				return false;
			}
		);
		
		$('#import_articles').click(
			function(){
				var link = this;
				var text = $(link).html();
				$(link).html('importing ...');
				
				var data = {
					action: 'anglr_import_articles_request'
				};
				
				$.get(anglr_params.ajaxurl, data, function(data){
					$(link).html('Imported '+data+' articles');
					
				});
				
				//prevent default behaviour of the link
				return false;
			}
		);
		 	
	}
)(jQuery);
