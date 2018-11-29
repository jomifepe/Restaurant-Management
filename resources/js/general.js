/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import MenuList from './components/MenuComponent.vue';
import sideBar from './components/sidebar/sideBarMenu.vue';
import Login from './components/login.vue';
import Logout from './components/logout';
import Vue from 'vue';
import Vuex from 'vuex';
import store from './stores/global-store';
import VueRouter from 'vue-router';
import Navigation from './components/sidebar/Navigation.vue';
import Register from './components/RegisterUserComponent.vue';

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(store);

Vue.component('sidebar-menu', sideBar);
Vue.component('menu-list', MenuList);
Vue.component('login', Login);
Vue.component('logout', Logout);
Vue.component('navigation', Navigation);
Vue.component('register', Register);

axios.defaults.baseURL = 'http://project.dad/api';

const user = Vue.component('user', require('./components/user.vue'));
const login = Vue.component('login', require('./components/login.vue'));
const logout = Vue.component('logout', require('./components/logout.vue'));
const profile = Vue.component('profile', require('./components/profile.vue'));

/*
const routes = [
    { path: '/users', component: user, name: 'users'},
    { path: '/login', component: login, name: 'login'},
    { path: '/logout', component: logout, name: 'logout'},
    { path: '/profile', component: profile, name: 'profile'},
];

const router = new VueRouter({
    routes:routes
});
*/

const app = new Vue({
    data: {
        isUserLoggedIn: false,
        showMessage: false,
        showLoginForm: false,
        alertClass: "alert-success",
        alertMessage: ""
    },
    //router,
    store,
    methods: {
        showLogin() {
            this.showLoginForm = true;
        },
        onLoginSuccessful(message) {
            this.isUserLoggedIn = true;
            this.showMessage = true;
            this.showLoginForm = false;
            this.alertClass = "alert-success";
            this.alertMessage = message;
        },
        onLoginFailed(message) {
            this.showMessage = true;
            this.alertClass = "alert-danger";
            this.alertMessage = message;
        },
        onLogoutSuccessful() {
            this.isUserLoggedIn = false;
            this.showMessage = true;
            this.alertClass = "alert-success";
            this.alertMessage = "User was logged out successfully";
        },
        closeAlertMessage() {
            this.showMessage = false
        },

    },
    created() {
        console.log('-----');
        console.log(this.$store.state.user);
        this.$store.commit('loadTokenAndUserFromSession');
        console.log(this.$store.state.user);
    }
}).$mount('#app');
