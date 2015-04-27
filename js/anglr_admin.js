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
					$('#api_key').val(data);
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
