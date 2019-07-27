<?php 
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Redirect Rules
 *
 * @wordpress-plugin
 * Plugin Name:       Redirect Rules
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Kunal malviya
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       redirect-rules
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* Steven redirect widget
**/

/**
* Registering the custom posttype for redirect widget
**/
add_action( 'init', 'widget_redirect_rules_cb' );
function widget_redirect_rules_cb() {
    $labels = array(
        'name'               => _x( 'Redirect Rules', 'post type general name', 'your-plugin-textdomain' ),
        'singular_name'      => _x( 'Redirect Rule', 'post type singular name', 'your-plugin-textdomain' ),
        'menu_name'          => _x( 'Redirect Rules', 'admin menu', 'your-plugin-textdomain' ),
        'name_admin_bar'     => _x( 'Redirect Rule', 'add new on admin bar', 'your-plugin-textdomain' ),
        'add_new'            => _x( 'Add New', 'Job', 'your-plugin-textdomain' ),
        'add_new_item'       => __( 'Add New Redirect Rule', 'your-plugin-textdomain' ),
        'new_item'           => __( 'New Redirect Rule', 'your-plugin-textdomain' ),
        'edit_item'          => __( 'Edit Redirect Rule', 'your-plugin-textdomain' ),
        'view_item'          => __( 'View Redirect Rule', 'your-plugin-textdomain' ),
        'all_items'          => __( 'All Redirect Rules', 'your-plugin-textdomain' ),
        'search_items'       => __( 'Search Redirect Rules', 'your-plugin-textdomain' ),
        'parent_item_colon'  => __( 'Parent Redirect Rules:', 'your-plugin-textdomain' ),
        'not_found'          => __( 'No Redirect Rule found.', 'your-plugin-textdomain' ),
        'not_found_in_trash' => __( 'No Redirect Rule found in Trash.', 'your-plugin-textdomain' )
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        // 'rewrite'            => array( 'slug' => 'jobs' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor' )
    );

    register_post_type( 'simple_jobs1', $args );
}


/**
* Registering the meta boxes for other job information
**/
add_action( 'add_meta_boxes_simple_jobs1', 'adding_simple_jobs1_boxes', 10, 2 );
function adding_simple_jobs1_boxes() {
    $screen = get_current_screen();
    add_meta_box(
        'other-job-informations-meta-box',
        __( 'Other Informations' ),
        'render_redirect_other_informations_meta_box'
    );    
}

/**
* Callback function of other information meta box
**/
function render_redirect_other_informations_meta_box() {
    if( !empty($_GET['post']) ) {
        $url = get_post_meta($_GET['post'], '_redirect_url', ARRAY_A);
    }
    else {
        $url = "";
    }
    echo '<div id="feedsGeneratorId1">          
        <form action="" method="post">          
            <ul>
                <li>
                    <label for="redirect_url" style="width:10%">Redirect Url: </label>
                    <input type="text" name="_redirect_url" style="width:88%" id="redirect_url" value="'.$url.'" required/>
                </li>                               
            </ul>            
        </form>     
    </div>';
}

/**
* Hooking the save post action
**/
add_action('save_post', 'redirect_rules_on_save');
function redirect_rules_on_save($post_id) {  
    if( !empty($_POST['_redirect_url']) && $post_id) {
        update_post_meta($post_id, '_redirect_url', $_POST['_redirect_url']);        
    }
}

/**
* Adding shortcode for this plugin
**/
add_shortcode( 'show_text', 'show_text_shortcode_callback' );
function show_text_shortcode_callback( $atts, $shortCodecontent ) {
    $oldShortCodecontent = $shortCodecontent;
    // Get shortcodes
    $a = shortcode_atts( array(
        "id" => "",
    ), $atts );

    $post        = get_post($a['id']);
    $redirectUrl = get_post_meta($a['id'], '_redirect_url', ARRAY_A);
    
    if( !empty($_GET['wrid']) ) {
        $referenceShortCodeData = get_post($_GET['wrid']);        
        return $referenceShortCodeData->post_content;
    }

    if($post->post_status == 'publish') {
        // If id is set then redirect to other page with query string
        if( $a['id'] ) {
            $parsed  = get_string_between($oldShortCodecontent, '[tag]', '[/tag]');
            $search  = '[tag]'.$parsed.'[/tag]';
            $replace = '<a href="'.$redirectUrl.'?wrid='.$a['id'].'" target="_blank">'.$parsed.'</a>';
            return str_replace($search, $replace, $shortCodecontent);
        }
        else {
            return $shortCodecontent;   
        }
    }
    // return "shortcode not seems to be publish";       
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
