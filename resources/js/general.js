/*jshint esversion: 6 */

"use strict";

require('./bootstrap');

window.Vue = require('vue');

import MenuList from './components/MenuComponent.vue'

Vue.component('menu-list', MenuList);

const app = new Vue({ 
    el: '#app'
})
