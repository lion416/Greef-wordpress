<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
// ===============================================================================================
// -----------------------------------------------------------------------------------------------
// METABOX OPTIONS
// -----------------------------------------------------------------------------------------------
// ===============================================================================================
$options      = array();

// -----------------------------------------
// Page Metabox Options                    -
// -----------------------------------------
$options[]    = array(
  'id'        => 'ultimate_hover_options',
  'title'     => 'Hover Effect Options',
  'post_type' => 'u_hover_effect',
  'context'   => 'normal',
  'priority'  => 'default',
  'sections'  => array(

    // begin: a section
    array(
      'name'  => 'hover_items',
      'title' => 'Hover Items',
      'icon'  => 'fa fa-picture-o',

      // begin: fields
      'fields' => array(


        array(
          'id'              => 'option',
          'type'            => 'group',
          'title'           => '',
          //'dependency'   => array( 'style_circle', '==', 'true' ),
          'button_title'    => 'Add New Hover Item',
          'accordion_title' => 'Hover Item',
          'fields'          => array(
          
            array(
              'id'    => 'image',
              'type'  => 'image',
              'title' => 'Hover Image',
            ),

            array(
              'id'       => 'effect',
              'type'     => 'select',
              'title'    => 'Select Effect',
              'options'  => array(
                'effect-lily' => 'Effect 1',
                'effect-sadie' => 'Effect 2',
                'effect-honey' => 'Effect 3',
                'effect-layla' => 'Effect 4',
                'effect-zoe' => 'Effect 5',
                'effect-oscar' => 'Effect 6',
                'effect-marley' => 'Effect 7',
                'effect-ruby' => 'Effect 8',
                'effect-roxy' => 'Effect 9',
                'effect-bubba' => 'Effect 10',
                'effect-romeo' => 'Effect 11',
                'effect-dexter' => 'Effect 12',
                'effect-sarah' => 'Effect 13',
                'effect-chico' => 'Effect 14',
                'effect-milo' => 'Effect 15',
                'effect-ming' => 'Effect 16',
                'effect-julia' => 'Effect 17',
                'effect-goliath' => 'Effect 18',
                'effect-hera' => 'Effect 19',
                'effect-winston' => 'Effect 20',
              ),
              'default'  => 'effect-lily',
            ),            
            array(
              'id'    => 'title',
              'type'  => 'text',
              'title' => 'Title',
              'default' => 'Heading Here',
              //'dependency'   => array( 'style_circle', '==', 'true' ),
            ),
            array(
              'id'    => 'desc',
              'type'  => 'textarea',
              'title' => 'Description',
              'default' => 'description goes here',
              //'dependency'   => array( 'style_circle', '==', 'true' ),
            ),
            
            array(
              'id'       => 'on_click',
              'type'     => 'select',
              'title'    => 'On Click: <br /><span style="color: #d63434">Pro Only</span>',
              //'dependency'   => array( 'circle_effect', 'any', 'effect2' ),
              'options'  => array(
                'do_nothing'  => 'Do Nothing',
              ),
              'default'  => 'do_nothing',
            ),            
            array(
              'id'    => 'image_link',
              'type'  => 'text',
              'title' => 'Image Link',
              'desc' => 'Insert custom URL including http://',
              'dependency' => array( 'on_click', '==', 'custom_link' ),
            ),                                
            array(
              'id'       => 'open_link',
              'type'     => 'select',
              'title'    => 'Open Link in:',
              'options'  => array(
                ''  => 'Same Window',
                '_blank' => 'New Window',
                ),
              'default'  => 'same',
              'dependency' => array( 'on_click', '==', 'custom_link' ),
            ),  
            
            array(
              'type'    => 'notice',
              'class'   => 'danger',
              'content' => '<h3 align="center">To get all features working, please buy the pro version here <a target="_blank" href="https://themebon.com/item/ultimate-hover-effects-pro/">Ultimate Hover Effects Pro</a> for only $11</h3>',
            ),                      
            
            
          ),           
          
        ),


      ), // end: fields   
    ), // end: a section



    // begin: a section
    array(
      'name'  => 'settings',
      'title' => 'Settings',
      'icon'  => 'fa fa-cogs',

      // begin: fields
      'fields' => array(


            array(
              'id'       => 'column_number',
              'type'     => 'select',
              'title'    => 'Number of Column:',
              'options'  => array(
                '1'  => '1',
                '2'   => '2',
                '3'   => '3',
                '4'   => '4',
                //'5'   => '5',
                '6'   => '6',
              ),
              'default'  => '3',
            ),
            array(
              'id'       => 'custom_image_size',
              'type'     => 'select',
              'title'    => 'Image Size:',
              'options'  => array(
                ''  => 'Default',
                'custom'   => 'Custom',
              ),
              'default'  => 'custom',
            ),
            array(
              'id'      => 'image_width',
              'type'    => 'number',
              'title'   => 'Image Width <br /><span style="color: #d63434">Pro Only</span>',
              'after'   => '<i class="cs-text-muted">(px)</i>',
              'desc'    => 'default value is 300px',
              'default'  => '300',
              'dependency'   => array( 'custom_image_size', '==', 'custom' ),
            ),

            array(
              'id'      => 'image_height',
              'type'    => 'number',
              'title'   => 'Image Height <br /><span style="color: #d63434">Pro Only</span>',
              'after'   => '<i class="cs-text-muted">(px)</i>',
              'desc'    => 'default value is 300px',
              'default'  => '300',
              'dependency'   => array( 'custom_image_size', '==', 'custom' ),
            ),
            array(
              'id'       => 'remove_image_gap',
              'type'     => 'checkbox',
              'title'    => 'Remove Images Gap: <br /><span style="color: #d63434">Pro Only</span>',
              'dependency'   => array( 'custom_image_size', '==', 'custom' ),
            ),





            array(
              'id'    => 'extra_class',
              'type'  => 'text',
              'title' => 'Extra CSS Class <br /><span style="color: #d63434">Pro Only</span>',
              'desc' => 'Extra css class for customizing',
              'default'  => '',
              //'dependency'   => array( 'style_circle', '==', 'true' ),
            ),
            array(
              'id'    => 'custom_css',
              'type'  => 'textarea',
              'title' => 'Custom CSS <br /><span style="color: #d63434">Pro Only</span>',
              'desc' => 'You can override css here',
            ),         
            array(
              'type'    => 'notice',
              'class'   => 'danger',
              'content' => '<h3 align="center">To get all features working, please buy the pro version here <a target="_blank" href="https://themebon.com/item/ultimate-hover-effects-pro/">Ultimate Hover Effects Pro</a> for only $11</h3>',
            ),




      ), // end: fields   
    ), // end: a section


    // begin: a section
    array(
      'name'  => 'typography',
      'title' => 'Typography',
      'icon'  => 'fa fa-font',

      // begin: fields
      'fields' => array(


            array(
              'id'        => 'heading_font',
              'type'      => 'typography',
              'title'     => 'Select Heading Font <br /><span style="color: #d63434">Pro Only</span>',
              'default'   => array(
                'family'  => 'Open Sans',
                'variant' => '800',
                'font'    => 'google', // this is helper for output
              ),
            ),

            array(
              'id'      => 'heading_font_size',
              'type'    => 'number',
              'title'   => 'Heading Font Size <br /><span style="color: #d63434">Pro Only</span>',
              'after'   => '<i class="cs-text-muted">(px)</i>',
              'desc'    => 'default value is 24px',
              'default'  => '24',
            ),                    
            array(
              'id'      => 'heading_color',
              'type'    => 'color_picker',
              'title'   => 'Heading Color',
              'default' => '#fff',
              'desc'    => 'default color is #fff',
            ),
            array(
              'id'       => 'heading_text_transform',
              'type'     => 'select',
              'title'    => 'Heading Text Transform <br /><span style="color: #d63434">Pro Only</span>',
              'options'  => array(
                ''  => 'Default',
                'uppercase'   => 'Upercase',
              ),
            ),            
            array(
              'id'       => 'heading_italic',
              'type'     => 'select',
              'title'    => 'Heading Font Style: <br /><span style="color: #d63434">Pro Only</span>',
              'options'  => array(
                'normal'  => 'Default',
                'italic'   => 'Italic',
              ),
            ),            
            
              
            array(
              'id'        => 'desc_font',
              'type'      => 'typography',
              'title'     => 'Select Description Font <br /><span style="color: #d63434">Pro Only</span>',
              'default'   => array(
                'family'  => 'Open Sans',
                'variant' => 'regular',
                'font'    => 'google', // this is helper for output
              ),
            ),         
            array(
              'id'      => 'desc_font_size',
              'type'    => 'number',
              'title'   => 'Description Font Size',
              'after'   => '<i class="cs-text-muted">(px)</i>',
              'desc'    => 'default value is 20px',
              'default'  => '18',
            ),
            array(
              'id'      => 'desc_color',
              'type'    => 'color_picker',
              'title'   => 'Description Color <br /><span style="color: #d63434">Pro Only</span>',
              'default' => '#fff',
              'desc'    => 'default color is #fff',
            ),
            array(
              'id'       => 'desc_text_transform',
              'type'     => 'select',
              'title'    => 'Description Text Transform <br /><span style="color: #d63434">Pro Only</span>',
              'options'  => array(
                ''  => 'Default',
                'uppercase'   => 'Upercase',
              ),
            ),
            array(
              'id'       => 'desc_italic',
              'type'     => 'select',
              'title'    => 'Description Font Style: <br /><span style="color: #d63434">Pro Only</span>',
              'options'  => array(
                'normal'  => 'Default',
                'italic'   => 'Italic',
              ),
            ),            
            array(
              'id'      => 'desc_line_height',
              'type'    => 'number',
              'title'   => 'Description Text Line Height <br /><span style="color: #d63434">Pro Only</span>',
              'after'   => '<i class="cs-text-muted">(px)</i>',
              'desc'    => 'default value is 22px',
              'default'  => '22',
            ),            
            array(
              'type'    => 'notice',
              'class'   => 'danger',
              'content' => '<h3 align="center">To get all features working, please buy the pro version here <a target="_blank" href="https://themebon.com/item/ultimate-hover-effects-pro/">Ultimate Hover Effects Pro</a> for only $11</h3>',
            ),            


      ), // end: fields   
    ), // end: a section



  ),
);

CSFramework_Metabox::instance( $options );
