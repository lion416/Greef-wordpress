<?php

if ( ! defined( 'ABSPATH' ) ) { die; }

function uhe_circle_shortcode($atts, $content = null){
    extract( shortcode_atts( array(
    
        'id' => '',
        
    ), $atts) );
    
    
    $q = new WP_Query(
        array('posts_per_page' => -1, 'post_type' => 'u_hover_effect', 'p' => $id)
    );

    while($q->have_posts()) : $q->the_post();
    $idd = get_the_ID();

    $options = get_post_meta( $idd, 'ultimate_hover_options', true );
    
    $extra_class = $options['extra_class'];
    
    //typography
    //$heading_font = $options['heading_font'];
    //$desc_font = $options['desc_font'];
    $heading_font_size = $options['heading_font_size'];
    $heading_color = $options['heading_color'];
    //$heading_text_transform = $options['heading_text_transform'];
    //$heading_italic = $options['heading_italic'];
    $desc_font_size = $options['desc_font_size'];
    //$desc_color = $options['desc_color'];
    //$desc_text_transform = $options['desc_text_transform'];
    //$desc_italic = $options['desc_italic'];
    //$desc_line_height = $options['desc_line_height'];
    
    //image sizes
    $custom_image_size = $options['custom_image_size'];
    $image_width = $options['image_width'];
    //$image_height = $options['image_height'];
    //$remove_image_gap = $options['remove_image_gap'];

    //item column
    $column_number = $options['column_number'];
    switch ($column_number) {
    case 1:
        $column = 12;
        break;
    case 2:
        $column = 6;
        break;
    case 3:
        $column = 4;
        break;
    case 4:
        $column = 3;
        break;
    case 6:
        $column = 2;
        break;                       
    default:
        $column = 4;
} 
    

$output ='';

if( ! empty( $options['option'] ) ) {
    
    $groups = $options['option'];


    $output .='<div class="hover-wrap row">';

    foreach( $groups as $group ){
        
    $image = $group['image'];
    $image = wp_get_attachment_image_src( $image, 'full' );
    $effect = $group['effect'];
        
    
    $output .='<div class="ultimate-hover-item mg-col-md-'.$column.' mg-col-xs-12 mg-col-sm-6">';
    

    $output .= '<div class="hover-item" style="">';
    
    $output .= '<a href="" class="ultimate-link noHover">';
        
    $output .= '<figure style="width:300px; height:300px" class="ultimate-hover effect-hover '.$effect.' ratiooriginal effect-fonts ultimate-lazyload">';

    
    $output .= '<img style="width:300px; height:300px" data-src="'.$image[0].'" alt="'.$group['title'].'"/>';
  
    $output .= '<figcaption>
            <div class="effect-caption">
                <div class="effect-heading">
                    <h2 style="font-size:'.$heading_font_size.'px; color:'.$heading_color.';">'.$group['title'].'</h2>
                </div>

                <div class="effect-description">
                    <p style="font-size:'.$desc_font_size.'px;" class="description">'.$group['desc'].'</p>                
                </div>

            </div>
        </figcaption>
    </figure>';

    $output .= '</a>';
        
    $output .= '</div>';    
    
$output .='</div>';

    }
    
    $output .='</div>';//hover wraper close

}
      
    
    endwhile;
    wp_reset_query();
    return $output;
    
}
add_shortcode('u_hover_effect', 'uhe_circle_shortcode');