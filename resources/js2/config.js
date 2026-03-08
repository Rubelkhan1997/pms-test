import axios from "axios";


const Base_Url       = process.env.MIX_APP_URL; // Laravel env excess rule : configuration then npm  run watch  
const Api_Base_Url   = 'http://account.cw/api';
const Passport_Token = axios.defaults.headers.common['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
const Auth           = localStorage.getItem('auth');
const Current_Date   = new Date().toJSON().slice(0, 10).replace(/-/g, '-');

export { Base_Url, Api_Base_Url, Auth, Passport_Token, Current_Date};
