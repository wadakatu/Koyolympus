const state = {
    url: null,
    genre: null,
    like: [],
}

const getters = {
    url: state => state.url ? state.url : '/photo',
    genre: state => state.genre ? state.genre : 0,
    like: state => state.like ? state.like : [],
}

const mutations = {
    setUrl(state, url) {
        state.url = url;
    },
    setGenre(state, genre) {
        state.genre = genre;
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
        const result = likeArray.includes(likePhotoId)
        if (result) {
            //Like済配列を検索
            for (let i = 0; i < likeArray.length; i++) {
                //Like済であれば、インデックスを返却
                if (likeArray[i] === likePhotoId) {
                    return true;
                }
            }
        } else {
            //未LIKE時は、NULLを返却
            return false;
        }
        return false;
    },
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
}

