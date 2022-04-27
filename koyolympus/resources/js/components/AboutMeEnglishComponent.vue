<template>
    <div class="col">
        <h1 class="aboutMe_english">About me</h1>
        <language-select-component></language-select-component>
        <div class="intro">
            <h4 class="question_en">My Name</h4>
            <h5 class="answer_en">Koyo Isono</h5>
            <h4 class="question_en">My Address</h4>
            <h5 class="answer_en">Japan, between Tokyo and Saitama Pref.</h5>
            <h4 class="question_en">My Age/Zodiac sign/Birthday</h4>
            <h5 class="answer_en">22-year-old / Aries / March 30th</h5>
            <h4 class="question_en">My interest</h4>
            <h5 class="answer_en">Camera / Road Bike / Video game</h5>
            <h4 class="question_en">My Camera</h4>
            <h5 class="answer_en">OLYMPUS PEN-F / OMD EM-1 MarkⅡ</h5>
            <h4 class="question_en">My Motto</h4>
            <h5 class="answer_en">Fake it until you make it.</h5>
            <button class="top_button" @click="$router.push('/')">HOME</button>
        </div>
    </div>
</template>

<script>

export default {
    name: "AboutMeEnglishComponent.vue",
    components: {
        'language-select-component': () => import('./LanguageSelectComponent'),
    }
}
</script>

<style scoped>

h5 {
    margin: 0 0 1vh 0;
}

.col {
    flex-basis: 50%;
}

.intro {
    text-align: center;
}

.aboutMe_english {
    color: #fff;
    font-size: 40px;
    margin-bottom: 15px;
    font-weight: 500;
    text-align: center;
}

.question_en {
    color: #fff;
    font-size: 16px;
}

.answer_en {
    color: #fff;
    font-size: 22px;
    border-bottom: solid 2px #1f6fb2;
    margin-bottom: 14px;
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

    .aboutMe_english {
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

    .question_en {
        font-size: 14px;
    }

    .answer_en {
        font-size: 19px;
    }

    .top_button {
        margin-top: 3vh;
    }
}

</style>
