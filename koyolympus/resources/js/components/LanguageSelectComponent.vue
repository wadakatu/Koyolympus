<template>
    <div class="select-dropdown">
        <select v-model="language" @change="changeLang()">
            <option value="japanese">日本語</option>
            <option value="english">English</option>
            <option value="french">French</option>
            <option value="korean">한국말</option>
            <option value="chinese">中文</option>
        </select>
    </div>
</template>

<script>
export default {
    name: "LanguageSelectComponent.vue",
    data: () => ({
        language: 'japanese'
    }),
    methods: {
        changeLang() {
            this.$emit('update', this.language);
        }
    },
}
</script>

<style scoped>
.select-dropdown,
.select-dropdown * {
    margin: 0;
    padding: 0;
    position: relative;
    box-sizing: border-box;
}

.select-dropdown {
    width: 100px;
    margin-left: auto;
    position: relative;
    background-color: #E6E6E6;
    border-radius: 4px;
}

.select-dropdown select {
    font-size: 1rem;
    font-weight: normal;
    min-width: 100%;
    padding: 8px 24px 8px 10px;
    border: none;
    background-color: transparent;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.select-dropdown select:active, .select-dropdown select:focus {
    outline: none;
    box-shadow: none;
}

.select-dropdown:after {
    content: "";
    position: absolute;
    top: 50%;
    right: 8px;
    width: 0;
    height: 0;
    margin-top: -2px;
    border-top: 5px solid #aaa;
    border-right: 5px solid transparent;
    border-left: 5px solid transparent;
}

@media screen and (max-width: 810px) {
    .select-dropdown {
        width: 80px;
        height: 25px;
    }

    .select-dropdown select {
        padding: 4px 8px 0 10px;
    }
}
</style>
