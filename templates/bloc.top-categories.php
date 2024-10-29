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
?>

<div class="adfever-top-categories">

<h3><?php _e('Top categories', 'adfever'); ?></h3>

<?php
if ( !empty($top_categories) && is_array($top_categories) ) {
	echo '<ul>';
	foreach( (array) $top_categories as $category ) {
		if(file_exists(ADFEVER_FOLDER.'/inc/images/topcategory/'.$category['kwd'].'.jpg')) {
			$image = ADFEVER_URL.'/inc/images/topcategory/'.$category['kwd'].'.jpg';
		}
		else {
			$image = 'http://static.adfever.com/wordpress/top/'.$category['kwd'].'.jpg';
		}
		echo '<li><a href="'.get_adfever_link('category', (!isset($category['kwd'])?$category['id']:$category['kwd']) ).'"><img class="adf_img_200" src="http://static.adfever.com/wordpress/top/'.($category['imgs']!="" ? $category['imgs'] : "image_novisuel_130x130.gif").'" alt="'.attribute_escape($category['name']).'" /> '.wp_specialchars($category['name']).'</a></li>' . "\n";
	}
	echo '</ul>';
} else {
	_e('No top categories actually.', 'adfever');
}
?>

<div class="adfever-clear"></div>
</div>
