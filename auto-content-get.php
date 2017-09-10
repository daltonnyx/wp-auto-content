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

        public function load_content($contenturl, $selectors = null){
          try {
                $link = trim($contenturl);
                $curl = curl_init($link);
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                $raw_html = curl_exec($curl);
                $error = curl_error($curl);
                curl_close($curl);
                $raw_dom = str_get_html($raw_html);
                if($raw_dom === false) {
                  echo "failed to get data from $contenturl \r\n";
                  echo $error;
                  echo "\r\n";
                  return null;
                }
                $category_selector = $selectors['category'];
                $post_content = $selectors['content'];
                $title = $selectors['title'];
                $content = array();
                if(!empty($category_selector)) {
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
                }
                $contentTags = $raw_dom->find($post_content);
                if($contentTags == null) {
                  echo "failed to get content\r\n";
                  echo var_dump($post_content)."\r\n";
                  return null;
                }
                $contentText = '';
                foreach ($contentTags as $contentTag) {
                  $contentText .= trim($contentTag->innertext);
                }
                $imgTags = array();
                preg_match_all('/<img[^>]+(src|srcset)="([^">]+)"/', $contentText, $imgTags);
                $contentText = preg_replace('/<(script|iframe)\b[^>]*>([\s\S]*?)<\/(script|iframe)>/', '', $contentText);
                foreach($imgTags[2] as $imgTag) {
                  $imgSrcs = array();
                  echo var_dump($imgTag);
                  preg_match_all('/\S*\.(jpg|JPG|jpeg|JPEG|tiff|TIFF|gif|GIF|png|PNG)/',$imgTag, $imgSrcs);
                  echo var_dump($imgSrcs[0]);
                  foreach($imgSrcs[0] as $imgSrc) {
                      $contentText = str_replace($imgSrc, $this->replace($imgSrc, false),$contentText);
                  }
                }
                $content['post_content'] = $contentText;
                $content_title = $raw_dom->find($title,0);
                if($content_title == null) {
                  echo "failed to get title";
                  return null;
                }
                $content['title'] = $content_title->plaintext;
            return $content;
          }
          catch(Exception $e) {
            return null;
          }
        }
        public function import_content(array $contenturls, $post_id = null){
            if(empty($contenturls)){
                return false;
            }
            $selectors = array(
            'category' => get_post_meta($post_id, '_vnb_category',true),
            'content' => get_post_meta($post_id, '_vnb_content',true),
            'title' => get_post_meta($post_id, '_vnb_title',true),
          );
            foreach($contenturls as $key => $contenturl){
              try {
                // if(preg_match('/\/(category|author|tag)\//',$contenturl) === 1)
                //     continue;
                $contentData = $this->load_content($contenturl, $selectors);
                if($contentData == null) {
                  //echo var_dump($contenturl);
                  continue;
                }
                $post = array(
                    'post_content' => $contentData['post_content'],
                    'post_title' => $contentData['title'],
                    'post_status' => 'draft',
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
              catch(Exception $e) {
                echo $contenturl;
                continue;
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
                return '';
            }
            $id = media_handle_sideload($file_array,0);
            if(is_wp_error($id)){
                @unlink($tmp);
                return '';
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
