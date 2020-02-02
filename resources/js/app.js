import './bootstrap';
import Vue from 'vue';
import GithubRepoViewer from "./components/GithubRepoViewer";
import Layout from "./components/Layout";

Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('github-layout-viewer', GithubRepoViewer);
Vue.component('layout', Layout);

window.Vue = Vue;
const app = new Vue({
    el: '#app'
});
