jQuery(document).ready(function($){
	var message_confirm = eddpf_object.trad_confirm;

	$("#eddpf_addfeatures").on('click',function(){
		add_feature = $(".tr_parent").clone().removeClass('tr_parent').addClass("sortitem");
		add_feature.find('td input[type="text"]').val("");
		add_feature.find('td textarea').val("");
		add_feature.find('td').eq(2).append('<button type="button" title="'+eddpf_object.trad_delete+'" class="button eddpf_delete"><span title="'+eddpf_object.trad_delete+'" class="dashicons dashicons-no"></span></button>');
		$("#eddpf_table").append(add_feature);
	});
		$(document).on('click','.eddpf_delete',function(){
			object = $(this);
			inputex = $(this).parent().parent().find('input[type=text]').val();
			textarea = $(this).parent().parent().find('textarea').val();
			if(inputex!='' || textarea!='') {
				if(confirm(message_confirm)){
						$(this).parent().parent().css({'background-color':'#D04040'}).fadeOut(700,function(){
						object.remove();
					});
				}
			}else{
					$(this).parent().parent().css({'background-color':'#D04040'}).fadeOut(700,function(){
						object.remove();
				});
			}
		
		});
});