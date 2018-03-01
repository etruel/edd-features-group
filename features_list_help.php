<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$helpcampaignlist = array( 
	'Howtouse' => array( 
		'columns' => array( 
			'title' => __('Como Usar.', 'edd-features-group' ),
			'tip' => 
				'<b>'.__('Grupos de Características', 'edd-features-group' ).'</b>: '.__('En cada item se cargan <b><u>todas</u></b> las features de un producto con sus addons.', 'edd-features-group' ).'<br>'.
				'<b>'.__('Ejemplo:', 'edd-features-group' ).'</b>: '.__('WPeMatico Features.', 'edd-features-group' ).'<br>'.
				'<b>'.__('Core y AddOns', 'edd-features-group' ).'</b>: '.__('En cada producto aparecerá un metabox que permite seleccionar su grupo de características por nombre.', 'edd-features-group' ).'<br>'.
				__('Al seleccionar uno traerá un listado de todas las features con 2 checkbox.', 'edd-features-group' ).'<br>'.
				__('Uno para seleccionar la característica propia de ese producto y otro para seleccionar si mostrarlo como no disponible en la tabla de características del producto.', 'edd-features-group' ).'<br>'.
				__('To visualize these characteristics in the product page you must use the shortcode <b>[edd-features-group]</b> in the content of the product description.','edd-features-group').'<br>',
		),
	),
	'Settings' => array( 
		'bulk_actions' => array( 
			'title' => __('Settings.', 'edd-features-group' ),
			'tip' => 
				__('La página de settings permite seleccionar cuales CPT son los productos en los que aparecerá el metabox para seleccionar sus features.', 'edd-features-group' ).'<br>',
		),
	),
);
$helpcampaignlist = apply_filters('edd-features-group_help_campaign_list', $helpcampaignlist);

$screen = $current_screen; //WP_Screen::get('edd-features-group_page_edd-features-group_settings ');
foreach($helpcampaignlist as $key => $section){
	$tabcontent = '';
	foreach($section as $section_key => $sdata){
		$helptip[$section_key] = htmlentities($sdata['tip']);
		$tabcontent .= '<p><strong>' . $sdata['title'] . '</strong><br />'.
				$sdata['tip'] . '</p>';
		$tabcontent .= (isset($sdata['plustip'])) ?	'<p>' . $sdata['plustip'] . '</p>' : '';
	}
	$screen->add_help_tab( array(
		'id'	=> $key,
		'title'	=> $sdata['title'],
		'content'=> $tabcontent,
	) );
}
