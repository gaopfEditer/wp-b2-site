<?php
use B2\Modules\Common\Newsflashes;

get_header();

$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

$tags = b2_get_option('newsflashes_main','newsflashes_tags');
if($tags){
    $tags = explode(',',$tags);
}

$nav_type = b2_get_option('newsflashes_main','newsflashes_pagenav_type');
$term = get_queried_object();
$term_id = $term->term_id;
$name = $term->name;
$vote_up = b2_get_option('newsflashes_main','newsflashes_vote_up_text');
$vote_down = b2_get_option('newsflashes_main','newsflashes_vote_down_text');
$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
?>

<div class="b2-single-content wrapper">
    <div id="primary-home" class="wrapper content-area">
        <main class="site-main">
            <div class="news-content" ref="paged" data-paged="<?php echo $paged; ?>" data-termid="<?php echo $term_id; ?>">
            <div id="post-news" :class="['modal',showPostFrom ? 'show-modal' : '']" v-cloak>
            <input type="password" style="position:absolute;top:-999px" v-cloak/>
                    <div class="modal-content">
                        <span class="close-button" @click="postNewsflashes()">×</span>
                        <div class="news-title mg-b">
                            <h2><?php echo sprintf(__('发布%s','b2'),$newsflashes_name); ?></h2>
                        </div>
                        <div class="post-news b2-radius mg-b b2-pd">
                            <div class="post-news-header">
                                <label class="news-title">
                                    <p><?php echo __('标题：','b2'); ?></p>
                                    <input type="text" v-model="data.title"/>
                                </label>
                            </div>
                            <div class="news-des">
                                <textarea class="news-des-textarea" ref="newsTextarea" v-model="data.content" placeholder="<?php echo sprintf(__('%s内容','b2'),$newsflashes_name); ?>"></textarea>
                            </div>
                            <div class="news-des-footer">
                                <label class="news-url">
                                    <p><?php echo __('来源网址：','b2'); ?></p>
                                    <input type="text" v-model="data.from"/>
                                </label>
                            </div>
                            <div class="footer-tools">
                                <label class="news-image">
                                    <div><?php echo b2_get_icon('b2-image-fill').'<span>'.__('添加图片','b2').'</span>'; ?></div>
                                    <input id="avatar-input" type="file" class="b2-hidden-always" ref="fileInput" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event,'avatar')" :disabled="locked">
                                </label>
                                <p class="desc"><?php echo __('发布完审核之后才会显示','b2'); ?></p>
                            </div>
                            <div class="news-img" v-if="data.img.url">
                                <img :src="data.img.url" />
                                <span @click="removeImage()">×</span>
                            </div>
                            <div class="news-footer">
                                <button @click="submitNewsflashes" :disabled="locked" :class="locked ? 'b2-loading' : ''"><?php echo __('立刻发布','b2'); ?></button>
                                <?php if(!empty($tags)){ ?>
                                    <label class="news-tags" data-tag="<?php echo $name; ?>" ref="tag">
                                        <select v-model="data.tag">
                                            <?php 
                                                foreach ($tags as $k => $v) {
                                                    echo '<option value="'.$v.'">'.$v.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box b2-radius newsflashes-list">
                    <div class="newsflahses-cover" style="background-image:url(<?php echo b2_get_thumb(array('thumb'=>get_term_meta($term_id,'b2_tax_img' , true),'width'=>800,'height'=>200)); ?>)">
                        <div class="n-desc">
                            <div class="n-desc-name">
                                <h1><?php echo $name; ?></h1>
                                <p><?php echo get_term_meta($term_id,'b2_tax_desc' , true); ?></p>
                            </div>
                            <div class="po-n">
                                <button @click="showForm"><?php echo __('我要爆料','b2'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="news-list-box">
                        <div>
                            <?php 
                                $data = Newsflashes::get_newsflashes_data($paged,$term_id);
                                
                                $html = '';
                                if(!empty($data['data'])){
                                    foreach ($data['data'] as $k => $v) {
                                        $html .= '<div class="news-item" v-if="list == \'\'">
                                            <div class="news-item-date b2-radius"><p>'.$v[0]['date']['date'].'</p></div>
                                                <ul>';
                                        foreach ($v as $_k => $_v) {

                                            $html .= '<li id="news-item-'.$_v['id'].'">
                                               
                                                <div class="news-item-content">
                                                    <div class="news-item-h">
                                                        <div>
                                                            '.($_v['title'] ? '<h2 class="anhover"><a href="'.$_v['link'].'">'.$_v['title'].'</a></h2>' : '').'
                                                            <div class="news-item-header">
                                                                <span>'.$_v['date']['time'].'</span>
                                                                <span>'.__('作者:','b2').'<a href="'.$_v['author']['link'].'">'.$_v['author']['name'].'</a></span>
                                                                
                                                            </div>
                                                            <div>
                                                                <p class="b2-hover">'.$_v['desc'].($_v['from'] ? '<a href="'.esc_url($_v['from']).'" target="_blank" rel="nofollow">'.b2_get_icon('b2-external-link-line').__('原文连接','b2').'</a>' : '').'</p>
                                                            </div>
                                                        </div>
                                                        '.($_v['img'] ? b2_get_img(array('src'=>$_v['img'],'alt'=>$_v['title'],'class'=>array('news-item-img','b2-radius'))) : '').'
                                                    </div>
                                                    <div class="new-meta">
                                                        <div class="new-meta-left">
                                                            <p class="news-vote-up" @click="vote(\''.$_v['id'].'\',\'up\',\''.$k.'\',\''.$_k.'\')">
                                                                <button :class="list !== \'\' && list[\''.$k.'\'][\''.$_k.'\'][\'vote\'][\'up_isset\'] ? \'isset\' : \'\'">'.b2_get_icon('b2-arrow-drop-up-fill').'<b>'.$vote_up.'</b><b v-cloak>{{list !== \'\' ? list[\''.$k.'\'][\''.$_k.'\'][\'vote\'][\'up\'] : \''.$_v['vote']['up'].'\'}}</b></button>
                                                            </p>
                                                            <p class="news-vote-down" @click="vote(\''.$_v['id'].'\',\'down\',\''.$k.'\',\''.$_k.'\')">
                                                                <button :class="list !== \'\' && list[\''.$k.'\'][\''.$_k.'\'][\'vote\'][\'down_isset\'] ? \'isset\' : \'\'">'.b2_get_icon('b2-arrow-drop-down-fill').'<b>'.$vote_down.'</b><b v-cloak>{{list !== \'\' ? list[\''.$k.'\'][\''.$_k.'\'][\'vote\'][\'down\'] : \''.$_v['vote']['down'].'\'}}</b></button>
                                                            </p>
                                                            '.(!empty($_v['tag']) ? '<span class="new-tag anhover"><a target="_blank" href="'.$_v['tag']['link'].'">'.b2_get_icon('b2-flashlight-fill').esc_textarea($_v['tag']['name']).'</a></span>' : '').'
                                                        </div>
                                                        <div class="new-meta-right">'.__('分享到','b2').'<span class="new-weibo" @click="openWin(\''.$_v['share']['weibo'].'\',\'weibo\',\''.$k.'\',\''.$_k.'\')">'.b2_get_icon('b2-weibo-line').'</span><span class="new-weixin">'.b2_get_icon('b2-wechat-line').'</span><span class="new-qq" @click="openWin(\''.$_v['share']['qq'].'\',\'qq\',\''.$k.'\',\''.$_k.'\')">'.b2_get_icon('b2-qq-line').'</span></div>
                                                    </div>
                                                </div>
                                            </li>';
                                        }

                                        $html .= '</ul></div>';
                                    }
                                }

                                echo $html;
                            ?>
                            <div class="news-item" v-else v-for="(item,index) in list" v-cloak>
                            <div class="news-item-date b2-radius"><p v-html="item[0]['date']['date']"></p></div>
                                <ul>
                                    <li v-for="(_item,_index) in item" :id="'news-item-'+_item.id">
                                        <div class="news-item-content">
                                            <div class="news-item-h">
                                                <div>
                                                    <h2 class="anhover" v-if="_item['title']"><a :href="_item['link']" v-html="_item['title']" target="_blank"></a></h2>
                                                    <div class="news-item-header">
                                                        <span>{{_item['date']['time']}}</span>
                                                        <span><?php echo __('作者：','b2'); ?><a :href="_item['author']['link']" target="_blank">{{_item['author']['name']}}</a></span>
                                                    </div>
                                                    <div>
                                                        <p class="b2-hover"><span v-text="_item['desc']"></span><a :href="_item['from']" target="_blank" rel="nofollow" v-if="_item['from']" class="item-from" rel="nofollow"><?php echo b2_get_icon('b2-external-link-line').__('原文连接','b2'); ?></a></p>
                                                    </div>
                                                </div>
                                                <?php echo b2_get_img(array(
                                                    'src_data'=>':src="_item[\'img\']"',
                                                    'class'=>array('news-item-img','b2-radius'),
                                                    'pic_data'=>' v-if="_item[\'img\']"',
                                                    'source_data'=>':srcset="_item[\'img_webp\']"'
                                                ));?>
                                            </div>
                                            <div class="new-meta">
                                                <div class="new-meta-left">
                                                    <p class="news-vote-up" @click="vote(_item['id'],'up',index,_index)">
                                                        <button :class="list !== '' && _item['vote']['up_isset'] ? 'isset' : ''"><?php echo b2_get_icon('b2-arrow-drop-up-fill'); ?><b><?php echo $vote_up; ?></b><b v-cloak v-text="_item['vote']['up']"></b></button>
                                                    </p>
                                                    <p class="news-vote-down" @click="vote(_item['id'],'down',index,_index)">
                                                        <button :class="list !== '' && _item['vote']['down_isset'] ? 'isset' : ''"><?php echo b2_get_icon('b2-arrow-drop-down-fill'); ?><b><?php echo $vote_down; ?></b><b v-cloak v-text="_item['vote']['down']"></b></button>
                                                    </p>
                                                    <span class="new-tag anhover" v-if="_item['tag']"><a target="_blank" :href="_item['tag']['link']"><?php echo b2_get_icon('b2-flashlight-fill'); ?>{{_item['tag']['name']}}</a></span>
                                                </div>
                                                <div class="new-meta-right"><?php echo __('分享到','b2'); ?><span class="new-weibo " @click="openWin(_item['share']['weibo'],'weibo',index,_index)" data-title="<?php echo __('分享到微博','b2'); ?>"><?php echo b2_get_icon('b2-weibo-line'); ?></span><span class="new-weixin" data-title="<?php echo __('分享到微信','b2'); ?>"><?php echo b2_get_icon('b2-wechat-line'); ?></span><span class="new-qq" data-title="<?php echo __('分享到QQ','b2'); ?>" @click="openWin(_item['share']['qq'],'qq',index,_index)"><?php echo b2_get_icon('b2-qq-line'); ?></span></div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        </div>
                    <div class="b2-pagenav <?php echo $data['pages'] <= 1 ? 'b2-hidden-always' : ''; ?>" data-max="<?php echo $data['pages']; ?>">
                        <page-nav ref="goldNav" paged="<?php echo $paged; ?>" navtype="json" pages="<?php echo $data['pages']; ?>" type="m" :box="selecter" :opt="opt" :api="api" url="<?php echo  get_category_link($term_id); ?>" title="<?php echo $name; ?>" @return="get"></page-nav>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php 
        get_sidebar(); 
    ?>
</div>
<?php
get_footer();