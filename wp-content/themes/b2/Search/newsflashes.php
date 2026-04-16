<?php
    use B2\Modules\Common\Newsflashes;
    $key = get_search_query();
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $vote_up = b2_get_option('newsflashes_main','newsflashes_vote_up_text');
    $vote_down = b2_get_option('newsflashes_main','newsflashes_vote_down_text');
?>

    <div class="hidden-line box b2-radius">
        <div class="document-category document-home-left">
            <div class="news-list-box">
                <?php 
                    $data = Newsflashes::get_newsflashes_data($paged,null,null,$key);
                    
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
                                                    <p class="b2-hover">'.$_v['content'].($_v['from'] ? '<a href="'.esc_url($_v['from']).'" target="_blank" rel="nofollow">'.b2_get_icon('b2-external-link-line').__('原文连接','b2').'</a>' : '').'</p>
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
                    }else{
                        echo '<div class="box">'.B2_EMPTY.'</div>';
                    }

                    echo $html;
                ?>
            </div>
        </div>
    </div>
    
<?php if($data['pages'] > 1){ ?>
    <div class="b2-pagenav post-nav">
        <?php echo b2_pagenav(array('pages'=>$data['pages'],'paged'=>$paged)); ?>
    </div>
<?php } ?>