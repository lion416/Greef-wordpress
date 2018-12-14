<?php
/*
  Plugin Name: Typed
  Description: Typed Plugin created based on "typed.js" a jQuery plugin that types. Enter in any string, and watch it type at the speed you've set, backspace what it's typed, and begin a new sentence for however many strings you've set.
  Version: 1.1.4
  Author: zonnix
  Author URI: http://zonnix.net
  License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Zon_Typed')) :

    class Zon_Typed {

        public function __construct() {
            //create typed custom post type
            add_action('init', array($this, 'typed_init'));
            //create custom fields
            add_action('add_meta_boxes', array($this, 'add_typed_meta'));
            //save custom fields
            add_action('save_post', array($this, 'typed_save_meta_box_data'));
            //add typed to admin
            add_action('admin_enqueue_scripts', array($this, 'add_typed_to_admin'));
            //add shortcode
            add_shortcode('typed', array($this, 'typed_func'));
            //add frontend script
            add_action('wp_enqueue_scripts', array($this, 'typed_frontend_scripts'));
        }

        public function typed_init() {
            $labels = array(
                'name' => __('Typed', 'zon-typed'),
                'singular_name' => __('Typed', 'zon-typed'),
                'menu_name' => __('Typed', 'zon-typed'),
                'name_admin_bar' => __('Book', 'zon-typed'),
                'add_new' => __('Add New', 'zon-typed'),
                'add_new_item' => __('Add New Typed', 'zon-typed'),
                'new_item' => __('New Typed', 'zon-typed'),
                'edit_item' => __('Edit Typed', 'zon-typed'),
                'view_item' => __('View Typed', 'zon-typed'),
                'all_items' => __('All Typed List', 'zon-typed'),
                'search_items' => __('Search Typed', 'zon-typed'),
                'parent_item_colon' => __('Parent Typed:', 'zon-typed'),
                'not_found' => __('No typed found.', 'zon-typed'),
                'not_found_in_trash' => __('No typed found in Trash.', 'zon-typed')
            );

            $args = array(
                'labels' => $labels,
                'description' => __('Description.', 'zon-typed'),
                'public' => false,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'typed'),
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title')
            );

            register_post_type('typed', $args);
        }

        public function add_typed_meta() {
            add_meta_box('typed-perview', 'Perview', array($this, 'typed_perview_meta_box_callback'), 'typed');
            add_meta_box('typed-options', 'Typed Options', array($this, 'typed_meta_box_callback'), 'typed');
            add_meta_box('typed-display-options', 'Display Options', array($this, 'typed_display_meta_box_callback'), 'typed');
        }

        public function typed_perview_meta_box_callback($post) {
            $post_id = $post->ID;
            $typed_strings = get_post_meta($post_id, 'typed_strings', true);
            if (!$typed_strings)
                $typed_strings = array();

            $strings = '';
            foreach ($typed_strings as $string) {
                $strings .= $string . ';#$#;';
            }
            $strings = trim($strings, ';#$#;');
            $font_size = get_post_meta($post_id, 'font_size', true);
            $font_type = get_post_meta($post_id, 'font_type', true);
            $color = get_post_meta($post_id, 'font_color', true);
            $font_weight = get_post_meta($post_id, 'font_weight', true);
            $anchor_color = get_post_meta($post_id, 'anchor_color', true);
            $cursor_color = get_post_meta($post_id, 'cursor_color', true);
            ?>
            <style>
                
                <?php if (get_post_meta($post_id, 'blinking_cursor', true) == 'yes'): ?>
                .typed-div-<?php echo $post_id; ?> .typed-cursor{
                    opacity: 1;
                    -webkit-animation: blink 0.7s infinite;
                    -moz-animation: blink 0.7s infinite;
                    animation: blink 0.7s infinite;
                }
                @keyframes blink{
                    0% { opacity:1; }
                    50% { opacity:0; }
                    100% { opacity:1; }
                }
                @-webkit-keyframes blink{
                    0% { opacity:1; }
                    50% { opacity:0; }
                    100% { opacity:1; }
                }
                @-moz-keyframes blink{
                    0% { opacity:1; }
                    50% { opacity:0; }
                    100% { opacity:1; }
                }
                <?php endif; ?>
                .typed-div-<?php echo $post_id; ?> {
                    <?php if ($font_size): ?>font-size: <?php
                        echo $font_size . 'px;';
                    endif;
                    ?>
                     <?php if ($font_type): ?>font-family: <?php
                        echo $font_type . ';';
                    endif;
                    ?>
                        <?php if ($color): ?>color: <?php
                            echo $color . ';';
                        endif;
                        ?>
                        <?php if ($font_weight): ?>font-weight: <?php
                            echo $font_weight . ';';
                        endif;
                        ?>
                }

                .typed-div-<?php echo $post_id; ?> a {
                    <?php if ($anchor_color): ?>color: <?php
                        echo $anchor_color . ';';
                    endif;
                    ?>
                }

                .typed-div-<?php echo $post_id; ?> .typed-cursor {
                    <?php if ($cursor_color): ?>color: <?php
                        echo $cursor_color . ';';
                    endif;
                    ?>
                }
            </style>
            <div class="typed-div-<?php echo $post_id; ?>">    
                <span style="white-space:pre;word-wrap: break-word;line-height: 1;" class="typed_perview_div" data-cursor-char="<?php echo get_post_meta($post_id, 'cursor_char', true); ?>" data-cursor="<?php echo get_post_meta($post_id, 'show_cursor', true); ?>" data-strings="<?php echo $strings; ?>" data-speed="<?php echo get_post_meta($post_id, 'type_speed', true); ?>" data-delay="<?php echo get_post_meta($post_id, 'start_delay', true); ?>" data-back-delay="<?php echo get_post_meta($post_id, 'back_delay', true); ?>" data-back-speed="<?php echo get_post_meta($post_id, 'back_speed', true); ?>" data-loop="<?php echo get_post_meta($post_id, 'typed_loop', true); ?>"></span>
            </div>
            <br><pre class="update-nag shorCode">Generated Shortcode: [typed id='<?php echo $post_id; ?>']</pre>
            <?php
        }

        public function typed_meta_box_callback($post) {
            // Add a nonce field so we can check for it later.
            wp_nonce_field('typed_save_meta_box_data', 'typed_meta_box_nonce');

            $post_id = $post->ID;
            $typed_strings = get_post_meta($post_id, 'typed_strings', true);
            if (!$typed_strings)
                $typed_strings = array();
            ?>
            <table class="widefat" style="border: 0px;">
                <tbody>
                    <tr>
                        <td style="width: 15%;">Strings</td>
                        <td class="typed_strings_container">
                            <?php
                            $index = 0;
                            if (count($typed_strings) > 0):
                                foreach ($typed_strings as $string):
                                    ?>
                                    <p><input type="text" name="typed_strings[]" value="<?php echo str_replace('"', "'", $string); ?>"/> <a href="javascript:void(0)" class="add_typed_string button-secondary">+</a> <?php if ($index > 0): ?><a href="javascript:void(0)" class="remove_typed_string button-secondary">-</a><?php endif; ?></p>
                                    <?php
                                    $index++;
                                endforeach;
                            else:
                                ?>
                                <p><input type="text" name="typed_strings[]" value=""/> <a href="javascript:void(0)" class="add_typed_string button-secondary">+</a></p>
                            <?php
                            endif;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Type Speed</td>
                        <td>
                            <input type="text" value="<?php echo get_post_meta($post_id, 'type_speed', true) ? get_post_meta($post_id, 'type_speed', true) : 0; ?>" name="type_speed"/>
                            <br><span class="description">typing speed</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Start Delay</td>
                        <td>
                            <input type="text" value="<?php echo get_post_meta($post_id, 'start_delay', true) ? get_post_meta($post_id, 'start_delay', true) : 0; ?>" name="start_delay"/>
                            <br><span class="description">time before typing starts</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Back Speed</td>
                        <td>
                            <input type="text" value="<?php echo get_post_meta($post_id, 'back_speed', true) ? get_post_meta($post_id, 'back_speed', true) : 0; ?>" name="back_speed"/>
                            <br><span class="description">backspacing speed</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Back Delay</td>
                        <td>
                            <input type="text" value="<?php echo get_post_meta($post_id, 'back_delay', true) ? get_post_meta($post_id, 'back_delay', true) : 500; ?>" name="back_delay"/>
                            <br><span class="description">time before backspacing</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Loop</td>
                        <td>
                            <input type="checkbox" value="yes" name="typed_loop" <?php if (get_post_meta($post_id, 'typed_loop', true) == 'yes'): ?>checked=""<?php endif; ?>/>
                            <br><span class="description">make it a loop</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Show Cursor</td>
                        <td>
                            <input type="checkbox" value="yes" name="show_cursor" <?php if (get_post_meta($post_id, 'show_cursor', true) == 'yes'): ?>checked=""<?php endif; ?>/>
                            <br><span class="description">show cursor</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Cursor Char</td>
                        <td>
                            <input type="text" value="<?php echo get_post_meta($post_id, 'cursor_char', true) ? get_post_meta($post_id, 'cursor_char', true) : '|'; ?>" name="cursor_char"/>
                            <br><span class="description">character for cursor</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Blinking Cursor</td>
                        <td>
                            <input type="checkbox" value="yes" name="blinking_cursor" <?php if (get_post_meta($post_id, 'blinking_cursor', true) == 'yes'): ?>checked=""<?php endif; ?>/>
                            <br><span class="description">blinking cursor</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        public function typed_display_meta_box_callback($post) {
            $post_id = $post->ID;
            ?>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td style="width: 15%;">Font Size</td>
                        <td>
                            <input type="text" placeholder="eg. 24" value="<?php echo get_post_meta($post_id, 'font_size', true); ?>" name="font_size"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 15%;">Font Family</td>
                        <td>
                            <input type="text" placeholder='Helvetica,Arial,sans-serif' value="<?php echo get_post_meta($post_id, 'font_type', true); ?>" name="font_type"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 15%;">Font Weight</td>
                        <td>
                            <input type="text" placeholder="eg. 300" value="<?php echo get_post_meta($post_id, 'font_weight', true); ?>" name="font_weight"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Color</td>
                        <td>
                            <input class="typed-color-field" type="text" value="<?php echo get_post_meta($post_id, 'font_color', true); ?>" name="font_color"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Anchor Color</td>
                        <td>
                            <input class="typed-color-field" type="text" value="<?php echo get_post_meta($post_id, 'anchor_color', true); ?>" name="anchor_color"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Cursor Color</td>
                        <td>
                            <input class="typed-color-field" type="text" value="<?php echo get_post_meta($post_id, 'cursor_color', true); ?>" name="cursor_color"/>
                        </td>
                    </tr>                    
                </tbody>
            </table>
            <?php
        }

        public function typed_save_meta_box_data($post_id) {
            /*
             * We need to verify this came from our screen and with proper authorization,
             * because the save_post action can be triggered at other times.
             */

            // Check if our nonce is set.
            if (!isset($_POST['typed_meta_box_nonce'])) {
                return;
            }

            // Verify that the nonce is valid.
            if (!wp_verify_nonce($_POST['typed_meta_box_nonce'], 'typed_save_meta_box_data')) {
                return;
            }

            // If this is an autosave, our form has not been submitted, so we don't want to do anything.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (isset($_POST['post_type']) && 'typed' == $_POST['post_type']) {
                if (isset($_POST['font_size']))
                    update_post_meta($post_id, 'font_size', $_POST['font_size']);

                if (isset($_POST['font_type']))
                    update_post_meta($post_id, 'font_type', $_POST['font_type']);

                if (isset($_POST['font_color']))
                    update_post_meta($post_id, 'font_color', $_POST['font_color']);

                if (isset($_POST['anchor_color']))
                    update_post_meta($post_id, 'anchor_color', $_POST['anchor_color']);

                if (isset($_POST['cursor_color']))
                    update_post_meta($post_id, 'cursor_color', $_POST['cursor_color']);

                if (isset($_POST['font_weight']))
                    update_post_meta($post_id, 'font_weight', $_POST['font_weight']);

                if (isset($_POST['typed_strings'])){
                    $_POST['typed_strings'] = array_map( 'esc_attr', $_POST['typed_strings'] );               
                    update_post_meta($post_id, 'typed_strings', $_POST['typed_strings']);
                }
                
                if (isset($_POST['type_speed']))
                    update_post_meta($post_id, 'type_speed', $_POST['type_speed']);

                if (isset($_POST['start_delay']))
                    update_post_meta($post_id, 'start_delay', $_POST['start_delay']);

                if (isset($_POST['back_speed']))
                    update_post_meta($post_id, 'back_speed', $_POST['back_speed']);

                if (isset($_POST['back_delay']))
                    update_post_meta($post_id, 'back_delay', $_POST['back_delay']);

                if (isset($_POST['typed_loop']))
                    update_post_meta($post_id, 'typed_loop', $_POST['typed_loop']);
                else
                    update_post_meta($post_id, 'typed_loop', 'no');

                if (isset($_POST['show_cursor']))
                    update_post_meta($post_id, 'show_cursor', $_POST['show_cursor']);
                else
                    update_post_meta($post_id, 'show_cursor', 'no');

                if (isset($_POST['blinking_cursor']))
                    update_post_meta($post_id, 'blinking_cursor', $_POST['blinking_cursor']);
                else
                    update_post_meta($post_id, 'blinking_cursor', 'no');
                
                if (isset($_POST['cursor_char']))
                    update_post_meta($post_id, 'cursor_char', $_POST['cursor_char']);
            }
        }

        public function add_typed_to_admin() {
            global $post_type;
            if ($post_type != 'typed')
                return;

            // Add the color picker css file      
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('typed-admin', plugin_dir_url(__FILE__) . '/js/typed.js', array('jquery'));
            wp_enqueue_script('script-admin', plugin_dir_url(__FILE__) . '/js/admin.js', array('jquery', 'wp-color-picker'));
        }

        public function typed_func($attr) {
            ob_start();
            //get typed
            if (isset($attr['id'])) {
                $post_id = intval($attr['id']);
                $typed = get_post($post_id);
                if ($typed) :
                    $font_size = get_post_meta($post_id, 'font_size', true);
                	$font_type = get_post_meta($post_id, 'font_type', true);
                    $color = get_post_meta($post_id, 'font_color', true);
                    $font_weight = get_post_meta($post_id, 'font_weight', true);
                    $anchor_color = get_post_meta($post_id, 'anchor_color', true);
                    $cursor_color = get_post_meta($post_id, 'cursor_color', true);
                    $typed_strings = get_post_meta($post_id, 'typed_strings', true);
                    if (!$typed_strings)
                        $typed_strings = array();

                    $strings = '';
                    foreach ($typed_strings as $string) {
                        $strings .= $string . ';#$#;';
                    }
                    $strings = trim($strings, ';#$#;');
                    ?>
                    <style>
                        .typed-div-<?php echo $post_id; ?> {
                            <?php if ($font_size): ?>font-size: <?php
                                echo $font_size . 'px;';
                            endif;
                            ?>
                            <?php if ($font_type): ?>font-family: <?php
                                echo $font_type . ';';
                            endif;
                            ?>
                                <?php if ($color): ?>color: <?php
                                    echo $color . ';';
                                endif;
                                ?>
                                <?php if ($font_weight): ?>font-weight: <?php
                                    echo $font_weight . ';';
                                endif;
                                ?>
                        }

                        .typed-div-<?php echo $post_id; ?> a {
                            <?php if ($anchor_color): ?>color: <?php
                                echo $anchor_color . ';';
                            endif;
                            ?>
                        }

                        .typed-div-<?php echo $post_id; ?> .typed-cursor {
                            <?php if ($cursor_color): ?>color: <?php
                                echo $cursor_color . ';';
                            endif;
                            ?>
                        }
                        
                        <?php if (get_post_meta($post_id, 'blinking_cursor', true) == 'yes'): ?>
                            .typed-div-<?php echo $post_id; ?> .typed-cursor{
                                opacity: 1;
                                -webkit-animation: blink 0.7s infinite;
                                -moz-animation: blink 0.7s infinite;
                                animation: blink 0.7s infinite;
                            }
                            @keyframes blink{
                                0% { opacity:1; }
                                50% { opacity:0; }
                                100% { opacity:1; }
                            }
                            @-webkit-keyframes blink{
                                0% { opacity:1; }
                                50% { opacity:0; }
                                100% { opacity:1; }
                            }
                            @-moz-keyframes blink{
                                0% { opacity:1; }
                                50% { opacity:0; }
                                100% { opacity:1; }
                            }
                        <?php endif; ?>
                    </style>
                    <div class="typed-div-<?php echo $post_id; ?>">                        
                        <span style="white-space:pre;" class="typed_perview_div" data-cursor-char="<?php echo get_post_meta($post_id, 'cursor_char', true); ?>" data-cursor="<?php echo get_post_meta($post_id, 'show_cursor', true); ?>" data-strings="<?php echo $strings; ?>" data-speed="<?php echo get_post_meta($post_id, 'type_speed', true); ?>" data-delay="<?php echo get_post_meta($post_id, 'start_delay', true); ?>" data-back-delay="<?php echo get_post_meta($post_id, 'back_delay', true); ?>" data-back-speed="<?php echo get_post_meta($post_id, 'back_speed', true); ?>" data-loop="<?php echo get_post_meta($post_id, 'typed_loop', true); ?>"></span>
                    </div>
                    <?php
                endif;
            }
            return ob_get_clean();
        }

        public function typed_frontend_scripts() {
            wp_enqueue_script('script-typed', plugin_dir_url(__FILE__) . '/js/typed.js', array('jquery'));
            wp_enqueue_script('script-typed-frontend', plugin_dir_url(__FILE__) . '/js/frontend.js', array('jquery'));
        }

    }

    $Zon_Typed = new Zon_Typed();
    
endif;