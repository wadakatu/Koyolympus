<template>
    <button id="likeButton" class="button dark" :class="{liked:likeStatus(id)}" :disabled="isProcessing"
            @click="pushLike(id)">
        <div class="hand">
            <div class="thumb"></div>
        </div>
        <span>Like<span>d</span></span>
    </button>
</template>

<script>
export default {
    name: "LikeComponent.vue",
    data() {
        return {
            isProcessing: false,
        };
    },
    props: {
        id: {
            type: String,
        },
    },
    methods: {
        async pushLike(photoId) {
            let self = this;
            //ボタンの多重起動防止ON
            self.isProcessing = true;
            document.getElementById('likeButton').classList.toggle('liked');
            await self.$store.dispatch('photo/searchLikedPhoto', photoId)
                //LIKE済の場合
                .then(async function (result) {
                    if (!result) {
                        //LIKE処理
                        await self.proceedLike(photoId);
                    } else {
                        //LIKE解除処理
                        await self.removeLike(photoId);
                    }
                })
                .catch(function (error) {
                    document.getElementById('likeButton').classList.toggle('liked');
                });
            //ボタンの多重起動防止OFF
            self.isProcessing = false;
        },
        async likePhoto(photoId) {
            await axios.post(`/api/like`, {id: photoId});
        },
        async unlikePhoto(photoId) {
            await axios.post('/api/unlike', {id: photoId});
        },
        async proceedLike(photoId) {
            await this.likePhoto(photoId).catch(
                e => {
                    this.$store.commit('error/setCode', e.status);
                }
            );
            await this.$store.commit('photo/setLike', photoId);
            this.good++;
            this.like = true;
        },
        async removeLike(photoId) {
            await this.unlikePhoto(photoId).catch(
                e => {
                    this.$store.commit('error/setCode', e.status);
                }
            );
            await this.$store.commit('photo/unsetLike', photoId);
            this.good < 0 ? this.good = 0 : this.good--;
            this.isLiked = false;
            this.like = false;
        },
        likeStatus(photoId) {
            let self = this;
            const likeObj = self.$store.state.photo.like;
            return likeObj.includes(photoId);
        }
    },
}
</script>

<style scoped>
#likeButton {
    text-align: right;
}

.button {
    display: block;
    outline: none;
    cursor: pointer;
    position: relative;
    border: 0;
    background: none;
    padding: 8px 20px 8px 24px;
    border-radius: 9px;
    line-height: 27px;
    font-family: inherit;
    font-weight: 600;
    font-size: 1rem;
    color: var(--color);
    -webkit-appearance: none;
    -webkit-tap-highlight-color: transparent;
    transition: color 0.2s linear;
    margin: 0 auto;
}

.button.dark {
    --color: #F6F8FF;
    --color-hover: #F6F8FF;
    --color-active: #fff;
    --icon: #8A91B4;
    --icon-hover: #BBC1E1;
    --icon-active: #fff;
    --background: #39393d;
    --background-hover: #242428;
    --background-active: #275EFE;
    --border: transparent;
    --border-active: transparent;
    --shadow: rgba(0, 17, 119, 0.16);
}

.button:hover {
    --icon: var(--icon-hover);
    --color: var(--color-hover);
    --background: var(--background-hover);
    --border-width: 2px;
}

.button:active {
    --scale: .95;
}

.button:not(.liked):hover {
    --hand-rotate: 8;
    --hand-thumb-1: -12deg;
    --hand-thumb-2: 36deg;
}

.button.liked {
    --span-x: 2px;
    --span-d-o: 1;
    --span-d-x: 0;
    --icon: var(--icon-active);
    --color: var(--color-active);
    --border: var(--border-active);
    --background: var(--background-active);
}

.button:before {
    content: "";
    min-width: 103px;
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    transition: background 0.2s linear, transform 0.2s, box-shadow 0.2s linear;
    transform: scale(var(--scale, 1)) translateZ(0);
    background: var(--background);
    box-shadow: inset 0 0 0 var(--border-width) var(--border), 0 4px 8px var(--shadow), 0 8px 20px var(--shadow);
}

.button .hand {
    width: 11px;
    height: 11px;
    border-radius: 2px 0 0 0;
    background: var(--icon);
    position: relative;
    margin: 10px 8px 0 0;
    transform-origin: -5px -1px;
    transition: transform 0.25s, background 0.2s linear;
    transform: rotate(calc(var(--hand-rotate, 0) * 1deg)) translateZ(0);
}

.button .hand:before, .button .hand:after {
    content: "";
    background: var(--icon);
    position: absolute;
    transition: background 0.2s linear, box-shadow 0.2s linear;
}

.button .hand:before {
    left: -5px;
    bottom: 0;
    height: 12px;
    width: 4px;
    border-radius: 1px 1px 0 1px;
}

.button .hand:after {
    right: -3px;
    top: 0;
    width: 4px;
    height: 4px;
    border-radius: 0 2px 2px 0;
    background: var(--icon);
    box-shadow: -0.5px 4px 0 var(--icon), -1px 8px 0 var(--icon), -1.5px 12px 0 var(--icon);
    transform: scaleY(0.6825);
    transform-origin: 0 0;
}

.button .hand .thumb {
    background: var(--icon);
    width: 10px;
    height: 4px;
    border-radius: 2px;
    transform-origin: 2px 2px;
    position: absolute;
    left: 0;
    top: 0;
    transition: transform 0.25s, background 0.2s linear;
    transform: scale(0.85) translateY(-0.5px) rotate(var(--hand-thumb-1, -45deg)) translateZ(0);
}

.button .hand .thumb:before {
    content: "";
    height: 4px;
    width: 7px;
    border-radius: 2px;
    transform-origin: 2px 2px;
    background: var(--icon);
    position: absolute;
    left: 7px;
    top: 0;
    transition: transform 0.25s, background 0.2s linear;
    transform: rotate(var(--hand-thumb-2, -45deg)) translateZ(0);
}

.button .hand,
.button span {
    display: inline-block;
    vertical-align: top;
}

.button .hand span,
.button span span {
    opacity: var(--span-d-o, 0);
    transition: transform 0.25s, opacity 0.2s linear;
    transform: translateX(var(--span-d-x, 4px)) translateZ(0);
}

.button > span {
    transition: transform 0.25s;
    transform: translateX(var(--span-x, 4px)) translateZ(0);
}
</style>
