jQuery(function($){
			// simple multiple select
			var template_features = '';
			// multiple select with AJAX search
			$('#eddpf_select2_posts').change(function(){
				if($(this).val()!=''){
				$("#eddpf_section_features").find('table').remove();
				$("#eddpf_section_features").html('<b>'+eddpf_object.trad_loading+'....</b>');

				   var data = {
		              'action': 'eddpf_ajax_post',
		              'type': 'json',
		             _ajax_nonce :eddpf_object.nonce,
		              'p':$("#eddpf_select2_posts").val(),
		           };
		           jQuery.post(ajaxurl, data, function(response) {
		             	json = $.parseJSON(response);
		             	var options = [];
		             	console.log(json);
							if ( json ) {
								// json is the array of arrays, and each of them contains ID and the Label of the option
								$.each( json, function( index, text ) { // do not forget that "index" is just auto incremented value
									options.push( { id: text[0], text: text[1],template:text[2]  } );
									template_features = text[2];
								});
								$("#eddpf_section_features").html(template_features);
							}
		           });
		       }//clear
			});
			//$(document).on("change","#eddpf_select2_posts",function(){
			//});
});