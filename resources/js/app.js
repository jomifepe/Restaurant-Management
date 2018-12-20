/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import '@fortawesome/fontawesome-free/css/all.css'
import Vue from 'vue';
import Vuex from 'vuex';
import store from './stores/global-store';
import VueRouter from 'vue-router';
import Vuelidate from 'vuelidate'
import VueMoment from 'vue-moment'
import VeeValidate from 'vee-validate';
import Routes from './routes';
import App from './components/App.vue';
import Vuetify from 'vuetify'
import Toasted from 'vue-toasted';

Vue.use(VeeValidate);
Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(store);

Vue.use(Vuelidate);

Vue.use(VueMoment);
Vue.use(Vuetify, {iconfont: 'fa'});
Vue.use(Toasted);

axios.defaults.baseURL = 'http://project.dad/api';

const router = new VueRouter({
    routes: Routes
});

// router.beforeEach((to, from, next) => {
//     if ((to.name == 'profile') || (to.name == 'logout')) {
//         if (!store.state.user) {
//             next("/login");
//             return;
//         }
//     }
//     next();
// });

new Vue({
    el: "#app",
    components: { App },
    router,
    store
});
