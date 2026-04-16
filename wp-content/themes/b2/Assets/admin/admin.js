var phoneType = jQuery('#phome_select').find(':selected').val();
if(phoneType == 'yunpian'){
		jQuery('#yunpian-sms').show();
}else if(phoneType == 'juhe'){
		jQuery('#juhe-sms').show();
}else if(phoneType == 'aliyun'){
		jQuery('#aliyun-sms').show();
}else if(phoneType == 'others'){
	jQuery('#others-sms').show();
}else if(phoneType == 'submail'){
	jQuery('#submail-sms').show();
}else if(phoneType == 'tencent'){
	jQuery('#tencent-sms').show();
}else{
	jQuery('#zhongzheng-sms').show();
}

jQuery('#phome_select').on('change', function() {
		var val = jQuery(this).find(':selected').val();
		var array = ['yunpian','juhe','aliyun','zhongzheng','others','submail','tencent'];
		array.forEach(function(_val){
				if(val == _val){
						jQuery('#'+_val+'-sms').show();
				}else{
						jQuery('#'+_val+'-sms').hide();
				}
		});
})

var checkType = jQuery('#check_type').find(':selected').val();
if(checkType == 'tel' || checkType == 'telandemail'){
	jQuery('#sms-select').show()
}else if(checkType == 'luo'){
	jQuery('#luosimao').show()
}

jQuery('#check_type').on('change', function() {
	var val = jQuery(this).find(':selected').val();
	if(val == 'tel' || val == 'telandemail'){
		jQuery('#sms-select').show()
		jQuery('#luosimao').hide()
	}else if(val == 'luo'){
		jQuery('#sms-select').hide()
		jQuery('#luosimao').show()
	}else{
		jQuery('#sms-select').hide()
		jQuery('#luosimao').hide()
	}
})



var b2distribution = jQuery('#distribution_conditions').find(':selected').val();
if(b2distribution === '2'){
	jQuery('.cmb2-id-distribution-user-lv').show()
}

jQuery('#distribution_conditions').on('change', function() {
	var val = jQuery(this).find(':selected').val();
	jQuery('.cmb2-id-distribution-user-lv').hide()

	if(val ==='2'){
		jQuery('.cmb2-id-distribution-user-lv').show()
	}
})

var circle_settings = jQuery('#b2_circle_topic_role_open').find(':selected').val();
if(circle_settings == 1){
	jQuery('#circle-topic-custom-settings').show()
}
jQuery('#b2_circle_topic_role_open').on('change', function() {
	var val = jQuery(this).find(':selected').val();
	jQuery('#circle-topic-custom-settings').hide()

	if(val ==='1'){
		jQuery('#circle-topic-custom-settings').show()
	}
})

function changeRadioChange(){
	jQuery('.model-picked').find('input[type="radio"]').on('change',function(){
		changeRaido();
	})
}

function changeRaido(){
	jQuery('.model-picked').each(function(index,el){
		var picked = jQuery(this).find('input[type="radio"]:checked').val();

		jQuery(this).parents('.cmb-repeatable-grouping').find('.set-hidden').hide();

		if(picked){
			jQuery(this).parents('.cmb-repeatable-grouping').find('.'+picked+'-module').show();
		}
	})	
}

changeRadioChange();
changeRaido();

jQuery('.cmb-add-group-row').on( 'click',function() {
	setTimeout(function(){
		changeRadioChange();
		changeRaido();
	},500)
	downupaction();
})

function downupaction(){
	setTimeout(function(){
		jQuery('.button-secondary').on( 'click',function() {
			console.log(111);
			setTimeout(function(){
			changeRadioChange();
			changeRaido();
			b2Lvs()
			})
	})
	},500)
}
downupaction();

var shop_settings = jQuery('#zrz_shop_multi').find(':selected').val();
if(shop_settings == 0){
	jQuery('#shop_count_metabox').show()
	jQuery('#shop_multi_metabox').hide()
	jQuery('#shop_price_metabox').show()
	
}else{
	jQuery('#shop_count_metabox').hide()
	jQuery('#shop_multi_metabox').show()
	jQuery('#shop_price_metabox').hide()
}
jQuery('#zrz_shop_multi').on('change', function() {
	var val = jQuery(this).find(':selected').val();
	

	if(val == 0){
		jQuery('#shop_count_metabox').show()
		jQuery('#shop_multi_metabox').hide()
		jQuery('#shop_price_metabox').show()
	}else{
		jQuery('#shop_count_metabox').hide()
		jQuery('#shop_multi_metabox').show()
		jQuery('#shop_price_metabox').hide()
	}

	shop_settings = val
})

//商品设置
var shopType = jQuery('#zrz_shop_type').find(':selected').val();
if(shopType == 'normal'){
	if(shop_settings == 0){
		jQuery('#shop_price_metabox').show();
	}else{
		jQuery('#shop_multi_metabox').show()
	}
		
}else if(shopType == 'lottery'){
		jQuery('#shop_lottery').show();
		jQuery('#shop_price_metabox').hide();
	jQuery('#shop_multi_metabox').hide()
	jQuery('.cmb2-id-zrz-shop-multi').hide()
}else if(shopType == 'exchange'){
		jQuery('#shop_exchange').show();
		jQuery('#shop_price_metabox').hide();
	jQuery('#shop_multi_metabox').hide()
	jQuery('.cmb2-id-zrz-shop-multi').hide()
	
}

jQuery('#zrz_shop_type').on('change', function() {
	var val = jQuery(this).find(':selected').val();
	jQuery('#shop_price_metabox').hide();
	jQuery('#shop_multi_metabox').hide()
	jQuery('#shop_lottery').hide();
	jQuery('#shop_exchange').hide();
	jQuery('.cmb2-id-zrz-shop-multi').hide()
	if(val == 'normal'){
		if(shop_settings == 0){
			jQuery('#shop_price_metabox').show();
		}else{
			jQuery('#shop_multi_metabox').show()
		}
		jQuery('.cmb2-id-zrz-shop-multi').show()
	}else if(val == 'lottery'){
		jQuery('#shop_lottery').show();
	}else if(val == 'exchange'){
		jQuery('#shop_exchange').show();
	}
})

//小工具颜色选择
// jQuery(document).on( 'cmb_pre_init', function(evt, cmb) {
// 	var jQuerypicker = jQuery('#<FIELD_ID>');
// 	var changeCallback = function(event, obj) {
// 		console.log('picker changed to:', obj.color.toString());
// 	};
// 	var args = jQuerypicker.data('colorpicker');
// 	if ( args ) {
// 		args.change = args.change || changeCallback;
// 	} else {
// 		args = {
// 			change: changeCallback
// 		}
// 	}
// 	jQuerypicker.data('colorpicker', args);
// });

//文章页面视频选择
var postStyle = jQuery('input[name=b2_single_post_style]:checked').val();

if(postStyle == 'post-style-5'){
	jQuery('#post-style-5-box').show();
}else{
	jQuery('#post-style-5-box').hide();
}

jQuery('input[name=b2_single_post_style]').on('change', function() {
		var val = jQuery(this).val();
		console.log(val);
		if(val == 'post-style-5'){
			jQuery('#post-style-5-box').show();
		}else{
			jQuery('#post-style-5-box').hide();
		}
})

//文章页面视频权限选择
var phoneType = jQuery('#b2_single_post_video_role').find(':selected').val();
	if(phoneType == 'money'){
			jQuery('.cmb2-id-b2-single-post-video-money').show();
	}else if(phoneType == 'credit'){
			jQuery('.cmb2-id-b2-single-post-video-credit').show();
	}else if(phoneType == 'roles'){
			jQuery('.cmb2-id-b2-single-post-video-roles').show();
	}else if(phoneType == 'none'){
		jQuery('.cmb2-id-b2-single-post-video-money').hide();
		jQuery('.cmb2-id-b2-single-post-video-credit').hide();
		jQuery('.cmb2-id-b2-single-post-video-roles').hide();
	}

	jQuery('#b2_single_post_video_role').on('change', function() {
			var val = jQuery(this).find(':selected').val();
			var array = ['money','credit','roles','none','login','comment'];
			array.forEach(function(_val){
					if(val == _val){
							jQuery('.cmb2-id-b2-single-post-video-'+_val).show();
					}else{
							jQuery('.cmb2-id-b2-single-post-video-'+_val).hide();
					}
			});
	})

//文章页面隐藏内容查看权限
var phoneType = jQuery('#b2_post_reading_role').find(':selected').val();
if(phoneType == 'money'){
		jQuery('.cmb2-id-b2-post-money').show();
}else if(phoneType == 'credit'){
		jQuery('.cmb2-id-b2-post-credit').show();
}else if(phoneType == 'roles'){
		jQuery('.cmb2-id-b2-post-roles').show();
}else if(phoneType == 'none'){
	jQuery('.cmb2-id-b2-post-money').hide();
	jQuery('.cmb2-id-b2-post-credit').hide();
	jQuery('.cmb2-id-b2-post-roles').hide();
}

jQuery('#b2_post_reading_role').on('change', function() {
		var val = jQuery(this).find(':selected').val();
		var array = ['money','credit','roles','none','login','comment'];
		array.forEach(function(_val){
				if(val == _val){
						jQuery('.cmb2-id-b2-post-'+_val).show();
				}else{
						jQuery('.cmb2-id-b2-post-'+_val).hide();
				}
		});
})

var checkType_alipay = jQuery('#alipay').find(':selected').val();
let alipay_type = ['alipay_normal','xunhu','alipay_hupijiao','mapay','xorpay','payjs','yipay','pay020','suibian'];
alipay_type.forEach(e => {
	if(checkType_alipay === e){
		jQuery('#'+e).show()
	}
});

var b2_paypal = jQuery('#paypal_open').find(':selected').val();
if(b2_paypal == 1){
	jQuery('#paypal_normal').show()
}else{
	jQuery('#paypal_normal').hide()
}

jQuery('#paypal_open').on('change', function() {
	let val = jQuery(this).find(':selected').val();

	if(val == 1){
		jQuery('#paypal_normal').show()
	}else{
		jQuery('#paypal_normal').hide()
	}
})

var checkType_wecatpay = jQuery('#wecatpay').find(':selected').val();
let wecatpay_type = ['wecatpay_normal','xunhu','wecatpay_hupijiao','mapay','xorpay','payjs','yipay','pay020','suibian'];
wecatpay_type.forEach(e => {
	if(checkType_wecatpay === e){
		jQuery('#'+e).show()
	}
});

jQuery('#alipay').on('change', function() {
	let val = jQuery(this).find(':selected').val();
	let w = jQuery('#wecatpay').find(':selected').val();
	alipay_type.forEach(e => {
		if(val === e){
			jQuery('#'+val).show()
		}else if(w !== e){
			jQuery('#'+e).hide()
		}
	});
})

jQuery('#wecatpay').on('change', function() {
	let val = jQuery(this).find(':selected').val();
	let a = jQuery('#alipay').find(':selected').val();
	wecatpay_type.forEach(e => {
		if(val === e){
			jQuery('#'+val).show()
		}else if(a !== e){
			jQuery('#'+e).hide()
		}
	});
})

var shopxuni = jQuery('#shop_xuni_type').find(':selected').val();
if(shopxuni == 'html'){
		jQuery('.cmb2-id-shop-xuni-html-resout').show();
}else if(shopxuni == 'cards'){
		jQuery('.cmb2-id-shop-xuni-cards-resout').show();
}

jQuery('#shop_xuni_type').on('change', function() {
		var val = jQuery(this).find(':selected').val();
		jQuery('.cmb2-id-shop-xuni-cards-resout').hide();
		jQuery('.cmb2-id-shop-xuni-html-resout').hide();
		if(val == 'html'){
			jQuery('.cmb2-id-shop-xuni-html-resout').show();
		}else if(val == 'cards'){
			jQuery('.cmb2-id-shop-xuni-cards-resout').show();
		}
})

var xunitype = jQuery('#zrz_shop_commodity').find(':selected').val();
if(xunitype == '0'){
		jQuery('#shop_xuni').show();
}

jQuery('#zrz_shop_commodity').on('change', function() {
		var val = jQuery(this).find(':selected').val();

		if(val == '0'){
			jQuery('#shop_xuni').show();
		}else{
			jQuery('#shop_xuni').hide();
		}
})

var b2chat = jQuery('#aside_chat_type').find(':selected').val();
if(b2chat == 'none'){
	jQuery('#aside_chat_qq_d').hide();
	jQuery('#aside_chat_crisp_d').hide();
	jQuery('#aside_chat_dmsg_d').hide();
}else if(b2chat == 'qq'){
	jQuery('#aside_chat_qq_d').show();
	jQuery('#aside_chat_crisp_d').hide();
	jQuery('#aside_chat_dmsg_d').hide();
}else if(b2chat == 'crisp'){
	jQuery('#aside_chat_qq_d').hide();
	jQuery('#aside_chat_crisp_d').show();
	jQuery('#aside_chat_dmsg_d').hide();
}else if(b2chat == 'dmsg'){
	jQuery('#aside_chat_qq_d').hide();
	jQuery('#aside_chat_crisp_d').hide();
	jQuery('#aside_chat_dmsg_d').show();
}else{
	jQuery('#aside_chat_qq_d').hide();
	jQuery('#aside_chat_crisp_d').hide();
	jQuery('#aside_chat_dmsg_d').hide();
}

jQuery('#aside_chat_type').on('change', function() {
		var val = jQuery(this).find(':selected').val();

		if(val == 'none'){
			jQuery('#aside_chat_qq_d').hide();
			jQuery('#aside_chat_crisp_d').hide();
			jQuery('#aside_chat_dmsg_d').hide();
		}else if(val == 'qq'){
			jQuery('#aside_chat_qq_d').show();
			jQuery('#aside_chat_crisp_d').hide();
			jQuery('#aside_chat_dmsg_d').hide();
		}else if(val == 'crisp'){
			jQuery('#aside_chat_qq_d').hide();
			jQuery('#aside_chat_crisp_d').show();
			jQuery('#aside_chat_dmsg_d').hide();
		}else if(val == 'dmsg'){
			jQuery('#aside_chat_qq_d').hide();
			jQuery('#aside_chat_crisp_d').hide();
			jQuery('#aside_chat_dmsg_d').show();
		}else{
			jQuery('#aside_chat_qq_d').hide();
			jQuery('#aside_chat_crisp_d').hide();
			jQuery('#aside_chat_dmsg_d').hide();
		}
})

//拖动排序
function b2Lvs(){
	var lvs = document.querySelectorAll('#user_lv_group_repeat .cmb-repeatable-grouping');

	if(lvs){
		for (let i = 0; i < lvs.length; i++) {
			
			lvs[i].querySelector('.cmb-group-title').innerText = lvs[i].querySelector('.cmb-repeat-group-field input').value+'(lv'+i+')'
			
			console.log(lvs[i].querySelector('.cmb-group-title').innerHtml)
			jQuery('#user_lv_group_'+i+'_name').on('input', function(val) {
				lvs[i].querySelector('.cmb-group-title').innerText = val.target.value+'(lv'+i+')'
			});
		}
	}

	var vips = document.querySelectorAll('#user_vip_group_repeat .cmb-repeatable-grouping');
	if(vips){
		for (let i = 0; i < vips.length; i++) {
			
			vips[i].querySelector('.cmb-group-title').innerText = vips[i].querySelector('.cmb-repeat-group-field input').value+'(vip'+i+')'

			jQuery('#user_vip_group_'+i+'_name').on('input', function(val) {
				vips[i].querySelector('.cmb-group-title').innerText = val.target.value+'(vip'+i+')'
			});
		}
	}

	var multi = document.querySelectorAll('#multi_group_repeat .cmb-repeatable-grouping');

	if(multi){
		for (let i = 0; i < multi.length; i++) {
			
			multi[i].querySelector('.cmb-group-title').innerText = multi[i].querySelector('.cmb-repeat-group-field input').value
			

			jQuery('#multi_group_'+i+'_title').on('input', function(val) {
				multi[i].querySelector('.cmb-group-title').innerText = val.target.value
			});
		}
	}

	var index = document.querySelectorAll('#index_group_repeat .cmb-repeatable-grouping');
	if(index){
		for (let i = 0; i < index.length; i++) {
			index[i].querySelector('.cmb-group-title').innerText = index[i].querySelector('.cmb-repeat-group-field input').value
			
			jQuery('#index_group_'+i+'_title').on('input', function(val) {
				index[i].querySelector('.cmb-group-title').innerText = val.target.value
			});

			if(index[i].querySelector('#index_group_'+i+'_short_code')){
				index[i].querySelector('#index_group_'+i+'_short_code').value = '[b2_index_module key='+index[i].querySelector('#index_group_'+i+'_key').value+']'

				jQuery('#index_group_'+i+'_key').on('input', function(val) {
					index[i].querySelector('#index_group_'+i+'_short_code').value = '[b2_index_module key='+val.target.value+']'
				});
			}

			var posttype = jQuery(index[i]).find('[name="index_group['+i+'][post_type]"]:checked').val();

			if(posttype == 'post-3'){
				jQuery(index[i]).find('.list-width').show();
				jQuery(index[i]).find('.list-width-normal').hide();
			}else{
				jQuery(index[i]).find('.list-width').hide();
				jQuery(index[i]).find('.list-width-normal').show();
			}

			if(posttype == 'post-6'){
				jQuery(index[i]).find('.custom-key').show();
			}else{
				jQuery(index[i]).find('.custom-key').hide();
			}
		
			jQuery('[name="index_group['+i+'][post_type]"]').on('change', function() {

				var val = jQuery(this).val();

				if(val == 'post-3'){
					jQuery(index[i]).find('.list-width').show();
					jQuery(index[i]).find('.list-width-normal').hide();
				}else{
					jQuery(index[i]).find('.list-width').hide();
					jQuery(index[i]).find('.list-width-normal').show();
				}

				if(val == 'post-6'){
					jQuery(index[i]).find('.custom-key').show();
				}else{
					jQuery(index[i]).find('.custom-key').hide();
				}

			})
			
		}
	}
			
	var video =document.querySelectorAll('#b2_single_post_video_group_repeat .cmb-repeatable-grouping');
	if(video){
		for (let i = 0; i < video.length; i++) {
			video[i].querySelector('.cmb-group-title').innerText = video[i].querySelector('#b2_single_post_video_group_'+i+'_title').value
			
			jQuery('#b2_single_post_video_group_'+i+'_title').on('input', function(val) {
				video[i].querySelector('.cmb-group-title').innerText = val.target.value
			});
		}
	}

	var download =document.querySelectorAll('#b2_single_post_download_group_repeat .cmb-repeatable-grouping');
	if(download){
		for (let i = 0; i < download.length; i++) {
			download[i].querySelector('.cmb-group-title').innerText = download[i].querySelector('#b2_single_post_download_group_'+i+'_name').value
			
			jQuery('#b2_single_post_download_group_'+i+'_name').on('input', function(val) {
				download[i].querySelector('.cmb-group-title').innerText = val.target.value
			});
		}
	}

	var downloads =document.querySelectorAll('#downloads_group_repeat .cmb-repeatable-grouping');
	if(downloads){
		for (let i = 0; i < downloads.length; i++) {
			downloads[i].querySelector('.cmb-group-title').innerText = downloads[i].querySelector('#downloads_group_'+i+'_title').value
			
			jQuery('#downloads_group_'+i+'_title').on('input', function(val) {
				downloads[i].querySelector('.cmb-group-title').innerText = val.target.value
			});
		}
	}

	var catfliter =document.querySelectorAll('#fliter_group_repeat .cmb-repeatable-grouping');
	if(catfliter){
		for (let i = 0; i < catfliter.length; i++) {
			catfliter[i].querySelector('.cmb-group-title').innerText = catfliter[i].querySelector('#fliter_group_'+i+'_title').value
			
			jQuery('#fliter_group_'+i+'_title').on('input', function(val) {
				catfliter[i].querySelector('.cmb-group-title').innerText = val.target.value
			});
		}
	}

	var custompay = document.querySelectorAll('#b2_pay_custom_group_repeat .cmb-repeatable-grouping');
	if(custompay){
		for (let i = 0; i < custompay.length; i++) {
			custompay[i].querySelector('.cmb-group-title').innerText = custompay[i].querySelector('#b2_pay_custom_group_'+i+'_name').value
			
			jQuery('#b2_pay_custom_group_'+i+'_name').on('input', function(val) {
				custompay[i].querySelector('.cmb-group-title').innerText = val.target.value
			});
		}
	}

	var cpayselect = jQuery('#b2_pay_custom_group_repeat').find('.cmb2_select')
	if(cpayselect.length > 0){
		for (let i = 0; i < cpayselect.length; i++) {

			let val = jQuery(cpayselect[i]).find(':selected').val()
	
			var parent = jQuery(cpayselect[i]).parent().parent().parent().parent(),
			id = parent.attr('data-iterator')
			console.log(id)
	
			if(val == 'textarea' || val == 'text'){
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-value').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-count').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-value').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-count').hide()
			}
	
			if(val == 'file'){
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-type').show()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-count').show()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-type').show()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-count').show()
	
			}else{
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-count').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-count').hide()
			}
	
			if(val == 'radio' || val == 'checkbox' || val == 'select'){
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-value').show()
				parent.find('.cmb2-id-b2-pay-custom-group-1-value').show()
			}else{
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-value').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-value').hide()
			}
	
			
		}
	
		jQuery('#b2_pay_custom_group_repeat .cmb2_select').on('change', function() {
			var val = jQuery(this).find(':selected').val();
	
			var parent = jQuery(this).parent().parent().parent().parent(),
			id = parent.attr('data-iterator')
			
			if(val == 'file'){
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-type').show()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-count').show()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-type').show()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-count').show()
			}else{
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-count').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-count').hide()
			}
	
			if(val == 'radio' || val == 'checkbox' || val == 'select'){
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-value').show()
				parent.find('.cmb2-id-b2-pay-custom-group-1-value').show()
			}else{
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-value').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-value').hide()
			}
	
			if(val == 'textarea' || val == 'text'){
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-value').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-'+id+'-file-count').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-value').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-type').hide()
				parent.find('.cmb2-id-b2-pay-custom-group-1-file-count').hide()
			}
	
		})
	}
	

}
b2Lvs()
var tax =jQuery('#b2_group_repeat');

if(tax){
	var posttype = tax.find('[name="b2_group[0][post_type]"]:checked').val();
	
	if(posttype == 'post-3'){
		tax.find('.list-width').show();
		tax.find('.list-width-normal').hide();
	}else{
		tax.find('.list-width').hide();
		tax.find('.list-width-normal').show();
	}

	if(posttype == 'post-6'){
		tax.find('.custom-key').show();
	}else{
		tax.find('.custom-key').hide();
	}

	jQuery('[name="b2_group[0][post_type]').on('change', function() {

		var val = jQuery(this).val();

		if(val == 'post-3'){
			tax.find('.list-width').show();
			tax.find('.list-width-normal').hide();
		}else{
			tax.find('.list-width').hide();
			tax.find('.list-width-normal').show();
		}

		if(val == 'post-6'){
			tax.find('.custom-key').show();
		}else{
			tax.find('.custom-key').hide();
		}
	})
}


function modulessortable(){
	// 	jQuery( "#index_group_repeat" ).sortable({
	// 	connectWith: ".cmb-row cmb-repeatable-grouping"
	//   });

	  jQuery( ".cmb-repeatable-group" ).not('#user_lv_group_repeat').not('#user_vip_group_repeat').sortable({
		connectWith: ".cmb-row cmb-repeatable-grouping",
		cancel: ".cmb-group-description",
		delay: 150,
		handle: ".cmbhandle-title",
		axis: "y",
		dropOnEmpty: false
	  });

	//   jQuery( "#b2_single_post_download_group_repeat" ).sortable({
	// 	connectWith: ".cmb-row cmb-repeatable-grouping"
	//   });

	  jQuery('.cmb-repeatable-grouping .cmb-field-list').mousedown(function(e){
		e.stopPropagation();

	  }).mouseup(function(e){
		e.stopPropagation();

	  })

	//   jQuery('.cmb-repeatable-grouping .cmb-field-list').on('touchstart',function(e){
	// 	e.stopPropagation();

	//   }).on('touchend',function(e){
	// 	e.stopPropagation();

	//   })

}
modulessortable()

	jQuery( '#index_group_repeat' )
	.on( 'cmb2_remove_row', function() {
		setTimeout(() => {
			b2Lvs()
			modulessortable()
			downloadTemplate()
			b2getdogcloudvideo()
		}, 500);
	});

	// fieldTable.on( 'cmb2_add_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
	// });

	// var lv  = jQuery( '#user_lv_group_repeat' ) ;
	// lv.on( 'cmb2_remove_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });

	// lv.on( 'cmb2_add_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });

	// var vip  = jQuery( '#user_vip_group_repeat' ) ;
	// vip.on( 'cmb2_remove_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });
	// vip.on( 'cmb2_add_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });

	// var video  = jQuery( '#b2_single_post_video_group_repeat' ) ;
	// video.on( 'cmb2_remove_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });
	// video.on( 'cmb2_add_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });

	// var download  = jQuery( '.cmb-repeatable-group' ) ;
	// download.on( 'cmb2_remove_row', function() {
	// 	setTimeout(() => {
	// 		b2Lvs()
	// 	}, 500);
		
	// });
	jQuery( '.cmb-repeatable-group' ).on( 'cmb2_add_row', function() {
		setTimeout(() => {
			b2Lvs()
			modulessortable()
			downloadTemplate()
			b2getdogcloudvideo()
		}, 500);
	});

var xunitype = jQuery('#b2_dark_room').find(':selected').val();
if(xunitype == 1){
		jQuery('#dark-room').show();
}

jQuery('#b2_dark_room').on('change', function() {
		var val = jQuery(this).find(':selected').val();

		if(val == 1){
			jQuery('#dark-room').show();
		}else{
			jQuery('#dark-room').hide();
		}
})


function b2getFilename(event){

	let file = event.target.files[0]

	var formData = new FormData();


	formData.append('file',file,file.name)

	jQuery.ajax({
		url: cmb2_l10.ajaxurl+'?action=b2_insert_settings',
		type: 'POST',
		data: formData,
		success: function (data) {
			alert('导入成功')
			document.querySelector('#inputsettings').value = ''
		},
		cache: false,
		contentType: false,
		processData: false
	});
	
}

var b2multi = new Vue({
	el:'#shop_multi_metabox',
	data:{
		select:'none',
		key:'',
		keys:[],
		values:[],
		skuList:[],
		attrs:[],
		skuLocked:false,
		imageFrame:''
	},
	mounted(){

		if(typeof b2shoptemplate == 'undefined') return
		
		if(b2shoptemplate.default){
			this.setJson(b2shoptemplate.default)
		}

		jQuery('#b2_multi_box').on('input',(ev)=>{
			this.setJson(ev.target.value,true)
		});

		this.skuList = this.cartesian(this.values)
	},
	computed:{
		data(){
			let data = {
				keys:this.keys,
				values:this.values,
				skuList:this.skuList,
				attrs:this.attrs
			}
			document.querySelector('#b2_multi_box').value = JSON.stringify(data)
		}
	},
	watch:{
		select(val){
			if(val !== 'none'){
				this.keys = b2shoptemplate.templates[val]['keys']
				this.values = b2shoptemplate.templates[val]['values']
				this.skuLocked = false
				this.skuList = this.cartesian(this.values)
				console.log(this.skuList)
			}
		},
		skuList(){
			if(this.skuLocked == true) return
			this.skuLocked = false
			let arr = []
			this.skuList.forEach((val,index)=>{
				arr[index] = {
					price:'',
					dprice:'',
					uprice:'',
					credit:'',
					count:'',
					sell:'',
					img:'',
					imgId:''
				}
			})
			this.attrs = arr

		}
	},
	methods:{
		openLibrary(i){
			if(!this.imageFrame){
				this.imageFrame = wp.media({
					title: '选择图片',
					multiple : false,
					library : {
						type : 'image',
					},
					button: {text: '选择'}
				});
				
			}

			// let selection =  this.imageFrame.state().get('selection');
			// selection.add( [] );
			this.imageFrame.off('select')
			this.imageFrame.off('open')

			this.imageFrame.on('select',()=>{
				let selection =  this.imageFrame.state().get('selection');
				let _attachment = selection.first().toJSON();

				if(i == 'all'){
					let url = {
						target:{
							value:_attachment.url
						}
					}
					this.batch('img',url)

					let id = {
						target:{
							value:_attachment.id
						}
					}
					this.batch('imgId',id)
				}else{
					this.attrs[i].imgId = _attachment.id
					this.attrs[i].img = _attachment.url
				}

				
			});

			// image_frame.on('close',()=>{
			// 	var selection =  image_frame.state().get('selection');
			// 	console.log(selection)
			// });

			this.imageFrame.on('open',()=>{

				let selection =  this.imageFrame.state().get('selection');

				let index = i

				if(i == 'all'){
					index = 0
				}

				if(this.attrs.length > 0 && this.attrs[index].imgId){
					let attachment = wp.media.attachment(this.attrs[index].imgId);
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				}
				
			});
			this.imageFrame.open();
		},
		setJson(val,action){
			let data = JSON.parse(val)
			this.keys = data.keys
			this.values = data.values
			this.skuList = data.skuList
			this.attrs = data.attrs

			if(!action){
				document.querySelector('#b2_multi_box').value = val
			}
			
			this.skuLocked = true
		},
		batch(type,event){

			this.attrs.forEach((val,index)=>{
				this.attrs[index][type] = event.target.value
			})

		},
		addKey(){
			this.skuLocked = false
			this.keys.push(this.key)
			this.key = ''
			this.values.push(['-'])
			this.skuList = this.cartesian(this.values)
		},
		addVaule(index,event){
			this.skuLocked = false
			if(event.target.previousElementSibling.value){

				if(!this.values[index] || this.values[index][0] == '-'){
					this.$set(this.values,index,[])
				}

				if(!this.values[index].includes(event.target.previousElementSibling.value)){
					this.values[index].push(event.target.previousElementSibling.value)

					event.target.previousElementSibling.value = ''
					this.skuList = this.cartesian(this.values)
				}
			}
		},
		remove(index){
			this.skuLocked = false
			var r = confirm("确定要删除这个规格吗？");
			if (r == true) {
				this.keys.splice(index,1)
				this.values.splice(index,1)

				this.skuList = this.cartesian(this.values)
			}
		},
		removeKey(index,i){
			this.skuLocked = false
			var r = confirm("确定要删除这个值吗？");
			if (r == true) {
				this.values[index].splice(i,1)
				
				this.skuList = this.cartesian(this.values)
				console.log(this.skuList)
			}
		},
		stopSubmit(event){
			if(event.keyCode==13) return false
		},
		rebuildList(){
			
		},
		cartesian(arr) {
			if (arr.length < 2) return arr[0] || [];
			return [].reduce.call(arr, function (col, set) {
				let res = [];
				col.forEach(c => {
					set.forEach(s => {
						let t = [].concat(Array.isArray(c) ? c : [c]);
						t.push(s);
						res.push(t);
					})
				});
				return res;
			});
		}
	}
})

function downloadTemplate(){
	const downloads = document.querySelectorAll('#b2_single_post_download_group_repeat .cmb-repeatable-grouping')

	const tem = b2downloadtemplate;

	for (let index = 0; index < downloads.length; index++) {

		jQuery(downloads[index]).find('#b2_single_post_download_group_'+index+'_template').on('change', function() {
			console.log(11)
			let k = jQuery(this).find(':selected').val()

			console.log(tem[k-1])

			let p = jQuery('#cmb-group-b2_single_post_download_group-'+index)
			p.find('#b2_single_post_download_group_'+index+'_url').val(tem[k-1].url)
			p.find('#b2_single_post_download_group_'+index+'_thumb').val(tem[k-1].thumb)
			p.find('#b2_single_post_download_group_'+index+'_name').val(tem[k-1].name)
			p.find('#b2_single_post_download_group_'+index+'_view').val(tem[k-1].view)
			p.find('#b2_single_post_download_group_'+index+'_attr').val(tem[k-1].attr)
			p.find('#b2_single_post_download_group_'+index+'_rights').val(tem[k-1].rights)
		})

	}
}

downloadTemplate()

function b2getdogcloudvideo(){

	const videoBox = document.querySelectorAll('#post-style-5-box .cmb-repeatable-grouping');
	let locked = []
	if(videoBox.length > 0){
		for (let i = 0; i < videoBox.length; i++) {

			let el = document.querySelector('#cmb-group-b2_single_post_video_group-'+i);
			let button = el.querySelector('.get-video-button');

			button.onclick = function (e){
				console.log(e)
				let id = e.target.parentNode.parentNode.querySelector('input').value

					if(locked[i]) return
					locked[i] = true
					el.querySelector('.get-video-button').innerText = '获取中...'
					jQuery.ajax({
						url: cmb2_l10.ajaxurl+'?action=b2_get_dogecloud_data&id='+id,
						type: 'POST',
						success: (data)=>{
		
							if(data.data.url){
								locked[i] = false
								el.querySelector('.get-video-button').innerText = '获取'
		
								el.querySelector('#b2_single_post_video_group_'+i+'_title').value = data.data.name
								el.querySelector('#b2_single_post_video_group_'+i+'_poster').value = data.data.poster
								el.querySelector('#b2_single_post_video_group_'+i+'_url').value = '播放时会自动获取'
		
								return
							}else{
								if(data.success === false){
									alert(data.data)
									locked[i] = false
									el.querySelector('.get-video-button').innerText = '获取'
									return
								}
								if(data.data.code != 200){
									alert(data.data.msg)
									locked[i] = false
									el.querySelector('.get-video-button').innerText = '获取'
									return
								}
								
							}
							
						},
						error:  (data)=>{
							locked[i] = false
						},
						cache: false,
						contentType: false,
						processData: false
					});
			}


		}
	}
}
b2getdogcloudvideo()

Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
        "S": this.getMilliseconds() //毫秒 
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

const fliterA = new Vue({
	el: '#fliter-box',
	data() {
	  return {
		zh: vdp_translation_zh.js,
		start:'',
		end:''
	  }
	},
	components: {
		vuejsDatepicker
	},
	mounted(){
		const params = new URL(window.location.href).searchParams;
		this.start = params.get('start'); // "1"
		this.end = params.get('end'); // "2" 
	},
	methods: {
		fliter(){
			if(!this.start || !this.end) return

			let url = new URL(window.location.href);
			url.searchParams.set('start', new Date(this.start).Format("yyyy-MM-dd"));
			url.searchParams.set('end', new Date(this.end).Format("yyyy-MM-dd"));

			window.location.href = url
		},
		canel(){
			let url = new URL(window.location.href);
			url.searchParams.delete('start');
			url.searchParams.delete('end');

			window.location.href = url
		}
	}
})

const invOut = new Vue({
	el:'#inv-fliter-box',
	data:{
		outStart:'',
		outEnd:''
	}
})

document.addEventListener("DOMContentLoaded", () => {
	const userTop = new Vue({
		el:'#money-top',
		data:{
			list:'',
			mpaged:1,
			cpaged:1,
			locked:false
		},
		mounted(){
			this.getUserList()
		},
		methods:{
			getUserList(){
				this.locked = true
				jQuery.ajax({
					url: cmb2_l10.ajaxurl+'?action=user_list',
					type: 'POST',
					success: (data)=>{
						this.list = JSON.parse(data)
						this.locked = false
					}
				});
			},
			getCreditList(){
				this.locked = true
				jQuery.ajax({
					url: cmb2_l10.ajaxurl+'?action=user_list_credit',
					type: 'POST',
					data:{
						paged:this.cpaged
					},
					success: (data)=>{
						this.list.credit.list = JSON.parse(data)
						this.locked = false
					}
				});
			},
			getMoneyList(){
				this.locked = true
				jQuery.ajax({
					url: cmb2_l10.ajaxurl+'?action=user_list_money',
					type: 'POST',
					data:{
						paged:this.mpaged
					},
					success: (data)=>{
						this.list.money.list = JSON.parse(data)
						this.locked = false
					}
				});
			},
		}
	})
	
})

