<?php
if(!current_user_can('administrator')) wp_die('您无权访问此页');
get_header();
?>
<style>
    .update-box .box > div{
        padding:20px;
        border-bottom:1px solid #ccc
    }
    .update-box .box > div h2{
        margin-bottom:20px;
        font-weight:700
    }
    .update-box .box > div p{
        margin-top:20px
    }
</style>
<div class="update-box">
<div class="box wrapper">
        <div>
            <h2>2.7.0升级</h2>
            <button :disabled="updateCats.status == 'success' || updateCats.status == 'go' || true" @click="update('updateCats')">
                <span v-if="updateCats.status == 'success'" v-cloak>升级成功</span>
                <span v-else-if="updateCats.status == 'go'" v-cloak>升级中</span>
                <span v-else>升级</span>
            </button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>2.6.9以下版本升级首页模块设置（2.6.9版本以上请勿操作）</h2>
            <button :disabled="indexModules.status == 'success' || indexModules.status == 'go' || true" @click="update('indexModules')">
                <span v-if="indexModules.status == 'success'" v-cloak>升级成功</span>
                <span v-else-if="indexModules.status == 'go'" v-cloak>升级中</span>
                <span v-else>升级</span>
            </button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>升级评论点赞</h2>
            <button :disabled="commentVote.status == 'success' || commentVote.status == 'go' || true" @click="update('commentVote')">
                <span v-if="commentVote.status == 'success'" v-cloak>升级成功</span>
                <span v-else-if="commentVote.status == 'go'" v-cloak>升级中</span>
                <span v-else>升级</span>
            </button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>升级文章点赞</h2>
            <button :disabled="postVote.status == 'success' || postVote.status == 'go' || true" @click="update('postVote')">
                <span v-if="postVote.status == 'success'" v-cloak>升级成功</span>
                <span v-else-if="postVote.status == 'go'" v-cloak>升级中</span>
                <span v-else>升级</span>
            </button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>升级文章内容购买</h2>
            <button disabled>升级</button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>升级文章打赏</h2>
            <button disabled>升级</button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>升级用户关注</h2>
            <button disabled>升级</button>
        </div>
    </div>
    <div class="box wrapper">
        <div>
            <h2>升级文章收藏</h2>
            <button disabled>升级</button>
        </div>
    </div>
</div>

<?php
get_footer();