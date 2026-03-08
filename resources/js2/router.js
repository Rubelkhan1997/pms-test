// Vue Router Import
import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import login               from './backend/login';
import dashboardPage       from './backend/dashboard/dashboard';
//  ************ Account Page *********//
import accountPage         from './backend/page/account/account';
import accountCostReport   from './backend/page/account/account-cost-report';
import accountCreditReport from './backend/page/account/account-credit-report';
import accountDebitReport  from './backend/page/account/account-debit-report';
// **************** Bank Page *********//
import bankDepositPage     from './backend/page/bank/bank-deposit';
import bankTransferPage    from './backend/page/bank/bank-amount-transfer';
import bankReport          from './backend/page/bank/bank-report';
// ************* Setting Page *********//
import profilePage         from './backend/profile-page';
import settingPage         from './backend/setting-page';
import notfoundPage        from './backend/404';


const router = new VueRouter({
    mode:'history',
    routes:[
        { path: '/', name: 'login-page', component: login,
            // If the user is already logged in, it will be sent to the dashboard
            beforeEnter: (to, from, next) => {
                const isAuthenticated = localStorage.getItem('token') ? true : false;
                if (to.name == 'login-page' && isAuthenticated) next({ name: 'dashboard-page' })
                else next();
            },
        },
        { path: '/admin/dashboard',                name: 'dashboard-page',        component: dashboardPage },
        //  ************ Account Route *********//
        { path: '/admin/account-page',             name: 'account-page',          component: accountPage },
        { path: '/admin/account-cost-report',      name: 'account-cost-report',   component: accountCostReport },
        { path: '/admin/account-credit-report',    name: 'account-credit-report', component: accountCreditReport },
        { path: '/admin/account-debit-report',     name: 'account-debit-report',  component: accountDebitReport },
        // **************** Bank Route *********//
        { path: '/admin/bank-deposit-page',        name: 'bank-deposit-page',     component: bankDepositPage },
        { path: '/admin/bank-amount-transfer',     name: 'bank-transfer-page',    component: bankTransferPage },
        { path: '/admin/bank-report',              name: 'bank-report',           component: bankReport },
        // ************* Setting Route *********//
        { path: '/admin/profile',                  name: 'profile-page',          component: profilePage },
        { path: '/admin/setting',                  name: 'setting-page',          component: settingPage },
        { path: '*',                               name: 'not-found-page',        component: notfoundPage },

       
    ],
    
});
// Global Validation  If the user is logged in, it will be sent to the dashboard
router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('token') ? true : false;
    if (to.name !== 'login-page' && !isAuthenticated) next({ name: 'login-page' })
    else next()
})


export default router;