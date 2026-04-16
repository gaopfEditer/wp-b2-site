var b2MissionPage = new Vue({
    el:'#mission',
    data:{
        mission:'',
        pages:{
            today:0,
            always:0
        },
        opt:{
            count:20,
            paged:1,
            type:'today'
        },
        url:'',
        api:'getMissionList',
        selecter:'.mission-page-list ul',
        list:''
    },
    mounted(){
        if(this.$refs.missionPage){
            this.getData()
            this.opt.paged = this.$refs.missionPage.getAttribute('data-paged');
            this.opt.type = this.$refs.missionPage.getAttribute('data-type');
            this.url = this.$refs.missionPage.getAttribute('data-url')+'/'+this.opt.type
            this.getList()
        }
    },
    methods:{
        change(type){
            this.opt.type = type
            if(this.url.indexOf('today') !== -1 || this.url.indexOf('always') !== -1){
                this.url = this.url.replace('today',type).replace('always',type)
            }else{
                this.url = this.url+'/'+type
            }
            this.opt.paged = 1
            this.getList()
        },
        getData(){
            this.mission = b2Mission
            this.opt.type = this.mission.type
            if(this.mission.data == ''){
                this.mission.getData(10)
            }
        },
        getList(){
            this.$refs.missionNav.go(this.opt.paged,'comment',true)
        },
        get(data){
            this.list = data.data
            this.pages[this.opt.type] = data.pages
        },
        tk(){

            payCredit.data = {
                'title':b2_global.js_text.global.mission_tk,
                'order_price':this.mission.data.mission.tk.credit,
                'order_type':'mission',
                'post_id':0
            }
            payCredit.user.credit = this.mission.data.mission.my_credit;
            payCredit.show = true;

        }
    }
})