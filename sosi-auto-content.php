<?php
	/**
	*	Plugin Name: Auto get content
	*	Description: Tu dong lay du lieu
	*	Author: Dalton Nyx
	*	Version: 1.0
	*/
	if(!defined('AUTO_CONTENT_PATH'))
		define( 'AUTO_CONTENT_PATH', plugin_dir_path( __FILE__ ) );
  include AUTO_CONTENT_PATH . '/auto-content-rss.php';
  include AUTO_CONTENT_PATH . '/auto-content-get.php';
  include AUTO_CONTENT_PATH . '/auto-content-option.php';
  include AUTO_CONTENT_PATH . '/libs/vinabits-extra-type.php';
  include AUTO_CONTENT_PATH . '/libs/vinabits-extra-tax.php';
  include AUTO_CONTENT_PATH . '/libs/vinabits-extra-box.php';
  $option_page = new auto_content_option();
  function get_data(){
		$links = new WP_Query(array(
			'post_type' => 'get_link',
			'posts_per_page' => -1
		));
		$loader = new save_content();
		if($links->have_posts()) : while($links->have_posts()) :
			$links->the_post();
			$urls = get_post_meta(get_the_ID(), '_vnb_links', true);
			$content_urls = explode("\n",$urls);
			$loader->import_content($content_urls, get_the_ID());
			
		endwhile;endif;
  }
  function update_button(){
      if(isset($_POST['update']) && $_POST['update'] == true){
          get_data();
          $_POST['update'] == '';
					die();
      }
  }

  function register_scripts($hook){
      wp_enqueue_script('auto-update-content',plugins_url('inc/update.content.js',__FILE__),array('jquery'),'',true);
  }

	VinabitsExtraType::RegisterType("get_link", "Link", "Link", [], ['title']);
	VinabitsExtraBox::RegisterMetabox("links", "Links to get", "get_link", 'multiline');
	VinabitsExtraBox::RegisterMetabox("title", "Title Pattern", "get_link");
	VinabitsExtraBox::RegisterMetabox("category", "Category Pattern", "get_link");
	VinabitsExtraBox::RegisterMetabox("content", "Content Pattern", "get_link");

  add_action('admin_enqueue_scripts','register_scripts');
  add_action('load-get_link_page_sosi_auto_content','update_button');
  //add_action('load-edit.php','auto_update');
?>
