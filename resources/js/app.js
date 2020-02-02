import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router';
import router from './routes';
import GithubRepoViewer from "./components/GithubRepoViewer";
import Layout from "./components/Layout";

Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('github-layout-viewer', GithubRepoViewer);
Vue.component('layout', Layout);

window.Vue = Vue;
Vue.use(VueRouter);
const app = new Vue({
    el: '#app',
    router
});

$.ajaxSetup({
});
