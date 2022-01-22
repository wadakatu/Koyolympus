import Vue from "vue";
import VueRouter from "vue-router";
import store from "./store";
import MainCardComponent from "./components/MainCardComponent";

Vue.use(VueRouter);

const router = new VueRouter({
    mode: "history",
    routes: [
        {
            path: '/',
            components: {
                default: () => import('./components/MainMessageComponent'),
                card: MainCardComponent
            },
            name: 'main',
        },
        {
            path: '/aboutme',
            components: {
                default: () => import('./components/AboutMeComponent'),
                card: MainCardComponent,
            },
            name: 'about.me',
        },
        {
            path: '/aboutme/english',
            components: {
                default: () => import('./components/AboutMeEnglishComponent'),
                card: MainCardComponent,
            },
            name: 'about.me.english'
        },
        {
            path: '/aboutme/french',
            components: {
                default: () => import('./components/AboutMeFrenchComponent'),
                card: MainCardComponent,
            },
            name: 'about.me.french',
        },
        {
            path: '/aboutme/korean',
            components: {
                default: () => import('./components/AboutMeKoreanComponent'),
                card: MainCardComponent,
            },
            name: 'about.me.korean',
        },
        {
            path: '/aboutme/chinese',
            components: {
                default: () => import('./components/AboutMeChineseComponent'),
                card: MainCardComponent,
            }
        },
        {
            path: '/bizinq',
            components: {
                default: () => import('./components/BizInquiriesComponent'),
                card: MainCardComponent,
            },
            name: 'main.biz'
        },
        {
            path: '/upload',
            components: {
                default: () => import('./components/PhotoUpComponent'),
                card: MainCardComponent,
            },
            name: 'photo.upload',
            beforeEnter(to, from, next) {
                if (store.getters['auth/check']) {
                    next();
                } else {
                    next('/login')
                }
            }
        },
        {
            path: '/login',
            components: {
                default: () => import('./components/LoginComponent'),
                card: MainCardComponent,
            },
            name: 'login',
            beforeEnter(to, from, next) {
                if (store.getters['auth/check']) {
                    next('/upload');
                } else {
                    next();
                }
            }
        },
        {
            path: '*',
            components: {
                default: () => import('./pages/errors/CommonErrorComponent'),
            },
        },
        {
            path: '/photo',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.all',
        },
        {
            path: '/photo/random',
            component: () => import('./components/RandomPhotoList'),
            name: 'photo.random',
        },
        {
            path: '/photo/landscape',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.landscape'
        },
        {
            path: '/photo/animal',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.animal'
        },
        {
            path: '/photo/portrait',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.portrait'
        },
        {
            path: '/photo/others/snapshot',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.snapshot'
        },
        {
            path: '/photo/others/livecomposite',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.livecomposite'
        },
        {
            path: '/photo/others/pinfilm',
            component: () => import('./components/PhotoListComponent'),
            props: route => {
                const page = route.query.page;
                return {page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1}
            },
            name: 'photo.pinfilm'
        },
    ]
});

export default router;
