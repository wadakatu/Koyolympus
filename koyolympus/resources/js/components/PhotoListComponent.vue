<template>
    <div class="photo-list" ontouchstart="">
        <h2 v-show="noPhoto">There are no photos in this page.</h2>
        <div class="images" v-viewer="{movable: false}">
            <a class="luminous" v-for="photo in photos">
                <img class="item" :src="photo.url" :key="photo.url"
                     alt="This photo taken by Koyo Isono.">
            </a>
        </div>
        <PaginateComponent :current-page="currentPage" :last-page="lastPage"></PaginateComponent>
    </div>
</template>

<script>
import 'viewerjs/dist/viewer.css'
import Viewer from 'v-viewer'
import Vue from 'vue'

Vue.use(Viewer)

export default {
    name: "PhotoListComponent.vue",
    components: {
        PaginateComponent: () => import('./PaginateComponent'),
    },
    props: {
        page: {
            type: Number,
            required: false,
            default: 1,
        },
    },
    data() {
        return {
            photos: [],
            currentPage: 0,
            lastPage: 0,
            noPhoto: false,
        }
    },
    methods: {
        async fetchPhotos() {
            let self = this;
            let response;
            try {
                response = await axios.get(`/api/photos/?page=${self.page}`, {params: {genre: self.genre}});
            } catch (err) {
                self.$store.commit('error/setCode', err.status);
                return;
            }
            self.photos = response.data.data;
            self.currentPage = response.data.current_page;
            self.lastPage = response.data.last_page;
        },
    },
    watch: {
        $route: {
            async handler() {
                await this.fetchPhotos().catch(e => {
                    throw 'getPhoto error' + e.message
                });

                this.noPhoto = this.photos.length === 0;
            },
            immediate: true,
        }
    },
    computed: {
        genre() {
            return this.$store.state.photo.genre;
        }
    }
}
</script>

<style scoped>

.photo-list {
    text-align: center;
}

h2 {
    color: #fff;
    font-size: 40px;
}

img {
    display: inline-block;
    width: 15vw;
    height: 25vh;
    object-fit: cover;
    cursor: zoom-in;
    padding: 0 5px;
}

@media screen and (max-width: 1350px) {
    .photo-list {
        margin-bottom: 10px;
    }

    h2 {
        font-size: 35px;
    }

    .luminous {
        font-size: 11px;
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
