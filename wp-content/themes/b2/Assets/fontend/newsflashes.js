var b2Newsflashes = new Vue({
    el:'.news-content',
    data:{
        login:'',
        showPostFrom:false,
        locked:false,
        data:{
            title:'',
            content:'',
            tag:'',
            from:'',
            img:{
                url:'',
                id:''
            }
        },
        locked:false,
        progress:0,
        toast:'',
        paged:1,
        list:'',
        pages:0,
        term:0,
        //分页
        selecter:'news-list-box',
        opt:{
            paged:'',
            term:''
        },
        api:'getNewsflashesList'
    },
    mounted(){
        
        if(b2token){
            this.login = true
        }else{
            this.login = false
        }

        if(this.$refs.paged){
            this.data.tag = this.$refs.tag.getAttribute('data-tag')

            this.opt.paged = this.$refs.paged.getAttribute('data-paged')
            this.opt.term = this.$refs.paged.getAttribute('data-termid') ? this.$refs.paged.getAttribute('data-termid') : 0

            autosize(this.$refs.newsTextarea);
    
            this.$refs.goldNav.go(this.opt.paged,'comment',true)
            // if(B2ClientWidth > 768 && document.querySelector('#menu-newsflashes')){
            //     new Flickity(document.querySelector('#menu-newsflashes'),{
            //         pageDots: false,
            //         groupCells: true,
            //         draggable: true,
            //         prevNextButtons: false,
            //         freeScroll: false,
            //         wrapAround:false,
            //         selectedAttraction:0.15,
            //         friction:1,
            //         freeScrollFriction: 0.1,
            //         cellAlign: 'left'
            //     });
            // }
        }

        if(b2GetQueryVariable('action') === 'showbox'){
            this.showPostFrom = true
        }

        // this.$nextTick(()=>{
        //     lazyLoadInstance.update()
        // })
        
    },
    methods:{
        showForm(){
            if(!this.login){
                login.show = true
                login.loginType = 1
                return
            }
            this.showPostFrom = true
        },
        removeImage(){
            this.data.img.url = ''
            this.data.img.id = ''
            this.$refs.fileInput.value = null
        },
        get(data){
            if(this.list === ''){
                this.list = data.data
            }else{
                this.list = data.data
            }
            this.pages = data.pages
            this.locked = false
            this.$nextTick(()=>{
                b2tooltip('.new-meta-right span')
            })
        },
        getFile(event){
            if(event.target.files.length <= 0) return
            if(this.locked == true) return
            this.locked = true
            this.progress = 0
            let file = event.target.files[0]

            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", 1)
            formData.append("type", 'newsflashes')

            let config = {
                onUploadProgress: progressEvent=>{
                    this.progress = progressEvent.loaded / progressEvent.total * 100 | 0
                }
            }

            this.toast = Qmsg['loading']('Loading...('+this.progress+'%)');

            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{
                if(res.data.status == 401){
                    Qmsg['warning'](res.data.message,{html:true});
                    

                }else{
                    this.data.img.url = res.data.url
                    this.data.img.id = res.data.id
                }
                
                this.progress = 0
                this.$refs.fileInput.value = null
                this.locked = false;
                this.toast.close()
            }).catch(err=>{
  
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
                this.progress = 0
                this.$refs.fileInput.value = null
                this.toast.close()
            })
        },
        postNewsflashes(){
            if(!this.login){
                login.show = true
                login.loginType = 1
                return
            }

            if(!userTools.role.newsflashes){

                Qmsg['warning'](b2_global.js_text.global.not_allow,{html:true});
                return
            }

            this.showPostFrom = !this.showPostFrom
        },
        submitNewsflashes(){
            if(this.locked == true) return
            this.locked = true

           if(!this.data.content || !this.data.title){
                Qmsg['warning'](b2_global.js_text.global.newsflashe_r,{html:true});
                this.locked = false
                return
           }

           this.$http.post(b2_rest_url+'submitNewsflashes',Qs.stringify(this.data)).then(res=>{
   
                Qmsg['success'](b2_global.js_text.global.newsflashe_insert_success,{html:true});
                //this.showPostFrom = false
                this.data = {
                    title:'',
                    content:'',
                    tag:'',
                    from:'',
                    img:{
                        url:'',
                        id:''
                    }
                },
                this.showPostFrom = false
                this.locked = false
           }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
           })
        },
        getList(){
            this.$http.post(b2_rest_url+'getNewsflashesList','paged='+this.paged+'&term_id='+this.term).then(res=>{
                
                this.list = res.data.data
                this.pages = res.data.pages
                this.locked = false

                this.paged++

                this.$nextTick(()=>{
                    b2tooltip('.new-meta-right span')
                })
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        vote(id,type,key1,key2){

            if(!this.login){
                login.show = true
                login.loginType = 1
                return
            }

            if(this.locked == true) return
            this.locked = true

            this.$http.post(b2_rest_url+'postVote','type='+type+'&post_id='+id).then(res=>{
                this.list[key1][key2]['vote']['up'] = parseInt(this.list[key1][key2]['vote']['up']) + parseInt(res.data.up)
                this.list[key1][key2]['vote']['down'] = parseInt(this.list[key1][key2]['vote']['down']) + parseInt(res.data.down)

                if(res.data.up > 0){
                    this.list[key1][key2]['vote'].up_isset = true
                }else{
                    this.list[key1][key2]['vote'].up_isset = false
                }

                if(res.data.down > 0){
                    this.list[key1][key2]['vote'].down_isset = true
                }else{
                    this.list[key1][key2]['vote'].down_isset = false
                }

                this.locked = false
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        },
        openWin(url,type,key1,key2){

            if(type == 'weibo'){
                url = url+'&pic='+this.list[key1][key2]['img']
            }else{
                url = url+'&pics='+this.list[key1][key2]['img']
            }

            openWin(url,type,500,500)
        }
    },
    watch:{
        showPostFrom(val){

            if(!val){
                window.history.pushState('news', '', b2removeURLParameter(window.location.href,'action'))
            }
        },
        progress(val){
            this.toast.$elem.firstChild.lastElementChild.innerText = 'Loading...('+val+'%)';
        }
    }
})