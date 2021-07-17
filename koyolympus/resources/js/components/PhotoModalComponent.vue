<template>
    <transition name="component-fade">
        <div id="overlay" @click="$emit('close')">
            <div id="modal-content">
                <div id="modal-content-top">
                    <img :src="val.url" alt="This photo taken by Koyo Isono.">
                </div>
                <div id="modal-content-bottom">
                    <button class="good">いいね！</button>
                    <button class="close" @click="$emit('close')">閉じる</button>
                </div>
            </div>
        </div>
    </transition>
</template>s

<script>
export default {
    name: "PhotoModalComponent.vue",
    props: ['val']
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
    background-color: rgba(0, 0, 0, 0.8);

    /*　画面の中央に要素を表示させる設定　*/
    display: flex;
    align-items: center;
    justify-content: center;
}

#modal-content {
    z-index: 2;
    width: 80%;
    height: 55vh;
    padding: 1em;
    background-color: rgba(0, 0, 0, 0.5);
}

#modal-content-top {
    width: 80vw;
    text-align: center;
    /*width: 50vw;*/
    /*text-align: center;*/
}

#modal-content-bottom {
    width: 80vw;
    margin-top: 5vh;
    text-align: center;
    /*display: inline-block;*/
    /*width: 15vw;*/
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
}

.close {
    /*top: 0;*/
    /*left: 25vw;*/
}

</style>
