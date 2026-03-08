<template>
    <div class="bank-amount-transfer">
        <form @submit.prevent="transfer()" method="post">
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title">From</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Select Bank</label>
                                <el-select v-model="form.from_bank_id" filterable @change="from_bank_select()"  style="width:100%" placeholder="Select bank">
                                    <el-option v-for="bank in banks"  :key="bank.bank_id"  
                                        :label="bank.label" :value="bank.bank_id">
                                    </el-option>
                                </el-select>
                                <!-- from_total_amount -->
                                <span class="from_total_amount text-danger" style="width:100%"> </span> 
                                <!-- Error -->   
                                <span class="text-danger from_bank_error" v-if="form.errors.has('from_bank_id')" v-html="form.errors.get('from_bank_id')" /> 
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <el-input type="number" v-model="form.from_amount" @input="send_amount()" placeholder="Amount"></el-input>
                                <!-- insufficient -->
                                <span class="insufficient text-danger" style="width:100%"> </span> 
                                <!-- Error -->   
                                <span class="text-danger" v-if="form.errors.has('from_amount')" v-html="form.errors.get('from_amount')" /> 
                            </div>
                            <div class="mb-3 pb-md-5">
                                <label class="form-label">Description</label>
                                <el-input v-model="form.description" placeholder="Description"></el-input>
                                <!-- Error -->   
                                <span class="text-danger" v-if="form.errors.has('description')" v-html="form.errors.get('description')" /> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title">To</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Select Bank</label>
                                <el-select v-model="form.to_bank_id" filterable @change="to_bank_select()" style="width:100%" placeholder="Select bank">
                                    <el-option v-for="bank in banks"   :key="bank.bank_id"  
                                        :label="bank.label" :value="bank.bank_id" :disabled="to_bank_option == bank.bank_id">
                                    </el-option>
                                </el-select>
                                <!-- to_total_amount -->
                                <span class="to_total_amount text-danger" style="width:100%"> </span> 
                                <!-- Error -->   
                                <span class="text-danger to_bank_error" v-if="form.errors.has('to_bank_id')" v-html="form.errors.get('to_bank_id')" /> 
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <el-input v-model="form.to_amount" :disabled="true" placeholder="Amount"></el-input>
                                <!-- Error -->   
                                <span class="text-danger" v-if="form.errors.has('amount')" v-html="form.errors.get('amount')" /> 
                            </div>
                            <div class="mb-3"  style="padding-bottom:15px">
                                <label class="form-label">Date</label>
                                <el-date-picker type="date" v-model="form.date"  style="width:100%" placeholder="Select date"></el-date-picker>
                                <!-- Error -->   
                                <span class="text-danger" v-if="form.errors.has('date')" v-html="form.errors.get('date')" /> 
                            </div>
                            <el-button type="primary" class="float-right" native-type="submit" size="small" :loading="busyForm">Submit</el-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<script>
export default {
    name: 'bank-amount-transfer',
    data(){
      return {
        form: new Form({
            date: '',
            from_bank_id: '',
            to_bank_id: '',
            from_amount: '',            
            to_amount: '',            
            description: '',            
        }),
        banks: [{  label: 'Home Bank',  bank_id: 1,}, 
                {  label: 'DBBL Bank',  bank_id: 2,}, 
                {  label: 'Prime Bank', bank_id: 3,}],
        busyForm  : false,
        to_bank_option     : '',
        from_total_amount  : '',
        to_total_amount    : '',
      }
    },
    mounted() {
        this.form.date = Current_Date;
    },
    methods:{
        transfer(){
            this.busyForm    = true;
            this.form.post(Api_Base_Url+'/bank-amount-transfer').then(res =>{
                this.$message({ showClose: true, message: res.data.success, type: 'success', });
                this.form.reset();
                this.busyForm   = false;
                this.form.date  = Current_Date;
                $('.insufficient').html('');
                $('.from_total_amount').html('');
                $('.to_total_amount').html('');
            }).catch(error =>{
                this.busyForm    = false;
            });
        },
        from_bank_select(){
            $('.from_bank_error').html('');
            $('.to_total_amount').html('');
            this.form.to_bank_id = ""; // Selected value empty
            this.to_bank_option = this.form.from_bank_id;
            // Check total amounnt bank wise
            axios.get(Api_Base_Url+'/bank/'+this.form.from_bank_id).then(res =>{
                $('.from_total_amount').html('Current amount : ' + res.data +'.00');
                if(res.data == 0){
                    this.busyForm  = true; 
                }else{
                    this.busyForm  = false;
                    this.from_total_amount = res.data;
                }
            });
        },
        to_bank_select(){
            $('.to_total_amount').html('');
            $('.to_bank_error').html('');
            if(this.form.from_bank_id == ""){
                this.form.to_bank_id = "";
                this.$message({ showClose: true, message: 'Please, first select from bank name', type: 'error', });
                return false;
            }
            // Check total amount bank wise
            axios.get(Api_Base_Url+'/bank/'+this.form.to_bank_id).then(res =>{
                $('.to_total_amount').html('Current amount : ' + res.data +'.00');
                if(this.form.from_amount != ""){
                    this.form.to_amount  = parseInt(this.form.from_amount) + parseInt(res.data) +'.00';
                }else{
                    this.form.to_amount   = res.data +'.00';
                    this.to_total_amount  = res.data;
                }
            });
        },
        send_amount(){
            if(this.form.from_bank_id == ""){
                this.form.from_amount = "";
                this.form.to_bank_id  = "";
                this.$message({ showClose: true, message: 'Please, first select from bank name', type: 'error', });
                return false;
            }
            else if(this.from_total_amount < this.form.from_amount){
                $('.insufficient').html('Insufficient balance');
                this.busyForm  = true; 
                return false;
            }
            else{
                $('.insufficient').html('');
                this.busyForm  = false; 
                
                if(this.form.to_amount != ""){
                    this.form.to_amount  = parseInt(this.form.from_amount) + parseInt(this.to_total_amount) +'.00';
                }else if(this.to_total_amount != ""){
                    this.form.to_amount  = this.to_total_amount+'.00';
                }
            }
        },
    }
}
</script>