(function($){
	
	$('document').ready(function(){
		
		console.log('Loaded: gallery/public/js/gallery.js');
		
		$(function() {
			//http://www.vulgarisoip.com/files/search.phps
			$('#post-title-type').suggest("?action=gallery-ajax", {
				onSelect: function() {
					$('#link-to-post-id').val( this.value );
				}
			});
			
		});
		
	});
	
})( jQuery );