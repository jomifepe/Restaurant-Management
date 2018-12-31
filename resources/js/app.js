/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import '@fortawesome/fontawesome-free/css/all.css'
import Vue from 'vue';
import Vuex from 'vuex';
import store from './stores/global-store';
import VueRouter from 'vue-router';
import VueMoment from 'vue-moment'
import VeeValidate from 'vee-validate';
import Routes from './routes';
import Vuetify from 'vuetify'
import Toasted from 'vue-toasted';
import axios from 'axios';
import VueSocketio from 'vue-socket.io';
import App from './components/App.vue';

Vue.use(VeeValidate);
Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(store);
Vue.use(VueMoment);
Vue.use(Vuetify, {iconfont: 'fa'});
Vue.use(Toasted);
Vue.use(new VueSocketio({
    debug: true,
    //connection: 'http://127.0.0.1:8080'
     connection: 'http://178.62.85.56:8080'
}));

axios.defaults.baseURL = 'http://project.dad/api';

const router = new VueRouter({
    routes: Routes
});

router.beforeEach((to, from, next) => {
    let sessionUser = JSON.parse(sessionStorage.getItem('user'));
    /* when the route requires authentication */
    if (to.matched[0].meta.requiresAuth) {
        /* if the user is authenticated */
        if (sessionUser) {
            let isUserAllowed = typeof to.meta.allowed !== 'undefined' && 
                (to.meta.allowed === true || to.meta.allowed.includes(sessionUser.type));
            if (!isUserAllowed) {
                /* goes back to previous route */
                next(from.fullPath);
            } else {
                next();
            }
        } else {
            /* when a user accesses a worker route while not logged in */
            next('login');
        }
    /* when the route doesn't requires authentication */
    } else {
        /* if a user accesses a non-worker route while logged in */
        if (sessionUser) {
            next({name: 'dashboard'});
        } else {
            next();
        }
    }
});

new Vue({
    el: "#app",
    components: { App },    
    router,
    store
});
