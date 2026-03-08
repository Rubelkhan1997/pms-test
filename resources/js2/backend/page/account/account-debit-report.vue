<template>
    <div class="account-debit-report-page">
        <div class="card"> 
            <div class="card-header border-bottom">
                <h4 class="card-text">Debit Report</h4>
            </div>
            <div class="card-body">
                <el-table
                    stripe
                    :data="debit_lists.data"
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
                    <el-table-column label="Amount">
                        <template slot-scope="scope">{{ scope.row.amount+'.00' }} </template>
                    </el-table-column>
                    <el-table-column label="Description">
                        <template slot-scope="scope">{{ scope.row.description }} </template>
                    </el-table-column> 
                    <el-table-column align="right" >
                        <template slot="header" slot-scope="scope">
                            <el-input v-model="search" size="mini" placeholder="Description to search"/>{{ scope.search }}
                        </template>
                        <template slot-scope="scope">
                            <el-button size="mini" type="primary" @click="debitEdit(scope.row.id)">Edit</el-button>
                            <!-- <el-button size="mini" type="danger" @click="handleDelete(scope.row.id)">Delete</el-button> -->
                        </template>
                    </el-table-column>
                </el-table>
                <el-pagination 
                    @current-change="pagination" 
                    :current-page.sync="currentPage" 
                    :page-size="debit_lists.per_page" 
                    layout="total, prev, pager, next"
                    :total="debit_lists.total">
                </el-pagination>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'account-debit-report-page',
    data() {
      return {
        loading: true,
        debit_lists: "",
        currentPage: 1,
        search: "",
      };
    },
    mounted() {
        this.debit();
    },
    methods: {
        debit(){
            axios.get(Api_Base_Url+'/account-debit-report').then(res =>{
                this.debit_lists = res.data;
                this.loading     = false;
            });
        },
        debitEdit(account_id) {
            console.log(account_id);
        },
        pagination() {
            axios.get(Api_Base_Url+'/account-debit-report?page='+this.currentPage).then(res => {
                this.debit_lists = res.data;
            });
        },
        
    },
}
</script>