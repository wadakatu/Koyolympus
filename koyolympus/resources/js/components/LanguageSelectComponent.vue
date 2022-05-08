<template>
    <ul class="language" ontouchstart="">
        <li class="language_title"><a>Language</a>
            <ul class="language_ul">
                <li class="lang"><a @click="changeLang('japanese')" class="lang_detail">日本語</a></li>
                <li class="lang"><a @click="changeLang('english')" class="lang_detail">English</a></li>
                <li class="lang"><a @click="changeLang('french')" class="lang_detail">French</a></li>
                <li class="lang"><a @click="changeLang('korean')" class="lang_detail">한국말</a></li>
                <li class="lang"><a @click="changeLang('chinese')" class="lang_detail">中文</a></li>
            </ul>
        </li>
    </ul>
</template>

<script>
export default {
    name: "LanguageSelectComponent.vue",
    methods: {
        changeLang(lang) {
            this.$emit('update', lang);
        }
    },
}
</script>

<style scoped>

.language {
    margin-right: 0;
    margin-left: auto;
    width: 75px;
    background-color: #1a5293;
    color: #fff;
    position: relative;
    list-style-type: none;
    font-size: 15px;
    padding: 5px;
    z-index: 990;
}

.language_title {
    width: 25%;
    margin: 0;
    padding: 0;
    text-align: center;
}

.lang_detail {
    display: block;
    margin: 0;
    color: #fff;
    font-size: 12px;
    font-weight: bold;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
    padding: 13px 15px;
    background: #1a5293;
    text-align: center;
}

.language_ul {
    list-style: none;
    position: absolute;
    top: 100%;
    left: 0;
    margin: 0;
    padding: 0;
}

.lang {
    overflow: hidden;
    width: 75px;
    height: 0;
    color: #fff;
    -moz-transition: .2s;
    -webkit-transition: .2s;
    -o-transition: .2s;
    -ms-transition: .2s;
    transition: .2s;
}

.language_title:hover > a {
    background: #1a5293;
    color: #e0dc62;
}

.language_title:hover .lang {
    overflow: visible;
    height: 38px;
    border-bottom: 1px solid #fff;
}

.language_title:hover .lang:first-child {
    border-top: 0;
}

.language_title:hover .lang:last-child {
    border-bottom: 0;
}
</style>
