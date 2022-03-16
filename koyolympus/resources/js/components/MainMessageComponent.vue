<template>
    <div class="col">
        <div class="profile">
            <h1 class="title">Koyolympus</h1>
            <p class="description">
                Photography is a means of capturing and preserving the instant that doesn't come twice.
                That's why I cannot stop exploring the world with my camera gear to cut out the exact moment
                that I would like to keep as it is.
            </p>
            <button class="photo_button" @click="this.photo">Explore My Photo</button>
        </div>
    </div>
</template>

<script>

export default {
    name: "MainMessageComponent.vue",
    methods: {
        photo() {
            let url = '/photo/random';
            this.$store.commit('photo/setUrl', url);
            this.$store.commit('photo/setGenre', null);
            this.$router.push({name: 'photo.random'}).catch(err => {
            });
        }
    },
}

</script>

<style scoped>

.col {
    flex-basis: 50%;
}

.title {
    color: #fff;
    font-size: 95px;
}

.description {
    color: #fff;
    font-size: 13px;
    line-height: 18px;
}

.photo_button {
    width: 180px;
    color: #000;
    font-size: 12px;
    padding: 12px 0;
    background: #fff;
    border: 0;
    border-radius: 20px;
    outline: none;
    margin-top: 30px;
    cursor: pointer;
}

@media screen and (max-width: 1150px) {
    .title {
        font-size: 80px;
    }

    .description {
        line-height: 20px;
    }
}

@media screen and (max-width: 950px) {
    .profile {
        text-align: center;
    }

    .title {
        font-size: 60px;
        margin-top: 20px;
    }

    .photo_button {
        margin: 20px 0 10px 0;
    }
}

@media screen and (max-width: 480px) {
    .title {
        font-size: 50px;
    }
}

</style>
