var b2LinkRegister = new Vue({
    el:'.links-register',
    data:{
        link_name:'',
        link_url:'',
        link_image:'',
        link_content:'',
        locked:false,
        progress:0,
        disabled:false,
        login:false
    },
    computed:{
        userData(){
            return this.$store.state.userData
        }
    },
    mounted(){
        if(this.$refs.registerLink){
            this.hasPending()
        }
        
        if(b2token){
            this.login = true
        }

        if(document.querySelector('.b2-tab-link-in')){
            b2loadScript(b2_global.site_info.site_uri+'/Assets/fontend/library/tocbot.min.js','',()=>{ 
                tocbot.init({
                    // Where to render the table of contents.
                    tocSelector: '.b2-tab-link-in',
                    // Where to grab the headings to build the table of contents.
                    contentSelector: '.links-home',
                    // Which headings to grab inside of the contentSelector element.
                    headingSelector: '.links-home .link-title h2',
                    hasInnerContainers: true,
                    headingsOffset: 140,
                    scrollSmoothOffset: -140
                });
    
                new StickySidebar('.b2-tab-links', {
                    containerSelector:'.toc-list ',
                    topSpacing: 120,
                    resizeSensor: true,
                });
            })
        }
        
    },
    methods:{
        hasPending(){
            this.$https.get(b2_rest_url+'linkHasPending').then(res=>{
                this.disabled = res.data
            })
        },
        submit(e){
            e.preventDefault()
            this.locked = true
            // console.log(tinyMCE.get('link_content_1').getContent({ format: 'text' }));
            let data = {
                link_name:this.link_name,
                link_url:this.link_url,
                link_category:this.$el.querySelector('.register-cat select').value,
                link_image:this.link_image,
                link_content:tinyMCE.get('link_content_1').getContent()
            }

            this.$https.post(b2_rest_url+'submitLink',Qs.stringify(data)).then(res=>{
                if(!this.userData.is_admin){
                    this.disabled = true
                }

                Qmsg['success'](b2_global.js_text.global.submit_success,{html:true});

                this.link_name = ''
                this.link_url = ''
                this.link_category = ''
                this.link_image = ''
                this.link_content = ''
                tinyMCE.get('link_content_1').setContent('');

                this.progress = 0
                
                this.locked = false
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
                // this.disabled = false
            })

        },
        imgUpload(event){
            if(event.target.files.length <= 0) return
            if(this.locked == true) return
            this.locked = true
            this.progress = 0
            let file = event.target.files[0]

            let formData = new FormData()

            formData.append('file',file,file.name)
            formData.append("post_id", 1)
            formData.append("type", 'avatar')

            let config = {
                onUploadProgress: progressEvent=>{
                    this.progress = progressEvent.loaded / progressEvent.total * 100 | 0
                }
            }

            this.toast = Qmsg['loading']('Loading...('+this.progress+'%)');
        
            this.$http.post(b2_rest_url+'fileUpload',formData,config).then(res=>{
                if(res.data.status == 401){
                    Qmsg['warning'](res.data.message,{html:true});
                    this.progress = 0
                }

                this.link_image = res.data.url

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
        }
    }
})

var linkSingle = new Vue({
    el:'.single-link-rating',
    data:{
        id:0,
        locked:false,
        up:0,
        isUp:false
    },
    mounted(){
        if(this.$refs.linkSingle){
            this.id = this.$refs.linkSingle.getAttribute('data-id')
            this.getVote()
        }
    },
    methods:{
        getVote(){
            this.$http.post(b2_rest_url+'getLinkVote','link_id='+this.id).then(res=>{
                this.up = res.data.count
                this.isUp = res.data.isup
            })
        },
        linkVote(){
            if(this.locked) return
            this.locked = true
            this.$http.post(b2_rest_url+'linkVote','link_id='+this.id).then(res=>{
                
                this.up = this.up + res.data

                if(res.data == -1){
                    this.isUp = false
                }else{
                    this.isUp = true
                }

                this.locked = false;
    
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this.locked = false
            })
        }
    }
})
