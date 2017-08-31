<?php
	/**
	*	This class will fetch the RSS link to take everything in that
	*	include title, date, content, feature image(maybe), video(hope i can do that)
	*	convert is to a array and pass it to another class to save it in database
	*	version: 1.0
	*/

	class rss_fetch {
		private $rss;
		public function __construct(){

		}
		public function rss_convert(){
            $link_text = get_option('_auto_links');
            $links = explode(",", $link_text);
            return $links;
		}
		public function get_data() {
		  $c = 0;
      $links = $this->rss_convert();
      $saved_link = array();
		  foreach($links as $link){
				if($this->url_exists($link)){
          $layer_links = $this->get_internal_links($link);
					echo var_dump(count($layer_links));
          while(count($layer_links) > 0 && count($saved_link) <= 500) {
              $inner_link = array_shift($layer_links);
              $saved_link[] = $inner_link;
              $sub_links = $this->get_internal_links($inner_link);
              $sub_links = array_diff($sub_links, $saved_link);
							foreach($sub_links as $sub_link) {
								if(!in_array($sub_link, $layer_links))
									$layer_links[] = $sub_link;
							}
              //$layer_links = array_merge($layer_links, $sub_links);
          }
       	}
      }
					//echo var_dump(count($saved_link));
          $saved_link = array_unique($saved_link);
					echo var_dump($saved_link);
          //$contenturls = $this->check_urls($contenturls);
          return $saved_link;
        }

        protected function get_internal_links($url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $matches = [];
						$urlMatches = [];
						preg_match('/(https?:\/\/(www\.)?[^\/\s]+\/)(.*)/',$url,$urlMatches);
						$url = $urlMatches[1];
            $url_pattern = str_replace(['/','.'],['\/','\.'], $url);
            preg_match_all('/<a[^>]*href="(('.$url_pattern.')?(?!http)\S*)"[^>]*>[^<]*<\/a>/', $response, $matches);
            $internal_links = $matches[1];
						echo var_dump($internal_links);
            return $internal_links;
        }
        private function check_urls($urlarray){
            $meta_query = new WP_Query(array('meta_key' => '_post_src_url','posts_per_page' => -1));
            if($meta_query->have_posts()) : while($meta_query->have_posts()) : $meta_query->the_post();
                $src_urls[] = get_post_meta(get_the_ID(),'_post_src_url',true);
            endwhile;endif;
            wp_reset_query();
            if(empty($src_urls))
                return $urlarray;
            foreach($urlarray as $key => $url){
                $link = $url['link'];
                if(in_array($link,$src_urls)){
                    unset($urlarray[$key]);
                }
            }
            return $urlarray;
        }
        private function url_exists($link){
            $link_header = @get_headers($link);
            if($link_header[0] == 'HTTP/1.1 404 Not Found') {
                return false;
            }
            else {
                return true;
            }

        }
	}

    //$a = new rss_fetch();
    //echo var_dump($a->get_data());

?>
