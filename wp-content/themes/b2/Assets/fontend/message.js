var b2MessagePage = new Vue({
    el:'#message-page',
    data:{
        opt:{
            paged:1,
            to:0,
            count:20,
            pages:0,
            read_clean:1
        },
        data:'',
        api:'getUserMessage'
    },
    computed:{
        userData(){
            return this.$store.state.userData
        }
    },
    mounted(){
        if(this.$refs.msgNav){

            const currentURL = window.location.href
            if(currentURL.indexOf('/page/') !== -1){
                this.opt.paged = window.location.pathname.match(/[0-9]*$/)
            }
    
            this.opt.to = b2GetQueryVariable('uid') || 0
            this.$refs.msgNav.go(this.opt.paged,'comment',true,true)
        }
    },
    methods:{
        get(data){

            this.data = data
            this.opt.pages = data.pages
        },
        msg(item){
            let from = '',post = '',gold = '',count = 1,request = ''
            if(item.msg.indexOf('${from}') !== -1){
               if(item.from.length > 0){
                    for (let i = 0; i < item.from.length; i++) {
                        if(item.from[i].link){
                            from += '<a href="'+item.from[i].link+'" target="_blank">'+item.from[i].name+'</a>、'
                        }else{
                            from += item.from[i].name+'、'
                        }
                    }

                    from = from.substring(0, from.length - 1)

                    if(item.count > 3){
                        from += ' '+b2_global.js_text.global.more_people
                    }
                }
            }

            if(item.msg.indexOf('${post_id}') !== -1 && item.post?.link){
                post = '<a href="'+item.post.link+'" target="_blank">'+item.post.title+'</a>'
            }

            if(item.msg.indexOf('${gold_page}') !== -1){
                gold = '<a href="'+b2_global.home_url+'/gold" target="_blank">'+b2_global.js_text.global.my_gold+'</a>'
            }

            if(item.msg.indexOf('${count}') !== -1){
                count = item.cont
            }

            if(item.msg.indexOf('${request_page}') !== -1){
                request = '<a href="'+b2_global.home_url+'/requests" target="_blank">'+b2_global.js_text.global.my_requests+'</a>'
            }

            return item.msg.replace('${from}',from+' ').replace('${post_id}',post).replace('${gold_page}',gold).replace('${request_page}',request).replace('${count}',count)
        }
    }
})