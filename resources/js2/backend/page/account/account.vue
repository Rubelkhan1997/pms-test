<template>
    <div class="account-page">
       <div class="col-12 col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title">Account</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="account()" method="post">
                        <div class="mb-3">
                            <label class="form-label">Select Bank</label>
                            <el-select v-model="form.bank_id" filterable @change="check_bank_amount()"  style="width:100%" placeholder="Select bank">
                                <el-option v-for="bank in banks"  :key="bank.bank_id"  
                                    :label="bank.label" :value="bank.bank_id">
                                </el-option>
                            </el-select>
                            <!-- total_amount -->
                            <span class="total_amount text-danger" style="width:100%"> </span> 
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('bank_id')" v-html="form.errors.get('bank_id')" /> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Date</label>
                            <el-date-picker type="date" v-model="form.date"  style="width:100%" placeholder="Select date"></el-date-picker>
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('date')" v-html="form.errors.get('date')" /> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Type</label>
                             <el-select v-model="form.type" filterable  style="width:100%" placeholder="Select type">
                                <el-option v-for="item in options"  :key="item.type"  
                                    :label="item.label" :value="item.type">
                                </el-option>
                            </el-select>
                            <!-- Error -->   
                            <span class="text-danger" v-if="form.errors.has('type')" v-html="form.errors.get('type')" /> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <el-input type="number" v-model="form.amount" @input="check_input_amount()" placeholder="Amount"></el-input>
                            <!-- insufficient -->
                            <span class="insufficient text-danger" style="width:100%"> </span> 
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
        <div class="col-12">
            <div class="card"> 
                <div class="card-header border-bottom">
                    <h4 class="card-text">Cost Report</h4>
                </div>
                <div class="card-body">
                    <el-table
                        stripe
                        :data="cost_lists.data"
                        v-loading="loading"
                        element-loading-text="Loading..."
                        element-loading-spinner="el-icon-loading"
                        element-loading-background="rgba(0, 0, 0, 0.8)"
                        style="width: 100%,">
                        <el-table-column label="SL">
                            <template slot-scope="scope">{{ scope.$index+1 }} </template>
                        </el-table-column>
                        <el-table-column label="Date">
                            <template slot-scope="scope">{{ scope.row.date }} </template>
                        </el-table-column>
                        <el-table-column label="Description">
                            <template slot-scope="scope">{{ scope.row.description }} </template>
                        </el-table-column> 
                        <el-table-column align="right" label="Amount">
                            <template slot-scope="scope">{{ scope.row.amount+'.00' }} </template>
                        </el-table-column>
                        <el-table-column align="right" label="Action">
                            <template slot="header" slot-scope="scope">
                                <el-input v-model="search" size="mini" placeholder="Description to search"/>{{ scope.search }}
                            </template>
                            <template slot-scope="scope">
                                <el-button size="mini" type="primary" @click="costEdit(scope.row.id)">Edit</el-button>
                                <!-- <el-button size="mini" type="danger" @click="handleDelete(scope.row.id)">Delete</el-button> -->
                            </template>
                        </el-table-column>
                    </el-table>
                    <div class="col-12 pt-2 pl-md-0">
                        <h4 class="pl-3 pl-md-0">Total amount:  {{ total_cost }}.00 TK</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'account-page',
    data(){
      return {
        form: new Form({
            date        : '',
            type        : '',
            amount      : '',              
            bank_id     : '',
            description : '' , 
        }),
        options: [{  label: 'Cost',   type: 1,}, 
                  {  label: 'Credit', type: 2,}, 
                  {  label: 'Debit',  type: 3,}],
        banks: [{  label: 'Home Bank',  bank_id: 1,}, 
                {  label: 'DBBL Bank',  bank_id: 2,}, 
                {  label: 'Prime Bank', bank_id: 3,}],
        busyForm  : false,
        total_amount : '',
        // List Variable
        search: "",
        loading: true,
        cost_lists: "",
        total_cost: "",
      }
    },
    mounted() {
      this.page_load();
      this.cost();
    },
    methods:{
        page_load(){
            this.form.date    = Current_Date;
            this.form.type    = 1;
            this.form.bank_id = 1;
            this.check_bank_amount();
        },
        account(){
            this.busyForm    = true;
            this.form.post(Api_Base_Url+'/account').then(res =>{
                this.$message({ showClose: true, message: res.data.success, type: 'success', });
                this.form.reset();
                this.page_load();
                this.cost();
                this.busyForm   = false;
                $('.total_amount').html('');
            }).catch(error =>{
                this.busyForm    = false;
            });
        },
        check_bank_amount(){
            // Check total amount bank wise
            axios.get(Api_Base_Url+'/bank/'+this.form.bank_id).then(res =>{
                $('.total_amount').html('Current amount : ' + res.data +'.00');
                this.total_amount = res.data;
                if(res.data <= 0 ){
                    this.busyForm    = true;
                }else{
                    this.busyForm    = false;
                }
            });
        },
        check_input_amount(){
            if(this.total_amount < this.form.amount){
                $('.insufficient').html('Insufficient balance');
                this.busyForm  = true; 
                return false;
            }
            else{
                $('.insufficient').html('');
                this.busyForm  = false; 
                return true;
            }
        },
        // List Method
        cost(){
            axios.get(Api_Base_Url+'/account').then(res =>{
                this.cost_lists = res.data.costs;
                this.total_cost = res.data.total_cost;
                this.loading    = false;
            });
        },
        costEdit(account_id) {
            console.log(account_id);
        },
    }
}
</script>