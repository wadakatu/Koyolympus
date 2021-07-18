<template>
    <transition name="component-fade">
        <div id="overlay" @click.self="$emit('close')">
            <div id="modal-content" @click.self="$emit('close')">
                <div id="modal-content-top" @click.self="$emit('close')">
                    <img :src="val.url" alt="This photo taken by Koyo Isono.">
                </div>
                <div id="modal-content-bottom">
                    <i id="like-heart" @click.self="like()"></i>
                    <p id="like-count">いいね数：3</p>
                    <span id="liked">liked!</span>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "PhotoModalComponent.vue",
    props: ['val'],
    methods: {
        like() {
            console.log(this.$store.state.photo.like);
            $("#like-heart,#liked").toggleClass("press", 1000);
        },
    },
    computed: {

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
    padding: 10px 12px 8px;
    background: #fff;
    border-radius: 50%;
    display: inline-block;
    margin: 0 0 15px;
    color: #aaa;
    transition: .2s;
}

#like-heart:hover {
    color: #666;
}

#like-heart:before {
    font-family: fontawesome;
    content: '\f004';
    font-style: normal;
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
    animation: size .6s;
    color: #e23b3b;
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
        padding: 10px 12px 8px;
    }
    50% {
        padding: 14px 16px 12px;
        margin-top: -4px;
    }
    100% {
        padding: 10px 12px 8px;
    }
}

</style>
