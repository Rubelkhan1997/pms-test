<template>
    <div class="bank-page">
       <div class="col-12 col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title">Deposit</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="deposit()" method="post">
                        <div class="mb-3">
                            <label class="form-label">Deposit Date</label>
                            <el-date-picker type="date" v-model="form.date"  style="width:100%" placeholder="Select date"></el-date-picker>
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('date')" v-html="form.errors.get('date')" /> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Bank</label>
                            <el-select v-model="form.bank_id" filterable  style="width:100%" placeholder="Select bank">
                                <el-option v-for="bank in banks"  :key="bank.bank_id"  
                                    :label="bank.label" :value="bank.bank_id">
                                </el-option>
                            </el-select>
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('bank_id')" v-html="form.errors.get('bank_id')" /> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <el-input type="number"  v-model="form.amount" placeholder="Amount"></el-input>
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('amount')" v-html="form.errors.get('amount')" /> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <el-input  v-model="form.description" placeholder="Description" rows="4"></el-input>
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('description')" v-html="form.errors.get('description')" />
                        </div>
                        <el-button type="primary" native-type="submit" size="small" :loading="busyForm">Submit</el-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'bank-page',
    data(){
      return {
        form: new Form({
            date: '',
            bank_id: '',
            amount: '',            
            description: '',            
        }),
        banks: [{  label: 'Home Bank',  bank_id: 1,}, 
                {  label: 'DBBL Bank',  bank_id: 2,}, 
                {  label: 'Prime Bank', bank_id: 3,}],
        busyForm  : false,
      }
    },
    mounted() {
        this.form.date = Current_Date;
    },
    methods:{
        deposit(){
            this.busyForm    = true;
            this.form.post(Api_Base_Url+'/bank').then(res =>{
                this.$message({
                    showClose: true,
                    message: res.data.success,
                    type: 'success',
                });
                this.form.reset();
                this.busyForm   = false;
                this.form.date  = Current_Date;
            }).catch(error =>{
                this.busyForm    = false;
            });
        },
    }
}
</script>