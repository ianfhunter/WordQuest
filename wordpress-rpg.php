<?php
/*
Plugin Name: Wordpress RPG
Plugin URI: 
Description: Incentivize your blogging with RPG Meta-elements
Author: Ian Hunter
Version: 0.0
Author URI: www.ianhunter.ie
*/


#Returns a user's active quest
function get_quest(){
    $file = WP_PLUGIN_DIR."/Wordpress-RPG/experience" . get_current_user_id() . ".rpg"; 
    $json = file_get_contents ( $file );
    if ($json == ""){
        $quest = "None";
    }else{
        $jsonD = json_decode($json);
        $quest = $jsonD->{"quest"};
    }
    return $quest;
}


#The info display in the top right hand corner.
function admin_info_header() {
    $file = WP_PLUGIN_DIR."/Wordpress-RPG/experience".get_current_user_id().".rpg"; 
    $json = file_get_contents ( $file );
    $jsonD = json_decode($json);
    $experience = $jsonD->{"total_experience"};
    $stats = calc_level($experience);
    $quest = $jsonD->{"quest"};

    echo '<div style="float:right">⚔ Level ' . $stats[0] . ' Templar - ' . $stats[1] . '/' . $stats[2] .' Exp - Quest: '. $quest.'</div>';
}

#Calculates what level you are given your experience
#Level 1 = 40
#Level N+1 = 1.6 * N
#Returns: Level, Remaining Exp, Next Level EXP
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

#Adds experience upon posting, based on character count
function add_experience() {

    #Making sure that experience is only added on newly published items    
    if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' ) ) {
        $file = WP_PLUGIN_DIR."/Wordpress-RPG/experience" . get_current_user_id() . ".rpg"; 
        $json = file_get_contents ( $file );
        if ($json == ""){
            $experience = 0;
            $quest = "None";
        }else{
            $jsonD = json_decode($json);
            $experience = $jsonD->{"total_experience"};
            $quest = $jsonD->{"quest"};
        }

        #Should check if quest is complete & Optionally assign a new task
        #Quests should be a 'write a post with X tag/category' - Other types?

        $exp = strlen($_POST['content']) + $experience;
        $json = json_encode(array( 
                                   "total_experience" => $exp, 
                                   "quest" => $quest
                                  ));
        file_put_contents ( $file ,$json );
    }
}

#Activates a quest
function add_quest($quest_title){
$file = WP_PLUGIN_DIR."/Wordpress-RPG/experience" . get_current_user_id() . ".rpg"; 
    $json = file_get_contents ( $file );
    if ($json == ""){
        $experience = 0;
    }else{
        $jsonD = json_decode($json);
        $experience = $jsonD->{"total_experience"};
    }

    $json = json_encode(array( 
                               "total_experience" => $experience, 
                               "quest" => $quest_title
                              ));
    file_put_contents ( $file ,$json );
}

function quest_metabox(){
    add_meta_box("rpg-metabox", "Wordpress RPG", draw_metabox, 'post', 'side', 'high');
}

function idle_messages(){
    $file = WP_PLUGIN_DIR."/Wordpress-RPG/idle.rpg"; 
    $contents = explode("\n",file_get_contents($file));
    return $contents;
}

function draw_metabox(){
    #Would be really nice to rotate through some phrases here.
    #And also have avatars based on your character
    #Credit: http://leon-murayami.deviantart.com/art/Illusion-of-Gaia-Will-XP-402827050

    echo " <img src='".plugins_url()."/Wordpress-RPG/hero.gif' /> <div id='idle_msg'>" . "Killing some slimes... " . "</div>" . "Current Quest: " . get_quest();
    echo '

    ';
    $params = array(
      'messages' => idle_messages(),
    );
    wp_register_script('rotation_script',plugins_url().'/Wordpress-RPG/idle_messages.js');
    wp_localize_script('rotation_script', 'object_name', $params );
    wp_enqueue_script( 'rotation_script' );
    #maybe echo active quest.
    #echo get_quest()

}


add_action('admin_bar_menu', 'admin_info_header');
#add_action('admin_menu', 'rpg_menu' );
add_action('publish_post', 'add_experience' ); 

add_action('add_meta_boxes', 'quest_metabox' ); 
add_action('save_post', 'save_quest_draft' ); 

?>