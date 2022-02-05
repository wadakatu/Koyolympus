<template>
    <div class="container">
        <div class="row">
            <router-view></router-view>
            <router-view name="card"></router-view>
        </div>
    </div>
</template>

<script>
export default {}
</script>

<style scoped>
.container {
    flex: 1;
    padding: 0 8vw 0 8vw;
    box-sizing: border-box;
}

.row {
    display: flex;
    height: 100%;
    align-items: center;
    justify-content: center;
}

@media screen and (max-width: 950px) {
    .row {
        flex-direction: column;
        height: 100%;
    }
}

@media screen and (max-width: 480px) {
    .container {
        width: 100vw;
    }
}
</style>
