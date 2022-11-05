<template>
    <div id="menuArea">
        <input type="checkbox" id="menuToggle"/>

        <label for="menuToggle" class="menuOpen">
            <span class="open"></span>
        </label>

        <div class="menu menuEffects">
            <label for="menuToggle"></label>
            <div class="menuContent" @click="close">
                <ul>
                    <li>
                        <router-link v-bind:to="{name: 'about.me'}">About Me</router-link>
                    </li>
                    <li @click="this.photo">
                        <router-link v-bind:to="{}">Photography</router-link>
                    </li>
                    <li>
                        <router-link v-bind:to="{name: 'main.biz'}">Biz Inquiries</router-link>
                    </li>
                    <li>
                        <a href="https://koyolympus.thebase.in/" target="_blank"
                           rel="noopener noreferrer">E-Commerce
                        </a>
                    </li>
                    <li>
                        <a href="https://www.facebook.com/koyolympus/" target="_blank" rel="noopener noreferrer">
                            Facebook
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/wadakatu/koyolympus" target="_blank" rel="noopener noreferrer">GitHub
                        </a>
                    </li>
                    <li>
                        <a href="https://www.instagram.com/wadakatu1234/?hl=ja" target="_blank"
                           rel="noopener noreferrer">Instagram
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "HamburgerMenuComponent.vue",
    methods: {
        photo() {
            let url = '/photo/random';
            this.$store.commit('photo/setUrl', url);
            this.$store.commit('photo/setGenre', null);
            this.$router.push({name: 'photo.random'}).catch(err => {});
        },
        close() {
            document.getElementById('menuToggle').checked = false;
        }
    },
}
</script>

<style scoped>
input {
    display: none;
}

.menuContent ul li router-link {
    display: block;
}

.menuContent ul li a {
    display: block;
}

.open {
    background-color: white;
    width: 24px;
    height: 4px;
    display: block;
    border-radius: 2px;
    cursor: pointer;
    position: relative;
    top: 8px;
}

.open:before {
    content: "";
    background-color: white;
    width: 24px;
    height: 4px;
    display: block;
    border-radius: 2px;
    position: relative;
    top: -8px;
    transform: rotate(0deg);
    transition: all 0.3s ease;
}

.open:after {
    content: "";
    background-color: white;
    width: 24px;
    height: 4px;
    display: block;
    border-radius: 2px;
    position: relative;
    top: 4px;
    transform: rotate(0deg);
    transition: all 0.3s ease;
}

.menuOpen {
    width: 24px;
    height: 20px;
    display: block;
    padding: 15px;
    cursor: pointer;
    float: right;
}

.menuOpen:hover .open:before {
    top: -9px;
}

.menuOpen:hover .open:after {
    top: 5px;
}

.menu {
    position: fixed;
    width: 100vw;
    height: 100vh;
    top: 0;
    left: 0;
    background: rgba(32, 50, 58, 0.95);
}

.menu label {
    width: 30px;
    height: 30px;
    position: absolute;
    right: 20px;
    top: 20px;
    background-size: 100%;
    cursor: pointer;
}

.menu .menuContent {
    position: relative;
    top: 30%;
    font-size: 30px;
    text-align: center;
    padding-bottom: 20px;
    overflow: auto;
}

.menu ul {
    list-style: none;
    padding: 0;
    margin: 0 auto;
}

.menu ul li a {
    display: block;
    color: white;
    text-decoration: none;
    transition: color 0.2s;
    /*font-family: Trebuchet MS;*/
    text-transform: uppercase;
    padding: 10px 0;
}

.menu ul li a:hover {
    color: #ff8702;
}

.menu ul li:hover {
    background: white;
}

.menuEffects {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.5s, visibility 0.5s;
}

.menuEffects ul {
    transform: translateY(0%);
    transition: all 0.5s;
}

#menuToggle:checked ~ .menuEffects {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.5s;
}

#menuToggle:checked ~ .menuEffects ul {
    opacity: 1;
}

#menuToggle:checked ~ .menuOpen .open {
    background-color: transparent;
}

#menuToggle:checked ~ .menuOpen .open:before {
    content: "";
    background-color: white;
    transform: rotate(45deg);
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1;
}

#menuToggle:checked ~ .menuOpen .open:after {
    content: "";
    background-color: white;
    transform: rotate(-45deg);
    position: relative;
    top: 0;
    right: 0;
    z-index: 1;
}

#menuToggle:not(:checked) ~ .menuEffects ul {
    transform: translateY(-5%);
}

</style>
