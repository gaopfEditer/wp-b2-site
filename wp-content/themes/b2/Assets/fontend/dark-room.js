var darkRoom = new Vue({
    el:'#dark-room',
    data:{
        paged:1,
        data:'',
        type:undefined,
        pages:0,
        opt:{}
    },
    mounted(){
        this.getDarkRoomUsers()
    },
    methods:{
        getDarkRoomUsers(type){
            this.type = type
            if(this.type){
                this.paged = 1
            }
            this.$http.post(b2_rest_url+'getDarkRoomUsers','paged='+this.paged+'&type='+type).then(res=>{
                this.data = res.data
                this.pages = res.data.pages
            })
        },
        getMoreRoomUsers(data){
            this.$set(this.data,'data',data.data)
        }
    }
})