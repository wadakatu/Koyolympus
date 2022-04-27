<template>
    <div class="col">
        <h1 class="aboutMe_chinese">关于我</h1>
        <language-select-component></language-select-component>
        <div class="intro">
            <h4 class="question_ch">我的名字</h4>
            <h5 class="answer_ch">磯野公耀</h5>
            <h4 class="question_ch">我的地址</h4>
            <h5 class="answer_ch">日本东京 埼玉县</h5>
            <h4 class="question_ch">年龄/标志/生日</h4>
            <h5 class="answer_ch">22岁/白羊座/三月三十号</h5>
            <h4 class="question_ch">我的爱好</h4>
            <h5 class="answer_ch">摄影/公路车/电竞游戏</h5>
            <h4 class="question_ch">我的相机</h4>
            <h5 class="answer_ch">OLYMPUS PEN-F/OMD EM-1 MarkⅡ</h5>
            <h4 class="question_ch">我的格言</h4>
            <h5 class="answer_ch">Fake it until you make it.</h5>
            <button class="top_button" @click="$router.push('/')">TOPへ</button>
        </div>
    </div>
</template>

<script>

export default {
    name: "AboutMeChineseComponent.vue",
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

.aboutMe_chinese {
    color: #fff;
    font-size: 40px;
    font-weight: 500;
    margin-bottom: 5px;
    text-align: center;
}

.question_ch {
    color: #fff;
    font-size: 14px;
}

.answer_ch {
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

    .aboutMe_chinese {
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
    .top_button {
        margin-top: 3vh;
    }
}

</style>
