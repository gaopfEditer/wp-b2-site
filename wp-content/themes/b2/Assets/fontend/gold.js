var b2gold = new Vue({
    el:'#gold',
    data:{
        data:[],
        paged:1,
        msg:'',
        pages:1,
        selecter:'.gold-page-list ul',
        opt:{
            paged:1,
            to:0,
            count:20,
            pages:0,
            gold_type:0,
            read_clean:1
        },
        api:'getGoldList',
        url:'',
        show:false,
        money:'',
        locked:false,
        success:false
    },
    computed:{
        userData(){
            return this.$store.state.userData
        }
    },
    mounted(){
        if(this.$refs.goldNav){

            const currentURL = window.location.href
            if(currentURL.indexOf('/page/') !== -1){
                this.opt.paged = window.location.pathname.match(/[0-9]*$/)
            }
    
            this.opt.to = b2GetQueryVariable('uid') || 0
            this.$refs.goldNav.go(this.opt.paged,'comment',true,true)

            this.getUserGoldData();

            // this.opt.user_id = this.$refs.goldData.getAttribute('data-user');
            // this.opt.paged = this.$refs.goldData.getAttribute('data-paged');
            // this.opt.type = this.$refs.goldData.getAttribute('data-type');
            // this.url = this.$refs.goldData.getAttribute('data-url')+'/'+this.opt.type
            // this.getUserGoldData()
            // this.$refs.goldNav.go(this.opt.paged,'comment',true)
        }
    },
    methods:{
        getUserGoldData(){
            this.$http.post(b2_rest_url+'getUserGoldData','user_id='+this.opt.to).then(res=>{
                this.data = res.data
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                
            })
        },
        msgContent(item){
            let count = '',post = '',from = '',gold = ''
            if(item.msg.indexOf('${count}') !== -1){
                count = ' '+item.count+' '
            }

            if(item.msg.indexOf('${post_id}') !== -1){
                post = '<a href="'+item.post.link+'" target="_blank">'+item.post.title+'</a>'
            }

            if(item.msg.indexOf('${from}') !== -1){
                from = '<a href="'+item.from.link+'" target="_blank"> '+item.from.name+' </a>'
            }

            if(item.msg.indexOf('${gold_page}') !== -1){
                gold = '<a href="'+b2_global.home_url+'/gold" target="_blank"> '+b2_global.js_text.global.my_gold+' </a>'
            }

            return item.msg.replace('${count}',count).replace('${post_id}',post).replace('${from}',from).replace('${gold_page}',gold)

        },
        // getGoldList(){
        //     this.$http.post(b2_rest_url+'getGoldList',Qs.stringify(this.opt)).then(res=>{
        //         this.msg = res.data.data
        //         this.pages = res.data.pages
        //         Vue.nextTick(()=>{
        //             b2Timeago.render(this.$refs.goldData.querySelectorAll('.b2timeago'), 'zh_CN');
        //         })
        //     }).catch(err=>{
        //         this.$toasted.show(err.response.data.message,{
        //             theme: 'primary', 
        //             position: 'top-center', 
        //             duration : 4000,
        //             type:'error'
        //         })
        //     })
        // },
        users(users){
            let str = ''
            let leng = users.length
            for (let i = 0; i < leng; i++) {
                if(i == 3 && leng > 3){
                    str = str.slice(0,-1)
                    str += '<span class="gold-and"> '+b2_global.js_text.global.and+'</span> ' 
                }

                if(users[i] instanceof Object){
                    str += '<a href="'+users[i].link+'" target="_blank">'+users[i].name+'</a>，'
                }else{
                    str += users[i]+'，'
                }
            }
            if(leng > 3){
                return str.slice(0,-1)+' '+b2_global.js_text.global.more_people;
            }else{
                return str.slice(0,-1)+' ';
            }
        },
        get(data){
            this.msg = data.data
            this.opt.pages = data.pages
        },
        change(type){
            this.opt.gold_type = type

            let ctype = 'credit'
                url = window.location.href
            if(type == 1){
                ctype = 'money'
            }
            this.opt.paged = 1
            // if(url.indexOf('money') !== -1 || url.indexOf('credit') !== -1){
            //     window.location.href = url.replace('money',ctype).replace('credit',ctype)
            // }else{
            //     window.location.href = url+'/'+ctype
            // }
            
            this.$refs.goldNav.go(this.opt.paged,'comment',true,true)
        },
        pay(){
            
            if(!b2token){
                login.show = true
            }else{
                b2DsBox.show = true
                b2DsBox.showtype = 'cz'
            }
        },
        buy(){
           
            if(!b2token){
                login.show = true
            }else{
                b2DsBox.show = true
                b2DsBox.showtype = 'cg'
            }
        },
        tx(){
            if(!window.confirm(b2_global.js_text.global.tx_alert)) return
            if(this.locked) return
            this.locked = true
            this.$http.post(b2_rest_url+'cashOut','money='+this.money).then(res=>{
                this.success = true
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        close(){
            this.show = !this.show
        },
        refresh(){
            location.reload();
        },
        circleText(type){
            return b2_global.js_text.circle.circle_date[type]
        }
    }
})

var goldTop = new Vue({
    el:'#gold-top',
    data:{
        data:''
    },
    mounted(){
        if(this.$refs.goldTop){
            this.getGoldTop()
        }
    },
    methods:{
        getGoldTop(){
            this.$http.post(b2_rest_url+'getGoldTop').then(res=>{
                this.data = res.data
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
            })
        }
    }
})