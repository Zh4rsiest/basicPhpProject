// // import bootstrap from 'bootstrap';
import Vue from 'vue/dist/vue.esm.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'jquery/src/jquery.js';
import 'bootstrap/dist/js/bootstrap.min.js';
import axios from 'axios';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import VueToastr from "vue-toastr";

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Vue.use(VueToastr);
Vue.use(VueRouter);

const home = Vue.component('home', require('./components/Home.vue').default);
const navbar = Vue.component('navbar', require('./components/Navbar.vue').default);
const login = Vue.component('login', require('./components/Login.vue').default);
const employeeAdd = Vue.component('employee-add', require('./components/employee/Add.vue').default);
const requests = Vue.component('requests', require('./components/request/Requests.vue').default);
const requestAdd = Vue.component('request-add', require('./components/request/Add.vue').default);

const routes = [
  // { path: '/profile/:unique_id', component: profile },
  { path: '/login', component: login },
  { path: '/', component: home },
  { path: '*', component: home },
];

const router = new VueRouter({
  mode: 'history',
  routes
});

Vue.use(Vuex);

const store = new Vuex.Store({
  state: {
    user: false,
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user;
    },
  },
  actions: {
    fetchUser(context) {
      axios.get('/user/get')
      .then(response => {
        context.commit('SET_USER', response.data);
      })
    },
  }
});

router.beforeEach((to, from, next) => {
  if (window.user === false && store.state.user === false) {
    if (to.path != '/login' ) {
      next({
        path: '/login'
      });
    } else {
      next();
    }
  } else {
    store.dispatch('fetchUser');
    next();
  }
});

const app = new Vue({
    el: '#app',
    router,
    store,
}).$mount('#app');
