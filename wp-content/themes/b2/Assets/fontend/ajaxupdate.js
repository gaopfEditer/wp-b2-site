var ajaxupdate = new Vue({
    el:'.update-box',
    data:{
        type:'',
        commentVote:{
            pages:0,
            paged:1,
            status:'ready'
        },
        postVote:{
            pages:0,
            paged:1,
            status:'ready'
        },
        indexModules:{
            pages:0,
            paged:1,
            status:'ready'
        },
        updateCats:{
            pages:0,
            paged:1,
            status:'ready'
        },
        buy:{
            pages:0,
            paged:1
        },
        ds:{
            pages:0,
            paged:1
        },
        follow:{
            pages:0,
            paged:1
        },
        sc:{
            pages:0,
            paged:1
        },
    },
    methods:{
        update(type){
            if(this.type){
                if(this[this.type].status === 'success') return
                this[this.type].status = 'go'    
            }
            
            this.type = type
            
            this.$http.post(b2_rest_url+'ajaxupdate','type='+type+'&paged='+this[this.type].paged).then(res=>{

                if(res.data !=='success'){
                    this.$set(this[this.type],'paged',this[this.type].paged+1)
                    this.update(this.type)
                }else{
                    this[this.type].status = 'success'
                }
                
            }).catch(err=>{
                Qmsg['warning'](err.response.data.message,{html:true});
                this[this.type].status = 'ready'
            })
        }
    }
})