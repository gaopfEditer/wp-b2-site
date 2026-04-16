<?php
$user_id =  get_query_var('author');

$paged = get_query_var('b2_paged') ? get_query_var('b2_paged') : 1;

//$data = Newsflashes::get_newsflashes_data($paged,0,$user_id);
$newsflashes_slug = b2_get_option('normal_custom','custom_newsflashes_link');
?>

<div id="author-newsflashes" ref="authornewsflasheslist" data-paged="<?php echo $paged; ?>">
    <div class="button empty b2-loading empty-page text box b2-radius" v-show="loading"></div>
    <div class="box b2-radius b2-pd" v-if="!loading && !empty" v-cloak>
        <div class="news-item" v-for="item in list">
            <div class="news-item-date b2-color" v-html="item[0]['date']['date']"></div>
            <ul>
                <li :id="'news-item-'+_item['id']" v-for="_item in item">
                    <div class="news-item-header">
                        <b v-html="_item['date']['time']"></b>
                        <span class="new-tag anhover" v-if="_item['tag']"><a target="_blank" :href="_item['tag']['link']" v-html="_item['tag']['name']"></a></span>
                    </div>
                    <div class="news-item-content">
                        <h2 class="anhover"><a :href="_item['link']" v-html="_item['title']"></a></h2>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div v-show="empty && !loading" v-cloak class="box b2-radius"><?php echo B2_EMPTY; ?></div>
    <div class="author-page-nav box b2-radius b2-pd mg-t" ref="AuthorSettings" v-show="list !== '' && pages > 1" v-cloak>
        <page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="options" :api="api" url="<?php echo get_author_posts_url($user_id).'/'.$newsflashes_slug; ?>" title="<?php echo b2_get_option('normal_custom','custom_newsflashes_name'); ?>" @return="get"></page-nav>
    </div>
</div>