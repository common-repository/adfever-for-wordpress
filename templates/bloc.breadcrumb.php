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

<div class="adfever-breadcrumb"><?php


function display_recursive_category( $category, $cats, $inactive, $is_sel, $separator = ' > ' ) {

	
	if($is_sel) {
		if(empty($cats) && !isset($category['category'])) {
			echo '<a href="'.get_adfever_link('category', (!isset($category['kwd'])?$category['id']:$category['kwd']) ).'">'.wp_specialchars($category['name']).'</a>';
		}
		else if ( @$category['link'] == 'false' && (in_array($category['id'], $cats) || $category['id']==1)) {
			echo wp_specialchars($category['name']);
		} 
		else if (in_array($category['id'], $cats)  || $category['id']==1) {
			echo '<a href="'.get_adfever_link('category', (!isset($category['kwd'])?$category['id']:$category['kwd']) ).'">'.wp_specialchars($category['name']).'</a>';
		}
		else if(in_array($category['id'], $inactive)) {
			echo wp_specialchars($category['name']);
		}
		
	
		if ( isset($category['category'][0]) && is_array($category['category'][0]) ) {
			if (in_array($category['id'], $cats) || $category['id']==1 || in_array($category['id'], $inactive)) echo $separator;
			display_recursive_category( $category['category'][0], $cats, $inactive, $is_sel );
		}
	}
	else {
	
		echo '<a href="'.get_adfever_link('category', (!isset($category['kwd'])?$category['id']:$category['kwd']) ).'">'.wp_specialchars($category['name']).'</a>';
		
	
		if ( is_array($category['category'][0]) ) {
		 	echo $separator;
			display_recursive_category( $category['category'][0], $cats, $inactive, $is_sel );
		}
	}
}


display_recursive_category( $breadcrumb, $active_cats, $inactive_cats, $is_sel );

?></div>
