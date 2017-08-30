<?php 
    /**
     * Class to save content from array returned from rss-fetch class
     */
     
     class save_content {
        private $rssopt;
        private $thumbnail_id;
        public function __construct(){
            if(!function_exists('file_get_html'))
                include_once (plugin_dir_path(__FILE__) . 'inc/simple_html_dom.php');
            //include '/inc/simple_html_dom.php';
            $this->rssopt = get_option('_auto_rssopts');
        }
        
        public function load_content($contenturl){
                $link = $contenturl;
                $curl = curl_init($link);
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                $raw_html = curl_exec($curl);
                curl_close($curl);
                $raw_dom = str_get_html($raw_html);
                $category_selector = $this->rssopt['category'];
                $post_content = $this->rssopt['post_content'];
                $title = $this->rssopt['title'];
                $content = array();
                foreach($raw_dom->find($category_selector) as $category) {
                    $category_title = $category->plaintext;
                    $term = get_term_by('name', $category_title, 'category');
                    $term_id = 1;
                    if($term === false) {
                        $termArr = wp_insert_term($category_title, 'category');
                        $term_id = $termArr['term_id'];
                    }
                    else {
                        $term_id = $term->term_id;
                    }
                    $content['cat_id'][] = $term_id;
                }
                $contentTag = $raw_dom->find($post_content,0);
                $contentText = trim($contentTag->innertext);
                $imgTags = array();
                preg_match_all('/<img[^>]+src="([^">]+)"/', $contentText, $imgTags);
                $contentText = preg_replace('/<script\b[^>]*>([\s\S]*?)<\/script>/', '', $contentText);
                foreach($imgTags[1] as $imgSrc) {
                    $contentText = str_replace($imgSrc, $this->replace($imgSrc, false),$contentText);
                }
                $content['post_content'] = $contentText;
                $content['title'] = $raw_dom->find($title,0)->plaintext;
            return $content;
        }
        public function import_content(array $contenturls){
            if(empty($contenturls)){
                return false;
            }
            foreach($contenturls as $key => $contenturl){
                if(preg_match('/\/(go|category|author|tag)\//',$contenturl) === 1)
                    continue;
                $contentData = $this->load_content($contenturl);
                $post = array(
                    'post_content' => $contentData['post_content'],
                    'post_title' => $contentData['title'],
                    'post_status' => $this->rssopt['status'],
                    'post_type' => 'post',
                    'post_category' => $contentData['cat_id'],
                    //'post_date' => $pubdate,
                    //'post_date_gmt' => $pubdate                    
                );
                if($post_id = wp_insert_post($post)){
                    set_post_thumbnail($post_id,$this->thumbnail_id);
                    add_post_meta($post_id,'_post_src_url',$contenturl,true);
                    $check[$key] = true;
                    $this->thumbnail_id = null;
                }
                else{
                    $check[$key] = false;
                }
            }
            return $check;                
        }
        private function convertDate($datestr = ""){
            $date = DateTime::createFromFormat('n/j/Y g:i:s A',$datestr);
            $newdatestr = $date->format('Y-m-d H:i:s');
            return $newdatestr;       
        }
        private function replace($src,$first = false){
            $url = trim($src);
            $tmp = download_url($url);
            $file_array = array(
                'name' => basename($url),
                'tmp_name' => $tmp
            );
            if(is_wp_error($tmp)){
                @unlink($tmp);
                return $tmp;
            }
            $id = media_handle_sideload($file_array,0);
            if(is_wp_error($id)){
                @unlink($tmp);
                return $id;
            }
            if($first)
                $this->thumbnail_id = $id;
            $new_src = wp_get_attachment_url($id);
            return $new_src;                
        }
        private function get_cat_id($cat_name){
            $categories = get_categories();
            foreach($categories as $category){
                if($cat_name == $category->cat_name){
                    $cat_id = $category->cat_ID;
                    break;
                }
            }
            return intval($cat_id);
        }
     }
    

?>
