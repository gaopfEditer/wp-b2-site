var carts = new Vue({
    el:'#carts-list',
    data:{
        picked:[],
        comment:'',
        address:{
            addresses:{}
        },
        showAddress:false,
        total:0,
        dataLength:0,
        pickedAll:false,
        step:1,
        //地址编辑
        showAddressBox:false,
        pickedAddress:'',
        addressEditData:{
            'address':'',
            'name':'',
            'phone':'',
            'province':'',
            'city':'',
            'county':''
        },
        editAddressKey:'',
        coupons:'',
        pickedCoupon:[],
        couponTotal:0,
        //邮箱
        pickedEmail:'',
        showEmailBox:false,
        showEmail:false,
        getLocked:false,
        id:0
    },
    computed:{
        data(){
            
            if(b2GetQueryVariable('id')){
                
                return this.$store.state._carts
            }else{
                
                return this.$store.state.carts
            }
        }
    },
    mounted(){
        this.id = b2GetQueryVariable('id'),
        count = b2GetQueryVariable('count');

        if(count && count <= 0){
            Qmsg['warning'](b2_global.js_text.global.buy_count_error,{html:true});
            return
        }

        if(this.id){
            this.getItems(this.id,b2GetQueryVariable('index'))
        }else{
            this.getCarts()
        }
    },
    methods:{
        showEmeilBoxAction(){
            // if(!b2token){
            //     login.show = true
            //     return
            // }
            this.showEmailBox = true 
        },
        showAddressBoxAction(){
            if(!b2token){
                if(this.hasCommodity()){
                    login.show = true
                    return
                }
            }
            this.showAddressBox = true 
        },
        init(){
            if(Object.keys(this.data).length > 0){
                Object.keys(this.data).forEach((key)=>{
                    this.picked.push(key)
                    if(this.data[key].commodity === 1){
                        this.showAddress = true
                    }
                    this.total += Calc.Mul(this.data[key].price.current_price,this.data[key].count);
                    this.dataLength++
                    this.pickedAll = true
                });

                this.getCoupons()
                this.getAddress()
                this.getEmail()
            }
        },
        getCarts(){
            if(this.getLocked == true) return
            this.getLocked = true
            this.$http.get(b2_rest_url+'getMyCarts').then((res)=>{
                console.log(res.data)
                if(res.data.length == 0){
                    this.$store.commit('setcartsData',{})
                }else{
                    this.$store.commit('setcartsData',res.data)
                }
                this.init()
                this.getLocked = false
            })
        },
        getCoupons(){
            if(!b2token){
                return
            }

            let ids = {
                'ids':this.picked
            }
            this.$http.post(b2_rest_url+'getCouponsByPostId',Qs.stringify(ids)).then((res)=>{
                this.coupons = res.data
                if(this.pickedCoupon.length > 0){
                    for (let i = 0; i < this.pickedCoupon.length; i++) {
                        if(this.coupons.hasOwnProperty(this.pickedCoupon[i]) === false){
                            this.pickedCoupon.splice(i,1)
                        }
                    }
                }
            })
        },
        couponClass(item){
            if(item.expiration_date.expired) return 'stamp04'
            if(item.products.length > 0) return 'stamp01'
            if(item.cats.length > 0) return 'stamp02'
            return 'stamp03'
        },
        pickedCouponArg(id){
            if(this.pickedCoupon.indexOf(id) !== -1){
                this.pickedCoupon.splice(this.pickedCoupon.indexOf(id),1)
            }else{
                this.pickedCoupon.push(id)
            }
        },
        couponTotalReset(){
            this.couponTotal = 0;
            for (let i = 0; i < this.pickedCoupon.length; i++) {
                this.couponTotal = Calc.Add(this.coupons[this.pickedCoupon[i]].money,this.couponTotal)  
            }
        },
        totalPay(){
            let total = Calc.Sub(this.total,this.couponTotal);
            if(total < 0) return 0
            return total
        },
        getItems(id,index){
            if(this.getLocked == true) return
            this.getLocked = true
            let ids = {
                ids:[id],
                index:index,
                return:{
                    'images':0,
                    'attrs':0
                }
            }

            this.$http.post(b2_rest_url+'getShopItemsData',Qs.stringify(ids)).then((res)=>{
                let data = res.data
                data[id].count = b2GetQueryVariable('count')

                this.$store.commit('set_cartsData',res.data)
                this.init()
                this.getLocked = false
            })
            
        },
        getEmail(){
            if(!b2token){
                return
            }
            this.$http.post(b2_rest_url+'getEmail').then(res=>{
                if(res.data.status != 403){
                    this.pickedEmail = res.data
                }
            })
        },
        getAddress(){

            //如果用户未登录，获取本地存储的地址
            if(!b2token){
                let address = localStorage.getItem('b2_address')
                if(address){
                    this.address.addresses = JSON.parse(address)
                    this.pickedAddress = localStorage.getItem('b2_default_address')
                }
                return
            }

            this.$http.post(b2_rest_url+'getAddresses').then(res=>{
                if(Object.keys(res.data.addresses).length > 0){
                    this.address = res.data
                    this.pickedAddress = this.address.default
                }
            })
        },
        mul(price,count){
            return Calc.Mul(price,count,2)
        },
        countAdd(key){
            if(this.data[key].count >= this.data[key].stock.total) return
            if(this.picked.indexOf(key) !== -1){
                this.total = Calc.Add(this.total,this.data[key].price.current_price)
            }
            this.data[key].count++
        },
        countSub(key){
            if(this.data[key].count <=1 ) return
            if(this.picked.indexOf(key) !== -1){
                this.total = Calc.Sub(this.total,this.data[key].price.current_price)
            }
            this.data[key].count--
        },
        totalMoney(){
            return Calc.Add(this.total,0,2);
        },
        totalReset(){
            this.showAddress = false
            this.showEmail = false
            this.total = 0
            console.log(this.picked,this.data)
            for (let i = 0; i < this.picked.length; i++) {
                if(this.data[this.picked[i]].commodity === 1){
                    this.showAddress = true
                }

                if(this.data[this.picked[i]].commodity === 0){
                    this.showEmail = true
                }
                
                this.total += Calc.Mul(this.data[this.picked[i]].price.current_price,this.data[this.picked[i]].count);
            }
        },
        allPicked(){
            if(this.data != 0 && this.data != null){
                Object.keys(this.data).forEach((key)=>{
                    if(this.picked.indexOf(key) === -1){
                        this.picked.push(key)
                    }
                });
            }
        },
        //关闭弹窗
        close(){
            this.showAddressBox = false
            this.showEmailBox = false
        },
        emptyAddress(){
            if(this.address === '') return false

            if(!this.data) return false
            
            if(this.pickedAddress === '') return false

            return true
        },
        deleteAddress(key){

            var r = confirm(b2_global.js_text.global.delete_address);
            if (r == true) {

                //如果用户未登录，删除本地存储的地址
                if(!b2token){
                    let address = localStorage.getItem('b2_address')
                    if(address){
                        address = JSON.parse(address)
                        delete address[key]
                        localStorage.setItem('b2_address',JSON.stringify(address))
                        if(key === localStorage.getItem('b2_default_address')){
                            if(Object.keys(address).length > 0){
                                localStorage.setItem('b2_default_address',Object.keys(address)[0])
                                this.pickedAddress = Object.keys(address)[0]
                            }else{
                                localStorage.setItem('b2_default_address','')
                            }
                        }

                        this.address.addresses = address
                    }
                    return
                }

                this.$http.post(b2_rest_url+'deleteAddress','key='+key).then(res=>{
                    if(res.data){
                        this.address.addresses = res.data.address;
                        if(key === this.address.default){
                            this.address.default = res.data.default
                        }
                        if(this.pickedAddress === key){
                            this.pickedAddress = res.data.default
                        }
                    }
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            } 
            return
        },
        pickedAddressAc(key){
            this.pickedAddress = key
            this.close()
        },
        editAddress(key){
            this.editAddressKey = key
            this.addressEditData = this.address.addresses[key]
        },
        addNewAddress(){
            this.editAddressKey = uuid(8, 16);
            this.addressEditData = {}
        },
        saveAddress(){
            
            //如果用户未登录，地址信息记录到本地
            if(!b2token){

                //创建一个以this.editAddressKey为键的对象
                this.address.addresses[this.editAddressKey] = this.addressEditData

                localStorage.setItem('b2_address',JSON.stringify(this.address.addresses))

                //设置默认地址
                localStorage.setItem('b2_default_address',this.editAddressKey)
                this.$nextTick(()=>{
                    this.pickedAddressAc(this.editAddressKey)
                    this.editAddressKey = ''
                    this.key = ''
                })
                return
            }

            this.$http.post(b2_rest_url+'saveAddress','address='+this.addressEditData.address+'&name='+this.addressEditData.name+'&phone='+this.addressEditData.phone+'&key='+this.editAddressKey).then(res=>{
                this.address.addresses = res.data.address;
                this.$nextTick(()=>{
                    this.pickedAddressAc(res.data.key)
                    this.editAddressKey = ''
                    this.key = ''
                })
                
            }).catch(err=>{

                Qmsg['warning'](err.response.data.message,{html:true});
            })
        },
        //只显示选中商品
        pickedProducts(key){
            if(this.picked.indexOf(key) !== -1) return true
            return false
        },
        //如果有虚拟物品
        hasVirtual(){
            if(!this.data) return false
            for (let i = 0; i < this.picked.length; i++) {
                if(this.data[this.picked[i]].commodity === 0){
                    return true
                }
            }
            return false
        },
        //如果有实物
        hasCommodity(){
            if(!this.data) return false
            for (let i = 0; i < this.picked.length; i++) {
                if(this.data[this.picked[i]].commodity === 1){
                    return true
                }
            }
            return false
        },
        //支付
        pay(){
            if(!b2token){
                if(this.hasCommodity()){
                    login.show = true
                    return
                }
            }

            if(this.showAddress === true && this.pickedAddress === ''){


                Qmsg['warning'](b2_global.js_text.global.add_address,{html:true});
                
                return
            }

            if(this.showEmail === true && this.pickedEmail == ''){

                Qmsg['warning'](b2_global.js_text.global.add_email,{html:true});
                return
            }

            this.step = 2
            let data = {
                'products':this.getPickedProducts(),
                'content':this.comment,
                'address':this.showAddress ? this.pickedAddress : '',
                'coupons':this.pickedCoupon,
                'email':this.pickedEmail ? this.pickedEmail : ''
            }
            
            b2DsBox.data = {
                'title':b2_global.js_text.global.buy,
                'order_price':this.totalPay(),
                'order_type':'g',
                'post_id':0,
                'order_value':JSON.stringify(data),
            }
            b2DsBox.show = true;
        },
        getPickedProducts(){
            let data = [];
            Object.keys(this.data).forEach((key)=>{
                if(this.picked.indexOf(key) !== -1){

                    data.push({'id':key.split(/_/)[0],'count':this.data[key]['count'],'index':this.data[key]['index']})
                }
            });

            return data;
        },
        deleteCarts(id){
            this.$https.post(b2_rest_url+'deleteMyCarts','id='+id).then(res=>{

                if(res.data.length == 0){
                    this.$store.commit('setcartsData',{})
                }else{
                    this.$store.commit('setcartsData',res.data)
                }

                setTimeout(() => {
                    this.totalReset()
                }, 500);
                
            })
        },
        deleteCartsItem(key){
            for (let i = 0; i < this.picked.length; i++) {
                if(this.picked[i] == key){
                    this.$delete(this.picked,i);
                }
            }
            this.deleteCarts(key)   
        },
        deleteAll(){
            this.picked = []
            Object.keys(this.data).forEach((key)=>{
                this.deleteCarts(key)
            });
        },
        orderPage(){
            if(b2token){
                return this.$store.state.userData.link+'/orders'
            }
        },
        buyDisabeld(){
            if(this.showAddress === true && !this.pickedAddress){
                return true
            } 

            return false;
        }
    },
    watch:{
        pickedCoupon(val){
            this.couponTotalReset()
        },
        pickedAll(val){
            if(val){
                this.allPicked()
            }else{
                if(this.picked.length === this.dataLength){
                    this.picked = []
                }
            }
        },
        picked(val){
            if(val.length !== this.dataLength){
                this.pickedAll = false
            }else{
                this.pickedAll = true
            }
            this.getCoupons()
            this.totalReset()
        }
    }
})