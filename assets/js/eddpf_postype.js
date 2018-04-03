jQuery(document).ready(function($){
	var message_confirm = eddpf_object.trad_confirm;

	$("#eddpf_addfeatures").on('click',function(){
		add_feature = jQuery(".column_parent").clone().removeClass('column_parent');
		add_feature.find('div p input[type="text"]').val("");
		add_feature.find('div p textarea').val("");
		add_feature.find('div p label.before_button_delete').append('<button type="button" title="'+eddpf_object.trad_delete+'" class="button eddpf_delete"><span title="'+eddpf_object.trad_delete+'" class="dashicons dashicons-no"></span></button>');		
		jQuery("#edd-feature").append(add_feature);
		$("#edd-feature").vSort();
	});
		$(document).on('click','.eddpf_delete',function(){
			object = $(this).parent().parent().parent().parent();
			inputex = object.find('div p input[type="text"]').val();
			textarea = object.find('div p textarea').val();
			if(inputex!='' || textarea!='') {
				if(confirm(message_confirm)){
					object.css({'background-color':'#D04040'}).fadeOut(700,function(){
						object.remove();
					});
				}
			}else{
					object.css({'background-color':'#D04040'}).fadeOut(700,function(){
						object.remove();
				});
			}
		
		});
});