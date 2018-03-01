<?php
/**
 * Plugin Name: EDD Features Group
 * Plugin URI: https://etruel.com
 * Description: Adds Features groups to englobe all the features of a main product with their extensions.  This will allow select in every product wich features has (and don't has) of all the group.  Showing them in a table through a shortcode.
 * Version: 0.2
 * Author: etruel
 * Author URI: http://www.netmdp.com
 * Text Domain: edd-features-group
 * Domain Path: /lang/
 *
 * @package         etruel\EDD Features Group
 * @author          Esteban Truelsegaard
 * @copyright       Copyright (c) 2018
 *
 */

//scripts
add_action( 'admin_enqueue_scripts', 'eddpf_select2_enqueue' );
function eddpf_select2_enqueue(){
	wp_enqueue_style('eddpf_enqueue_css', plugin_dir_url( __FILE__ ).'assets/css/eddpf_enqueue.css' );
	wp_enqueue_script('eddpf_enqueue_js', plugin_dir_url( __FILE__ ).'assets/js/eddpf_enqueue.js', array('jquery') );
}
//create settings page
add_action('admin_menu', 'eddpf_submenu_settings');
function eddpf_submenu_settings()
{
	add_submenu_page(
		'edit.php?post_type=eddpf_postype',         
		__( 'Settings', 'edd-features-group' ), 
		__( 'Settings', 'edd-features-group' ), 
			'manage_options',               
			'eddpf_config_postype',               
			'eddpf_config_postype'     
	);
}


function eddpf_sanitize_values($eddpf_value){
	foreach ($eddpf_value as $data_index => $value) {
		$eddpf_value[$data_index] = sanitize_text_field($value);
	}
	return $eddpf_value;
}
//admin ost
add_action( 'admin_post_eddpf_data','eddpf_data_callback');
function eddpf_data_callback(){
		check_admin_referer( 'eddpf_nonce_post', 'eddpf_nonce_post-field' );
		$eddpf_postype = isset( $_POST['eddpf_postype'] ) ? (array) $_POST['eddpf_postype'] : array();	
		$eddpf_postype = eddpf_sanitize_values($eddpf_postype);
		update_option('eddpf_postype_settings',$eddpf_postype);
		wp_redirect(admin_url('edit.php?post_type=eddpf_postype&page=eddpf_config_postype'));
		
}	
function eddpf_config_postype(){
	$eddpf_postype_settings = get_option('eddpf_postype_settings',array());
	$eddpf_cpostype=get_post_types();
	$checked_val='';
	$post_val = 0;
	echo '<form action="'.admin_url( 'admin-post.php' ).'" method="POST">';
	wp_nonce_field( 'eddpf_nonce_post', 'eddpf_nonce_post-field' );		 
	echo '<input type="hidden" name="action" value="eddpf_data">';
	echo '<br><table width="300px" style="background-color:white;">';
	echo '<caption style="font-size:20px; padding-bottom:10px; ">'.__('Set the Post Types with features Group','edd-features-group').'</caption>';
	foreach ($eddpf_cpostype as $data_value => $value) {
			if($data_value!='eddpf_postype'){
				$checked_val = '';
				if(count($eddpf_postype_settings)>0){
					$post_val = array_search($data_value,$eddpf_postype_settings);
					if($eddpf_postype_settings[$post_val]==$data_value) $checked_val='checked';
				}
				echo '<tr>';
				echo '<td style="padding:10px;">';
				echo '<input type="checkbox" name="eddpf_postype[]" '.$checked_val.' value="'.esc_attr($data_value).'">'.$data_value;
				echo '</td>';
				echo '</tr>';
			}	
	}
	echo '<tr>
		<td colspan="2" style="padding:15px;"><input type="submit" value="Save Data" class="button button-primary"></td>
	</tr>';
	echo '</table>
	</form>';

}

//Custom Post Type
add_action('in_admin_header', 'features_list_help');
function features_list_help() {
	global $post_type, $current_screen; 
	if($post_type != 'eddpf_postype') return;		
	if($current_screen->id=='edit-eddpf_postype')
		require(  dirname( __FILE__ ) . '/features_list_help.php' );
}

add_action('init','eddpf_postype');
function eddpf_postype(){
		$labels = array(
			'name' => __('Features Groups','edd-features-group'),
			'singular_name' => __('Features Group','edd-features-group'),
			'add_new' => __('New Features Group','edd-features-group'),
			'all_items'=> __('All Features Groups','edd-features-group'),
			'add_new_item'=> __('Add New Group','edd-features-group'),
			'edit_item' => __('Edit Group','edd-features-group'),
			'view_item ' => __('View Group','edd-features-group'),
			'search_item' => __('Search Group','edd-features-group'),
			'not_found' => __('Group Not Found','edd-features-group'),
			'not_found_in_trash ' => __('Not Found in trash','edd-features-group'),
			'parent_item_colon' => __('Parent Item','edd-features-group')
		);
		$args = array(
			'labels'=> $labels,
			'public'=> true,
			'has_archive'=>true,
			'publicly_queryable'=>true,
			'query_var'=>true,
			'rewrite'=>true,
			'capability_type'=>'post',
			'menu_icon' => 'dashicons-list-view',
			'hierarchical' => false,
			'supports'=> array('title'),
			'taxonomies' => array(''),
			'menu_position'=>5,
			'exclude_from_search'=>true
		);
	register_post_type('eddpf_postype',$args);
}

//Metaboxes
add_action('add_meta_boxes','eddpf_add_metabox');
function eddpf_add_metabox() {
	add_meta_box('eddpf_add_metabox', __('Features','edd-features-group'),'eddpf_add_metabox_callback', array('eddpf_postype'), 'normal', 'default');
}

function eddpf_add_metabox_callback($post) {	
	$eddpf_features = (array) get_post_meta($post->ID,'eddpf_features',true);
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$("#eddpf_addfeatures").on('click',function(){
				add_feature = $(".tr_parent").clone().removeClass('tr_parent');
				add_feature.find('td input[type="text"]').val("");
				add_feature.find('td textarea').val("");
				add_feature.find('td').eq(2).append('<button type="button" class="button eddpf_delete"><span class="dashicons dashicons-no"></span></button>');
				$("#eddpf_table").append(add_feature);
			});
			$(document).on('click','.eddpf_delete',function(){
				$(this).parent().parent().remove();
			});
		});
	</script>
	<style>
		.eddpf_delete{
			position: absolute; 
			right: 0; 
			top:0;
			padding: 0 10px;
		    background: #d62b2b !important;
		    border-color: #a91c1c #980e0e #980e0e !important;
		    -webkit-box-shadow: 0 1px 0 #980e0e !important;
		    box-shadow: 0 1px 0 #980e0e !important;
		    color: #fff !important;
		    text-decoration: none;
		    text-shadow: 0 -1px 1px #980e0e, 1px 0 1px #980e0e, 0 1px 1px #980e0e, -1px 0 1px #980e0e;
		}
		.eddpf_delete:hover{
			background: #f12f2f !important;
			border-color: #bb1c1c !important;
		}
		.eddpf_delete .dashicons{
			vertical-align: -5px;
		}
		.tr_main{
			display: flex; 
			flex-direction: column; 
			position: relative; 
			padding-bottom: 25px; 
			margin-bottom: 30px; 
			border-bottom: 1px solid #ddd;
		}
		.tr_main:last-child{
			border-bottom: 0 !important;
			margin-bottom: 0 !important;
			padding-bottom: 0 !important;
		}
		.tr_main td{
			width: 100%;
		}
		.tr_main td label{
			font-weight: 600;
		}
		.tr_main td input[type=text]{
			width: 50%;
			margin-bottom: 10px;
		}
		.tr_main td textarea{
			width: 100%;
		}
		@media (max-width: 767px){
			.tr_main{
				padding-top: 25px;
			}
			.tr_main td input[type=text]{
				width: 100% !important;
			}
		}
	</style>
	<table style="width: 100%; text-align: left;" id="eddpf_table">
			<?php if(count($eddpf_features)<=0){ ?>
			<tr class="tr_parent tr_main">
				<td><label><?php _e('Title','edd-features-group') ?></label></td>
				<td><input type="text" name="eddpf_title[]"></td>
				<td><label><?php _e('Description','edd-features-group') ?></label></td>
				<td><textarea name="eddpf_description[]" rows="3"></textarea></td>
				<td style="width: 5%;"></td>
			</tr>
			<?php }else{ 
			foreach ($eddpf_features as $data_index => $value) {
			?>
			<tr class="tr_main <?php if($data_index==0) echo 'tr_parent'; ?>">
				<td><label><?php _e('Title','edd-features-group') ?></label></td>
				<td><input type="text" name="eddpf_title[]" value="<?php echo esc_attr($value['title']); ?>"></td>
				<td><label><?php _e('Description','edd-features-group') ?></label></td>
				<td><textarea name="eddpf_description[]" rows="3"><?php echo esc_attr($value['description']);  ?></textarea></td>
				<td style="width: 5%;"><?php if($data_index>0) echo '<button type="button" class="button eddpf_delete"><span class="dashicons dashicons-no"></span></button>'; ?></td>
			</tr>	
			<?php } 
			}?>
	</table>
	<div class="textright">
		<input type="button" class="button button-primary" value="<?php _e('Add Feature','edd-features-group'); ?>" id="eddpf_addfeatures">
	</div>
	<?php
}
//save post
add_action('save_post','eddpf_save_features');
function eddpf_save_features($post) {
	 global $post_type;
	 if($post_type=='eddpf_postype') {
		//sanitize array values
		$eddpf_title = isset( $_POST['eddpf_title'] ) ? (array) $_POST['eddpf_title'] : array();	
		$eddpf_title = eddpf_sanitize_values($eddpf_title);

		$eddpf_description = isset( $_POST['eddpf_description'] ) ? (array) $_POST['eddpf_description'] : array();	
		$eddpf_description = eddpf_sanitize_values($eddpf_description);
		
		$data = array();
		foreach ($eddpf_title as $data_index => $value) {
			array_push($data,array(
				'title'=>$value,
				'description'=>$eddpf_description[$data_index]
			));
		}
		update_post_meta($post,'eddpf_features',$data);
	}
}


//ajax post features
add_action( 'wp_ajax_eddpf_ajax_post', 'eddpf_ajax_post_callback' ); // wp_ajax_{action}
function eddpf_ajax_post_callback(){
  	check_ajax_referer('eddpf_nonce');
	// we will pass post IDs and titles to this array
	$return = array();
	$features_template = '';
	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new WP_Query( array( 
		's'=> $_GET['q'], // the search query
		'post_status' => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'posts_per_page' => 50, // how much to show at oncem
		'post_type'=>'eddpf_postype'
	) );
	$features_template.='<table width="100%" border="1" cellspacing="0">';
	$features_template.='<tr>
				<th style="width: 20%;">'.__("Title","edd-features-group").'</th>
				<th style="width: 70%;">'.__("Description","edd-features-group").'</th>
				<th style="width: 5%;">'.__("Yes","edd-features-group").'</th>
				<th style="width: 5%;">'.__("No","edd-features-group").'</th>
			</tr>';

	if( $search_results->have_posts() ) :
		while( $search_results->have_posts() ) : $search_results->the_post();	
			// shorten the title a little
			$eddpf_features = get_post_meta($search_results->post->ID, 'eddpf_features', true);
			foreach ($eddpf_features as $data_index => $value) {
				$features_template.='<tr>
					<td style="width: 20%;"><input type="text" name="eddpf_title[]" readonly="readonly" value="'.$value['title'].'"></td>
					<td style="width: 75%;"><input style="width: 100%;" row="3"  type="text" name="eddpf_description[]" readonly="readonly" value="'.$value['description'].'"></td>
					<td style="width: 5%;"><input type="checkbox" name="yes_no[]" value="yes"></td>
					<td style="width: 5%;"><input type="checkbox" name="yes_no[]" value="no"></td>
				</tr>';
			}
			$features_template.='</table>';
			$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title,$features_template ); // array( Post ID, Post Title )
		endwhile;
	endif;

	echo json_encode( $return );
	die;
}

//metabox  in posttype product
add_action('add_meta_boxes','eddpf_features_setting');
function eddpf_features_setting(){
	$eddpf_postype_settings = get_option('eddpf_postype_settings',array());
	if(count($eddpf_postype_settings)<=0){
		$eddpf_postype_settings = get_post_types();
		unset($eddpf_postype_settings[array_search('eddpf_postype',$eddpf_postype_settings)]);
	}
	add_meta_box('eddpf_feature_metabox_setting', __('Features Group','edd-features-group'),'eddpf_features_setting_callback', array($eddpf_postype_settings), 'normal', 'default');
}
function eddpf_features_setting_callback($post) {
	$eddpf_features_setting = get_post_meta($post->ID,'eddpf_features_setting',true);
	$features_id = get_post_meta($post->ID, 'eddpf_features_id',true);
	$eddpf_features = get_post_meta($features_id,'eddpf_features',true);
	$nonce = wp_create_nonce('eddpf_nonce');
?>
	<script type="text/javascript">
		jQuery(function($){
			// simple multiple select
			var template_features = '';
			// multiple select with AJAX search
			$('#eddpf_select2_posts').select2({
		  		ajax: {
		    			url: ajaxurl, // AJAX URL is predefined in WordPress admin
		    			dataType: 'json',
		    			delay: 250, // delay in ms while typing when to perform a AJAX search
		    			data: function (params) {
		      				return {
		        				q: params.term, // search query
		        				action: 'eddpf_ajax_post', // AJAX action for admin-ajax.php
								_ajax_nonce : '<?php echo $nonce; ?>'
		      					
		      				};
		    			},
		    			processResults: function( data ) {
						var options = [];
						if ( data ) {
							// data is the array of arrays, and each of them contains ID and the Label of the option
							$.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
								options.push( { id: text[0], text: text[1],template:text[2]  } );
								template_features = text[2];
							});
						}
						return {
							results: options
						};
					},
					cache: true
				},
				minimumInputLength: 3 // the minimum of symbols to input before perform a search
			});
			$(document).on("change","#eddpf_select2_posts",function(){
		 		$("#eddpf_section_features").html(template_features);
			});
		});
		
	</script>
	<!--ShortCode Description-->
	<p><?php _e('To visualize these characteristics in the products you must insert the following shortcode <b>[edd-features-group]</b> in the description of the same','edd-features-group') ?></p>
	<!--select 2-->
	<select id="eddpf_select2_posts" name="eddpf_select2_posts">
		<option value=""><?php _e('Search Features','edd-features-group'); ?></option>
		<?php if(isset($features_id) && $features_id!=''){ ?>
			<option selected="selected" value="<?php echo esc_attr($features_id); ?>"><?php echo esc_attr(get_the_title($features_id)); ?></option>
		<?php } ?>
	</select>
	<!--section table features-->
	<br>
	<br>
	<div id="eddpf_section_features">
		<?php if(count($eddpf_features)>0){ ?>
			<table width="100%" cellspacing="0" border="1">
			<tr>
				<th style="width: 20%;"><?php _e('Title','edd-features-group') ?></th>
				<th style="width: 70%;"><?php _e('Description','edd-features-group') ?></th>
				<th style="width: 5%;">Yes</th>
				<th style="width: 5%;">No</th>
			</tr>
			<?php 
			$cont = 0;
			foreach ($eddpf_features as $data_index => $value) {
				if(!isset($eddpf_features[$data_index])){
					$eddpf_features[$data_index]['yes_no'] = '';
				}
			?>
			<tr>
				<td style="width: 20%;"><input type="text" readonly="readonly" value="<?php echo esc_attr($value['title']); ?>" name="eddpf_title[]"></td>
				<td style="width: 70%;"><input style="width: 100%;"  type="text" readonly="readonly" value="<?php echo esc_attr($value['description']); ?>" name="eddpf_description[]"></td>
				<td style="width: 5%;"><input <?php checked($eddpf_features[$data_index]['yes_no'],'yes'); ?> type="checkbox" name="yes_no[]" value="yes"></td>
				<td style="width: 5%;"><input <?php checked($eddpf_features[$data_index]['yes_no'],'no'); ?> type="checkbox" name="yes_no[]" value="no"></td>
			</tr>
			<?php
				} 
			?>
			</table>
		<?php } ?>
	</div>

<?php
}

add_action('save_post','eddpf_save_features_setting');
function eddpf_save_features_setting($post){
	global $post_type;
	if($post_type!='eddpf_postype') {

		$_POST['eddpf_title'] = isset( $_POST['eddpf_title'] ) ? (array) $_POST['eddpf_title'] : array();	
		$eddpf_title = eddpf_sanitize_values($eddpf_title);

		$_POST['eddpf_description'] = isset( $_POST['eddpf_description'] ) ? (array) $_POST['eddpf_description'] : array();	
		$eddpf_description = eddpf_sanitize_values($eddpf_description);
		
		$_POST['yes_no'] = isset( $_POST['yes_no'] ) ? (array) $_POST['yes_no'] : array();	
		$yes_no = eddpf_sanitize_values($yes_no);
		
		$features_id = isset($_POST['eddpf_select2_posts']) ? sanitize_text_field($_POST['eddpf_select2_posts']) : '';
		$data = array();
		$cont = 0;
		$checked_yes_no = '';	
		foreach ($eddpf_title as $data_index => $value) {
			if(isset($yes_no[$data_index])){
				$checked_yes_no = $yes_no[$data_index];
			}else{
				$checked_yes_no = '';
			}
			array_push($data,array(
				'title'=>$value,
				'description'=> $eddpf_description[$data_index],
				'yes_no'=>$checked_yes_no
			));
			$cont++;
		}
		update_post_meta($post,'eddpf_features_setting',$data);
		update_post_meta($post, 'eddpf_features_id',$features_id);
	}//closed if
}
//Add shortcode
add_shortcode('edd-features-group','eddpf_shortcode');
function eddpf_shortcode() {	
	$html = '';
	$have = '';
	$dont = '';
	$eddpf_features_setting = get_post_meta(get_the_id(),'eddpf_features_setting',true);
	$features_id = get_post_meta(get_the_id(), 'eddpf_features_id',true);
	$eddpf_features = get_post_meta($features_id,'eddpf_features',true);

	if(count($eddpf_features>0)){
		$html.= '
		<table class="su_freepro">
		<tbody>
			<tr>
			<td>
				<h3>'.__("Features","edd-features-group").'</h3>
			</td>
				<td class="">'.__("Yes","edd-features-group").'</td>
				<td class="">'.__("No","edd-features-group").'</td>
			</tr>
			<tr>';
		foreach ($eddpf_features as $data_index => $value) {
			if(!isset($eddpf_features[$data_index])){
				$eddpf_features[$data_index]['yes_no'] = '';
			}
			if($eddpf_features[$data_index]['yes_no']!=''){
				if($eddpf_features[$data_index]['yes_no']=='yes'){
					$have = 'have';
					$dont = '';
				}else{
					$have = '';
					$dont = 'dont';
				}
				$html.='<tr>
				<td>
				<div class="su-spoiler su-spoiler-style-default su-spoiler-icon-arrow-circle-1 freepro"><div class="su-spoiler-title">
				<span class="su-spoiler-icon"></span>'.$value['title'].'</div>
				<div class="su-spoiler-content su-clearfix">
				'.$value['description'].'
				</div>
				</div></td>
				<td class="'.$dont.'"></td>
				<td class="'.$have.'"></td>
				</tr>';
			}//close if child for
		}
		$html.='</tbody></table>';	
	}//closed if
	return $html;
}
