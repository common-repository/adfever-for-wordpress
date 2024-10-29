<?php
/*******************************************************************************
 *  Copyright (c) 2009 Inteliscent SAS.
 *  All rights reserved. This program and the accompanying materials
 *  are made available under the terms of the GNU Public License v2.0
 *  which accompanies this distribution, and is available at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *  
 *  Contributors:
 *      Be-API - initial API and implementation
 *      Inteliscent SAS - Stabilisation et definitive version
 ******************************************************************************/



if ( !defined('WP_ADMIN') )
define('WP_ADMIN', TRUE);

// Include wp-config.php
if (is_file('../../../../wp-load.php')) {
	require_once('../../../../wp-load.php');
} else {
	require_once('../../../../wp-config.php');
}

// Load WP-Admin inc
include( ABSPATH . 'wp-admin/admin.php' );
require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/client.shortcode.php';

// Check Whether User Can Adfever
if(!current_user_can('edit_posts')) {
	die('Access Denied');
}

// Init Shopping class
$shopbot = new AdfeverClient_Shortcode();
global $current_user;
$current_options = get_option( $shopbot->option_name );
$shop = new Shopping ( $current_options['aid'], ADFEVER_UUID );

// Get categories tree
$categories = $shop->getCategories();


// Current tab ?
$current_tab = ($_GET['tab'] == 'browse') ? 'browse' : 'search';

// Build form action
$form_action_url = ADFEVER_URL . "/inc/lightbox.php?tab=" . $current_tab;

// Paged ?
$_GET['paged'] = isset( $_GET['paged'] ) ? intval($_GET['paged']) : 0;
if ( $_GET['paged'] < 1 )
$_GET['paged'] = 1;
$start = ( $_GET['paged'] - 1 ) * 10;
if ( $start < 1 )
$start = 0;

if ( $current_tab == 'search' ) {

	// Try to send content to editor
	if ( $_GET['send_to_editor'] == 'true' && isset($_GET['item_id']) ) {
		media_send_to_editor( '<span class="adf_shortcode">[adfever type="product" value="'.intval($_GET['item_id']).'"]</span>');
		exit();
	}

	// Get datas
	if ( isset($_GET['s']) && !empty($_GET['s']) ) {

		$results = $shop->search( stripslashes($_GET['s']), intval($_GET['category']), $_GET['paged'] );
		$total_items = $results["shopping"]["meta"]["total"];
		$results = $results["shopping"]["products"]["product"];

	} else {

		$results = array();
		$total_items = 0;

	}
} else {

	// Try to send content to editor
	if ( $_GET['send_to_editor'] == 'true' && isset($_GET['cat_id']) ) {
		media_send_to_editor( '[adfever type="top-category" value="'.intval($_GET['cat_id']).'"]');
		exit();
	}

}

$GLOBALS['body_id'] = 'media-upload';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
<?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type"
	content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Adfever'); ?>
&#8212; <?php _e('WordPress'); ?></title>

	<?php
	global $wp_version;
	if ( version_compare( $wp_version, '2.6', '<' ) ) { // WP 2.5
		wp_admin_css( 'css/global' );
		wp_admin_css();
		wp_admin_css( 'css/colors' );
		wp_admin_css( 'css/media' );
	} else { // WP 2.6, 2.7, 2.8
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
		wp_enqueue_style( 'ie' );
	}

	wp_enqueue_style( 'jquery-tree', ADFEVER_URL . '/lib/jquery-treeview/jquery.treeview.css', array(), '1.4' );
	wp_enqueue_script( 'jquery-cookie', ADFEVER_URL . '/lib/jquery.cookie.js', array('jquery'), '1.0.0', false );
	wp_enqueue_script( 'jquery-tree', ADFEVER_URL . '/lib/jquery-treeview/jquery.treeview.min.js', array('jquery', 'jquery-cookie'), '1.4', false );
	?>

<script type="text/javascript">
	//<![CDATA[
	addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
	var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time() ?>'};
	//]]>
	</script>

	<?php
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
	do_action('admin_head');

	if ( $current_tab == 'browse' ) :
	?>
<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#tree").treeview({
				animated: "fast",
				collapsed: true,
				unique: true,
				persist: "cookie"
			});
		});
		</script>
	<?php endif; ?>

</head>
<body

<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?>>
<div id="media-upload-header">
<ul id='sidemenu'>
	<li><a href='<?php echo add_query_arg(array('tab'=>'search')); ?>'
	<?php if ( $current_tab == 'search' ) echo "class='current'"; ?>><?php _e('Search', 'adfever'); ?></a></li>
	<li><a href='<?php echo add_query_arg(array('tab'=>'browse')); ?>'
	<?php if ( $current_tab == 'browse' ) echo "class='current'"; ?>><?php _e('Browse', 'adfever'); ?></a></li>
</ul>
</div>

<div style="margin: 0 1em;">
<p><?php _e('You can choose to insert a product in particular by searching below. If you want to insert random products from a category, please click on the "Browse" link above.', 'adfever'); ?>
</p>

	<?php if ( $_GET['tab'] == 'browse' ) : ?>

<h3 class="media-title"><?php _e('Browse in categories of Adfever', 'adfever'); ?></h3>

	<?php
	if ( empty($categories['shopping']['category'][0]['categories']['category']) ) {
			
		echo '<p>'.__('No category currently. Strange ! Perhaps your server can\'t call the Adfever server', 'adfever').'</p>';
			
	} else {

		echo '<ul id="tree">' . "\n";
			
		recursive_categories_li( $categories['shopping']['category'][0]['categories']['category'] );

		echo '</ul>' . "\n";
			
	}
	?> <?php else : /* Search */ ?>

<h3 class="media-title"><?php _e('Search for a product', 'adfever'); ?></h3>

<form id="filter" action="" method="get" style="margin: 0;"><input
	type="hidden" name="tab" value="search" />

<p id="media-search" class="search-box"><label
	class="screen-reader-text" for="media-search-input"><?php _e('Search', 'adfever'); ?></label>
<input type="text" id="media-search-input" name="s"
	value="<?php echo attribute_escape($_GET['s']); ?>" /> <select
	name="category" id="cat"
	style="padding: 0; margin: 0; font-size: 11px;">
	<option value=""><?php _e('All categories', 'adfever'); ?></option>
	
	<?php recursive_categories_select( $categories['shopping']['category'][0]['categories']['category'], intval($_GET['category']) ); ?>
</select> <input type="submit" value="<?php _e('Search', 'adfever'); ?>"
	class="button" /></p>

	<?php
	$page_links = paginate_links( array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'prev_text' => __('&laquo;'),
				'next_text' => __('&raquo;'),
				'total' => ceil($total_items / 10),
				'current' => $_GET['paged']
	));
	if ( $page_links )
	echo "<div class='tablenav'><div class='tablenav-pages'>$page_links</div><br class='clear' /></div>";
	?></form>

	<?php if ( !empty($results) ) : ?>
<form action="" method="post" style="margin: 0;">
<table class="widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="manage-column"><?php _e('Thumb', 'adfever'); ?></th>
			<th scope="col" class="manage-column" style="width: 40%"><?php _e('Name', 'adfever'); ?></th>
			<th scope="col" class="manage-column"><?php _e('Preview', 'adfever'); ?></th>
			<th scope="col" class="manage-column"><?php _e('Price', 'adfever'); ?></th>
			<th scope="col" class="manage-column"><?php _e('Category', 'adfever'); ?></th>
			<th scope="col" class="manage-column" style="text-align: center"><?php _e('Choice', 'adfever'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col" class="manage-column"><?php _e('Thumb', 'adfever'); ?></th>
			<th scope="col" class="manage-column" style="width: 40%"><?php _e('Name', 'adfever'); ?></th>
			<th scope="col" class="manage-column"><?php _e('Preview', 'adfever'); ?></th>
			<th scope="col" class="manage-column"><?php _e('Price', 'adfever'); ?></th>
			<th scope="col" class="manage-column"><?php _e('Category', 'adfever'); ?></th>
			<th scope="col" class="manage-column" style="text-align: center"><?php _e('Choice', 'adfever'); ?></th>
		</tr>
	</tfoot>

	<tbody>
	<?php foreach( (array) $results as $result ) : ?>
		<tr>
			<td><img src="<?php echo clean_url( $result['image'] ); ?>"
				alt="<?php echo attribute_escape( $result['name'] ); ?>"
				style="float: left; height: 32px; margin: 2px; max-width: 40px;" /></td>
			<td><?php echo wp_specialchars( $result['name'] ); ?></td>
			<td><a href="" target="_blank"><?php _e('See details', 'adfever'); ?></a></td>
			<td><?php echo wp_specialchars( $result["prix_moins"]['value'] . ' ' . $result["prix_moins"]['currency'] ); ?></td>
			<td><?php echo wp_specialchars( $result['category']['name'] ); ?></td>
			<td><strong><a
				href="<?php echo add_query_arg( array('send_to_editor' => 'true', 'item_id' => $result['id'], 'cat_id' => $result['category']['id']) ); ?>"><?php _e('Select', 'adfever'); ?></a></strong></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>

</form>

		<?php else : ?>

<p class="clear"><?php _e('No result for this search. You dont start search ? Start now !', 'adfever'); ?></p>

<?php endif; ?> <?php endif; ?></div>

<?php do_action('admin_print_footer_scripts'); ?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
