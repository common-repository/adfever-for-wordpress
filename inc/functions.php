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





function is_afever() {
	global $adfever_obj;
	if ( $adfever_obj->is_adfever == true ) {
		return true;
	}
	return false;
}

function adfever_render( $echo = true ) {
	$comparator = & AdfeverClient_Comparator::getInstance();
	if ( $echo == true ) 
	echo $comparator->render();
	else
	return $comparator->render();

	return true;
}

function get_adfever_link( $object_type = 'category', $id = null) {
	$comparator = & AdfeverClient_Comparator::getInstance();
	return $comparator->link( $object_type, $id );
}

function recursive_categories_li( $categories = array() ) {
	foreach( (array) $categories as $category ) {

		$flag = false;
		if ( isset($category['name']) ) {
			$flag = true;
			echo '<li><a href="'.add_query_arg( array('send_to_editor' => 'true', 'cat_id' => $category['id']) ).'">'.wp_specialchars(($category['name'])).'</a>' . "\n";
				
			if ( is_array($category['categories']) && !empty($category['categories']) ) {
				echo '<ul>' . "\n";

				recursive_categories_li( $category['categories']['category'] );

				echo '</ul>' . "\n";
			}
				
			if ( $flag == true ) {
				echo '</li>' . "\n";
			}
		}

	}
}

function recursive_categories_checkbox( $categories = array(), $object = '', &$current_categories, $parent_id = 0 ) {
	global $adfever_term_id;

	foreach( (array) $categories as $category ) {

		$flag = false;
		if ( isset($category['name']) ) {

			if ( $object == 'univers' ) {
				$adfever_term_id = $category['id'];
			}
				
			$flag = true;
			echo '<li><input '.(in_array($category['id'], (array)$current_categories)?'checked="checked"':'').' type="checkbox" name="multiples_cat['.$adfever_term_id.']['.$parent_id.'][]" value="'.$category['id'].'" id="categories-checkbox-'.$category['id'].'" /> <label>'.wp_specialchars(($category['name'])).'</label>' . "\n";
				
			if ( is_array($category['categories']) && !empty($category['categories']) ) {
				echo "\t\t" . '<ul>' . "\n";

				recursive_categories_checkbox( $category['categories']['category'], 'categories', $current_categories, $category['id'] );

				echo "\t\t" . '</ul>' . "\n";
			}
				
			if ( $flag == true ) {
				echo '</li>' . "\n";
			}
		}

	}
}

function recursive_categories_select( $categories = array(), $current = -1 ) {
	
	foreach( (array) $categories as $category ) {

		if ( isset($category['name']) ) {
				
			$selected = ( $current == $category['id'] ) ? 'selected="selected"' : '';
			echo '<option '.$selected.' value="'.$category['id'].'">'.wp_specialchars(($category['name'])).'</option>' . "\n";
				
			if ( is_array($category['categories']) && !empty($category['categories']) ) {
				recursive_categories_select( $category['categories']['category'], $current );
			}
				
		}

	}
}

function select_quantity_stores() {
	return apply_filters( 'select_quantity_stores', array( 3, 4, 5, 6 ) );
}

function select_quantity_sub_category() {
	return apply_filters( 'select_quantity_sub_category', array( 3, 5, __('max', 'adfever') ) );
}

function select_quantity_products_per_page() {
	return apply_filters( 'select_quantity_products_per_page', array( 20, 50, 100 ) );
}
?>
