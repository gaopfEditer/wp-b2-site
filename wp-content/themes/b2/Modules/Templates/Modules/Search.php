<?php namespace B2\Modules\Templates\Modules;

class Search{
    public function init($data,$i){

        $cats = $data['search_cat'];

        $options = '';

        if(!empty($cats)){
            $options = '<ul v-show="show" v-cloak class="b2-radius">';
            $options .= '<li @click="picked(\'\',\''.__('全部','b2').'\',\''.B2_HOME_URI.'\')" :class="category == \''.__('全部','b2').'\' ? \'picked b2-radius\' : \' b2-radius\'">'.__('全部','b2').'</li>';
            foreach ($cats as $k => $v) {

                $term = get_term_by('id',$v, 'category');

                $options .= '<li @click="picked(\''.$v.'\',\''.$term->name.'\',\''.esc_url(get_term_link($term->slug, 'category')).'\')" :class="category == \''.$term->name.'\' ? \'picked\' : \'\'">'.$term->name.'</li>';
            }
            $options .= '</ul>';
        }

        $key = self::str_to_array($data['search_key']);

        $_key = '';

        $data['search_color'] = isset($data['search_color']) && $data['search_color'] ? $data['search_color'] : '#121212';

        if($data['search_key']){
            $_key .= '<ul>';
            $_key .= '<li style="color:'.$data['search_color'].'">'.__('热门搜索：','b2').'</li>';
            foreach ($key as $k => $v) {
                $v = trim($v, " \t\n\r\0\x0B\xC2\xA0");
                $_key .= '<li class="search-key"><a href="'.B2_HOME_URI.'/?s='.$v.'" target="_blank" style="color:'.$data['search_color'].'">'.$v.'</a></li>';
            }
            $_key .= '</ul>';
        }

        return '
            <div class="search-module search-'.$i.'" data-i="'.$i.'" id="search-module-'.$i.'">
                <div class="search-module-title" style="color:'.$data['search_color'].'">'.$data['search_title'].'</div>
                <div class="search-module-desc" style="color:'.$data['search_color'].'">'.$data['search_desc'].'</div>
                <div class="search-module-box">
                    <form method="get" class="search-module-form" :action="link" autocomplete="off">
                        '.$options.'
                        <div>
                            '.($options ? '<div class="picked-category b2-radius" @click.stop="show = true">
                            <span v-text="category"></span>
                        </div>' : '').'
                            <input type="text" :name="category == \''.__('全部','b2').'\' ? \'s\' : \'archiveSearch\'" v-model="keyword" class="search-module-input" placeholder="'.$data['search_input_desc'].'"/>
                            <input type="hidden" name="type" value="post" v-if="category == \''.__('全部','b2').'\'"/>
                        </div>
                        <button class="search-button-action">'.b2_get_icon('b2-search-line').'</button>
                    </form>
                </div>
                '.($_key ? '<div class="search-module-key">
                        '.$_key.'
                    </div>' : '').'
            </div>
        ';
    }

    public function str_to_array($str){
        $str = trim($str, " \t\n\r\0\x0B\xC2\xA0");
        $str = explode(PHP_EOL, $str );
        return $str;
    }
}