<?php namespace B2\Modules\Common;

use B2\Modules\Templates\Modules\Sliders;
use B2\Modules\Common\Coupon;
use B2\Modules\Common\Post;
use B2\Modules\Common\Circle;

class Shortcode{

    static $restApi = false;

    public function init(){
        if(!is_admin()){
            //文件下载
            add_shortcode( 'zrz_file', array(__CLASS__,'file_down'));
            add_shortcode( 'b2_file', array(__CLASS__,'file_down'));

            add_shortcode( 'zrz_file_mp', array(__CLASS__,'file_down_mp'));
            add_shortcode( 'b2_file_mp', array(__CLASS__,'file_down_mp'));

            //隐藏内容
            add_shortcode('content_hide',array(__CLASS__,'content_hide'));
            add_shortcode('b2_content_hide',array(__CLASS__,'b2_content_hide'));

            //插入文章
            add_shortcode('zrz_insert_post',array(__CLASS__,'insert_post'));
            add_shortcode('b2_insert_post',array(__CLASS__,'insert_post'));

            add_shortcode('zrz_insert_post_mp',array(__CLASS__,'insert_post_mp'));
            add_shortcode('b2_insert_post_mp',array(__CLASS__,'insert_post_mp'));

            //邀请短代码
            add_shortcode( 'zrz_inv', array(__CLASS__,'invitation_list'));
            add_shortcode( 'b2_inv', array(__CLASS__,'invitation_list'));

            //优惠劵短代码
            add_shortcode( 'b2_coupon', array(__CLASS__,'coupon'));

            //自定义支付
            add_shortcode( 'b2_custom_pay', array(__CLASS__,'custom_pay'));
            add_shortcode( 'b2_custom_pay_resout', array(__CLASS__,'custom_pay_resout'));
        }

        self::$restApi = strpos($_SERVER[ 'REQUEST_URI' ], '/b2/v1/') === false ? false : true;

    }

    public static function custom_pay_resout($atts){
        $atts = apply_filters( 'b2_custom_pay_resout_atts', $atts );
        if(!isset($atts['id'])) return;

        $atts['id'] = (int)$atts['id'];

        $post_id = 0;

        global $post;
        if(isset($post->ID)){
            $post_id = $post->ID;
        }

        return '<div class="cpay-resout-list" ref="cpayresout" data-id="'.$atts['id'].'">123123</div>';
    }

    public static function custom_pay($atts){

        $atts = apply_filters( 'b2_custom_pay_atts', $atts );

        if(!isset($atts['id'])) return;

        $atts['id'] = (int)$atts['id'];

        $title = get_the_title($atts['id']);

        $title_html = '<div class="cpay-title">'.$title.'</div>';

        $post_id = 0;

        global $post;
        if(isset($post->ID)){
            $post_id = $post->ID;
            if($post->ID == $atts['id']){
                $title_html = '<h1 class="cpay-title">'.$title.'</h1>';
            }
        }

        $_title = apply_filters( 'b2_cpay_title_'.$atts['id'],$title_html ,$title );

        $price = get_post_meta($atts['id'],'b2_single_pay_money',true);
        $price = trim($price, " \t\n\r\0\x0B\xC2\xA0");

        $price_html = '<div class="cpay-price cpay-form-row">';

        if($price){
            // edited by fuzqing
            $info = b2_is_enable_related_pay_money($atts['id']);
            if ($info['related']) {
                $price_html .= '<div class="cpay-text-title cpay-form-title">'.__('金额','b2').'<b class="red">*</b></div>';
                $price_html .= '<div class="cpay-money-input"><span>'.B2_MONEY_SYMBOL.'</span><input type="number" autocomplete="off" readonly="readonly" step="0.01" name="price" v-model="price" id="cpay-money" /><span>'.__('元','b2').'</span></div>';
            } else {
                $price_html .= '<div class="cpay-text-title cpay-form-title">'.__('请选择金额','b2').'</div><ul>';
                $price = explode(PHP_EOL, $price );
                $price = array_map(function ($p){
                    return trim($p);
                },$price);
                foreach ($price as $k => $v) {
                    $price_html .= '<li '.($k == 0 ? 'ref="pickprice" data-price="'.$v.'"' : '').'><label :class="[\'button empty\',{\'picked\':price == '.$v.'}]">'.B2_MONEY_SYMBOL.$v.'<input type="radio" name="price" value="'.$v.'" v-model="price"/></label></li>';
                }
                $price_html .= '</ul>';
            }
        }else{
            $price_html .= '<div class="cpay-text-title cpay-form-title">'.__('请输入金额','b2').'<b class="red">*</b></div>';
            $price_html .= '<div class="cpay-money-input"><span>'.B2_MONEY_SYMBOL.'</span><input type="number" autocomplete="off" step="0.01" name="price" v-model="price" id="cpay-money" /><span>'.__('元','b2').'</span></div>';
        }

        $price_html .= '</div>';

        $price_html = apply_filters('b2_cpay_price_'.$atts['id'], $price_html, $price);

        $button = get_post_meta($atts['id'],'b2_pay_button',true);

        $html = get_post_meta($atts['id'],'b2_pay_custom_html',true);

        $pay_group = get_post_meta($atts['id'],'b2_pay_custom_group',true);

        if(!empty($pay_group)){
            $form = '';

            foreach($pay_group as $k=>$v){
                // edited by fuzqing
                if (!isset($v['key'],$v['name'])) {
                    continue;
                }


                if($v['type'] === 'radio' || $v['type'] === 'checkbox' || $v['type'] === 'select'){
                    $str = trim($v['value'], " \t\n\r\0\x0B\xC2\xA0");

                    $arr = array();

                    if(!empty($str)){
                        $str = explode(PHP_EOL, $str );

                        foreach($str as $_k=>$_v){
                            $__k = explode('=',$_v);

                            $arr[] = array(
                                'k'=>isset($__k[0]) ? $__k[0] : 'none',
                                'v'=>isset($__k[1]) ? $__k[1] : __('请正确填写待选值','b2'),
                            );

                        }

                    }else{
                        $arr[] = array(
                            'k'=>'none',
                            'v'=>__('请填写设置项','b2')
                        );
                    }

                    $pay_group[$k]['value_arg'] =  $arr;
                }

                $field_type = 'cpay_form_'.$v['type'];

                if(!isset($pay_group[$k]['required'])){
                    $pay_group[$k]['required'] = 0;
                }

                $pay_group[$k]['desc'] = isset( $pay_group[$k]['desc']) ? $pay_group[$k]['desc'] : '';

                $form .= '<div class="cpay-form-row">
                    '.self::$field_type($pay_group[$k],$atts['id']).'
                </div>';

            }

        }

        $button = get_post_meta($atts['id'],'b2_pay_button',true);
        $button = $button ? $button : __('支付','b2');
        $button = '<div class="cpay-submit"><button @click="submit(\''.$title.'\','.$atts['id'].','.$post_id.')">'.$button.'</button></div>';

        $custom_html = get_post_meta($atts['id'],'b2_pay_custom_html',true);
        $custom_html = self::cpay_custom_html($custom_html);


        $form_name = get_post_meta($atts['id'],'b2_pay_form_name',true);
        $form_name = $form_name ? $form_name : __('表单','b2');

        $res_name = get_post_meta($atts['id'],'b2_pay_res_name',true);
        $res_name = $res_name ? $res_name : __('结果','b2');

        $show_res_list = apply_filters('b2_cpay_tabs_'.$atts['id'],
        '<div class="cpay-tab" v-if="allow != 0" v-cloak>
            <div :class="[\'cpay-tab-item\',{\'picked\':tab == \'form\'}]" @click="tab = \'form\'"><span>'.$form_name.'</span></div>
            <div :class="[\'cpay-tab-item\',{\'picked\':tab == \'list\'}]" @click="tab = \'list\'"><span>'.$res_name.'</span></div>
            </div>'
        ,$atts['id']);


        $list = self::cpay_resout_list($atts['id']);
        $list = apply_filters( 'b2_cpay_list_'.$atts['id'],$list ,$atts['id']);

        // edited by fuzqing

        return '<div class="custom-pay-box b2-radius" id="cpay-'.$atts['id'].'" data-id="'.$atts['id'].'">
            '.$_title.'
            '.$show_res_list.'
            <div v-if="active_time.active" v-cloak>
                <form class="cpay-form" ref="form" @submit.prevent v-show="tab == \'form\'">
                '.$form.'
                '.$price_html.'
                '.$custom_html.'
                '.$button.'
                </form>
            </div>    
            <div class="cpay-active-time-tips" v-else v-cloak>
                {{ active_time.tips }}
                '.$custom_html.'
            </div>
            <div class="cpay-resout-list" ref="cpayresout" data-id="'.$atts['id'].'" v-show="tab == \'list\'" v-cloak>
                <div class="cpay-resout-list-in" v-if="list.data.length > 0">'.$list.'</div>
                <div class="cpay-resout-list-in" v-else>'.B2_EMPTY.'</div>
                <pagenav-new class="cpay-resout-list-nav" ref="reslist" navtype="post" :pages="list.pages" type="p" :box="selecter" :opt="list" :api="api" :rote="false" @return="getList"></pagenav-new>
            </div>
        </div>';
    }

    public static function cpay_resout_list($id){
        return '
            <div v-for="(item,i) in list.data" :key="i">
                <div class="cpay-list-header">
                    <div class="cpay-list-user">
                        '.b2_get_img(array(
                            'src_data'=>':src="item.user.avatar"',
                            'class'=>array('avatar','b2-radius'),
                            'pic_data'=>' v-if="item.user.avatar"',
                            'source_data'=>':srcset="item.user.avatar_webp"'
                        )).'
                        <div class="w-a-name">
                            <a :href="item.user.link" class="link-block"></a> 
                            <p>
                                <span v-text="item.user.name"></span>
                                <span class="uverify" v-if="item.user.title">已认证</span>
                            </p> 
                            <div class="w-a-lv">
                                <span :class="[\'lv-icon\',\'user-lv\',\'b2-\'+item.user.lv.lv.lv]">
                                    <b v-text="item.user.lv.lv.name"></b>
                                    <i v-text="item.user.lv.lv.lv"></i>
                                </span>
                                <span :class="[\'lv-icon\',\'user-vip\',\'b2-\'+item.user.lv.vip.lv]">
                                    <i></i>
                                    <b :style="\'color:\'+item.user.lv.vip.color" v-text="item.user.lv.vip.name"></b>
                                </span>                       
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="cpay-price-r" v-text="\''.B2_MONEY_SYMBOL.'\'+item.price"></span>
                    </div>
                </div>
                <div class="cpay-list-info jt" v-if="item.data">
                    '.self::get_cpay_list_item().'
                </div>
            </div>
        ';
    }

    public static function get_cpay_list_item(){
        return '
            <div class="cpay-list-item" v-for="(row,index) in item.data" :key="index" v-if="row.value">
                <div class="cpay-list-item-name" v-text="row.name"></div>
                <div class="cpay-list-item-box" v-if="row.type != \'file\'">
                    <div v-text="row.value" v-if="row.type != \'checkbox\'"></div>
                    <div v-else class="cpay-list-checkbox">
                        <span v-for="(value,_i) in row.value" v-text="value"></span>
                    </div>
                </div>
                <div class="cpay-list-item-box" v-else>
                    <div v-for="(f,fi) in row.value" :key="fi" class="cpay-list-item-file b2-radius">
                        <a :href="f.url" target="_blank" class="link-block"></a>
                        <div class="cpay-list-thumb">
                            <div v-if="f.type == \'image\'" class="cpay-list-file-thumb">
                                <img :src="f.thumb" />
                            </div>
                            <div v-else class="cpay-list-file-ext">
                                <span v-text="f.ext"></span>
                            </div>
                        </div>
                        <div class="cpay-list-filename" v-text="f.name"></div>
                        <div class="cpay-list-size" v-text="f.size"></div>
                    </div>
                </div>
            </div>
        ';
    }

    public static function cpay_custom_html($str){
        if (strpos($str, '<' . '?') !== false) {
            ob_start();
            eval('?' . '>' . $str);
            $text = ob_get_contents();
            ob_end_clean();
        }else{
            $text = $str;
        }

        return '<div class="cpay-html-box cpay-form-row">'.$text.'</div>';
    }

    public static function cpay_required($opt){

        $opt['required'] = isset($opt['required']) ? (int)$opt['required'] : 0;

        if($opt['required']){
            return '<b class="red">*</b>';
        }
        return '';
    }

    public static function cpay_form_file($item,$id){

        $count = $item['file_count'] ? (int)$item['file_count'] : 1;

        $html = '<div class="cpay-file-box cpay-file-box-'.$item['key'].'" ref="'.$item['key'].'required" data-required="'.$item['required'].'">
            <div class="cpay-text-title cpay-form-title">'.$item['name'].self::cpay_required($item).'</div>
            <div class="cpay-file-box-in" v-cloak>
                <label :class="[\'cpay-file-upload-box\',{\'locked\':locked[\''.$item['key'].'\']}]">
                    <input '.($count > 1 ? 'multiple="multiple"' : '').' :disabled="locked[\''.$item['key'].'\'] || count[\''.$item['key'].'\'] >= '.$count.'" name="'.$item['key'].'_file"  type="file" accept="'.$item['file_type'].'" @change="fileChange($event,\''.$count.'\',\''.$item['key'].'\',\''.$id.'\')" class="cpay-file-input"/>
                    <span>'.__('选择文件','b2').($count > 1 ? '<b>'.sprintf(__('最多%s个','b2'),$count).'</b>' : '').'</span>
                </label>
                <input name="'.$item['key'].'" type="text" class="cpay-file-hidden"/>
                <div class="cpay-file-review" v-if="files.hasOwnProperty(\''.$item['key'].'\')">
                    <div v-for="(item,i) in files[\''.$item['key'].'\']" class="b2-radius" v-show="files[\''.$item['key'].'\'].length > 0" v-cloak>
                        <div :class="progress[\''.$item['key'].'\'][i][\'status\'] == \'fail\' ? \'cpay-fail-res\' : \'\'">
                            <b v-if="progress[\''.$item['key'].'\'][i][\'status\'] == \'fail\'" class="cpay-upload-fail">
                                {{progress[\''.$item['key'].'\'][i][\'msg\']}}
                            </b>
                            <b :style="\'width:\'+progress[\''.$item['key'].'\'][i][\'number\']+\'%\'" v-else-if="progress[\''.$item['key'].'\'][i][\'status\'] == \'doing\'" class="cpay-upload-progress">
                               {{progress[\''.$item['key'].'\'][i][\'number\'] != 100 ? progress[\''.$item['key'].'\'][i][\'number\']+\'%\' : \''.__('上传成功，请稍后','b2').'\'}}
                            </b>
                            <span class="cpay-upload-name"><a :href="files[\''.$item['key'].'\'][i][\'url\']" target="_blank"><b>{{item.name}}</b></a><i>{{item.size}}</i></span>
                            <span class="cpay-upload-close" v-if="progress[\''.$item['key'].'\'][i][\'status\'] != \'doing\'" @click="deleteAc(\''.$item['key'].'\',i)">'.b2_get_icon('b2-close-line').'</span>
                        </div>
                    </div>
                </div>
            </div>
            '.($item['desc'] ? '<p class="cpay-desc">'.$item['desc'].'</p>' : '').'
        </div>';

        return apply_filters( 'b2_cpay_form_type_file', $html, $item,$id);
    }

    public static function cpay_form_text($item,$id){
        $html = '<label class="cpay-text-box cpay-text-box-'.$item['key'].'" ref="'.$item['key'].'required" data-required="'.$item['required'].'">
            <div class="cpay-text-title cpay-form-title">'.$item['name'].self::cpay_required($item).'</div>
            <input name="'.$item['key'].'" />
            '.($item['desc'] ? '<p class="cpay-desc">'.$item['desc'].'</p>' : '').'
        </label>';

        return apply_filters( 'b2_cpay_form_type_text', $html, $item,$id);
    }

    public static function cpay_form_textarea($item,$id){

        $html = '<label class="cpay-textarea-box cpay-textarea-box-'.$item['key'].'" ref="'.$item['key'].'required" data-required="'.$item['required'].'">
            <div class="cpay-textarea-title cpay-form-title">'.$item['name'].self::cpay_required($item).'</div>
            <textarea name="'.$item['key'].'" ></textarea>
            '.($item['desc'] ? '<p class="cpay-desc">'.$item['desc'].'</p>' : '').'
        </label>';

        return apply_filters( 'b2_cpay_form_type_textarea', $html, $item,$id);

    }

    public static function cpay_form_radio($item,$id){

        if(empty($item['value_arg'])) return;

        $input = '<div class="cpay-radio-box cpay-radio-box-'.$item['key'].'" ref="'.$item['key'].'required" data-required="'.$item['required'].'">
        <div class="cpay-radio-title cpay-form-title">'.$item['name'].self::cpay_required($item).'</div>
        <div class="cpay-radio-box-list b2-radius">';

        // edited by fuzqing
        $info = b2_is_enable_related_pay_money($id);
        $v_model = '';
        if ($info['related'] === true && $item['key'] === $info['related_field']) {
            $v_model = ' v-model="related_field" ';
        }
        foreach ($item['value_arg'] as $k => $v) {

            $input .= '<label><input type="radio" id="'.$v['k'].'" name="'.$item['key'].'" value="'.$v['k'].'" '.($k == 0 ? 'checked="checked"' : ''). $v_model .'/><span>'.$v['v'].'</span></label>';
        }

        $input .= '</div>'.($item['desc'] ? '<p class="cpay-desc">'.$item['desc'].'</p>' : '').'</div>';

        return apply_filters( 'b2_cpay_form_type_radio', $input, $item,$id);
    }

    public static function cpay_form_checkbox($item,$id){

        if(empty($item['value_arg'])) return;

        $input = '<div class="cpay-checkbox-box cpay-checkbox-box-'.$item['key'].'" ref="'.$item['key'].'required" data-required="'.$item['required'].'">
        <div class="cpay-checkbox-title cpay-form-title">'.$item['name'].self::cpay_required($item).'</div>
        <div class="cpay-checkbox-box-list b2-radius">';

        foreach ($item['value_arg'] as $k => $v) {
            $input .= '<label><input type="checkbox" id="'.$v['k'].'" name="'.$item['key'].'" value="'.$v['k'].'" /><span>'.$v['v'].'</span></label>';
        }

        $input .= '</div> '.($item['desc'] ? '<p class="cpay-desc">'.$item['desc'].'</p>' : '').'</div>';

        return apply_filters( 'b2_cpay_form_type_checkbox', $input, $item,$id);
    }

    public static function cpay_form_select($item,$id){

        if(empty($item['value_arg'])) return;

        // edited by fuzqing
        $info = b2_is_enable_related_pay_money($id);
        $v_model = '';
        if ($info['related'] === true && $item['key'] === $info['related_field']) {
            $v_model = ' v-model="related_field" ';
        }
        $select = '<div class="cpay-select-box cpay-select-box-'.$item['key'].'" ref="'.$item['key'].'required" data-required="'.$item['required'].'">
        <div class="cpay-select-title cpay-form-title">'.$item['name'].self::cpay_required($item).'</div>
            <select '.$v_model.' name="'.$item['key'].'" id="'.$item['key'].'">';

        foreach ($item['value_arg'] as $k => $v) {
            $select .= '<option value="'.$v['k'].'">'.$v['v'].'</option>';
        }

        $select .= '</select> '.($item['desc'] ? '<p class="cpay-desc">'.$item['desc'].'</p>' : '').'</div>';

        return apply_filters( 'b2_cpay_form_type_select',$select, $item,$id);
    }

    //获取优惠劵
    public static function coupon($atts,$content = null){

        $a = shortcode_atts( array(
            'id'=>''
        ), $atts );

        $coupons = Coupon::get_coupons(array($a['id']),1);

        if(!empty($coupons)){
            foreach ($coupons as $k => $v) {

                $products = $v['products'];

                $desc = '';
                if(!empty($products)){
                    $title = __('限制商品','b2');
                    $type = 'stamp01';
                    foreach ($products as $_k => $_v) {
                        $thumb = b2_get_thumb(array('thumb'=>$_v['image'],'height'=>80,'width'=>80));
                        $desc .= '<a href="'.$_v['link'].'" target="_blank">
                            '.b2_get_img(array('src'=>$thumb,'class'=>array('b2-radius'))).'
                        </a> ';
                    }
                }elseif(!empty($v['cats'])){
                    $title = __('限制商品分类','b2');
                    $type = 'stamp02';
                    foreach ($v['cats'] as $c_k => $c_v) {
                        $desc .= '[<a href="'.$c_v['link'].'" target="_blank">'.$c_v['name'].'</a>] ';
                    }
                }else{
                    $title = __('不限制使用','b2');
                    $type = 'stamp03';
                    $desc .= __('所有商品和商品类型均可使用','b2');
                }

                $roles = '';
                if(!empty($v['roles']['lvs'])){
                    foreach ($v['roles']['lvs'] as $r_k => $r_v) {
                        $roles .= $r_v.' ';
                    }
                }else{
                    $roles = __('任何人都可以使用','b2');
                }

                $date = '';
                if($v['receive_date']['expired']){
                    $date = '<div class="coupon-desc">'.__('领取时间','b2').'</div>'.__('无法领取','b2');
                    $type = 'stamp04';
                }else{
                    if((int)$v['receive_date']['date'] === 0){
                        $date = '<div class="coupon-desc">'.__('领取时间','b2').'</div>'.__('随时领取','b2');
                    }else{
                        $date = '<div class="coupon-desc">'.__('领取时间截止到','b2').'</div>'.$v['receive_date']['date'];
                    }
                }

                $shixiao = '';
                if((int)$v['expiration_date']['date'] !== 0){
                    $shixiao = '<div class="coupon-desc">'.__('使用时效：','b2').'</div>'.$v['expiration_date']['date'].__('天内使用有效','b2');
                }else{
                    $shixiao = '<div class="coupon-desc">'.__('使用时效：','b2').'</div>'.__('永久有效','b2');
                }

                return '
                <div class="shop-coupon-item shortcode-coupon">
                    <div class="stamp '.$type.' b2-radius">
                        <div class="par">
                            <p>'.$title.'</p>
                            <sub class="sign">'.B2_MONEY_SYMBOL.'</sub><span>'.$v['money'].'</span><sub>'.__('优惠劵','b2').'</sub>
                            <div class="coupon-date">
                                <div>'.$shixiao.'</div>
                            </div>
                        </div>
                        <div class="copy">
                        <div class="copy-date">'.$date.'</div>
                            <p><button '.($type === 'stamp04' ? 'disabled="true"' : false).' class="coupon-receive" data-id="'.$v['id'].'">'.($type === 'stamp04' ? __('已经过期','b2') : __('立刻领取','b2')).'</button></p>
                            <div class="coupon-info-box">
                                <button class="text more-coupon-info">'.b2_get_icon('b2-information-line').__('查看详情','b2').'</button>
                                <div class="coupon-info b2-radius">
                                    <div class="shop-coupon-title"><div class="coupon-title"><span>'.__('优惠劵ID：','b2').'</span><span class="coupon-id">'.$v['id'].'</span></div><span class="close-coupon-info">×</span></div>
                                    <div class="">
                                        <span class="coupon-title">'.$title.'：</span>
                                        <div class="">'.$desc.'</div>
                                    </div>
                                    <div class="coupon-roles">
                                        <span class="coupon-title">'.__('限制用户组','b2').'：</span>
                                        <div class="coupon-roles-desc">'.$roles.'</div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <i class="coupon-bg"></i>
                    </div>
                </div>
                ';
            }
        }
        return;
    }

    public static function insert_post_mp($atts,$content = null){

        $post_id = isset($atts['id']) ? $atts['id'] : false;
        if(!$post_id) return '';

        $circle_link = b2_get_option('normal_custom','custom_circle_link');
        $circle_name = b2_get_option('normal_custom','custom_circle_name');

        if(!is_numeric($post_id)){
            $url = $post_id;
            $post_id = url_to_postid($post_id);

            if(strpos($url,'/'.$circle_link) !== false && $post_id === 0){
                if($url === B2_HOME_URI.'/'.$circle_link || $url === B2_HOME_URI.'/'.$circle_link.'/'){
                    $circle_id = get_option('b2_circle_default');
                }else{
                    $slug = str_replace(B2_HOME_URI.'/'.$circle_link.'/','',$url);
                    $circle_id = get_term_by('slug', $slug, 'circle_tags');
                    if(isset($circle_id->term_id)){
                        $circle_id = $circle_id->term_id;
                    }else{
                        return '';
                    }
                }

                $circle_data = Circle::get_circle_data($circle_id);

                if(isset($circle_data['name'])){
                    return '<div class="insert-post circle_tags">
                    <div class="insert-post-content">
                        <div class="circle-type">'.$circle_name.'</div>
                        <div class="insert-post-title"><a href="'.$circle_data['link'].'" target="_blank">'.$circle_data['name'].'</a></div>
                    </div>
                    </div>
                    ';
                }
            }
        }

        $post_type = get_post_type($post_id);



        if(!$post_type) return;

        $post_meta = Post::post_meta($post_id);

        $html = '<div class="insert-post '.$post_type.'" style="background-color: #fafafa;
        margin-bottom: 18px;
        position: relative;
        overflow: hidden;
        border: 1px solid #f0f0f0;">';

        if($post_type === 'post'){
            $html .= '<div class="insert-post-content">
                <div class="insert-post-title"><a href="'.get_permalink($post_id).'" target="_blank">'.get_the_title($post_id).'</a></div>
                <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($post_id)).'</div>
                <div class="insert-post-meta" :style="font-size:12px">
                    <span class="insert-post-meta-avatar"><a href="'.$post_meta['user_link'].'">'.$post_meta['user_name'].'</a></span>
                    <span class="post-meta">
                        <b class="single-date">
                            '.$post_meta['date'].'
                        </b>
                        <b class="single-like">
                            '.__('喜欢：','b2').$post_meta['like'].'
                        </b>
                        <b class="single-eye">
                            '.__('访问：','b2').$post_meta['views'].'
                        </b>
                    </span>
                </div>
            </div>';
        }else if($post_type === 'page'){
            $html .= '<div class="insert-post-content">
                <div class="insert-post-title"><a href="'.get_permalink($post_id).'" target="_blank">'.get_the_title($post_id).'</a></div>
                <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($post_id)).'</div>
            </div>';
        }else

        //插入快讯
        if($post_type === 'newsflashes'){
            $vote_up = b2_get_option('newsflashes_main','newsflashes_vote_up_text');
            $vote_down = b2_get_option('newsflashes_main','newsflashes_vote_down_text');

            $vote = Post::get_post_vote_up($post_id);

            $html .= '<div class="insert-post-content">
                <div><a href="'.$post_meta['user_link'].'">'.$post_meta['user_name'].'</a></div>
                <div class="insert-post-title"><a href="'.get_permalink($post_id).'" target="_blank">'.get_the_title($post_id).'</a></div>
                <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($post_id)).'</div>
                <div class="insert-post-meta">
                    <div class="single-date">'.$post_meta['date'].'</div>
                    <div class="post-meta">
                        <div class="single-like">
                            '.$vote_up.$vote['up'].'
                        </div>
                        <div class="single-eye">
                            '.$vote_down.$vote['down'].'
                        </div>
                    </div>
                </div>
            </div>';
        }else

        //插入文档
        if($post_type === 'document'){
            $html .= '<div class="insert-post-content">
                <div class="insert-post-title"><a href="'.get_permalink($post_id).'" target="_blank">'.get_the_title($post_id).'</a></div>
                <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($post_id)).'</div>
            </div>';
        }else

        if($post_type === 'shop'){

            $data = Shop::get_shop_item_data($post_id,0);
            $type = $data['type'];
            $type = $type === 'normal' ? __('出售','b2') : ($type === 'lottery' ? __('抽奖','b2') : __('兑换','b2'));
            $icon = $data['type'] === 'normal' ? B2_MONEY_SYMBOL : __('积分：','b2');

            if(isset($data['price']['price'])){
                $html .= '<div class="insert-post-content">
                    <div class="insert-post-title"><a href="'.get_permalink($post_id).'" target="_blank">'.get_the_title($post_id).'</a><span class="insert-post-title-span">['.$type.']</span></div>
                    <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($post_id)).'</div>
                    <div class="insert-post-meta">
                        <div class="insert-shop-price">
                            <div class="price">'.$icon.$data['price']['current_price'].'</div>
                            <div class="delete">'.$icon.$data['price']['price'].'</div>
                        </div>
                        <div class="post-meta">
                            <div class="single-date">
                                '.__('库存：','b2').$data['stock']['total'].'
                            </div>
                            '.($data['stock']['sell'] ? '<div class="single-like">
                            '.__('已售：','b2').$data['stock']['sell'].'
                        </div>' : '').'
                            <div class="single-eye">
                                '.__('人气：','b2').$data['views'].'
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }else

        if($post_type === 'circle'){
            $title = get_the_title($post_id);
            if(!$title){
                $title = get_post_meta($post_id,'b2_auto_title',true);
            }

            if(!$title){
                $title = __('无标题的话题','b2');
            }
            $html .= '<div class="insert-post-content">
                <div class="circle-topic">'.sprintf(__('%s话题','b2'),$circle_name).'</div>
                <div class="insert-post-title"><a href="'.get_permalink($post_id).'" target="_blank">'.$title.'</a></div>
            </div>';
        }else{
            return '';
            global $wp;
            $current_url = B2_HOME_URI.'/'.add_query_arg(array(),$wp->request);
            $html .= '<div class="insert-post-content">
                <div class="insert-post-title"><a href="'.$current_url.'" target="_blank">'.wp_get_document_title().'</a></div>
            </div>';
        }

        $html .= '</div>';

        return $html;
    }

    public static function b2_content_hide($atts,$content = null){

        if(isset($atts['id'])){
            $post_id = $atts['id'];
            $order_id = $atts['order_id'];
        }else{
            $post_id = get_the_id();
            $order_id = '';
        }
		
		$user_id = get_current_user_id();

        $_role = get_post_meta($post_id,'b2_post_reading_role',true);
        if(!$_role || $_role == 'none') return $content;

        $role = self::get_content_hide_arg($post_id,$order_id,false,$user_id);

        $allow = false;

        if(is_array($role)){
            $str = $content;
        }else{
            $str = $role;
            $allow = true;
        }

       // return '11111111111'.$str;

        return '<div class="content-hidden">
            <div class="content-hidden-info '.($allow ? 'hidden-content' : 'show-content').'">
                <div>
                    '.do_shortcode(wpautop($str)).'
                </div>
            </div>
        </div>';
    }

    public static function content_hide($atts,$content = null){

        $post_id = get_the_id();
        $_role = get_post_meta($post_id,'b2_post_reading_role',true);
        if(!$_role || $_role == 'none') return $content;

        return '<div class="content-hidden">
            <div class="content-hidden-info">
                <div class="content-show-roles b2-mark">
                </div>
            </div>
        </div>';
    }

    public static function login_button($tag){

        //是否允许注册
        $allow_sign = b2_get_option('normal_login','allow_register');

        $login = '<div class="content-user-lv-login">
            <'.$tag.' class="empty content-cap-login button empty" onclick="userTools.login(1)" data-login="login">'.__('登录','b2').'</'.$tag.'>
            '.($allow_sign ? '<'.$tag.' class="content-cap-signin button" onclick="userTools.login(2)" data-login="register">'.__('注册','b2').'</'.$tag.'>' : '').'</div>';

        return $login;
    }

    public static function get_content_hide_arg($post_id,$order_id = '',$json = false,$user_id = 0){
		if(!$user_id){
			$user_id = b2_get_current_user_id();
		}
        

        $can_guset_pay = (int)get_post_meta($post_id,'b2_hidden_guest_buy',true);

        //检查用户的权限
        $role = self::check_reading_cap($post_id,$user_id,$order_id);

        if(isset($role['error'])) return '';

        if($role['cap'] === 'dark_room'){

            if($json){
                return array(
                    'data'=>'dark_room',
                    'role'=>$role
                );
            }

            return '<div class="content-cap">
                    <div>
                        <div class="content-cap-title"><span>'.b2_get_icon('b2-git-repository-private-line').__('小黑屋禁闭','b2').'</span></div>
                        <div class="content-buy-count"><span>'.__('无法查看隐藏内容','b2').'</span></div>
                    </div>
                    <div class="content-cap-info">
                        '.__('小黑屋思过中...','b2').'
                    </div>
                </div>';
        }

        //登录可见
        if($role['cap'] === 'login' && !$user_id){

            if($json){
                return array(
                    'data'=>'login',
                    'role'=>$role
                );
            }

            return '<div class="content-cap">
                <div>
                    <div class="content-cap-title" '.(!$user_id ? 'onclick="userTools.login(1)"' : '').'><span>'.b2_get_icon('b2-git-repository-private-line').__('隐藏内容，登录后阅读','b2').'</span></div>
                    <div class="content-buy-count"><span>'.__('若无账户，请先注册','b2').'</span></div>
                </div>
                <div class="content-cap-info">
                    '.self::login_button('a href="javascript:void(0)"').'
                </div>
                </div>';
        }

        //评论可见
        if($role['cap'] === 'comment' && $role['allow'] === false){

            if($json){
                return array(
                    'data'=>'comment',
                    'role'=>$role
                );
            }

            return '<div class="content-cap">
                <div>
                    <div class="content-cap-title" '.(!$user_id ? 'onclick="userTools.login(1)"' : '').'><span>'.b2_get_icon('b2-git-repository-private-line').__('隐藏内容，评论后阅读','b2').'</span></div>
                    <div class="content-buy-count"><span>'.__('评论后，请刷新页面','b2').'</span></div>
                </div>
                <div class="content-cap-info">
                    '.(!$user_id ? self::login_button('a href="javascript:void(0)"') : '<a href="javascript:void(0)" data-comment="1" class="mp-show content-cap-login button empty" data-id="'.$post_id.'" data-title="'.get_the_title($post_id).'">'.__('评论','b2').'</a>').'
                </div>
                </div>';
        }

        //限制等级可见
        if($role['cap'] === 'roles' && $role['allow'] === false){
            $lvs = '';
            foreach ($role['roles'] as $k => $v) {
                $lvs .= User::get_lv_icon($v);
            }

            if($json){

                $_lvs = array();

                foreach ($role['roles'] as $k => $v) {
                    $_lvs[] = User::get_lv_icon($v,true);
                }

                return array(
                    'data'=>$_lvs,
                    'role'=>$role
                );
            }

            return '<div class="content-cap content-see-lv">
                <div>
                    <div class="content-cap-title">
                        <span>'.b2_get_icon('b2-git-repository-private-line').__('隐藏内容，仅限以下用户组阅读','b2').'</span>
                    </div>
                    <div class="content-buy-count"><span>'.__('如果您未在其中，可以升级','b2').'</span></div>
                    <div class="content-cap-info content-user-lv">
                        '.$lvs.'
                    </div>
                </div>
                    '.(!$user_id ?
                        self::login_button('a href="javascript:void(0)"')
                        : '<div class="content-cap-title"><a href="'.b2_get_custom_page_url('vips').'" target="_blank" class="button mp-hidden" data-link="/pages/my/vips">'.__('立刻升级','b2').'</a><a href="javascript:void(0)" class="button mp-show" data-link="/pages/my/vips">'.__('立刻升级','b2').'</a></div>').'

            </div>';
        }

        if($role['cap'] === 'money' && $role['allow'] === false){

            $data = array(
                'order_price'=>$role['m_c'],
                'order_type'=>'w',
                'post_id'=>$post_id,
                'title'=>htmlspecialchars(wptexturize(b2_get_des(0,200,get_the_title($post_id))))
            );

            $buy_count = get_post_meta($post_id,'zrz_buy_user',true);
            $buy_count = is_array($buy_count) ? count($buy_count) : 0;

            $default_count = get_post_meta($post_id,'b2_post_hidden_count',true);
            $buy_count = $buy_count+(int)$default_count;

            $pass_times = get_post_meta($post_id,'b2_post_hidden_times',true);

            if($json){
                return array(
                    'data'=>array(
                        'pay'=>$data,
                        'count'=>$buy_count,
                        'pass_time'=>$pass_times
                    ),
                    'role'=>$role
                );
            }

            $is_mp_false = false;

            if(defined('B2_APP_VERSION')){
                $opt = get_option('b2_app_pay_normal');
                if(isset($opt['pay_allow_mpweixin']) && $opt['pay_allow_mpweixin'] == '0' && b2_is_agent('ios') && b2_is_mp()){
                    $is_mp_false = true;
                }
            }

            if(!$is_mp_false){
                $b = ' '.(!$can_guset_pay && !$user_id ? self::login_button('a href="javascript:void(0)"') : '
                <div class="content-user-lv-login"><a data-pay=\''.json_encode($data,true).'\' class="empty content-cap-login button" onClick="b2pay(this)" href="javascript:void(0)">'.__('支付','b2').'</a></div>' );
            }else{
                $b = '<div class="content-user-lv-login fs12 gray">IOS端不支持虚拟物品支付</div>';
            }

            return '<div class="content-cap">
                <div>
                    <div class="content-cap-title" '.(!$user_id ? 'onclick="userTools.login(1)"' : '').'>
                        <span>'.b2_get_icon('b2-git-repository-private-line').__('隐藏内容，支付费用后阅读','b2').'</span>'.($pass_times ? '<span class="hidden-tips">'.sprintf(__('购买完%s小时后过期','b2'),$pass_times).'</span>' : '').'
                    </div>
                    <div class="content-buy-count" '.(!$user_id ? 'onclick="userTools.login(1)"' : '').'><span>'.sprintf(__('已经有%s人购买查看了此内容','b2'),'<b>'.$buy_count.'</b>').'</span></div>
                    <div class="content-cap-info content-user-money">
                        <span class="user-money">'.B2_MONEY_SYMBOL.'<b>'.$role['m_c'].'</b></span>
                    </div>
                </div>
                '.$b.'
            </div>';
        }

        if($role['cap'] === 'credit' && $role['allow'] === false){
            $data = array(
                'order_price'=>$role['m_c'],
                'order_type'=>'w',
                'post_id'=>$post_id,
                'title'=>wptexturize(b2_get_des(0,200,get_the_title($post_id)))
            );

            $buy_count = get_post_meta($post_id,'zrz_buy_user',true);
            $buy_count = is_array($buy_count) ? count($buy_count) : 0;

            $default_count = get_post_meta($post_id,'b2_post_hidden_count',true);
            $buy_count = $buy_count+(int)$default_count;

            $pass_times = get_post_meta($post_id,'b2_post_hidden_times',true);

            if($json){
                return array(
                    'data'=>array(
                        'pay'=>$data,
                        'count'=>$buy_count,
                        'pass_time'=>$pass_times
                    ),
                    'role'=>$role
                );
            }

            return '<div class="content-cap">
                <div>
                    <div class="content-cap-title" '.(!$user_id ? 'onclick="userTools.login(1)"' : '').'><span>'.b2_get_icon('b2-git-repository-private-line').__('隐藏内容，支付积分后阅读','b2').'</span>'.($pass_times ? '<span class="hidden-tips">'.sprintf(__('购买完%s小时后过期','b2'),$pass_times).'</span>' : '').'</div>
                    <div class="content-buy-count" '.(!$user_id ? 'onclick="userTools.login(1)"' : '').'><span>'.sprintf(__('已经有%s人购买查看了此内容','b2'),'<b>'.$buy_count.'</b>').'</span></div>
                    <div class="content-cap-info content-user-money baidu-hidden">
                        <span class="user-money"><text class="b2font b2coin-line mp-show"></text>'.b2_get_icon('b2-coin-line mp-hidden').$role['m_c'].'</span>
                    </div>
                    <div class="user-money baidu-show b2color fs14 mg-b">'.$role['m_c'].'积分</div>
                </div>
                '.(!$user_id ?
                    self::login_button('a href="javascript:void(0)"')
                    : '<div class="content-user-lv-login">
                        <a data-pay=\''.json_encode($data,true).'\' data-paytype="credit" class="empty content-cap-login button mp-show" onClick="b2pay(this)" href="javascript:void(0)">'.__('支付','b2').'</a>
                        <button class="empty content-cap-login mp-hidden" data-pay=\''.json_encode($data,true).'\' onclick="b2creditpay(this)">'.__('支付','b2').'</button>
                    </div>').'
            </div>';
        }

        if($json){
            return array(
                'data'=>'',
                'role'=>$role
            );
        }

        $arg = array();
        $pattern = get_shortcode_regex();

        if (   preg_match_all( '/'. $pattern .'/s',get_post_field('post_content',$post_id) , $matches )
            && array_key_exists( 2, $matches )
            && in_array( 'content_hide', $matches[2] )
            && !empty($matches[0]))
        {
            foreach ($matches[0] as $k => $v) {
                if(strpos($v,'content_hide') !== false && strpos($v,'_content_hide') === false){
                    $content = str_replace(array('[content_hide]','[/content_hide]'),'',$v);
                    $content = str_replace( ']]>', ']]&gt;', $content);
                    $arg[] = do_shortcode(wpautop($content));
                }
            }
        }

        return $arg;
    }

    /**
     * 检查文章的阅读权限
     *
     * @param int $post_id 文章ID
     * @param int $user_id 当前用户的ID
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function check_reading_cap($post_id,$user_id,$order_id = ''){

        $cap = apply_filters('check_reading_cap', array('post_id'=>$post_id,'user_id'=>$user_id));

        if(isset($cap['user_id'])) return array('error'=>__('没有数据','b2'));
        $allow = false;

        $m_c = 0;

        $roles = array();

        $dark_room = (int)get_user_meta($user_id,'b2_dark_room',true);
        if($dark_room) {
            $allow = false;
            $cap = 'dark_room';
        }else{
            //如果没有限制权限
            if(!$cap || $cap === 'none' || user_can( $user_id, 'manage_options' )){
                $allow = true;
            }

            //如果是允许查看所有隐藏内容的用户
            elseif(User::check_user_can_read_all($user_id)){
                $allow = true;
            }

            //如果是登录可见
            elseif($cap === 'login' && $user_id) {
                $allow = true;
            }

            //如果是管理员
            elseif(user_can($user_id,'delete_users')) {
                $allow = true;
            }

            //如果是文章作者
            elseif(get_post_field('post_author',$post_id) == $user_id) {
                $allow = true;
            }

            //如果是评论可见
            elseif($cap === 'comment'){
                $allow = self::check_user_commented($user_id,$post_id);
            }

            //如果是限制等级可见
            elseif($cap === 'roles'){
                $roles = get_post_meta($post_id,'b2_post_roles',true);
                $roles = is_array($roles) ? $roles : array();
                $user_role = User::get_user_lv($user_id);

                if(isset($user_role['vip']['lv']) && isset($user_role['lv']['lv'])){
                    if(in_array($user_role['vip']['lv'],$roles) || in_array($user_role['lv']['lv'],$roles)){
                        $allow = true;
                    }
                }
            }else{

                $allow = apply_filters('b2_get_content_hide_allow', array('post_id'=>$post_id,'user_id'=>$user_id,'order_id'=>$order_id,'order_type'=>'w','order_key'=>0));

                if(!$allow){
                    if($cap === 'money'){
                        $m_c = get_post_meta($post_id,'b2_post_money',true);
                    }elseif($cap === 'credit'){
                        $m_c = get_post_meta($post_id,'b2_post_credit',true);
                    }
                }
            }
        }


        return array(
            'allow'=>$allow,
            'cap'=>$cap,
            'm_c'=>$m_c,
            'roles'=>$roles
        );

    }

    public static function check_user_commented($user_id,$post_id){
        $allow = false;

        //如果是游客
        if(!$user_id){
            $commenter = wp_get_current_commenter();

            if(!$commenter['comment_author_email']){
                $allow = false;
            }else{
                $args = array(
                    'post_id' => $post_id,
                    'author_email'=>$commenter['comment_author_email'],
                    'status'=>'approve'
                );

                $comment = get_comments($args);

                if(!empty($comment)){
                    $allow = true;
                }
            }



        //如果不是游客，检查是否在文章中评论过
        }else{
            $args = array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'status'=>'approve'
            );

            $comment = get_comments($args);

            if(!empty($comment)){
                $allow = true;
            }

        }

        return $allow;
    }

    public static function file_down($atts,$content = null){

        $a = shortcode_atts( array(
            'link'=>'',
            'name'=>'',
            'pass'=>'',
            'code'=>'',
        ), $atts );

        $html = '<div class="file-down b2-radius">';

        $html .= '<div class="file-down-icon">'.b2_get_icon('b2-download-cloud-line').'</div>';

        $html .= '<div class="file-down-box">
            <div class="file-down-code">
                <h2 class="_h2">'.$a['name'].'</h2>
                <div class="file-down-pass mp-fs12">
                    '.__('提取码：','b2').($a['pass'] ? '<code>'.esc_attr($a['pass']).'</code><span class="mp-hidden-all">'.__('复制','b2').'<input value="'.esc_attr($a['pass']).'" type="text" class="b2-hidden"></span>' : __('无','b2')).'
                </div>
                <div class="file-down-pass mp-fs12">
                    '.__('解压码：','b2').($a['code'] ? '<code>'.esc_attr($a['code']).'</code><span class="mp-hidden-all">'.__('复制','b2').'<input value="'.esc_attr($a['code']).'" type="text" class="b2-hidden"></span>' : __('无','b2')).'
                </div>
            </div>
            <div class="file-down-code-button"><a class="button empty" target="_blank" href="'.$a['link'].'">'.__('下载','b2').'</a></div>
        </div>';

        $html .= '</div>';

        return $html;
    }

    public static function file_down_mp($atts,$content = null){

        $a = shortcode_atts( array(
            'link'=>'',
            'name'=>'',
            'pass'=>'',
            'code'=>'',
        ), $atts );

        $html = '<div class="file-down-box">';

        $html .= '
            <div class="file-down-code">
                <div class="insert-post-title">'.$a['name'].'</div>
                <div class="file-down-pass">
                    '.__('提取码：','b2').($a['pass'] ? esc_attr($a['pass']) : __('无','b2')).'
                </div>
                <div class="file-down-pass">
                    '.__('解压码：','b2').($a['code'] ? esc_attr($a['code']) : __('无','b2')).'
                </div>
            </div>
            <div class="file-down-code-button"><a class="button empty" href="'.$a['link'].'">'.__('下载','b2').'</a></div>
        ';

        $html .= '</div>';

        return $html;
    }

    public static function insert_post($atts,$content = null){

        $_post_id = isset($atts['id']) ? $atts['id'] : false;
        if(!$_post_id) return '';

        $circle_link = b2_get_option('normal_custom','custom_circle_link');
        $circle_name = b2_get_option('normal_custom','custom_circle_name');

        if(!is_numeric($_post_id)){
            $url = $_post_id;

            $_post_id = url_to_postid($_post_id);

            if(strpos($url,'/'.$circle_link) !== false && $_post_id === 0){

                if($url === B2_HOME_URI.'/'.$circle_link || $url === B2_HOME_URI.'/'.$circle_link.'/'){
                    $circle_id = get_option('b2_circle_default');
                }else{
                    $slug = str_replace(B2_HOME_URI.'/'.$circle_link.'/','',$url);
                    $circle_id = get_term_by('slug', $slug, 'circle_tags');
                    if(isset($circle_id->term_id)){
                        $circle_id = $circle_id->term_id;
                    }else{
                        return '';
                    }
                }

                $circle_data = Circle::get_circle_data($circle_id);

                if(isset($circle_data['name'])){
                    return '<div class="insert-post b2-radius circle_tags">
                    <a href="'.$circle_data['link'].'" class="mp-hidden" data-type="circle_tags" data-id="'.$circle_id.'"></a>
                    <a href="javascript:void(0)" class="mp-show" data-type="circle_tags" data-id="'.$circle_id.'"></a>
                    <span class="insert-post-bg"></span>
                    <div class="insert-post-thumb"><img src="'.$circle_data['icon'].'" class="b2-radius"/></div>
                    <div class="file-down-icon">'.b2_get_icon('b2-donut-chart-fill').'</div><div class="insert-post-content">
                        <p class="mp-fs12">'.$circle_name.'</p>
                        <h2><a href="'.$circle_data['link'].'" target="_blank">'.$circle_data['name'].'</a></h2>
                    </div>
                    </div>
                    ';
                }
            }
        }

        $post_type = get_post_type($_post_id);

        if(!$post_type) return;

        $thumb_url = \B2\Modules\Common\Post::get_post_thumb($_post_id);

        $thumb_url = b2_get_thumb(array(
            'thumb'=>$thumb_url,
            'width'=>100,
            'height'=>100
        ));

        $thumb = self::$restApi ? '<img src="'.$thumb_url.'" />' : b2_get_img(array('src'=>$thumb_url,'class'=>array('b2-radius')));

        $post_meta = Post::post_meta($_post_id);

        $html = '<div class="insert-post b2-radius '.$post_type.'">
        <span class="insert-post-bg">
        '.$thumb.'
        </span>';

        $link = get_permalink($_post_id);

        $html .= '<div class="insert-post-thumb">
            <a href="'.$link.'" target="_blank">
            '.$thumb.'
            </a>
        </div>';



        if($post_type === 'post'){
            $html .= '<a href="'.$link.'" class="mp-hidden" data-type="post" data-id="'.$_post_id.'"></a><a href="javascript:void(0)" class="mp-show" data-type="post" data-id="'.$_post_id.'"></a><div class="insert-post-content">
                <h2><a href="'.$link.'" target="_blank">'.get_the_title($_post_id).'</a></h2>
                <div class="insert-post-meta">
                    <div class="insert-post-meta-avatar mp-hidden-all"><img class="avatar" src="'.$post_meta['user_avatar'].'" /><a href="'.$post_meta['user_link'].'">'.$post_meta['user_name'].'</a></div>
                    <ul class="post-meta">
                        <li class="single-date">
                            '.$post_meta['date'].'
                        </li>
                        <li class="single-like">
                            '.b2_get_icon('b2-heart-fill').'<span class="mp-show">'.__('喜欢：','b2').'</span>'.$post_meta['like'].'
                        </li>
                        <li class="single-eye">
                            '.b2_get_icon('b2-eye-fill').'<span class="mp-show">'.__('浏览：','b2').'</span>'.$post_meta['views'].'
                        </li>
                    </ul>
                </div>
            </div>';
        }else if($post_type === 'page'){
            $html .= '<a href="'.$link.'" class="mp-hidden" data-type="page" data-id="'.$_post_id.'"></a><a href="javascript:void(0)" class="mp-show" data-type="page" data-id="'.$_post_id.'"></a><div class="file-down-icon">'.b2_get_icon('b2-pages-line').'</div><div class="insert-post-content">
                <h2><a href="'.$link.'" target="_blank">'.get_the_title($_post_id).'</a></h2>
                <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($_post_id)).'</div>
            </div>';
        }else

        //插入快讯
        if($post_type === 'newsflashes'){

            $vote_up = b2_get_option('newsflashes_main','newsflashes_vote_up_text');
            $vote_down = b2_get_option('newsflashes_main','newsflashes_vote_down_text');

            $vote = Post::get_post_vote_up($_post_id);

            $html .= '<a href="'.$link.'" class="mp-hidden" data-type="newsflashes" data-id="'.$_post_id.'"></a><a href="javascript:void(0)" class="mp-show" data-type="newsflashes" data-id="'.$_post_id.'"></a><div class="insert-post-meta-avatar mp-hidden-all"><a href="'.$post_meta['user_link'].'"><img class="avatar" src="'.$post_meta['user_avatar'].'" /></a></div><div class="insert-post-content mg-l">
                <div class="mp-hidden-all"><a href="'.$post_meta['user_link'].'">'.$post_meta['user_name'].'</a></div>
                <h2><a href="'.$link.'" target="_blank">'.get_the_title($_post_id).'</a></h2>
                <div class="insert-post-meta">
                    <div>'.$post_meta['date'].'</div>
                    <ul class="post-meta">
                        <li class="single-like">
                            '.b2_get_icon('b2-funds-box-line').$vote_up.$vote['up'].'
                        </li>
                        <li class="single-eye">
                            '.b2_get_icon('b2-funds-box-line1').$vote_down.$vote['down'].'
                        </li>
                    </ul>
                </div>
            </div>';
        }else

        //插入文档
        if($post_type === 'document'){
            $html .= '<a href="'.$link.'" class="mp-hidden" data-type="document" data-id="'.$_post_id.'"></a><a href="javascript:void(0)" class="mp-show" data-type="document" data-id="'.$_post_id.'"></a><div class="document-icon">'.b2_get_icon('b2-questionnaire-line').'</div><div class="insert-post-content">
                <h2><a href="'.$link.'" target="_blank">'.get_the_title($_post_id).'</a></h2>
                <div class="insert-post-desc">'.Sliders::get_des('',150,b2_get_excerpt($_post_id)).'</div>
            </div>';
        }else

        if($post_type === 'shop'){

            $data = Shop::get_shop_item_data($_post_id,0);
            $type = $data['type'];
            $type = $type === 'normal' ? __('出售','b2') : ($type === 'lottery' ? __('抽奖','b2') : __('兑换','b2'));
            $icon = $data['type'] === 'normal' ? B2_MONEY_SYMBOL : b2_get_icon('b2-coin-line');

            if(isset($data['price']['price'])){
                $html .= '<a href="'.$link.'" class="mp-hidden" data-type="shop" data-id="'.$_post_id.'"></a><a href="javascript:void(0)" class="mp-show" data-type="shop" data-id="'.$_post_id.'"></a><div class="insert-post-content">
                    <h2><a href="'.$link.'" target="_blank">'.get_the_title($_post_id).'</a><span class="s-type">['.$type.']</span></h2>
                    <div class="insert-post-meta">
                        <div class="insert-shop-price">
                            <div class="price">'.$icon.$data['price']['current_price'].'</div>
                            <div class="delete">'.$icon.$data['price']['price'].'</div>
                        </div>
                        <ul class="post-meta">
                            <li class="single-date">
                                '.__('库存：','b2').$data['stock']['total'].'
                            </li>
                            '.($data['stock']['sell'] ? '<li class="single-like">
                            '.__('已售：','b2').$data['stock']['sell'].'
                        </li>' : '').'
                            <li class="single-eye">
                                '.__('人气：','b2').$data['views'].'
                            </li>
                        </ul>
                    </div>
                </div>';
            }
        }else

        if($post_type === 'circle'){
            $title = get_the_title($_post_id);
            if(!$title){
                $title = get_post_meta($_post_id,'b2_auto_title',true);
            }

            if(!$title){
                $title = __('无标题的话题','b2');
            }

            $html .= '<a href="'.$link.'" class="mp-hidden" data-type="topic" data-id="'.$_post_id.'"></a><a href="javascript:void(0)" class="mp-show" data-type="topic" data-id="'.$_post_id.'"></a><div class="file-down-icon">'.b2_get_icon('b2-chat-smile-3-line').'</div><div class="insert-post-content">
                <p class="mp-fs12">'.sprintf(__('%s话题','b2'),$circle_name).'</p>
                <h2><a href="'.$link.'" target="_blank">'.$title.'</a></h2>
            </div>';
        }else
        if($post_type === 'links'){

            $term = wp_get_post_terms($_post_id,'link_cat');
            $cat = '';
            if($term){
                $cat = '<div class="s-link-cat b2-color"><a href="'.get_term_link($term[0]->term_id).'" target="_blank">'.$term[0]->name.'</a></div>';
            }

            $icon = b2_get_thumb(array('thumb'=>get_post_meta($_post_id,'b2_link_icon',true),'width'=>100,'height'=>100));

            $desc = Sliders::get_des('',100,b2_get_excerpt($_post_id));
            $desc = $desc ? $desc : __('这个网站没有任何描述信息','b2');

            $html .= '<div class="s-link-item mp-hidden-all">
            <div class="s-link-info">
                '.self::$restApi ? b2_get_img(array('src'=>$icon)) : '<img src="'.$icon.'" />'.'
                <div class="s-link-data">
                    <h2><a href="'.get_permalink($_post_id).'" target="_blank">'.get_the_title($_post_id).'</a>'.$cat.'</h2>
                    <p>'.$desc.'</p>
                </div>
            </div>
        </div>';
        }else{
            return '';
            global $wp;
            $current_url = B2_HOME_URI.'/'.add_query_arg(array(),$wp->request);
            $html .= '<div class="document-icon">'.b2_get_icon('b2-pages-line').'</div><div class="insert-post-content">
                <h2><a href="'.$current_url.'" target="_blank">'.wp_get_document_title().'</a></h2>
            </div>';
        }

        $html .= '</div>';

        return $html;
    }

    public static function invitation_list($atts,$content = null){

        $mp = false;
        if(!isset($atts['id']) || !$atts['id']){
            global $post;
            if(!isset($post->ID)) return;
            $atts['id'] = $post->ID;
        }else{
            $mp = true;
        }

        $user_id = get_post_field('post_author', $atts['id']);

        $user = get_userdata($user_id);

        if(!empty($user->roles) && !in_array('administrator', $user->roles)){
            return '';
        }

        $start = isset($atts['start']) ? (int)$atts['start'] : 1;
        $end = isset($atts['end']) ? (int)$atts['end'] : 20;
        $owner = isset($atts['owner']) ? (int)$atts['owner'] : $user_id;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_invitation';
        $codes = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name WHERE
                invitation_owner=%d AND (id>=%d AND id<=%d)
                ",
                $owner,$start, $end
            ),ARRAY_A );

        $html = '';
        if(count($codes) > 0){
            $html = '<div class="b2-table-box"><table class="wp-list-table widefat fixed striped shop_page_order_option" '.($mp ? 'style="font-size:24rpx;"' : '').'>
            <thead class="b-thead">
                <tr class="b-tr"><td class="b-td">'.__('编号','b2').'</td><td>'.__('邀请码','b2').'</td><td>'.__('奖励','b2').'</td><td>'.__('使用状态','b2').'</td><td>'.__('使用者','b2').'</td></tr>
            </thead>
            <tbody class="b-tbody">';
            $i = 0;
            foreach ($codes as $code) {
                $i++;
                if($code['invitation_user']){
                    $user = '<a target="__blank" href="'.get_author_posts_url($code['invitation_user']).'">'.get_the_author_meta('display_name',$code['invitation_user']).'</a>';
                }else{
                    $user = __('无','b2');
                }
                $html .= '<tr class="b-tr">
                <td class="b-td">'.$i.'</td>
                <td class="b-td">'.$code['invitation_nub'].'</td>
                <td class="b-td">'.$code['invitation_credit'].'</td>
                <td class="b-td">'.($code['invitation_status'] ? '<span style="color:green">已使用</span>' : '<span style="color:red">未使用</span>').'</td>
                <td class="b-td">'.$user.'</td>
                </tr>';
            }
            $html .= '</tbody>
            </table></div>';
        }
        return $html;
    }
}
