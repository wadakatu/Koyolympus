<template>
    <transition name="component-fade">
        <div id="overlay" @click.self="$emit('close')">
            <div id="modal-content" @click.self="$emit('close')">
                <div id="modal-content-top" @click.self="$emit('close')">
                    <img :src="val.url" alt="This photo taken by Koyo Isono.">
                </div>
                <div id="modal-content-bottom">
                    <button id="like-heart" v-bind:class="{ press: likeStatus(val.id) || isLiked }"
                            @click.self="like(val.id)" :disabled="isProcessing"></button>
                    <p id="like-count">いいね数：3</p>
                    <span id="liked" v-bind:class="{ press: likeStatus(val.id) || isLiked }">liked!</span>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "PhotoModalComponent.vue",
    props: ['val'],
    data() {
        return {
            isLiked: false,
            isProcessing: false
        };
    },
    methods: {
        like(photoId) {
            let self = this;
            self.isProcessing = true;
            this.$store.dispatch('photo/LikePhotoAction', photoId).then(function (result) {
                self.isLiked = !!result;
            });
            self.isProcessing = false;
        },
    },
    computed: {
        likeStatus: function () {
            self = this;
            return function (photoId) {
                const likeObj = self.$store.state.photo.like;
                return likeObj.includes(photoId);
            };
        }
    }
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

#liked {
    position: absolute;
    bottom: 100px;
    left: 0;
    right: 0;
    visibility: hidden;
    transition: 1s;
    z-index: -2;
    font-size: 5px;
    color: transparent;
    font-weight: 400;
}

#like-heart.press {
    animation: size, border .6s;
    color: #e23b3b;
    border: solid 3px #e23b3b;
}

#liked.press {
    bottom: 180px;
    font-size: 20px;
    visibility: visible;
    animation: fade 2s;
}

#like-count {
    color: #fff;
}

@keyframes fade {
    0% {
        color: #transparent;
    }
    50% {
        color: #e23b3b;
    }
    100% {
        color: #transparent;
    }
}

@keyframes size {
    0% {
        padding: 20px;
    }
    50% {
        padding: 25px;
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

</style>
