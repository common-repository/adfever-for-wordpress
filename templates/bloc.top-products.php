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

<div class="adfever-top-products">

<?php
if ( !empty($top_products) && is_array($top_products) ) {
	echo '<h3>';
	_e('Top products', 'adfever');
	echo '</h3>';
	echo '<ul>';
	foreach( (array) $top_products as $product ) {
		echo '<li><a href="'.get_adfever_link('product', $product['id']).'"><img class="adf_img" src="'.$product['image'].'" alt="'.attribute_escape($product['name']).'" /> '.wp_specialchars($product['name']).'</a></li>' . "\n";
	}
	echo '</ul>';
} 
/*
else {
	_e('No top products actually.', 'adfever');
}
*/
?>

<div class="adfever-clear"></div>
</div>

