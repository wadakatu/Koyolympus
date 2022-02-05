<template>
    <div class="col" v-if="this.card && cardStatus">
        <div class="card" v-model="genre">
            <div class="card_detail landscape" @click="searchLandscape">
                <h5 class="card_title">Landscape</h5>
                <p class="card_description">The landscape are there, and I just take them.</p>
            </div>
            <div class="card_detail animal" @click="searchAnimal">
                <h5 class="card_title">Animal</h5>
                <p class="card_description">If you want to be a better animal photographer, stand in front of more
                    animals.</p>
            </div>
            <div class="card_detail portrait" @click="searchPortrait">
                <h5 class="card_title">Portrait</h5>
                <p class="card_description">The whole point of taking portraits is so that I can see how far people have
                    come.</p>
            </div>
            <div class="card_detail others" @click="showOthers">
                <h5 class="card_title">Others</h5>
                <p class="card_description">The Earth is art, The photographer is only a witness.</p>
            </div>
        </div>
    </div>
    <other-card-component v-else-if="!this.card && cardStatus"></other-card-component>
</template>

<script>
export default {
    components: {
        OtherCardComponent: () => import('./OtherCardComponent'),
    },
    data() {
        return {
            genre: '',
            url: '',
            cardStatus: true,
            width: window.innerWidth,
            height: window.innerHeight,
            currentPath: this.$route.path,
        }
    },
    methods: {
        searchLandscape() {
            this.genre = 1;
            this.url = '/photo/landscape';
            this.$store.commit('photo/setUrl', this.url);
            this.$store.commit('photo/setGenre', this.genre);
            this.$router.push({name: 'photo.landscape'});
        },
        searchAnimal() {
            this.genre = 2;
            this.url = '/photo/animal';
            this.$store.commit('photo/setUrl', this.url);
            this.$store.commit('photo/setGenre', this.genre);
            this.$router.push({name: 'photo.animal'});
        },
        searchPortrait() {
            this.genre = 3;
            this.url = '/photo/portrait';
            this.$store.commit('photo/setUrl', this.url);
            this.$store.commit('photo/setGenre', this.genre);
            this.$router.push({name: 'photo.portrait'});
        },
        showOthers() {
            this.$store.commit('photo/setCard', false);
        },
        handleResize: function () {
            // resizeのたびにこいつが発火するので、ここでやりたいことをやる
            this.width = window.innerWidth;
            this.height = window.innerHeight;
            const currentPath = this.$route.path;

            this.cardStatus = !(this.width < 950 && (currentPath.match("aboutme") || currentPath === '/bizinq'));
        },
    },
    computed: {
        card() {
            return this.$store.state.photo.card;
        },
    },
    watch: {
        $route() {
            const currentPath = this.$route.path;

            this.cardStatus = currentPath === "/" || 950 < this.width;
        }
    },
    created: function () {
        const currentPath = this.$route.path;
        this.cardStatus = currentPath === "/" || 950 < this.width;
    },
    mounted: function () {
        window.addEventListener('resize', this.handleResize);
    },
    beforeDestroy: function () {
        window.removeEventListener('resize', this.handleResize)
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

.card_detail {
    position: relative;
    width: 30vh;
    height: 32vh;
    border-radius: 10px;
    padding: 15px 25px;
    box-sizing: border-box;
    cursor: pointer;
    background-position: center;
    background-size: cover;
    vertical-align: bottom;
    transition: transform 0.5s;
}

.landscape {
    background-image: url('/images/yellow.jpeg');
}

.animal {
    background-image: url('/images/cat.jpeg');
}

.portrait {
    background-image: url('/images/portrait.jpeg');
}

.others {
    background-image: url('/images/wine.jpeg');
}

.card_detail:hover {
    transform: translateY(-10px);
}

.card_title {
    color: #fff;
    text-shadow: 0 0 5px #999;
}

.card_description {
    color: #fff;
    font-size: 11px;
    text-shadow: 0 0 15px #000;
}

@media screen and (max-width: 1245px) {
    .card {
        flex-direction: column;
        align-items: center;
    }

    .card_detail {
        height: 15vh;
        width: 35vw;
    }
}

@media screen and (max-width: 950px) {

    .card {
        padding-bottom: 25px;
    }

    .card_detail {
        height: 10vh;
        width: 60vh;
        margin-top: 10px;
    }
}

@media screen and (min-height: 910px) and (max-width: 1310px) {
    .card_detail {
        height: 15vh;
        width: 30vw;
    }
}

@media screen and (max-width: 480px) {
    .card_detail {
        width: 40vh;
        margin-top: 3vh;
    }

    .card_description {
        display: none;
    }
}

</style>
