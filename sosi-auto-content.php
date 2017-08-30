<?php 
	/**
	*	Plugin Name: Auto get content
	*	Description: Tu dong lay du lieu
	*	Author: Dalton Nyx
	*	Version: 1.0
	*/
    
    include plugin_dir_path(__FILE__) . '/auto-content-rss.php';
    include plugin_dir_path(__FILE__) . '/auto-content-get.php';
    include plugin_dir_path(__FILE__) . '/auto-content-option.php';
    $option_page = new auto_content_option();
    function get_data(){
            register_meta('post','_post_src_url','sanitize_cb');
            $rss = new rss_fetch();
            $contentLink = $rss->get_data();
            $importposts = new save_content();
            $importposts->import_content($contentLink);
    }
    function update_button(){
        if($_POST['update'] == true){
            get_data();
            $_POST['update'] == '';
        }
    }
    function auto_update(){
        $rss_opts = get_option('_auto_rssopts');        
        if($rss_opts['autoget'] == 'on')
            get_data();
    }
    function sanitize_cb($value){
        $value = sanitize_text_field($value);
        return $value;        
    }
    function register_scripts($hook){
        wp_enqueue_script('auto-update-content',plugins_url('inc/update.content.js',__FILE__),array('jquery'),'',true);
    }
    add_action('admin_enqueue_scripts','register_scripts');
    add_action('load-posts_page_sosi_auto_content','update_button');
    add_action('load-edit.php','auto_update');
?>
