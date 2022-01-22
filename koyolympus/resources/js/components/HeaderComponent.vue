<template>
    <div>
        <div class="navbar">
            <router-link v-bind:to="{name: 'main'}"><img src="/images/mylogo_white.png"
                                                         class="logo" alt="myLogo"></router-link>
            <nav>
                <ul>
                    <li><a href="">
                        <router-link v-bind:to="{name: 'about.me'}">About Me</router-link>
                    </a></li>
                    <li><a href="" @click="this.photo">
                        <router-link v-bind:to="{}">Photography</router-link>
                    </a></li>
                    <li><a>
                        <router-link v-bind:to="{name: 'main.biz'}">Biz Inquiries</router-link>
                    </a></li>
                </ul>
            </nav>
            <img src="/images/menu.png" class="menu-icon" alt="menu" @click="showNav">
        </div>
        <sidebar-menu-component v-bind:showSidebar="showSidebars" @close="showSidebars = false"
                                class="sidebar"></sidebar-menu-component>
    </div>
</template>

<script>

export default {
    name: "HeaderComponent.vue",
    components: {
        'sidebar-menu-component': () => import('./SidebarMenuComponent'),
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
    height: 12%;
    display: flex;
    align-items: center;
    padding-top: 20px;
    z-index: 999;
}

.logo {
    width: 150px;
    cursor: pointer;
}

.menu-icon {
    width: 30px;
    cursor: pointer;
    margin-left: 40px;
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

@media screen and (max-width: 760px) {
    .logo {
        width: 150px;
        text-align: center;
    }

    nav {
        display: none;
    }

    .menu-icon {
        width: 40px;
        position: fixed;
        top: 7%;
        right: 20vw;
    }

    .sidebar {
        position: fixed;
        right: 0;
    }
}

@media screen and (max-width: 480px) {
    .navbar {
        height: 12%;
        display: flex;
        align-items: center;
        padding-top: 20px;
        z-index: 999;
    }

    .logo {
        width: 150px;
        text-align: center;
    }

    nav {
        display: none;
    }

    .menu-icon {
        width: 40px;
        position: static;
        padding-left: 20%;
    }

    .sidebar {
        position: fixed;
        right: 0;
    }
}

</style>
