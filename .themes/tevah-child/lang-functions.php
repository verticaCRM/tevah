<?php

add_action( 'after_setup_theme', 'my_child_theme_setup' );
function my_child_theme_setup() {
load_theme_textdomain( 'bbcrmint', get_template_directory() . '/languages' );
load_child_theme_textdomain( 'bbcrmint', get_stylesheet_directory() . '/languages' );
}
