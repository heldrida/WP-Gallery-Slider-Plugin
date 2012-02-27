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

		$(function(){
			$('.cpt-o-pos').on('keyup', function(){

				var post_id = $(this).attr('name').replace(/[^0-9]/ig, '' );

				$.get('', { 'action' : 're_order', 'post_id' : post_id, 'order_pos' : $(this).val() }, function(data) {
					console.log( data );
				});

			});
		});

		$(function() {

			var setOrderNr = function(el){
					
				var post_id = $(el).attr('name').replace(/[^0-9]/ig, '' );
				console.log( 'Post_id: ' + post_id );
				$.get('', { 'action' : 're_order', 'post_id' : post_id, 'order_pos' : $(el).val() }, function(data) {
					console.log('Success:');
					console.log( data );
				});

			};

			var reOrderItems = function(){
				console.log( $('.wp-list-table tr') );
				$('.wp-list-table tbody tr').each(function(k,v){

					var i = k + 1;
					var curEl = $('.wp-list-table tbody tr').eq(k).find('.cpt-o-pos');

					curEl.val( i );
					setOrderNr( curEl );

					/*
			   		var index = $(el.item).index() + 1;
			   		var curEl = $(el.item).find('.cpt-o-pos');

			   		// set order val
			   		console.log(index);
			   		curEl.val( index );
			   		console.log( curEl.val() );
					*/
				});

			};

			$( "#the-list" ).sortable({
			   update: function(e, el) {
			   		
			   		reOrderItems();
			   		/*
			   		var index = $(el.item).index() + 1;
			   		var curEl = $(el.item).find('.cpt-o-pos');

			   		// set order val
			   		console.log(index);
			   		curEl.val( index );
			   		console.log( curEl.val() );
			   		*/
			   }
			});
			$( "#the-list" ).disableSelection();

		});
		
	});
	
})( jQuery );