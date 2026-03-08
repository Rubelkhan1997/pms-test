require('./bootstrap');
window.Vue = require('vue').default;
// Event Bus 
window.EventBus = new Vue();
// Vue Router Import
import router from './router';
// V-form Import
import vform from 'vform';
window.Form = vform;
// Config Import
import { Base_Url, Api_Base_Url, Auth, Passport_Token, Current_Date } from './config';
window.Base_Url       = Base_Url;
window.Api_Base_Url   = Api_Base_Url;
window.Auth           = Auth;
window.Passport_Token = Passport_Token;
window.Current_Date   = Current_Date;
// // Message
// import { Message } from 'element-ui';
// import 'element-ui/lib/theme-chalk/index.css';
// Vue.prototype.$message = Message;
// All Feature 
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
Vue.use(ElementUI);
// configure language
import locale from 'element-ui/lib/locale';
import lang from 'element-ui/lib/locale/lang/en';
locale.use(lang);

// import components


// Master Route Component
Vue.component('master-route', require('./backend/master-route').default);
// Pagination
Vue.component('pagination', require('laravel-vue-pagination'));


const app = new Vue({
    el: '#app',
    router,
});
