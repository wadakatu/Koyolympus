<template>
    <div class="col" v-if="this.card && cardStatus">
        <div class="card" v-model="genre">
            <card-component data-image="/images/yellow.jpeg" title="Landscape"
                            msg="The landscape are there, and I just take them thoroughly."
                            @click.native="searchLandscape"></card-component>
            <card-component data-image="/images/cat.jpeg" title="Animal"
                            msg="If you want to be a better animal photographer, stand in front of more animals."
                            @click.native="searchAnimal"></card-component>
            <card-component data-image="/images/portrait.jpeg" title="Portrait"
                            msg="The whole point of taking portraits is so that I can see how far people have come."
                            @click.native="searchPortrait"></card-component>
            <card-component data-image="/images/wine.jpeg" title="Others"
                            msg="The Earth is art, The photographer is only a witness."
                            @click.native="showOthers"></card-component>
        </div>
    </div>
    <other-card-component v-else-if="!this.card && cardStatus"></other-card-component>
</template>

<script>
export default {
    components: {
        OtherCardComponent: () => import('./OtherCardComponent'),
        'card-component': () => import('./CardComponent2')
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

@media screen and (max-width: 950px) {

    .card {
        padding-bottom: 25px;
    }
}

</style>
