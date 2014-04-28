<?php
/*
Plugin Name: Wordpress RPG
Plugin URI: 
Description: Incetivize your blogging with RPG Meta-elements
Author: Ian Hunter
Version: 0.0
Author URI: www.ianhunter.ie
*/


#The info display in the top right hand corner
function admin_info_header() {
    $file = WP_PLUGIN_DIR."/Wordpress-RPG/experience.rpg"; 
    $experience = file_get_contents ( $file );
    $stats = calc_level($experience);
    echo '<div style="float:right">Level ' . $stats[0] . ' Templar - ' . $stats[1] . '/' . $stats[2] .' Exp - 2 Quests</div>';
}

#Calculates what level you are given your experience
#Level 1 = 40
#Level N+1 = 1.6 * N

function calc_level($experience){
    $level = 500;
    $modifier = 1.6;
    $count = 0;
    while($experience - $level > 0){
        $experience = $experience - $level;
        $level = $level * $modifier;
        $count = $count + 1;
    }
    return array($count,$experience,$level);
}

#Gives menu item in settings
function rpg_menu() {
    add_options_page( 'RPG Settings', 'Wordpress RPG', 'manage_options', 'wordpress-rpg', 'rpg_options' );
}

#Forms in settings menu item
function rpg_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    echo '<div class="wrap">';
    echo '<p>Here is where the form would go if I actually had options.</p>';
    echo '</div>';
}

#Adds experience upon posting
function add_experience() {
    $file = WP_PLUGIN_DIR."/Wordpress-RPG/experience.rpg"; 
    $experience = file_get_contents ( $file );
    $exp = strlen($_POST['content']) + $experience;
    file_put_contents ( $file ,$exp );
}

add_action('admin_bar_menu', 'admin_info_header');
add_action('admin_menu', 'rpg_menu' );
add_action('publish_post', 'add_experience' ); 

?>