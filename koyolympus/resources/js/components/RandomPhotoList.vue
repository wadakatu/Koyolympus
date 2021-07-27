<template>
    <div>
        <div class="photo-list" ontouchstart="">
            <h2 v-show="noPhoto">There are no photos in this page.</h2>
            <div class="images" v-for="photo in photos">
                <img class="item" :src="photo.url" :key="photo.url" v-lazy="photo.url" @click="openModal(photo)"
                     alt="This photo taken by Koyo Isono.">
            </div>
        </div>
        <photo-modal-component :val="postItem" :pressed="liked" v-show="showContent"
                               @close="closeModal"></photo-modal-component>
    </div>
</template>

<script>
import {OK} from '../util';
import Vue from 'vue'
import VueLazyload from 'vue-lazyload'

const loadImage = require('/public/images/Spin-0.7s-154px.png')
const errorImage = require('/public/images/20200501_noimage.png')

Vue.use(VueLazyload, {
    preLoad: 1.1,
    loading: loadImage,
    error: errorImage,
    attempt: 1
})

export default {
    name: "RandomPhotoList.vue",
    components: {
        PaginateComponent: () => import('./PaginateComponent'),
        PhotoModalComponent: () => import('./PhotoModalComponent')
    },
    data() {
        return {
            photos: [],
            noPhoto: false,
            showContent: false,
            postItem: '',
            liked: false
        }
    },
    methods: {
        async fetchPhotos() {
            let self = this;
            let response;
            try {
                response = await axios.get(`/api/randomPhotos`).catch(e => {
                    throw 'getPhoto error' + e.message
                });
            } catch (err) {
                self.$store.commit('error/setCode', err.status);
                return;
            }
            if (response.status !== OK) {
                self.$store.commit('error/setCode', response.status);
                return false;
            }
            self.photos = response.data;

            if (response.data.length === 0) {
                self.noPhoto = true;
            }
        },
        openModal: function (photo) {
            this.postItem = photo;

            this.liked = false;
            const likeArray = this.$store.state.photo.like;
            console.log(likeArray);
            if (likeArray.includes(photo.id)) {
                this.liked = true;
            }

            this.showContent = true;
        },
        closeModal: function () {
            this.showContent = false;
        }
    },
    computed: {
        likeStatus() {
            return this.$store.state.photo.like;
        }
    },
    watch: {
        $route: {
            async handler() {
                await this.fetchPhotos().catch(e => {
                    throw 'getPhoto error' + e.message
                });
            },
            immediate: true,
        }
    },
}
</script>

<style scoped>

.photo-list {
    text-align: center;
    height: 50vh;
    margin-bottom: 5vh;
}

h2 {
    color: #fff;
    position: fixed;
    top: 50vh;
    left: 30vw;
    font-size: 40px;
}

img {
    width: 10vw;
    height: 15vh;
    object-fit: cover;
    cursor: zoom-in;
    padding: 0 5px;
}

.images {
    display: inline-block;
}

@media screen and (max-width: 1350px) {
    .photo-list {
        margin-bottom: 10px;
    }

    h2 {
        font-size: 35px;
    }

    img {
        width: 14vw;
        height: 22vh;
    }
}

@media screen and (max-width: 1050px) {

    h2 {
        left: 28vw;
        font-size: 30px;
    }
}

@media screen and (max-width: 950px) {

    h2 {
        left: 25vw;
    }

    .images {
        margin-top: 10vh;
    }
}

@media screen and (max-width: 900px) {
    img {
        height: 20vh;
    }
}

@media screen and (max-width: 710px) {
    img {
        width: 12vw;
    }
}

@media screen and (max-width: 650px) {
    .photo-list {
        margin-top: 3vh;
    }

    h2 {
        left: 20vw;
        font-size: 25px;
    }
}

@media screen and (max-width: 515px) {
    img {
        width: 12vw;
    }
}

@media screen and (max-width: 480px) {
    .photo-list {
        min-height: 80vh;
        width: 100vw;
    }

    .images {
        margin-top: 1vh;
    }

    h2 {
        font-size: 17px;
    }

    img {
        width: 40vw;
        height: 12vh;
    }
}

</style>
