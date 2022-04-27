<template>
    <div class="col">
        <h1 class="aboutMe_french">À propos de moi</h1>
        <language-select-component></language-select-component>
        <div class="intro">
            <h4 class="question_fr">Mon Nom</h4>
            <h5 class="answer_fr">Koyo Isono</h5>
            <h4 class="question_fr">Mon Adresse</h4>
            <h5 class="answer_fr">Japan, entre Tokyo et Saitama.</h5>
            <h4 class="question_fr">Mon Âge/Signe du zodiaque/Anniversaire</h4>
            <h5 class="answer_fr">22 ans / Le Bélier / Le 30 Mars</h5>
            <h4 class="question_fr">Mes centres d’intérêt</h4>
            <h5 class="answer_fr">Caméra / Vélo de route / Jeu vidéo</h5>
            <h4 class="question_fr">Ma caméra</h4>
            <h5 class="answer_fr">OLYMPUS PEN-F / OMD EM-1 MarkⅡ</h5>
            <h4 class="question_fr">Ma devise</h4>
            <h5 class="answer_fr">Tout est dans l'attitude</h5>
            <button class="top_button" @click="$router.push('/')">HOME</button>
        </div>
    </div>
</template>

<script>
export default {
    name: "AboutMeFrenchComponent.vue",
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

.aboutMe_french {
    color: #fff;
    font-size: 40px;
    margin-bottom: 15px;
    font-weight: 500;
    text-align: center;
}

.question_fr {
    color: #fff;
    font-size: 16px;
}

.answer_fr {
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
    margin-top: 9px;
}

@media screen and (max-width: 950px) {
    .col {
        height: 100vh;
        width: 70vw;
        padding: 2vh;
    }

    .aboutMe_french {
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

    .question_fr {
        font-size: 14px;
    }

    .answer_fr {
        font-size: 19px;
    }

    .top_button {
        margin-top: 3vh;
    }
}


</style>
