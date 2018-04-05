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
								$("#eddpf_section_features").vSort();
							}
		           });
		       }//clear
			});
			
			 $(document).on('change','.check_yes,.check_no',function(){
			 	result = $(this).val();
			 	namesort = $(this).parent().parent().find('input.eddpf_sort');
			 	if($(this).parent().parent().find('.check_yes').is(':checked')){
			 		}else if($(this).parent().parent().find('.check_no').is(':checked')){
			 	}else{
			 		namesort.val("hidden");
			 	}
			 	if(result=='yes'){
			 		$(this).parent().parent().find('.check_no').attr('checked',false);
			 		if($(this).is(':checked')){
			 			namesort.val("yes");
			 		}
			 	}else if(result=="no"){
			 		$(this).parent().parent().find('.check_yes').attr('checked',false);
			 		if($(this).is(':checked')){
			 			namesort.val("no");
			 		}
			 	}

			 	
		 	});

			 $(document).on('change','.yes_all',function(){
			 	if($(this).is(':checked')){
			 		$('.no_all').attr('checked',false);
			 		$('.check_no').attr('checked',false);
			 		$('.check_yes').attr('checked',true);
			 		$("input.eddpf_sort").val("yes");
			 	}else{
			 		$('.no_all').attr('checked',false);
			 		$('.check_no').attr('checked',false);
			 		$('.check_yes').attr('checked',false);
			 		$("input.eddpf_sort").val("hidden");

			 	}
		 	});	
			$(document).on('change','.no_all',function(){
			 	if($(this).is(':checked')){
			 		$('.yes_all').attr('checked',false);
			 		$('.check_yes').attr('checked',false);
			 		$('.check_no').attr('checked',true);
			 		$("input.eddpf_sort").val("no");	
			 	}else{
			 		$('.yes_all').attr('checked',false);
			 		$('.check_yes').attr('checked',false);
			 		$('.check_no').attr('checked',false);
			 		$("input.eddpf_sort").val("hidden");
			 	}
		 	});	

});