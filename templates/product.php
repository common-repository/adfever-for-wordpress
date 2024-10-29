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

<div class="adfever-product">{include:breadcrumb}

<div class="adfever-content"><?php
if ( !empty($product['image']) ) // Display image if it exist.
echo '<img src="'.clean_url($product['image']).'" alt="'.attribute_escape($product['name']).'" class="adfever-img adf_img" />';
?>

<h3><?php echo wp_specialchars($product['name']); ?></h3>
<?php echo apply_filters('the_adfever_description', $product['description'] ); ?>

<?php if ( !empty($product['group']) ) : ?>
<p><a href="#data-sheet-title" class="data-sheet-link"><?php _e('Data sheet', 'adfever'); ?></a></p>
<?php endif; ?> <?php if ( !empty($product['rating']) && $product['rating']!='0,0') : ?>
<p><?php printf( __('Rating : %s', 'adfever' ), $product['rating'] ); ?></p>
<?php endif; ?>
<?php if($product['minprice']!=$product['maxprice']): ?>
<p><?php printf( __('<strong>Price range</strong> : %s - %s', 'adfever'), number_format($product['minprice'], 2, ',', ' ').'€', number_format($product['maxprice'], 2, ',', ' ').'€' ); ?></p>
<?php endif; ?>
<div class="adfever-clear"></div>
</div>

<table class="best-price">
	<tr>
		<td>
		<h4><a <?php echo ($external==1) ? 'class="external"' : ''; ?> <?php echo ($nofollow==1 ? 'rel="nofollow "' : ''); ?> href="<?php echo clean_url($product['best_offer']['link']); ?>"><?php _e('Best price', 'adfever'); ?></a></h4>
		</td>
		<td><a <?php echo ($external==1) ? 'class="external"' : ''; ?> <?php echo ($nofollow==1 ? 'rel="nofollow "' : ''); ?> href="<?php echo clean_url($product['best_offer']['link']); ?>">
		<?php echo number_format($product['best_offer']['price']['value'], 2, ',', ' ').$product['best_offer']['price']['currency']; ?>
		<br />
		<em><?php printf( __('At %s', 'adfever'), wp_specialchars($product['best_offer']['merchant']) ); ?></em>
		</a></td>
		<td><a class="link-see <?php echo ($external==1) ? ' external' : ''; ?>" <?php echo ($nofollow==1 ? 'rel="nofollow "' : ''); ?>
			href="<?php echo clean_url($product['best_offer']['link']); ?>"><?php _e("&raquo; See offer", 'adfever'); ?></a></td>
	</tr>
</table>

		<?php if ( is_array($stores) && !empty($stores) ) : ?>
<table class="sortable list-stores tablesorter">
	<thead>
		<tr>
			<th><?php _e('Retailer', 'adfever'); ?></th>
			<th><?php _e('Availability', 'adfever'); ?></th>
			<th><?php _e('Total price (incl.delivery)', 'adfever'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach( (array) $stores as $store ) :
	?>
		<tr>
			<td><?php
			//$obj = $adfever_obj['comparator'];
			$logo_url = $store['logo'];
			if ( !empty($logo_url) )
			echo '<a '.(($external==1) ? 'class="external"' : '').' '.($nofollow==1 ? 'rel="nofollow"' : '').' href="'.$store['link'].'"><img class="adf_img" src="'.$logo_url.'" alt="'.attribute_escape($store['merchant']).'" /></a>'; // Display logo for each stores
			else
			echo '<a '.(($external==1) ? 'class="external"' : '').' '.($nofollow==1 ? 'rel="nofollow"' : '').' href="'.$store['link'].'">'.wp_specialchars($store['merchant']).'</a>';
			?></td>
			<td><a class="adf_text<?php echo ($external==1) ? ' external' : ''; ?>" <?php echo ($nofollow ? 'rel="nofollow "' : ''); ?> <?php if ($this->current_options['c-enable-colors']=='1') {
			echo 'style="color:'.$this->current_options['c-text-table'].';"';
		} ?> href="<?php echo $store['link']; ?>"><?php echo $store['stock']; ?></a></td>
			<td><a class="adf_price<?php echo ($external==1) ? ' external' : ''; ?>" <?php echo ($nofollow ? 'rel="nofollow "' : ''); ?> <?php if ($this->current_options['c-enable-colors']=='1') {
			echo 'style="color:'.$this->current_options['c-price'].';"';
		} ?>  href="<?php echo $store['link']; ?>"><?php echo number_format($store['total_price']['value'], 2, ',', ' '); ?>€
			<br />
			<img src="<?php echo ADFEVER_URL; ?>/inc/images/bt-go.jpg" border="0" /></a></td>
		</tr>
		<?php
		endforeach;
		?>
	</tbody>
</table>
		<?php else : ?>
<p><?php _e('No retailers for this product.', 'adfever'); ?></p>
<?php endif; ?> <?php if ( !empty($product['group']) ) : ?>
<h3 id="data-sheet-title"><?php _e('Data sheet', 'adfever'); ?></h3>
<table id="data-sheet">
<?php foreach( (array) $product['group'] as $characteristic ) : ?>
	<tr>
		<td><?php echo wp_specialchars($characteristic['name']); ?></td>
		<td>
		<ul>
		<?php foreach( (array) $characteristic['property'] as $property ) : ?>
			<li><strong><?php echo wp_specialchars($property['name']); ?> :</strong>
			<?php echo wp_specialchars($property['value']); ?></li>
			<?php endforeach; ?>
		</ul>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
	<?php endif; ?>

<table class="best-price">
	<tr>
		<td>
		<h4><a <?php echo ($external==1) ? 'class="external"' : ''; ?> class="external" <?php echo ($this->current_options['nofollow']==1 ? 'rel="nofollow "' : ''); ?> href="<?php echo clean_url($product['best_offer']['link']); ?>"><?php _e('Best price', 'adfever'); ?></a></h4>
		</td>
		<td><a <?php echo ($external==1) ? 'class="external"' : ''; ?> class="external" <?php echo ($this->current_options['nofollow']==1 ? 'rel="nofollow "' : ''); ?> href="<?php echo clean_url($product['best_offer']['link']); ?>">
		<?php echo number_format($product['best_offer']['price']['value'], 2, ',', ' ').$product['best_offer']['price']['currency']; ?>
		<br />
		<em><?php printf( __('At %s', 'adfever'), wp_specialchars($product['best_offer']['merchant']) ); ?></em>
		</a></td>
		<td><a class="link-see external" <?php echo ($external==1) ? 'class="external"' : ''; ?> <?php echo ($this->current_options['nofollow'] ? 'rel="nofollow "' : ''); ?>
			href="<?php echo clean_url($product['best_offer']['link']); ?>"><?php _e("&raquo; See offer", 'adfever'); ?></a></td>
	</tr>
</table>

{include:search}</div>
