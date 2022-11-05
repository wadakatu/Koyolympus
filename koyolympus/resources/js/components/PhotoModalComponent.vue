<template>
    <transition name="component-fade">
        <div id="overlay" @click.self="$emit('close')">
            <div id="modal-content" @click.self="$emit('close')">
                <div id="modal-content-top">
                    <img :src="val.url" alt="This photo taken by Koyo Isono.">
                </div>
                <div id="modal-content-bottom">
                    <like-component :id="photoId"></like-component>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import LikeComponent from './LikeComponent';

export default {
    name: "PhotoModalComponent.vue",
    components: {
        'like-component': LikeComponent
    },
    props: {
        val: Object,
    },
    computed: {
        photoId: {
            get() {
                return this.$props.val.id;
            },
        }
    },
}
</script>

<style scoped>

#overlay {
    /*　要素を重ねた時の順番　*/
    z-index: 2;

    /*　画面全体を覆う設定　*/
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);

    /*　画面の中央に要素を表示させる設定　*/
    display: flex;
    align-items: center;
    justify-content: center;
}

#modal-content-top {
    text-align: center;
}

#modal-content-bottom {
    margin-top: 3vh;
}

img {
    object-fit: contain;
    height: 60vh;
    pointer-events: none;
    border: 1px solid white;
}

@media screen and (max-width: 1350px) {
    img {
        height: 45vh;
    }
}

@media screen and (max-width: 1050px) {
    img {
        height: 35vh;
    }
}
</style>
