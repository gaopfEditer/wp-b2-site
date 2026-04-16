<?php
use B2\Modules\Common\Newsflashes;
$post_id = get_the_id();
$data = Newsflashes::get_newsflashes_item_data($post_id);