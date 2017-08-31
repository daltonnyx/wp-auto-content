<?php
    /**
     * Setting metabox for post
     * Create option page
     *
     */
     class auto_content_option {
        private $auto_rss;
        private $auto_opt;
        /**
         * Hook in construct function
         */
        public function __construct(){
            add_action('admin_menu',array($this,'create_page'));
            add_action('admin_init',array($this,'option_page_init'));
        }
        /**
         * function with above hook contain add_post_page function
         */
        public function create_page(){
            $page_name = add_posts_page(
                'Auto Get Content',
                'Auto Get','manage_options',
                'sosi_auto_content',
                array($this,'create_page_cb')
            );
        }
        /**
         * add_posts_page callback
         */
        public function create_page_cb() {
            $this->auto_opt = get_option('_auto_rssopts');
            $this->auto_rss = get_option('_auto_links');
            $disabled_button = '';
?>
            <div class="wrap">
                <h2>Auto Content Setting</h2>
                <?php settings_errors(); ?>
                <form action="options.php" method="POST">
                    <?php
                        settings_fields('_sosi_autocontent');
                        do_settings_sections('sosi_auto_content');
                        submit_button('Save Changes');
                        if(empty($this->auto_opt) && empty($this->auto_rss))
                            $disabled_button = 'disabled';
                        echo '<p>Please Save changes before update!</p>';
                        echo '<p><input type="button" class="button" name="_update_button" id="_update_button" value="Update" '. $disabled_button .' onClick="update_my_post()"/>';
                        echo '<img id="image-loading" src="' . plugins_url('inc/loaderA64.gif',__FILE__) . '" style="width:20px;height:auto;display:none;" /></p>';
                    ?>
                </form>
            </div>
<?php
        }
        public function option_page_init() {
            register_setting('_sosi_autocontent','_auto_links',array($this,'_sanitize_links'));
            register_setting('_sosi_autocontent','_auto_rssopts',array($this,'_sanitize_rssopts'));
            add_settings_section('auto_content_section','Auto Get Content',array($this,'section_render'),'sosi_auto_content');
            add_settings_field('_sosi_link','Links to fetch (seperate by comma; Ex: example1.com,example2.com):',array($this,'_auto_link_box_render'),'sosi_auto_content','auto_content_section');
            add_settings_field('_auto_status','Post status :',array($this,'_auto_status_render'),'sosi_auto_content','auto_content_section');
            add_settings_field('_auto_selector', 'Selector (seperate by comma as same order as link above)', array($this, null), 'sosi_auto_content','auto_content_section');
            add_settings_field('_auto_category','Category selector:',array($this,'_auto_category_render'),'sosi_auto_content','auto_content_section');
            add_settings_field('_auto_post_content','Post Content selector:',array($this,'_auto_post_content_render'),'sosi_auto_content','auto_content_section');
            add_settings_field('_auto_title','Title selector:',array($this,'_auto_title_render'),'sosi_auto_content','auto_content_section');
            add_settings_field('_auto_publish_date','Publish Date selector:',array($this,'_auto_publish_date_render'),'sosi_auto_content','auto_content_section');
        }

        public function section_render(){
            echo '<p>Set options for Auto get content</p>';
        }
        public function _auto_link_box_render(){
            $rsslinks = $this->auto_rss;
            echo '<textarea name="_auto_links" type="text" class="widefat">'.$rsslinks.'</textarea>';
        }
        public function _auto_status_render(){
            $value = $this->auto_opt;
            $item_array = array('publish','draft','pending','future');
            echo '<select id="_auto_status" name="_auto_rssopts[status]">';
            foreach($item_array as $item){
                $selected = '';
                if($item == $value['status'])
                    $selected = 'selected';
                printf('<option value="%1$s" %2$s>%1$s</option>',$item,$selected);
            }
            echo '</select>';
        }
        public function _auto_category_render(){
            $value = $this->auto_opt;
            echo '<input id="_auto_category" name="_auto_rssopts[category]" class="widefat" value="'.$value['category'].'" />';
        }
        public function _auto_post_content_render(){
            $value = $this->auto_opt;
            echo '<input id="_auto_post_content" name="_auto_rssopts[post_content]" class="widefat" value="'.$value['post_content'].'" />';
        }
        public function _auto_title_render(){
            $value = $this->auto_opt;
            echo '<input id="_auto_title" name="_auto_rssopts[title]" class="widefat" value="'.$value['title'].'" />';
        }
        public function _auto_publish_date_render(){
            $value = $this->auto_opt;
            echo '<input id="_auto_publish_date" name="_auto_rssopts[publish_date]" class="widefat" value="'.$value['publish_date'].'" />';
        }
        public function _sanitize_links($input){
            return $input;
        }
        public function _sanitize_rssopts($input){
            if(isset($input['status']))
                $input['status'] = sanitize_text_field($input['status']);
            if(isset($input['category']))
                $input['category'] = sanitize_text_field($input['category']);
            return $input;
        }
     }

?>
