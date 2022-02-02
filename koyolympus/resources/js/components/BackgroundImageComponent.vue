<template>
    <div class="container">
        <header-component></header-component>
        <div class="row">
            <router-view></router-view>
            <router-view name="card"></router-view>
        </div>
        <footer-component></footer-component>
    </div>
</template>

<script>
export default {
    components: {
        'header-component': () => import('./HeaderComponent'),
        'footer-component': () => import('./FooterComponent'),
    }
}
</script>

<style scoped>
.container {
    width: 100%;
    height: 100vh;
    padding: 0 8vw 0 8vw;
    box-sizing: border-box;
}

.row {
    display: flex;
    height: 75%;
    align-items: center;
    justify-content: center;
}

@media screen and (max-width: 950px) {
    .row {
        flex-direction: column;
    }
}

@media screen and (max-width: 480px) {
    .container {
        width: 100vw;
        height: 100%;
    }
}
</style>
