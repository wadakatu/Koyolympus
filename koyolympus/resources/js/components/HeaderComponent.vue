<template>
    <!--    <div>-->
    <div class="navbar">
        <router-link v-bind:to="{name: 'main'}"><img src="/images/mylogo_white.png"
                                                     class="logo" alt="myLogo"></router-link>
        <nav>
            <ul>
                <li class="about_me"><a href="">
                    <router-link v-bind:to="{name: 'about.me'}">About Me</router-link>
                </a></li>
                <li class="photo"><a href="" @click="this.photo">
                    <router-link v-bind:to="{}">Photography</router-link>
                </a></li>
                <li class="biz"><a>
                    <router-link v-bind:to="{name: 'main.biz'}">Biz Inquiries</router-link>
                </a></li>
                <li class="more_menu">
                    <div class="dropdown">
                        <a class="dropbtn">More<i class="fa fa-caret-down"></i></a>
                        <div class="dropdown-content">
                            <a href="https://koyolympus.thebase.in/" target="_blank" rel="noopener noreferrer">E-Commerce</a>
                            <a href="https://www.facebook.com/koyolympus/" target="_blank" rel="noopener noreferrer">Facebook</a>
                            <a href="https://github.com/wadakatu/koyolympus" target="_blank" rel="noopener noreferrer">GitHub</a>
                            <a href="https://www.instagram.com/wadakatu1234/?hl=ja" target="_blank"
                               rel="noopener noreferrer">Instagram</a>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
        <hamburger-menu-component class="hamburger"></hamburger-menu-component>
    </div>
</template>

<script>

export default {
    name: "HeaderComponent.vue",
    components: {
        'hamburger-menu-component': () => import('./HamburgerMenuComponent'),
    },
    data: () => {
        return {
            showSidebars: false,
        }
    },
    methods: {
        showNav() {
            this.showSidebars = this.showSidebars === false;
        },
        photo() {
            let url = '/photo/random';
            this.$store.commit('photo/setUrl', url);
            this.$store.commit('photo/setGenre', null);
            this.$router.push({name: 'photo.random'}).catch(err => {
            });
        },
    },
}
</script>

<style scoped>

.navbar {
    padding: 3vh 8vw 0 8vw;
    display: flex;
    align-items: center;
    z-index: 999;
}

.logo {
    width: 150px;
    cursor: pointer;
}

nav {
    flex: 1;
    text-align: right;
}

nav ul li {
    list-style: none;
    display: inline-block;
    margin-left: 60px;
}

nav ul li a {
    text-decoration: none;
    color: #fff;
    font-size: 15px;
}

/* Dropdown button */
.dropdown .dropbtn {
    /*font-size: 16px;*/
    border: none;
    outline: none;
    color: white;
    background-color: inherit;
    /*margin: 0; !* Important for vertical align on mobile phones *!*/
}

/* Dropdown content (hidden by default) */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: black;
    min-width: 5vw;
    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

/* Links inside the dropdown */
.dropdown-content a {
    float: none;
    color: white;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    text-align: left;
}

/* Add a grey background color to dropdown links on hover */
.dropdown-content a:hover {
    background-color: #252a2d;
}

/* Show the dropdown menu on hover */
.dropdown:hover .dropdown-content {
    display: block;
}

.hamburger {
    display: none;
}

@media screen and (max-width: 810px) {
    .logo {
        text-align: center;
    }

    nav {
        display: none;
    }

    .hamburger {
        display: block;
        margin-left: auto;
    }
}
</style>
