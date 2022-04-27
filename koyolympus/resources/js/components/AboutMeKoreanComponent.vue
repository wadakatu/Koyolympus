<template>
    <div class="col">
        <h1 class="aboutMe_korean">자기소개</h1>
        <language-select-component></language-select-component>
        <div class="intro">
            <h4 class="question_kr">이름</h4>
            <h5 class="answer_kr">코요 이소노</h5>
            <h4 class="question_kr">주소</h4>
            <h5 class="answer_kr">일본, 도쿄와 사이타마 현 사이</h5>
            <h4 class="question_kr">나이/ 별자리/ 생일</h4>
            <h5 class="answer_kr">22살 / 양자리 / 3월 30일</h5>
            <h4 class="question_kr">취미</h4>
            <h5 class="answer_kr">사진 찍기 / 로드 바이킹 / 비디오 게임</h5>
            <h4 class="question_kr">내 카메라</h4>
            <h5 class="answer_kr">올림푸스 PEN-F/ OMD EM-1 MarkⅡ</h5>
            <h4 class="question_kr">내 좌우명</h4>
            <h5 class="answer_kr">될 때까지 부딫혀보자.</h5>
            <button class="top_button" @click="$router.push('/')">HOME</button>
        </div>
    </div>
</template>

<script>

export default {
    name: "AboutMeKoreanComponent.vue",
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

.aboutMe_korean {
    color: #fff;
    font-size: 40px;
    margin-bottom: 5px;
    font-weight: 500;
    text-align: center;
}

.question_kr {
    color: #fff;
    font-size: 15px;
}

.answer_kr {
    color: #fff;
    font-size: 20px;
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
    margin-top: 9px;
}

@media screen and (max-width: 950px) {
    .col {
        height: 100vh;
        width: 70vw;
        padding: 2vh;
    }

    .aboutMe_korean {
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
