/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import './bootstrap';
import Vue from 'vue';
import router from './router';
import store from './store';
import {INTERNAL_SERVER_ERROR, NOT_FOUND} from "./util";

require('./bootstrap');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

Vue.component('background-image-component', () => import('./components/BackgroundImageComponent'))

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const createApp = async () => {
    await store.dispatch('auth/currentUser');

    const app = new Vue({
        el: '#app',
        router: router,
        store,
        computed: {
            errorCode() {
                return this.$store.state.error.code;
            }
        },
        watch: {
            errorCode: {
                handler(val) {
                    if (val === INTERNAL_SERVER_ERROR || val === NOT_FOUND) {
                        this.$router.push('/error');
                    }
                },
                immediate: true
            },
            $route() {
                this.$store.commit('error/setCode', null);
            }
        }
    })
}


createApp()
