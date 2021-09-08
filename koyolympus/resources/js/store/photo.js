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
    async searchLikedPhoto(context, likePhotoId) {
        const likeArray = context.getters.like;
        //Like済配列を検索
        for (let i = 0; i < likeArray.length; i++) {
            //Like済であれば、インデックスを返却
            if (likeArray[i] === likePhotoId) {
                return i;
            }
        }
        //Likeされていなければエラー返却
        throw new Error('Liked Photo Not Found.');
    },
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
}

