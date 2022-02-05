<template>
    <div>
        <div class="photo-list" ontouchstart="">
            <h2 v-show="noPhoto">There are no photos in this page.</h2>
            <div class="images" v-for="photo in photos">
                <img class="item" :src="photo.url" :key="photo.url" v-lazy="photo.url" @click="openModal(photo)"
                     alt="This photo taken by Koyo Isono.">
            </div>
        </div>
        <photo-modal-component :val="postItem" v-show="showContent"
                               @close="closeModal"></photo-modal-component>
    </div>
</template>

<script>
import Vue from 'vue'
import VueLazyload from 'vue-lazyload'
import loadImage from '../../../public/images/spin.png';
import errorImage from '../../../public/images/no_image.png';

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
            postItem: {},
        }
    },
    methods: {
        async fetchPhotos() {
            let self = this;
            let response;
            try {
                response = await axios.get(`/api/randomPhotos`);
            } catch (err) {
                self.$store.commit('error/setCode', err.status);
                return;
            }
            self.photos = response.data;

            if (response.data.length === 0) {
                self.noPhoto = true;
            }
        },
        openModal: function (photo) {
            this.postItem = photo;
            this.showContent = true;
        },
        closeModal: function () {
            this.showContent = false;
        },
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
    height: 70vh;
    overflow: auto;
}

h2 {
    color: #fff;
    position: fixed;
    top: 50vh;
    left: 30vw;
    font-size: 40px;
}

img {
    width: 15vw;
    height: 20vh;
    object-fit: cover;
    cursor: zoom-in;
    padding: 0 5px;
}

.images {
    display: inline-block;
}

@media screen and (max-width: 1350px) {

    h2 {
        font-size: 35px;
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

    img {
        width: 25vw;
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
