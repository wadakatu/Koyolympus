<template>
    <div class="col">
        <h1 class="aboutMe">About me</h1>
        <language-select-component @update="updateLang"></language-select-component>
        <div class="intro">
            <p v-for="(value, index) in language"
               :class="index % 2 === 0 ? question : answer">
                {{ value }}
            </p>
        </div>
        <button class="top_button" @click="$router.push('/')">TOPへ</button>
    </div>
</template>

<script>
import aboutMe from '../lang/aboutMe.json';
export default {
    components: {
        'language-select-component': () => import('./LanguageSelectComponent'),
    },
    data() {
        return {
            language: aboutMe.japanese,
            question: 'question',
            answer: 'answer'
        }
    },
    methods: {
        updateLang(value) {
            this.language = aboutMe[value];
        }
    }
}

</script>

<style scoped>

p {
    margin: 0 0 1vh 0;
}

.col {
    flex-basis: 50%;
    text-align: center;
}

.aboutMe {
    color: #fff;
    font-size: 40px;
    font-weight: 500;
    margin-bottom: 5px;
    text-align: center;
}

.intro {
    text-align: center;
}

.question {
    color: #fff;
    font-size: 14px;
}

.answer {
    color: #fff;
    font-size: 19px;
    border-bottom: solid 2px #1f6fb2;
    margin-bottom: 10px;
}

.top_button {
    width: 180px;
    color: #000;
    font-size: 12px;
    padding: 12px 0;
    background: #fff;
    border: 0;
    border-radius: 20px;
    outline: none;
    margin-top: 1vh;
}

@media screen and (max-width: 950px) {
    .col {
        height: 100vh;
        width: 70vw;
        padding: 2vh;
    }

    .aboutMe {
        font-size: 35px;
        font-weight: 450;
        margin-bottom: 2vh;
    }

    .top_button {
        width: 130px;
        font-size: 11px;
    }
}

@media screen and (max-width: 480px) {
    button {
        margin-top: 3vh;
    }
}

</style>
