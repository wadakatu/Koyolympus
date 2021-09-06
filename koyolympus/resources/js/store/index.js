import Vue from 'vue';
import Vuex from 'vuex';
import createPersistedState from "vuex-persistedstate";
import auth from './auth';
import error from './error';
import photo from './photo';

Vue.use(Vuex)

const store = new Vuex.Store({
    modules: {
        auth,
        error,
        photo,
    },
    plugins: [createPersistedState(
        { // ストレージのキーを指定。デフォルトではvuex
            key: 'koyolympus',

            // 管理対象のステートを指定。pathsを書かない時は`modules`に書いたモジュールに含まれるステート全て。`[]`の時はどれも保存されない
            paths: [
                'photo.url',
                'photo.genre',
                'photo.card',
                'photo.like'
            ],

            // ストレージの種類を指定する。デフォルトではローカルストレージ
            storage: window.sessionStorage
        }
    )]
})


export default store
