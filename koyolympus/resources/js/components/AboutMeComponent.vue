<template>
    <div class="col">
        <h1 class="aboutMe">About me</h1>
        <language-select-component></language-select-component>
        <div class="intro">
            <h4 class="question_jp">名前</h4>
            <h5 class="answer_jp">磯野 公耀</h5>
            <h4 class="question_jp">生息地</h4>
            <h5 class="answer_jp">東京都と埼玉県の狭間</h5>
            <h4 class="question_jp">年齢/星座/誕生日</h4>
            <h5 class="answer_jp">22歳/おひつじ座/3月30日</h5>
            <h4 class="question_jp">趣味</h4>
            <h5 class="answer_jp">カメラ/ロードバイク/ゲーム</h5>
            <h4 class="question_jp">私のカメラ</h4>
            <h5 class="answer_jp">OLYMPUS PEN-F/OMD EM-1 MarkⅡ</h5>
            <h4 class="question_jp">座右の銘</h4>
            <h5 class="answer_jp">Fake it until you make it.</h5>
            <button class="top_button" @click="$router.push('/')">TOPへ</button>
        </div>
    </div>
</template>

<script>

export default {
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

.question_jp {
    color: #fff;
    font-size: 14px;
}

.answer_jp {
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
