<template>
    <transition name="component-fade">
        <div id="overlay" @click="$emit('close')">
            <div id="modal-content">
                <img :src="val.url" alt="This photo taken by Koyo Isono.">
                <button @click="$emit('close')">閉じる</button>
            </div>
        </div>
    </transition>
</template>

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
    background-color: rgba(0, 0, 0, 0.5);

    /*　画面の中央に要素を表示させる設定　*/
    display: flex;
    align-items: center;
    justify-content: center;
}

#modal-content {
    z-index: 2;
    width: 80%;
    height: 60vh;
    padding: 1em;
    background: #fff;
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
    object-fit: cover;
    width: 650px;
}


</style>
