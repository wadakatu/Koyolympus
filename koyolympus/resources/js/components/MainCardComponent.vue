<template>
    <div class="col">
        <div class="card" v-if="!isOthers && isVisible">
            <card-component v-for="(value, index) in Object.entries(this.categories).slice(0,4)"
                            :data-image="value[1].image"
                            :title="value[0]"
                            :msg="value[1].message"
                            @click.native="typeof value[1].genre === 'undefined' ? setIsOthers(true) : searchPhoto(value[1])">
            </card-component>
        </div>
        <div class="card" v-else-if="isOthers && isVisible">
            <card-component v-for="(value, index) in Object.entries(this.categories).slice(4,8)"
                            :data-image="value[1].image"
                            :title="value[0]"
                            :msg="value[1].message"
                            @click.native="typeof value[1].genre === 'undefined' ? setIsOthers(false) : searchPhoto(value[1])">
            </card-component>
        </div>
    </div>
</template>

<script>
import categories from '../json/category.json';

export default {
    components: {
        'card-component': () => import('./CardComponent2')
    },
    data() {
        return {
            categories: categories,
            isOthers: false,
            isVisible: true,
            width: window.innerWidth,
            height: window.innerHeight,
            currentPath: this.$route.path,
        }
    },
    methods: {
        searchPhoto(photo) {
            this.$store.commit('photo/setUrl', photo.url);
            this.$store.commit('photo/setGenre', photo.genre);
            this.$router.push({name: photo.name});
        },
        setIsOthers(isOthers) {
            this.isOthers = isOthers;
        },
        handleResize: function () {
            // resizeのたびにこいつが発火するので、ここでやりたいことをやる
            this.width = window.innerWidth;
            this.height = window.innerHeight;

            this.isVisible = !(this.width < 950 && (this.currentPath.match("aboutme") || this.currentPath === '/bizinq'));
        },
        setIsVisible() {
            const currentPath = this.$route.path;
            this.isVisible = currentPath === "/" || 950 < this.width;
        }
    },
    watch: {
        $route() {
            this.setIsVisible();
        }
    },
    created: function () {
        this.setIsVisible();
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
