<?php

/**
* The plugin bootstrap file
*
* This file is read by WordPress to generate the plugin information in the plugin
* admin area. This file also includes all of the dependencies used by the plugin,
* registers the activation and deactivation functions, and defines a function
* that starts the plugin.
*
* @link              https://github.com/pieroproietti
* @since             1.0.1
* @package           Romanphil
*
* @wordpress-plugin
* Plugin Name:       romanphil
* Plugin URI:        romanphil
* Description:       Catalogo per francobolli
* Version:           1.0.0
* Author:            piero proietti
* Author URI:        https://github.com/pieroproietti
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       romanphil
* Domain Path:       /languages
*/
/*
UPDATE `wp_posts`
SET post_date_gmt='2018-08-01 00:00:00', post_date='2018-08-01 02:00:00'
WHERE post_date_gmt >= '2018-03-01' AND post_date_gmt < '2018-07-31'
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* Currently plugin version.
* Start at version 1.0.0 and use SemVer - https://semver.org
* Rename this for your plugin and update it as you release new versions.
*/
define( 'PLUGIN_NAME_VERSION', '1.0.0' );


/**
* The code that runs during plugin activation.
* This action is documented in includes/class-romanphil-activator.php
*/
function activate_romanphil() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-romanphil-activator.php';
	Romanphil_Activator::activate();
}

/**
* The code that runs during plugin deactivation.
* This action is documented in includes/class-romanphil-deactivator.php
*/
function deactivate_romanphil() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-romanphil-deactivator.php';
	Romanphil_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_romanphil' );
register_deactivation_hook( __FILE__, 'deactivate_romanphil' );

/**
* The core plugin class that is used to define internationalization,
* admin-specific hooks, and public-facing site hooks.
*/
require plugin_dir_path( __FILE__ ) . 'includes/class-romanphil.php';

/**
* Begins execution of the plugin.
*
* Since everything within the plugin is registered via hooks,
* then kicking off the plugin from this point in the file does
* not affect the page life cycle.
*
* @since    1.0.0
*/
function run_romanphil() {

	$plugin = new Romanphil();
	$plugin->run();

}
run_romanphil();

/*
* Aggiungo azione romanphil_catalog
*/
add_action( 'woocommerce_product_meta_end', 'romanphil_catalog' );

/*
* Usato per disattivare paypal
*/
add_filter( 'woocommerce_available_payment_gateways', 'payment_gateway_disable_role' );



/*
* definisco azioni SE NON definite!
*/

if(!
function_exists( 'romanphil_woo_render_acf_field' ) ) {

	function romanphil_catalog ( $content ) {

		$content .= '<div="catalogo">';
		if (  class_exists( 'Acf' ) ) {
			// Poiche ACF get_field restituisce una stringa 'null' lo confronto con essa e,
			// se è 'null' non stampo niente!
			if ( get_field('catalogo')!=='null' || trim(get_field('numero'))!=='' ) {
				$fields = get_field_objects();
				if( $fields )
				{
					?>
					<span class="posted_in">
					<?php
					foreach( $fields as $field_name => $field )
					{
						//var_dump($field);
						if ($field_name == '') continue;
						if ($field_name == 'catalogo' && $field["value"]!=array(0) ) continue;
						if ($field_name != 'Numero'){ // Se è Numero non stampa la label
							echo $field['label'].': ';
						}
						romanphil_woo_render_acf_field( $field );
						echo "&nbsp;" ;
					}
					?>
					</span>
					<?php
				}
			}
		};
		$content .='</div>';
	}

	function romanphil_woo_render_acf_field( $field ) {
		if ($field['value']!==NULL){

			switch( $field['type'] ) {

				case 'post_object':
				?>
				<a href="<?php echo get_the_permalink( $field['value']->ID ); ?>"><?php echo $field['value']->post_title; ?></a>
				<?php
				break;
				case 'date_picker':
				echo date(
					get_option( 'date_format' ) ,
					strtotime( $field['value'] )
				);
				break;

				case 'file':
				$file = $field['value'];

				if( is_array( $file ) )
				{
					$url = $file['url'];
					$title = $file['title'];
					$caption = $file['caption'];



					if( $caption ): ?>

					<div class="wp-caption">

					<?php endif; ?>

					<a href="<?php echo $url; ?>" title="<?php echo $title; ?>">
					<span><?php echo $title; ?></span>
					</a>

					<?php if( $caption ): ?>

					<p class="wp-caption-text"><?php echo $caption; ?></p>

					</div>
					<?php endif;
				}
				elseif( is_numeric($file) ) {
					?>
					<a href="<?php echo wp_get_attachment_url( intval( $file ) );?>">
					<?php echo wp_get_attachment_url( intval( $file ) );?>
					</a>
					<?php
				}
				else
				{
					?>
					<a href="<?php echo $file;?>">
					<?php echo $file;?>
					</a>
					<?php
				}
				break;

				case 'image':
				//echo $field['value']['sizes']['thumbnail']
				$image = $field['value'];
				if( is_array( $image ) )
				{
					?>
					<a class="fancybox" rel="group" href="<?php echo $image['url'];?>"><img src="<?php echo $image['sizes']['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" />
					<?php
				}
				elseif( is_numeric($image) ) {
					echo wp_get_attachment_image( intval( $image ), 'thumbnail' );
				}
				else {
					?>
					<a class="fancybox" rel="group" href="<?php echo $image;?>"><img src="<?php echo $image;?>" />
					<?php
				}
				break;

				case 'wysiwyg':
				echo wp_kses_post( $field['value'] );
				break;
				case 'true_false':
				if( $field['value'] ) {
					esc_html_e( 'Yes', 'afwp' );
				}
				else {
					esc_html_e( 'No', 'afwp' );
				}
				break;
				case 'relationship':
				$output_array = array();
				foreach(  $field['value'] as $sub_field ) {
					if( is_object( $sub_field ) && is_a( $sub_field, 'WP_Post' ) ) {

						$output_array[] = '<a href="'.get_the_permalink( $sub_field->ID ).'">'.$sub_field->post_title.'</a>';
					}
					?>

					<?php
				}
				echo implode( ', ',  $output_array);
				break;
				case 'taxonomy':
				$output_array = array();
				if( is_array( $field['value'] ) ) {
					foreach( $field['value'] as $sub_field ) {
						if( is_numeric( $sub_field ) ) {

							$sub_field = get_term_by( 'id', intval($sub_field), $field['taxonomy'], OBJECT );
						}

						if( is_object( $sub_field ) ) {
							$output_array[] =  '<a href="'.get_term_link($sub_field->term_id).'">'.$sub_field->name.'</a>';
						}


					}
				}
				elseif( is_object( $field['value'] ) ) {
					$sub_field = $field['value'];
					$output_array[] =  '<a href="'.get_term_link($sub_field->term_id).'">'.$sub_field->name.'</a>';
				}
				elseif( is_numeric( $field['value'] ) ) {

					$sub_field  = get_term_by( 'id', intval( $field['value']), $field['taxonomy'], OBJECT );
					$output_array[] =  '<a href="'.get_term_link($sub_field->term_id).'">'.$sub_field->name.'</a>';

				}

				echo implode( ', ', $output_array );
				break;

				case 'user':
				if( $field['field_type'] == 'select' )
				{
					?>
					<br/>
					<a href="<?php echo get_author_posts_url( $field['value']['ID'], $field['value']['user_nicename'] );?>" title="<?php echo $field['value']['user_nicename'];?>" alt="<?php echo $field['value']['user_nicename'];?>"><?php echo $field['value']['user_avatar'];?></a>


					<a href="<?php echo get_author_posts_url( $field['value']['ID'], $field['value']['user_nicename'] );?>" title="<?php echo $field['value']['user_nicename'];?>" alt="<?php echo $field['value']['user_nicename'];?>">
					<?php echo $field['value']['display_name'];?>
					</a>
					<?php
				}
				else
				{
					$output_array = array();
					foreach( $field['value'] as $sub_field ) {
						$output_array[] = '<a href="'.get_author_posts_url( $sub_field['ID'], $sub_field['user_nicename'] ).'" title="'.$sub_field['user_nicename'].'" alt="'.$sub_field['user_nicename'].'">'.$sub_field['user_avatar'].'</a><br/>
						<a href="'.get_author_posts_url( $sub_field['ID'], $sub_field['user_nicename'] ).'" title="'.$sub_field['user_nicename'].'" alt="'.$sub_field['user_nicename'].'">'.$sub_field['display_name'].'
						</a>';
					}
					echo '<br/>'.implode( '<br/><br/>', $output_array );
				}

				break;
				case 'color_picker':
				?>
				<span style="width:25px; background-color:<?php echo $field['value'];?>"><?php echo
				str_repeat('&nbsp;',12);?></span>
				<?php
				break;
				default:
				//select multiple
				if( is_array( $field['value'] ) ) {
					$field_val = $field['value'];
					array_filter( $field_val );
					$field_val = array_map( 'trim', $field_val);
					$field_val = array_map( 'esc_html', $field_val);
					echo implode(', ', $field_val);
				}
				else {
					echo esc_html( $field['value'] );
				}
			}
		}
	}

}


/*
* Rimuove il metodo di pagamento paypal per la categoria 
*/
function payment_gateway_disable_role( $available_gateways ) {
	global $woocommerce, $post;
	$hidePaymentCategory = array(858);	// euro-oro-monete-euro-vaticano

	foreach ($woocommerce->cart->cart_contents as $key => $values ) {
		$terms = get_the_terms( $values['product_id'], 'product_cat' );
		foreach ($terms as $term) {
			if(in_array($term->term_id, $hidePaymentCategory)){	
				unset( $available_gateways['paypal'] ); //disattiva paypal
			}
			break;
		}			
	}
	return $available_gateways;
}


/* Ordinamento prodotti
* https://docs.woocommerce.com/document/custom-sorting-options-ascdesc/
* “menu_order”, “popularity”, “rating”, “date”, “price”, “price-desc”.
 */
function patricks_woocommerce_catalog_orderby( $orderby ) {
	// Add "Sort by date: oldest to newest" to the menu
	// We still need to add the functionality that actually does the sorting
	//$orderby['oldest_to_recent'] = __( 'Ordina per inserimento: nuovi-vecchi', 'woocommerce' );

	//unset($orderby["menu_order"]);
	unset($orderby["popularity"]);
	unset($orderby["rating"]);
	unset($orderby["date"]);
	unset($orderby["price"]);
	unset($orderby["price-desc"]);

	$orderby['title'] = __( 'Alfabetico', 'woocommerce' );
	$orderby['title-desc'] = __( 'Alfabetico inverso', 'woocommerce' );
	$orderby["price"] = __('Prezzo, dal più economico', 'woocommerce');
	$orderby["price-desc"] = __('Prezzo, dal più costoso', 'woocommerce');
	$orderby["popularity"] = __('Popolarità', 'woocommerce');;
	//$orderby["date"] = __('Ordina per inserimento: vecchi->nuovi', 'woocommerce');

	return $orderby;
}

add_filter('woocommerce_default_catalog_orderby', 'custom_default_catalog_orderby');
add_filter( 'woocommerce_catalog_orderby', 'patricks_woocommerce_catalog_orderby', 20 );

function custom_default_catalog_orderby() {
		return 'title-desc';
}


// Add the ability to sort by oldest to newest
function patricks_woocommerce_get_catalog_ordering_args( $args ) {
	if (!isset($orderby_value)){
		$orderby_value='title-desc';
	}
	//$orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

	switch ( $orderby_value ) {
		case 'title_desc':
			$args['orderby'] = 'title';
			$args['order']   = 'DESC';
		break;
		case 'title':
		$args['orderby'] = 'title';
	}

	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'patricks_woocommerce_get_catalog_ordering_args', 20 );

