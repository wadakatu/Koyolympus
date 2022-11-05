<template>
    <div class="select" tabindex="1">
        <input class="selectopt" name="test" type="radio" id="opt1" checked>
        <label for="opt1" class="option" @click="changeLang('japanese')">日本語</label>
        <input class="selectopt" name="test" type="radio" id="opt2">
        <label for="opt2" class="option" @click="changeLang('english')">English</label>
        <input class="selectopt" name="test" type="radio" id="opt3">
        <label for="opt3" class="option" @click="changeLang('french')">French</label>
        <input class="selectopt" name="test" type="radio" id="opt4">
        <label for="opt4" class="option" @click="changeLang('korean')">Korean</label>
        <input class="selectopt" name="test" type="radio" id="opt5">
        <label for="opt5" class="option" @click="changeLang('chinese')">Chinese</label>
    </div>
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

.select {
    display: flex;
    flex-direction: column;
    position: relative;
    width: 120px;
    height: 40px;
    margin-left: auto;
    border-radius: 20px;
}

.option {
    padding: 0 30px 0 10px;
    min-height: 40px;
    display: flex;
    align-items: center;
    background: #3f3e3e;
    border-top: white solid 1px;
    position: absolute;
    top: 0;
    width: 100%;
    pointer-events: none;
    order: 2;
    z-index: 1;
    transition: background .4s ease-in-out;
    box-sizing: border-box;
    overflow: hidden;
    white-space: nowrap;
    color: white;
}

.option:hover {
    background: #1f1f1f;
}

.select:focus .option {
    position: relative;
    pointer-events: all;
}

input {
    opacity: 0;
    position: absolute;
    left: -99999px;
}

input:checked + label {
    order: 1;
    z-index: 2;
    background: #3f3e3e;
    border-top: none;
    position: relative;
}

input:checked + label:after {
    content: '';
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid white;
    position: absolute;
    right: 10px;
    top: calc(50% - 2.5px);
    pointer-events: none;
    z-index: 3;
}

input:checked + label:before {
    position: absolute;
    right: 0;
    height: 40px;
    width: 40px;
    content: '';
    background: #3f3e3e;
}
</style>
