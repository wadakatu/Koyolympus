<template>
    <div class="card_container" :style="cssVars">
        <div class="overlay">
            <div class="items"></div>
            <div class="items head">
                <p>{{ title }}</p>
                <hr>
            </div>
            <div class="items cart">
                {{ msg }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "CardComponent2.vue",
    props: ['dataImage', 'title', 'msg'],
    computed: {
        cssVars() {
            return {
                'background-image': `url(${this.dataImage})`,
            }
        }
    }
}
</script>

<style lang="scss" scoped>
$bg: #ffffff;

.card_container {
    position: relative;
    width: 15vw;
    height: 30vh;
    border-radius: 10px;
    margin: 2vh;
    box-sizing: border-box;
    cursor: pointer;
    vertical-align: bottom;
    transition: transform 0.5s;

    background: black center;
    background-size: cover;

    -webkit-box-shadow: 0 0 5px #000;
    box-shadow: 0 0 5px #000;
}

.overlay {
    width: 100%;
    height: 100%;
    border-radius: 10px;

    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 1fr 2fr 2fr 1fr;

    color: $bg;
    transition: all 0.5s;

    font-family: 'Playfair Display', serif;
}


.items {
    letter-spacing: 3px;
}

.head {
    font-size: 20px;
    line-height: 30px;
    padding: 1em;
    text-shadow: 0 0 10px #000;

    transform: translateY(40px);
    transition: all 0.7s;

    hr {
        display: block;
        width: 0;

        border: none;
        border-bottom: solid 2px $bg;

        position: absolute;
        bottom: 0;
        left: 20px;

        transition: all .5s;
    }
}

.cart {
    font-size: 12px;
    opacity: 0;
    letter-spacing: 1px;
    font-family: 'Lato', sans-serif;
    transform: translateY(40px);
    transition: all 0.7s;
    padding: 1em;

    i {
        font-size: 16px;
    }

    span {
        margin-left: 10px;
    }
}

.card_container:hover .overlay {
    opacity: 1;
    background: rgba(16, 16, 16, 0.7);

    & .head {
        transform: translateY(0px);
    }

    & hr {
        width: 75px;
        transition-delay: 0.4s;
    }

    & .cart {
        transform: translateY(0px);
        transition-delay: 0.6s;
        opacity: 1;
    }
}

@media screen and (max-width: 950px) {
    .card_container {
        width: 35vw;
        height: 35vh;
        margin: 2vh;
    }
}

@media screen and (max-width: 630px) {
    .card_container {
        width: 30vw;
        height: 30vh;
    }
}

@media screen and (max-width: 550px) {
    .card_container {
        width: 70vw;
        height: 15vh;
        margin: 1vh;
    }

    .overlay {
        opacity: 1;
        background: transparent;
    }

    .cart {
        display: none;
    }
}

@media screen and (max-height: 600px) {
    .head {
        line-height: 0;
        transform: translateY(10px);
    }
}
</style>
