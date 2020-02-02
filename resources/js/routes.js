import VueRouter from 'vue-router';
import Layout from './components/Layout.vue';
import GithubRepoViewer from './components/GithubRepoViewer.vue';

let routes = [
    {
        path: '/',
        component: Layout
    }
];

export default new VueRouter({
    routes
});
