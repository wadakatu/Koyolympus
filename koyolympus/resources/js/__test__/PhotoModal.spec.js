import {shallowMount} from "@vue/test-utils";
import Component from '@/PhotoModalComponent.vue';

describe('Testing data', () => {
    let wrapper;
    beforeEach(() => {
        wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 1
                }
            },
        });
    });
    test('isProcessing is false', () => {
        expect(wrapper.vm.isProcessing).toBeFalsy();
    });
    test('like is false', () => {
        expect(wrapper.vm.like).toBeFalsy();
    });
    test('isLiked is false', () => {
        expect(wrapper.vm.isLiked).toBeFalsy();
    });
    test('good is 0', () => {
        expect(wrapper.vm.good).toBe(0);
    });
});

describe('Testing methods', () => {
    describe('Testing likeOrNot method', () => {
        test('succeed to like', async () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {dispatch: jest.fn().mockResolvedValue(false), commit: jest.fn()},
                },
                propsData: {
                    val: {
                        url: '/test',
                        id: 1
                    }
                },
            });
            wrapper.vm.likePhoto = jest.fn().mockResolvedValue({});
            wrapper.vm.unlikePhoto = jest.fn();

            await wrapper.vm.likeOrNot(10);

            expect(wrapper.vm.isProcessing).toBeFalsy();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('photo/searchLikedPhoto', 10);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledWith(10);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setLike', 10);
            expect(wrapper.vm.good).toBe(1);
            expect(wrapper.vm.like).toBeTruthy();
        });
        test('fail to like', async () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {dispatch: jest.fn().mockResolvedValue(false), commit: jest.fn()},
                },
                propsData: {
                    val: {
                        url: '/test',
                        id: 1
                    }
                },
            });
            wrapper.vm.likePhoto = jest.fn().mockRejectedValue({status: 500});
            wrapper.vm.unlikePhoto = jest.fn();

            await wrapper.vm.likeOrNot(11);

            expect(wrapper.vm.isProcessing).toBeFalsy();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('photo/searchLikedPhoto', 11);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledWith(11);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('error/setCode', 500);
            expect(wrapper.vm.good).toBe(0);
            expect(wrapper.vm.like).toBeFalsy();
        });
        test('succeed to unlike when good is 0', async () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {dispatch: jest.fn().mockResolvedValue(true), commit: jest.fn()},
                },
                propsData: {
                    val: {
                        url: '/test',
                        id: 1
                    }
                },
            });
            wrapper.vm.likePhoto = jest.fn();
            wrapper.vm.unlikePhoto = jest.fn().mockResolvedValue({});
            wrapper.vm.like = true;
            wrapper.vm.isLiked = true;

            await wrapper.vm.likeOrNot(12);

            expect(wrapper.vm.isProcessing).toBeFalsy();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('photo/searchLikedPhoto', 12);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledWith(12);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/unsetLike', 12);
            expect(wrapper.vm.good).toBe(0);
            expect(wrapper.vm.like).toBeFalsy();
            expect(wrapper.vm.isLiked).toBeFalsy();
        });
        test('succeed to unlike when good is 2', async () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {dispatch: jest.fn().mockResolvedValue(true), commit: jest.fn()},
                },
                propsData: {
                    val: {
                        url: '/test',
                        id: 1
                    }
                },
            });
            wrapper.vm.likePhoto = jest.fn();
            wrapper.vm.unlikePhoto = jest.fn().mockResolvedValue({});
            wrapper.vm.like = true;
            wrapper.vm.isLiked = true;
            wrapper.vm.good = 2;

            await wrapper.vm.likeOrNot(13);

            expect(wrapper.vm.isProcessing).toBeFalsy();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('photo/searchLikedPhoto', 13);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledWith(13);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/unsetLike', 13);
            expect(wrapper.vm.good).toBe(1);
            expect(wrapper.vm.like).toBeFalsy();
            expect(wrapper.vm.isLiked).toBeFalsy();
        });
        test('fail to unlike', async () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {dispatch: jest.fn().mockResolvedValue(true), commit: jest.fn()},
                },
                propsData: {
                    val: {
                        url: '/test',
                        id: 1
                    }
                },
            });
            wrapper.vm.likePhoto = jest.fn();
            wrapper.vm.unlikePhoto = jest.fn().mockRejectedValue({status: 500});
            wrapper.vm.like = true;
            wrapper.vm.isLiked = true;
            wrapper.vm.good = 2;

            await wrapper.vm.likeOrNot(14);

            expect(wrapper.vm.isProcessing).toBeFalsy();
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('photo/searchLikedPhoto', 14);
            expect(wrapper.vm.likePhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.unlikePhoto).toHaveBeenCalledWith(14);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('error/setCode', 500);
            expect(wrapper.vm.good).toBe(2);
            expect(wrapper.vm.like).toBeTruthy();
            expect(wrapper.vm.isLiked).toBeTruthy();
        });
    });
    test('Testing likePhoto method', () => {
        window.axios = {
            post: jest.fn(),
        };
        const wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 1
                }
            },
        });
        wrapper.vm.likePhoto(10);
        expect(window.axios.post).toHaveBeenCalledTimes(1);
        expect(window.axios.post).toHaveBeenCalledWith('/api/like', {id: 10});
    });
    test('Testing unlikePhoto method', () => {
        window.axios = {
            post: jest.fn(),
        };
        const wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 1
                }
            },
        });
        wrapper.vm.unlikePhoto(11);
        expect(window.axios.post).toHaveBeenCalledTimes(1);
        expect(window.axios.post).toHaveBeenCalledWith('/api/unlike', {id: 11});
    });
    test('Testing getPhoto method', () => {
        window.axios = {
            post: jest.fn(),
        };
        const wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 1
                }
            },
        });
        wrapper.vm.getLike(12);
        expect(window.axios.post).toHaveBeenCalledTimes(1);
        expect(window.axios.post).toHaveBeenCalledWith('/api/get/like', {id: 12});
    });
    describe('Testing likeStatus method', () => {
        let wrapper;
        beforeEach(() => {
            wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        state: {photo: {like: [1, 2, 3]}}
                    }
                },
                propsData: {
                    val: {
                        url: '/test',
                        id: 1
                    }
                },
            });
        });
        test('return true when already liked', () => {
            expect(wrapper.vm.likeStatus(1)).toBeTruthy();
        });
        test('return false when not liked yet', () => {
            expect(wrapper.vm.likeStatus(4)).toBeFalsy();
        });
    });
});

describe('Testing watch', () => {
    test('succeed to get like', async () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn()
                }
            },
            propsData: {
                val: {
                    url: '/test',
                    id: 2
                }
            },
        });
        wrapper.vm.likeStatus = jest.fn().mockReturnValue(true);
        wrapper.vm.getLike = jest.fn().mockResolvedValue({data: {all_likes: 10}});

        await wrapper.setProps({val: {id: 1, url: '/test'}});

        expect(wrapper.vm.likeStatus).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.getLike).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.getLike).toHaveBeenCalledWith(1);
        expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(0);
        expect(wrapper.vm.like).toBeTruthy();
        expect(wrapper.vm.isLiked).toBeTruthy();
        expect(wrapper.vm.good).toBe(10);
    });
    test('fail to get like', async () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn()
                }
            },
            propsData: {
                val: {
                    url: '/test',
                    id: 2
                }
            },
        });
        wrapper.vm.likeStatus = jest.fn().mockReturnValue(true);
        wrapper.vm.getLike = jest.fn().mockRejectedValue({response: {status: 500}});

        await wrapper.setProps({val: {id: 1, url: '/test'}});
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.likeStatus).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.getLike).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('error/setCode', 500);
        expect(wrapper.vm.like).toBeFalsy();
        expect(wrapper.vm.isLiked).toBeFalsy();
        expect(wrapper.vm.good).toBe(0);
    });
});

describe('Testing @ event', () => {
    let wrapper;
    beforeEach(() => {
        wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 2
                }
            },
        });
    });
    describe('call close emit event if click div elements', () => {
        test('div which has the id of overlay', () => {
            wrapper.find('#overlay').trigger('click.self');
            expect(wrapper.emitted('close')).not.toBeUndefined();
        });
        test('div which has the id modal-content', () => {
            wrapper.find('#modal-content').trigger('click.self');
            expect(wrapper.emitted('close')).not.toBeUndefined();
        });
        test('div which has the id modal-content-top', () => {
            wrapper.find('#modal-content-top').trigger('click.self');
            expect(wrapper.emitted('close')).not.toBeUndefined();
        });
    });
    test('call likeOrNot method when like button is clicked', () => {
        wrapper.vm.likeOrNot = jest.fn();
        wrapper.find('#like-heart').trigger('click.self');
        expect(wrapper.vm.likeOrNot).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.likeOrNot).toHaveBeenCalledWith(2);
    });
});

describe('Testing v-bind', () => {
    let wrapper;
    beforeEach(() => {
        wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 2
                }
            },
        });
    });
    afterEach(() => {
        jest.clearAllMocks();
    });
    test('Testing src in img', () => {
        expect(wrapper.find('img').attributes('src')).toBe('/test')
    });
    describe('Testing disabled', () => {
        test('calls likeOrNot method when isProcessing is false', () => {
            wrapper.vm.likeOrNot = jest.fn();
            wrapper.find('#like-heart').trigger('click.self');
            expect(wrapper.vm.likeOrNot).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.likeOrNot).toHaveBeenCalledWith(2);
        });
        test('not call likeOrNot method when is Processing is true', async () => {
            wrapper.vm.isProcessing = true;
            wrapper.vm.likeOrNot = jest.fn();

            await wrapper.vm.$nextTick();
            wrapper.find('#like-heart').trigger('click.self');
            expect(wrapper.vm.likeOrNot).toHaveBeenCalledTimes(0);
        });
    });
    describe('Testing class in button', () => {
        test('already liked', async () => {
            wrapper.vm.like = true;
            wrapper.vm.isLiked = true;

            await wrapper.vm.$nextTick();
            expect(wrapper.find('#like-heart').attributes('class')).toBe('static');
            expect(wrapper.find('#like-heart').attributes('class')).not.toBe('press');
        });
        test('push button and liked', async () => {
            wrapper.vm.like = true;

            await wrapper.vm.$nextTick();
            expect(wrapper.find('#like-heart').attributes('class')).not.toBe('static');
            expect(wrapper.find('#like-heart').attributes('class')).toBe('press');
        });
        test('not liked', async () => {
            await wrapper.vm.$nextTick();
            expect(wrapper.find('#like-heart').attributes('class')).not.toBe('static');
            expect(wrapper.find('#like-heart').attributes('class')).not.toBe('press');
        });
    });
});

describe('Testing SnapShot', () => {
    let wrapper;
    beforeEach(() => {
        wrapper = shallowMount(Component, {
            propsData: {
                val: {
                    url: '/test',
                    id: 2
                }
            },
        });
    });
    test('not liked SnapShot', () => {
        expect(wrapper.element).toMatchSnapshot();
    });
    test('already liked SnapShot', async () => {
        wrapper.vm.like = true;
        wrapper.vm.isLiked = true;
        await wrapper.vm.$nextTick();
        expect(wrapper.element).toMatchSnapshot();
    });
    test('push button and liked', async () => {
        wrapper.vm.like = true;
        await wrapper.vm.$nextTick();
        expect(wrapper.element).toMatchSnapshot();
    });
});
