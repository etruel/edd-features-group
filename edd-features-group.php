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

//Register Scripts
add_action('admin_enqueue_scripts','eddpf_register_script');
function eddpf_register_script()
{
	wp_register_script('eddpf_postype_js', plugin_dir_url( __FILE__ ).'assets/js/eddpf_postype.js',array('jquery'));
	wp_register_script('eddpf_postype_single_js', plugin_dir_url( __FILE__ ).'assets/js/eddpf_postype_single.js',array('jquery'));
	wp_register_script('eddpf_vsort_js', plugin_dir_url( __FILE__ ).'assets/js/jquery.vSort.min.js',array('jquery'));
}


//scripts
add_action( 'admin_enqueue_scripts', 'eddpf_enqueue' );
function eddpf_checking_postype($postype)
{
	$eddpf_postype_settings = get_option('eddpf_postype_settings',array());
	$result = false;
	foreach ($eddpf_postype_settings as $postype_value) {
		if($postype==$postype_value){
			$result = true;
		}
	}
	return $result;
}

function eddpf_enqueue()
{
	global $post;
	$eddpf_postype_settings = get_option('eddpf_postype_settings',array());
	if(isset($post->post_type)){
		if($post->post_type=='eddpf_postype'){
			wp_enqueue_style('eddpf_postype_css', plugin_dir_url( __FILE__ ).'assets/css/eddpf_postype.css' );
			wp_enqueue_script('eddpf_vsort_js');
			wp_enqueue_script('eddpf_postype_js');
			wp_localize_script('eddpf_postype_js','eddpf_object',array(
				'trad_delete'=>__('Delete','edd-features-group'),
				'trad_confirm'=>__('Do you want to delete this feature','edd-features-group')
			));
		}else if(eddpf_checking_postype($post->post_type)){
			wp_enqueue_script('eddpf_vsort_js');
			wp_enqueue_script('eddpf_postype_single_js');
			wp_localize_script('eddpf_postype_single_js','eddpf_object',array(
				'nonce'=> wp_create_nonce('eddpf_nonce'),
				'trad_loading'=>__('Loading','edd-features-group')
			));
		}
	}
	if(isset($_GET['page']) && $_GET['page']=='eddpf_config_postype'){
		wp_enqueue_script('select2_js', plugin_dir_url( __FILE__ ).'assets/js/select2.js',array('jquery'));
		wp_enqueue_style('select2_css', plugin_dir_url( __FILE__ ).'assets/css/select2.css' );
		wp_add_inline_script( 'jquery-migrate', 'jQuery(document).ready(function($){$(".js-example-basic-multiple").select2();});' );
	}
	if(count($eddpf_postype_settings)<=0)
	{
		wp_enqueue_script('eddpf_vsort_js');
			wp_enqueue_script('eddpf_postype_single_js');
			wp_localize_script('eddpf_postype_single_js','eddpf_object',array(
				'nonce'=> wp_create_nonce('eddpf_nonce'),
				'trad_loading'=>__('Loading','edd-features-group')
			));
	}	
	
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

//-----------------SETTINGS PAGE FEATURE GROUP---------------------
function eddpf_config_postype(){
	$eddpf_postype_settings = get_option('eddpf_postype_settings',array());
	$eddpf_cpostype=get_post_types();
	$checked_val='';
	$post_val = 0;
?>
<h3><?php _e('Set the Post Types with features Group','edd-features-group'); ?></h3>
<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
	<?php wp_nonce_field( 'eddpf_nonce_post', 'eddpf_nonce_post-field' ); ?> 
	<input type="hidden" name="action" value="eddpf_data">
	<select class="js-example-basic-multiple" name="eddpf_postype[]" multiple="multiple" style="width: 90%;">
	 <?php 	
	 		foreach ($eddpf_cpostype as $data_value => $value) { 
	 		if(count($eddpf_postype_settings)>0){
				$post_val = array_search($data_value,$eddpf_postype_settings);
			}
			$eddpf_postype_settings[$post_val] = isset($eddpf_postype_settings[$post_val]) ? $eddpf_postype_settings[$post_val] : '';

	 	?>
	  	<option <?php selected($eddpf_postype_settings[$post_val],$data_value); ?> value="<?php echo esc_attr($data_value) ?>"><?php echo $data_value; ?></option>
		<?php  } ?>
	</select>
	<br>
	<br>
	<input type="submit" value="<?php _e("Save Data","edd-features-group"); ?>" class="button button-primary">

</form>

<?php
}
//-----------------SETTINGS PAGE FEATURE GROUP CLOSED---------------------


//Custom Post Type
add_action('in_admin_header', 'features_list_help');
function features_list_help() {
	global $post_type, $current_screen; 
	if($post_type != 'eddpf_postype') return;		
	if($current_screen->id=='edit-eddpf_postype')
		require(  dirname( __FILE__ ) . '/features_list_help.php' );
}

add_action('init','eddpf_create_postype');
function eddpf_create_postype(){
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
	<!--DIV ELEMENT FEATURES-->
	<div id="edd-feature" data-callback=""> <!-- callback script to run on successful sort -->
		<?php if(count($eddpf_features)<=0){ ?>
	    <div class="sortitem edd-column-feature column_parent"> <!-- sort item -->
	        <span class="sorthandle"></span> <!-- sort handle -->
	      	<div>
	      		<p>
	      			<label class="before_button_delete"><?php _e('Title','edd-features-group') ?></label>
	      			<br>
	      			<input type="text" name="eddpf_title[]" style="width: 90%;">
	      		</p>
	      		<p>
	      			<label><?php _e('Description','edd-features-group') ?></label>
	      			<br>
	      			<textarea name="eddpf_description[]" rows="3" style="width: 90%;"></textarea>
	      		</p>
	      	</div>
	    </div>
	    <?php }else{ 
			foreach ($eddpf_features as $data_index => $value) {
			?>

		<div class="sortitem edd-column-feature <?php if($data_index==0) echo 'column_parent'; ?>"> <!-- sort item -->
	        <span class="sorthandle"></span> <!-- sort handle -->
	      	<div>
	      		<p>
	      			<label class="before_button_delete"><?php _e('Title','edd-features-group') ?>
	      				<?php if($data_index>0){?> 
							<button type="button" title="<?php _e('Delete','edd-features-group') ?>" class="button eddpf_delete"><span title="Delete" class="dashicons dashicons-no"></span></button>
							<?php } ?>
	      			</label>
	      			<br>
	      			<input type="text" value="<?php echo esc_attr($value['title']); ?>" name="eddpf_title[]" style="width: 90%;">
	      		</p>
	      		<p>
	      			<label><?php _e('Description','edd-features-group') ?></label>
	      			<br>
	      			<textarea name="eddpf_description[]" rows="3" style="width: 90%;"><?php echo esc_attr($value['description']);  ?></textarea>
	      		</p>
	      	</div>
	    </div>


		<?php }} ?>
	</div>
	<!--DIV ELEMENT FEATURES CLOSED-->

	<div class="textright">
		<input type="button" class="button button-primary" value="<?php _e('Add Feature','edd-features-group'); ?>" id="eddpf_addfeatures">
	</div>
	<style type="text/css">
		.sortitem{background:#fff;border:2px solid #ccc;padding-left:20px;}
        .sortitem .sorthandle{position:absolute;top:5px;bottom:5px;left:3px;width:8px;display:none;background-image:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAB3RJTUUH3wIDBycZ/Cj09AAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAAAWSURBVHjaY2DABhoaGupBGMRmYiAEAKo2BAFbROu9AAAAAElFTkSuQmCC');}
        .sortitem .sorthandle{display:block;}
	</style>
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
		'p'=> sanitize_text_field($_POST['p']), // the search query
		'post_status' => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'posts_per_page' => 1, // how much to show at oncem
		'post_type'=>'eddpf_postype'
	) );
	//llamaremos en este caso el div con todo el elemento
	$features_template.='';
	$cont=0;
	if( $search_results->have_posts() ) :
		while( $search_results->have_posts() ) : $search_results->the_post();	
			$eddpf_features = get_post_meta($search_results->post->ID, 'eddpf_features', true);
	
			foreach ($eddpf_features as $data_index => $value) {
				$features_template.='<div class="sortitem">
		        <span class="sorthandle"></span>
		      	<div>
		      		<p>
		      			<input type="hidden" value="hidden" name="eddpf_sort[]" class="eddpf_sort">
		      			<input type="text" value="'.$value['title'].'" name="eddpf_title[]">
		      			<input type="text"  style="width:70% !important;" name="eddpf_description[]" value="'.$value['description'].'">
		      			<span style="float:right;"><input type="checkbox" name="yes_no[]" value="yes"  class="check_yes">
						<input type="checkbox" name="yes_no[]" value="no"  class="check_no">
		      			</span>
		      		</p>
		      	</div>
		    </div>';
			}
			$features_template.='</div>';
			$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title,$features_template ); // array( Post ID, Post Title )
		endwhile;
	endif;

	//cierre del div con el elemento	
	echo json_encode( $return );
	die;
}

//metabox  in posttype product
add_action('add_meta_boxes','eddpf_features_single');
function eddpf_features_single(){
	$eddpf_postype_settings = get_option('eddpf_postype_settings',array());
	if(count($eddpf_postype_settings)<=0){
		$eddpf_postype_settings = get_post_types();
		unset($eddpf_postype_settings[array_search('eddpf_postype',$eddpf_postype_settings)]);
	}
	add_meta_box('eddpf_feature_metabox_setting', __('Features Group','edd-features-group'),'eddpf_features_single_callback', array($eddpf_postype_settings), 'normal', 'default');
}
function eddpf_features_single_callback($post) {

	$eddpf_features_single = empty(get_post_meta($post->ID,'eddpf_features_single',true)) ? false : get_post_meta($post->ID,'eddpf_features_single',true);
	$features_id = empty(get_post_meta($post->ID, 'eddpf_features_id',true)) ? false : get_post_meta($post->ID, 'eddpf_features_id',true);
	//$eddpf_features = empty(get_post_meta($features_id,'eddpf_features',true)) ? false : get_post_meta($features_id,'eddpf_features',true);

?>
	<!--ShortCode Description-->
	<p><?php _e('To visualize these characteristics in the products you must insert the following shortcode <b>[edd-features-group]</b> in the description of the same','edd-features-group') ?></p>
	<!--select 2-->
	<?php
		$args = array(
		'post_type' => 'eddpf_postype',
		'posts_per_page'=>'50'
		);
		$query = new WP_Query( $args ); 
	?>
	<select id="eddpf_select2_posts" name="eddpf_select2_posts">
		<option value=""><?php _e('Search Features','edd-features-group'); ?></option>
		<?php while($query->have_posts()){ 
			$query->the_post();
		?>
			<option <?php selected(get_the_ID(),$features_id); ?> value="<?php echo get_the_ID(); ?>"><?php echo get_the_title(); ?></option>
		<?php } ?>
	</select>
	<!--section table features-->
	<br>
	<br>
	<div>
				<div>
					<p>
				      <span style="margin-left: 20px !important;"><b><?php _e("Title","edd-features-group") ?></b></span>
				      	<span style="position: relative; margin-left: 150px !important;"><b><?php _e("Description","edd-features-group") ?></b></span>
				      	<span style="float: right !important;">
							<span><b><?php _e("Yes","edd-features-group") ?></b></span>
							<span><b><?php _e("No","edd-features-group") ?></b></span>
				      	</span>
				      	</p>
				      	<p style="float: right !important;">
				      		<b><?php _e("Select All","edd-features-group"); ?></b>
							<span><input type="checkbox" class="yes_all" name="yes_all"></span>
							<span><input type="checkbox" class="no_all" name="no_all"></span>
				      	</p>
				      	<div style="clear: both;"></div>

				  </div>
			
	</div>
	<div id="eddpf_section_features" data-callback="">	
	<?php  
		if(count($eddpf_features_single)>1){
			foreach ($eddpf_features_single as $dataindex => $value) {
			?>

				<div class="sortitem">
				        <span class="sorthandle"></span>
				      	<div>
				      		<p>
				      			<input type="hidden" class="eddpf_sort" value="<?php echo $eddpf_features_single[$dataindex]['yes_no']; ?>" name="eddpf_sort[]">
				      			<input type="text" value="<?php echo $eddpf_features_single[$dataindex]['title']; ?>" name="eddpf_title[]">
				      			<input type="text" style="width:70% !important;" name="eddpf_description[]" value="<?php echo $eddpf_features_single[$dataindex]['description']; ?>">
				      			<span style="float: right !important;"><input type="checkbox" <?php checked($eddpf_features_single[$dataindex]['yes_no'],'yes'); ?> class="check_yes" name="yes_no[]" value="yes">
								<input type="checkbox" <?php checked($eddpf_features_single[$dataindex]['yes_no'],'no'); ?>  class="check_no" name="yes_no[]" value="no">
				      			</span>
				      		</p>
				      	</div>
				</div>
				<?php
			}
		}

	?>
	</div>
	<style type="text/css">
		.sortitem{background:#fff;border:2px solid #ccc;padding-left:20px;}
        .sortitem .sorthandle{position:absolute;top:5px;bottom:5px;left:3px;width:8px;display:none;background-image:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAB3RJTUUH3wIDBycZ/Cj09AAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAAAWSURBVHjaY2DABhoaGupBGMRmYiAEAKo2BAFbROu9AAAAAElFTkSuQmCC');}
        .sortitem .sorthandle{display:block;}
	</style>
	
<?php
}

add_action('save_post','eddpf_save_features_setting');
function eddpf_save_features_setting($post){
	global $post_type;
	if($post_type!='eddpf_postype') {

		$eddpf_title = isset( $_POST['eddpf_title'] ) ? (array) $_POST['eddpf_title'] : array();	
		$eddpf_title = eddpf_sanitize_values($eddpf_title);
		$eddpf_description = isset( $_POST['eddpf_description'] ) ? (array) $_POST['eddpf_description'] : array();	
		$eddpf_description = eddpf_sanitize_values($eddpf_description);
		$eddpf_sort = isset( $_POST['eddpf_sort'] ) ? (array) $_POST['eddpf_sort'] : array();
		$features_id = isset($_POST['eddpf_select2_posts']) ? sanitize_text_field($_POST['eddpf_select2_posts']) : '';
		$data = array();
		$cont = 0;
		$checked_yes_no = '';	
		foreach ($eddpf_sort as $data_index => $value) {
			array_push($data,array(
				'title'=>$eddpf_title[$data_index],
				'description'=> $eddpf_description[$data_index],
				'yes_no'=>$eddpf_sort[$data_index]
			));
			$cont++;
		}
		update_post_meta($post,'eddpf_features_single',$data);
		update_post_meta($post, 'eddpf_features_id',$features_id);
	}//closed if
}
//Add shortcode
add_shortcode('edd-features-group','eddpf_shortcode');
function eddpf_shortcode() {	
	ob_start();
	$html = '';
	$have = '';
	$dont = '';
	$free = '';
	$pro = 'have';
	$eddpf_features_single = get_post_meta(get_the_id(),'eddpf_features_single',true);
	if(!empty($eddpf_features_single)){
	?>
		<table class="su_freepro">
		<tbody>
			<tr>
				<td><h3><?php echo __("Features","edd-features-group");  ?></h3></td>
				<td><?php echo __("FREE","edd-features-group");  ?></td>
				<td><?php echo __("PRO","edd-features-group");  ?></td>
			</tr>
	<?php
		foreach ($eddpf_features_single as $data_index => $value) {
			if($eddpf_features_single[$data_index]['yes_no']!='hidden'){
				if($eddpf_features_single[$data_index]['yes_no']=='yes'){
					$free = 'have';
				}else{
					$free = 'dont';
				}
			?>
			<tr>
				<td>
					<div class="su-spoiler su-spoiler-style-default su-spoiler-icon-arrow-circle-1 freepro su-spoiler-closed">
						<div class="su-spoiler-title">
							<span class="su-spoiler-icon"></span>
							<?php echo $eddpf_features_single[$data_index]['title']; ?>
						</div>
						<div class="su-spoiler-content su-clearfix">
							<?php echo $eddpf_features_single[$data_index]['description']; ?>
						</div>
					</div>
				</td>
				<td class="<?php echo $free; ?>"></td>
				<td class="<?php echo $pro; ?>"></td>
			</tr>
		<?php
			}//close if child for
		}
		?>
		<tr>
			<td style="vertical-align: middle; text-align: center; font-size: 1.4em;">
				<strong><?php echo __("Get WPeMatico Right Now!","edd-features-group");  ?></strong></td>
			<td class=""><span class="su-tooltip" data-close="no" data-behavior="hover" data-my="center right" data-at="center left" data-classes="su-qtip qtip-green su-qtip-size-3 qtip-rounded" data-title="" title="<?php echo __('Download Free version','edd-features-group');  ?>&lt;br /&gt;
				<?php echo __('from WordPress repository','edd-features-group');  ?>"><a href="https://downloads.wordpress.org/plugin/wpematico.zip" class="su-button su-button-style-default" style="color:#FFFFFF;background-color:#2D89EF;border-color:#246ebf;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px" target="_self" rel="nofollow"><span style="color:#FFFFFF;padding:5px 14px;font-size:12px;line-height:18px;border-color:#6cacf4;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;text-shadow:none;-moz-text-shadow:none;-webkit-text-shadow:none"><i class="fa fa-wordpress" style="font-size:12px;color:#FFFFFF"></i><?php echo __("Get","edd-features-group");  ?></span></a></span></td>
				<td class=""><span class="su-tooltip" data-close="no" data-behavior="hover" data-my="center left" data-at="center right" data-classes="su-qtip qtip-green su-qtip-size-3 qtip-rounded" data-title="" title="<?php echo __('Go to select license type','edd-features-group');  ?>&lt;br /&gt;
			<?php echo __("and buy now PRO Version!","edd-features-group");  ?>"><a href="#secondary" class="su-button su-button-style-default" style="color:#FFFFFF;background-color:#2D89EF;border-color:#246ebf;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px" target="_self"><span style="color:#FFFFFF;padding:5px 14px;font-size:12px;line-height:18px;border-color:#6cacf4;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;text-shadow:none;-moz-text-shadow:none;-webkit-text-shadow:none"><i class="fa fa-graduation-cap" style="font-size:12px;color:#FFFFFF"></i> <?php echo __("Buy","edd-features-group");  ?></span></a></span></td>
			</tr>
		</tbody>
	</table>
	<?php
	}//closed if
	 return ob_get_clean();
}

