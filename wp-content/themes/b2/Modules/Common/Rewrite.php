<?php namespace B2\Modules\Common;

class Rewrite{
    
    public function init(){
        if(b2_get_option('normal_main','remove_category_tag')){
            add_filter('request',array($this,'remove_category'));
            add_filter('pre_term_link',array($this,'pre_term_link'),10,2);
        }
    }

    public function remove_category($query_vars){
        if(!isset($_GET['page_id']) && !isset($_GET['pagename']) && !empty($query_vars['pagename'])){
            
            $pagename	= $query_vars['pagename'];
            if(strpos($pagename,'/') !== false){
                $pagename = explode('/',$pagename);
                $pagename = end($pagename);
            }
            $categories	= get_categories(['hide_empty'=>false]);
            $categories	= wp_list_pluck($categories, 'slug');
    
            if(in_array($pagename, $categories)){
                $query_vars['category_name'] = $pagename;
                unset($query_vars['pagename']);
            }
        }

        if(!isset($_GET['page_id']) && !isset($_GET['name']) && !empty($query_vars['name'])){
            $pagename	= $query_vars['name'];
            if(strpos($pagename,'/') !== false){
                $pagename = explode('/',$pagename);
                $pagename = end($pagename);
            }
            $categories	= get_categories(['hide_empty'=>false]);
            $categories	= wp_list_pluck($categories, 'slug');
    
            if(in_array($pagename, $categories)){
                $query_vars['category_name'] = $pagename;
                unset($query_vars['name']);
            }
        }

        return $query_vars;
    }

    public function pre_term_link($term_link, $term){

        if($term->taxonomy === 'category'){
            return '%category%';
        }
    
        return $term_link;
    }

    public static function get_custom_page_link($type){
        $permalink_structure = get_option('permalink_structure');
        $pages = b2_custom_page_arg();

        if(isset($pages[$type])){
            $page = $pages[$type];
            if($permalink_structure){
                return B2_HOME_URI.'/'.$page['key'];
            }else{
                return B2_HOME_URI.'?b2_page='.$page['key'];
            }
        }
    }
}