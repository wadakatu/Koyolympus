<template>
    <transition name="component-fade">
        <div id="overlay" @click.self="$emit('close')">
            <div id="modal-content" @click.self="$emit('close')">
                <div id="modal-content-top" @click.self="$emit('close')">
                    <img :src="val.url" alt="This photo taken by Koyo Isono.">
                </div>
                <div id="modal-content-bottom">
                    <button id="like-heart" v-bind:class="{ press: like, static: like && isLiked }"
                            @click.self="likeOrNot(val.id)" :disabled="isProcessing"></button>
                    <p id="like-count">いいね数：{{ good }}</p>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "PhotoModalComponent.vue",
    props: {
        val: Object,
    },
    data() {
        return {
            isProcessing: false,
            like: false,
            isLiked: false,
            good: 0
        };
    },
    methods: {
        async likeOrNot(photoId) {
            let self = this;
            //ボタンの多重起動防止ON
            self.isProcessing = true;
            await self.$store.dispatch('photo/searchLikedPhoto', photoId)
                //LIKE済の場合
                .then(async function (result) {
                    if (!result) {
                        //LIKE処理
                        await self.likePhoto(photoId).catch(
                            e => {
                                self.$store.commit('error/setCode', e.status);
                            }
                        );
                        await self.$store.commit('photo/setLike', photoId);
                        self.good++;
                        self.like = true;
                    } else {
                        //LIKE解除処理
                        await self.unlikePhoto(photoId).catch(
                            e => {
                                self.$store.commit('error/setCode', e.status);
                            }
                        );
                        await self.$store.commit('photo/unsetLike', result);
                        self.good < 0 ? self.good = 0 : self.good--;
                        self.like = false;
                    }
                });
            //ボタンの多重起動防止OFF
            self.isProcessing = false;
        },
        async likePhoto(photoId) {
            await axios.post(`/api/like`, {id: photoId});
        },
        async unlikePhoto(photoId) {
            await axios.post('/api/unlike', {id: photoId});
        },
        async getLike(photoId) {
            return await axios.post('/api/get/like', {id: photoId});
        },
        likeStatus: function (photoId) {
            let self = this;
            const likeObj = self.$store.state.photo.like;
            return likeObj.includes(photoId);
        }
    },
    watch: {
        'val.id': function () {
            let self = this;
            const photoId = self.val.id;
            const liked = self.likeStatus(photoId);
            this.getLike(photoId)
                .then(res => {
                    self.good = res.data.all_likes;
                })
                .catch(e => {
                    self.$store.commit('error/setCode', e.response.status);
                });
            self.like = liked;
            self.isLiked = liked;
        }
    },
}
</script>

<style scoped>

#overlay {
    /*　要素を重ねた時の順番　*/
    z-index: 1;

    /*　画面全体を覆う設定　*/
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);

    /*　画面の中央に要素を表示させる設定　*/
    display: flex;
    align-items: center;
    justify-content: center;
}

#modal-content {
    z-index: 2;
    width: 80vw;
    height: 55vh;
    padding: 1em;
    background-color: rgba(0, 0, 0, 0.5);
}

#modal-content-top {
    width: 80vw;
    text-align: center;
}

#modal-content-bottom {
    width: 80vw;
    margin-top: 3vh;
    text-align: center;
}

.component-fade-enter-active,
.component-fade-leave-active {
    transition: opacity .4s ease;
}

.component-fade-enter,
.component-fade-leave-to {
    opacity: 0;
}

img {
    object-fit: contain;
    height: 400px;
    pointer-events: none;
    border: 1px solid white;
}

#like-heart {
    cursor: pointer;
    padding: 20px;
    background: #fff;
    border-radius: 80%;
    display: inline-block;
    margin: 0 0 15px;
    color: #aaa;
    transition: .2s;
    border: solid 3px #aaa;
}

#like-heart:hover {
    color: #666;
}

#like-heart:before {
    font-family: fontawesome;
    content: '\f004';
    font-style: normal;
    font-size: 25px;
}

#like-heart.press {
    animation: bound-anim .6s, size, border .6s;
    color: #e23b3b;
    border: solid 3px #e23b3b;
}

#like-heart.static {
    color: #e23b3b;
    border: solid 3px #e23b3b;
}

#like-count {
    color: #fff;
}

@keyframes size {
    0% {
        padding: 20px;
    }
    50% {
        padding: 50px;
        margin-top: -10px;
    }
    100% {
        padding: 20px;
    }
}

@keyframes border {
    0% {
        border: solid 3px #aaaaaa;
    }
    50% {
        border: solid 3px #d36561;
    }
    100% {
        border: solid 3px #e23b3b;
    }
}

@keyframes bound-anim {
    0%, 100% {
        top: 0;
        transform: scale(1);
    }
    30% {
        top: -60%;
        transform: scale(0.80, 1.20);
    }
    70% {
        top: 0;
        transform: scale(1.20, 0.80);
    }
}

</style>
