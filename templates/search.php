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

<div class="adfever-product">{include:search}
<div class="adfever-breadcrumb"><span class="quantity-right"><?php printf( __('%d results founds', 'adfever'), (int) $quantity_results ); ?></span>

<a href="<?php echo get_adfever_link('home'); ?>"><?php _e('Home', 'adfever'); ?></a>
- <?php printf( __('Your research : %s', 'adfever'), wp_specialchars($terms) ); ?>

<div class="clear"></div>
</div>

<?php if ( !empty($products) ) : ?>

<div id="tri-price"><?php printf( __('Sort by price <a href="%1$s">+</a>/<a href="%2$s">-</a>', 'adfever'), get_adfever_link('search-order', 'more'), get_adfever_link('search-order', 'less') ); ?>
</div>
<table id="list-products">
	<tbody>
	<?php foreach( (array) $products as $product ) :
	$store = $product['offer'][0];
	
	?>
		<tr>
			<td width="20%"><a class="link-image"
				href="<?php echo get_adfever_link('product', $product['id']); ?>"> <img class="adf_img"
				src="<?php echo clean_url( $product['image'] ); ?>"
				alt="<?php echo attribute_escape( $product['name'] ); ?>" /> </a></td>
			<td width="60%">
			<h2><a
				href="<?php echo get_adfever_link('product', $product['id']); ?>"><?php echo wp_specialchars( $product['name'] ); ?></a></h2>
				<?php echo apply_filters('the_adfever_description', $product['description']); ?>
			</td>
			<td width="20%" style="text-align: center;"><?php _e('Best price', 'adfever'); ?>
			<br />
			<?php echo number_format($product['prix_moins']['value'], 2, ',', ' ').$product['prix_moins']['currency']; ?>
			<br />
			<em><?php echo __('At', 'adfever'); printf(' <a href="%1$s">%2$s</a>', clean_url(get_adfever_link('product', $product['id'])), wp_specialchars($store['merchant']) ); ?></em>
			<br />
			<a href="<?php echo get_adfever_link('product', $product['id']); ?>">
			<img src="<?php echo ADFEVER_URL; ?>/inc/images/bt-go.jpg" alt=""
				style="border: 0;" /> </a></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
{pagination} <?php else : ?>

<p class="clear"><?php _e('Sorry, no product matches your search.', 'adfever'); ?></p>

		<?php endif; ?> {include:search}</div>
