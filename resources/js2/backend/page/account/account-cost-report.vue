<template>
    <div class="account-cost-report-page">
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
               <div class="row pt-2">
                    <div class="col-7"> 
                        <el-pagination 
                            @current-change="pagination" 
                            :current-page.sync="currentPage" 
                            :page-size="cost_lists.per_page" 
                            layout="total, prev, pager, next"
                            :total="cost_lists.total">
                        </el-pagination>
                    </div>
                    <div class="col-5">
                        <h4 class="pl-3">Total amount:  {{ total_cost }}.00 TK</h4>
                    </div>
               </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'account-cost-report-page',
    data(){
        return{
            search: "",
            loading: true,
            cost_lists: "",
            total_cost: "",
            currentPage: 1,
        }
    },
    mounted() {
        this.cost();
    },
    methods: {
        cost(){
            axios.get(Api_Base_Url+'/account-cost-report').then(res =>{
                this.cost_lists = res.data.costs;
                this.total_cost = res.data.total_cost;
                this.loading    = false;
            });
        },
        costEdit(account_id) {
            console.log(account_id);
        },
        pagination() {
            //console.log(Api_Base_Url+'/account-cost-report?page='+this.currentPage);
            axios.get(Api_Base_Url+'/account-cost-report?page='+this.currentPage).then(res => {
                this.cost_lists = res.data.costs;
                this.total_cost = res.data.total_cost;
                this.loading    = false;
            });
        },
    },
}
</script>
