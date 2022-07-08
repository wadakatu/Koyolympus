<template>
    <div class="pagination">
        <RouterLink
            v-if="! isFirstPage"
            :to="`${this.url}/?page=${currentPage - 1}`"
        >
            <button class="button prev">&laquo; prev</button>
        </RouterLink>
        <button class="button home" style="background-color: transparent;" @click="moveMainPage">HOME
        </button>
        <RouterLink
            v-if="! isLastPage"
            :to="`${this.url}/?page=${currentPage + 1}`"
        >
            <button class="button next">next &raquo;</button>
        </RouterLink>
    </div>
</template>

<script>
export default {
    name: "PaginateComponent.vue",
    props: {
        currentPage: {
            type: Number,
            required: true
        },
        lastPage: {
            type: Number,
            required: true
        },
    },
    methods: {
        moveMainPage() {
            this.$router.push('/');
        }
    },
    computed: {
        isFirstPage() {
            return this.currentPage === 1;
        },
        isLastPage() {
            return this.currentPage === this.lastPage;
        },
        url() {
            return this.$store.state.photo.url;
        }
    },
}
</script>

<style scoped>

.pagination {
    margin-top: 5vh;
}

.button {
    color: #FFF;
    display: inline-block;
    font-size: 16px;
    font-weight: bold;
    position: fixed;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid transparent;
    outline: rgba(255, 255, 255, 0.5) solid 1px;
    outline-offset: 0;
    text-align: center;
    text-shadow: none;
    transition: all 1.2s cubic-bezier(0.2, 1, 0.2, 1);
    background: transparent;
    cursor: pointer;
}

.button:hover {
    border-color: #FFF;
    box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.5), 0 0 20px rgba(255, 255, 255, 0.2);
    outline-color: transparent;
    outline-offset: 12px;
    text-shadow: 2px 2px 3px #000;
}

.next {
    bottom: 12%;
    right: 26%;
    width: 15vw;
    height: 5vh;
}

.prev {
    bottom: 12%;
    left: 26%;
    width: 15vw;
    height: 5vh;
}

.home {
    bottom: 12%;
    right: 45%;
    width: 10vw;
    height: 5vh;
}

@media screen and (max-width: 880px) {
    .next {
        bottom: 18vh;
        right: 20vw;
        width: 20vw;
    }

    .prev {
        bottom: 18vh;
        left: 20vw;
        width: 20vw;
    }

    .home {
        bottom: 18vh;
        text-align: center;
        height: 3vh;
    }
}

@media screen and (max-width: 680px) {
    .button {
        font-size: 12px;
    }
}

@media screen and (max-width: 480px) {
    .button {
        font-size: 11px;
        position: static;
        margin-top: 1vh;
    }

    .home {
        height: 5vh;
        width: 15vw;
    }

    .next {
        width: 30vw;
    }

    .prev {
        width: 30vw;
    }
}

</style>
