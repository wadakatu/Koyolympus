<template>
    <main-card-component v-if="this.card"></main-card-component>
    <div class="col" v-else-if="!this.card">
        <div class="card" v-model="genre">
            <!--            <div class="card_detail snapshot" @click="searchSnapshot">-->
            <!--                <h5 class="card_other_tittle">SnapShot</h5>-->
            <!--                <p class="card_other_description">It is more important to click with people than to click the-->
            <!--                    shutter.</p>-->
            <!--            </div>-->
            <card-component data-image="/images/snapshot.jpeg" title="SnapShot"
                            msg="It is more important to click with people than to click the shutter."
                            @click.native="searchSnapshot"></card-component>
            <!--            <div class="card_detail livecomp" @click="searchLivecomp">-->
            <!--                <h5 class="card_other_tittle">Live Composite</h5>-->
            <!--                <p class="card_other_description">Since I’m inarticulate, I express myself with images.</p>-->
            <!--            </div>-->
            <card-component data-image="/images/livecomp.jpeg" title="Live Composite"
                            msg="Since I’m inarticulate, I express myself with images."
                            @click.native="searchLivecomp"></card-component>
            <!--            <div class="card_detail pinfilm" @click="searchPinfilm">-->
            <!--                <h5 class="card_other_tittle">Pinhole/Film</h5>-->
            <!--                <p class="card_other_description">Seeing is not enough, you have to feel what you photograph</p>-->
            <!--            </div>-->
            <card-component data-image="/images/film.jpeg" title="Pinhole/Film"
                            msg="Seeing is not enough, you have to feel what you photograph"
                            @click.native="searchPinfilm"></card-component>
            <!--            <div class="card_detail back" @click="showMain">-->
            <!--                <h5 class="back_title">->Back</h5>-->
            <!--            </div>-->
            <card-component data-image="" title="->Back"
                            msg="What you see is what you get."
                            @click.native="showMain"></card-component>
        </div>
    </div>
</template>

<script>
export default {
    name: "OtherCardComponent.vue",
    components: {
        MainCardComponent: () => import('./MainCardComponent'),
        'card-component': () => import('./CardComponent2')
    },
    data() {
        return {
            genre: '',
            url: '',
        }
    },
    methods: {
        searchSnapshot() {
            this.genre = 4;
            this.url = "/photo/others/snapshot";
            this.$store.commit('photo/setUrl', this.url);
            this.$store.commit('photo/setGenre', this.genre);
            this.$router.push({name: 'photo.snapshot'})
        },
        searchLivecomp() {
            this.genre = 5;
            this.url = "/photo/others/livecomposite";
            this.$store.commit('photo/setUrl', this.url);
            this.$store.commit('photo/setGenre', this.genre);
            this.$router.push({name: 'photo.livecomposite'})
        },
        searchPinfilm() {
            this.genre = 6;
            this.url = "/photo/others/pinfilm";
            this.$store.commit('photo/setUrl', this.url);
            this.$store.commit('photo/setGenre', this.genre);
            this.$router.push({name: 'photo.pinfilm'})
        },
        showMain() {
            this.$store.commit('photo/setCard', true);
        },
    },
    computed: {
        card() {
            return this.$store.state.photo.card;
        }
    }
}
</script>

<style scoped>

.col {
    flex-basis: 50%;
}

.card {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

@media screen and (max-width: 950px) {

    .card {
        padding-bottom: 25px;
    }
}

</style>
