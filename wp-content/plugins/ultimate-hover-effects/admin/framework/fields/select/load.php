<?php

function gf_picker_field() {

    wp_enqueue_style('chosen_css', plugins_url( 'chosen.min.css' , __FILE__ ) );
    wp_enqueue_script('chosen_js', plugins_url( 'chosen.jquery.min.js' , __FILE__ ), array(), '', true );

  }
 add_action( 'admin_enqueue_scripts', 'gf_picker_field' );
