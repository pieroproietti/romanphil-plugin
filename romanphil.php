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
* @since             1.0.0
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
* definisco azioni SE NON definite!
*/

if(!
function_exists( 'romanphil_woo_render_acf_field' ) ) {
	
	function romanphil_catalog ( $content ) {
		
		$content .= '<div="catalogo">';
		if (  class_exists( 'Acf' ) ) {
			// Poiche ACF get_field restituisce una stringa 'null' lo confronto con essa e,
			// se è 'null' non stampo niente!
			if ( get_field('catalogo')!=='null') {
				$fields = get_field_objects();
				if( $fields )
				{
					?>
					<span class="posted_in">
					<?php
					foreach( $fields as $field_name => $field )
					{
						if ($field_name == '') continue;
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
* WIDGET
*/
// Register and load the widget
function romanphil_load_widget() {
	register_widget( 'romanphil_widget' );
}
add_action( 'widgets_init', 'romanphil_load_widget' );

// Creating the widget 
class romanphil_widget extends WP_Widget {
	
	function __construct() {
		parent::__construct(
			
			// Base ID of your widget
			'romanphil_widget', 
			
			// Widget name will appear in UI
			__('Romanphil Search', 'romanphil_widget_domain'), 
			
			// Widget description
			array( 'description' => __( 'Romanphil - Ricerca per catalogo', 'romanphil_widget_domain' ), ) 
		);
	}
	
	// Creating widget front-end
	
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		
		// This is where you run the code and display the output
		// echo __( 'Hello, World!', 'romanphil_widget_domain' );
		if( isset( $_POST ) ) {
			echo '<pre>';
			var_dump($_POST);
			echo '</pre>';
		}
		?>

		<form action="" method="post">
		<fieldset>
			<label>Catalogo</label>
			<div>
				<input type="radio" id="cei" name="catalogo" value="CEI" checked />
				<label for="scales">CEI</label>
			</div>

			<div>
				<input type="radio" id="bolaffi" name="catalogo" value="bolaffi" />
				<label for="bolaffi">Bolaffi</label>
			</div>

			<div>
				<input type="radio" id="yvert-et-tellier" name="catalogo" value="yvert-et-tellier" />
				<label for="yvert-et-tellier">Yvert & Tellier</label>
			</div>

			<div>
				<input type="radio" id="michel" name="catalogo" value="michel" />
				<label for="michel">Michel</label>
			</div>

			<div>
				<input type="radio" id="sassone" name="catalogo" value="sassone" />
				<label for="sassone">Sassone</label>
			</div>

			<div>
				<input type="radio" id="stanley-gibbons" name="catalogo" value="stanley-gibbons" />
				<label for="stanley-gibbons">Stanley Gibbons</label>
			</div>

			<div>
				<input type="radio" id="unificato" name="catalogo" value="unificato" />
				<label for="unificato">Unificato</label>
			</div>

			<div>
				<label for="numero">Numero</label>
				<input type="text" id="numero" name="numero">
			</div>

			<input type="submit" value="OK">
			<input type="reset" value="Annulla">
		</fieldset>
		</form>
		<?php
		echo $args['after_widget'];
	}
	
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'romanphil_widget_domain' );
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here


