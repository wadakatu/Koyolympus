const state = {
    url: null,
    genre: null,
    card: true,
    like: [],
}

const getters = {
    url: state => state.url ? state.url : '/photo',
    genre: state => state.genre ? state.genre : 0,
    card: state => state.card ? state.card : false,
    like: state => state.like ? state.like : [],
}

const mutations = {
    setUrl(state, url) {
        state.url = url;
    },
    setGenre(state, genre) {
        state.genre = genre;
    },
    setCard(state, card) {
        state.card = card;
    },
    setLike(state, likePhotoId) {
        state.like.push(likePhotoId);
    },
    unsetLike(state, index) {
        state.like.splice(index, 1);
    }
}

const actions = {
    async LikePhotoAction(context, likePhotoId) {
        let result = true;
        const likeArray = context.getters.like;

        for (let i = 0; i < likeArray.length; i++) {
            if (likeArray[i] === likePhotoId) {
                await context.dispatch('unlikePhoto', likePhotoId).then(r => console.log(r));
                context.commit('unsetLike', i);
                result = false;
                return new Promise((resolve, reject) => {
                    resolve(result);
                });
            }
        }

        await context.dispatch('likePhoto', likePhotoId).then(r => console.log(r));
        context.commit('setLike', likePhotoId);
        return new Promise((resolve, reject) => {
            resolve(result);
        });
    },
    async likePhoto(photoId) {
        return await axios.post(`/api/like`, {id: photoId});
    },
    async unlikePhoto(photoId) {
        return await axios.post(`/api/unlike`, {id: photoId});
    },
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
}

