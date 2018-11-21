/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import MenuList from './components/MenuComponent.vue'
import Login from './components/login.vue'
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex)


Vue.component('menu-list', MenuList);
Vue.component('login-user', Login);

const app = new Vue({ 
    el: '#app'
})
