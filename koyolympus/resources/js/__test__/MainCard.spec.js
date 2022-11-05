import {shallowMount} from '@vue/test-utils';
import Component from '@/MainCardComponent.vue';

describe('Testing data', () => {
    let wrapper;
    beforeEach(() => {
        window.innerWidth = 1000;
        window.innerHeight = 200;
        wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/test/path',
                }
            }
        });
    });
    describe('Testing cardCategories', () => {
        test('genre landscape', () => {
            const landscape = wrapper.vm.categories.Landscape;
            expect(landscape.genre).toBe(1);
            expect(landscape.name).toBe('photo.landscape');
            expect(landscape.url).toBe('/photo/landscape');
            expect(landscape.message).toBe('The landscape are there, and I just take them thoroughly.');
            expect(landscape.image).toBe('/images/yellow.jpeg');
        });
        test('genre Animal', () => {
            const animal = wrapper.vm.categories.Animal;
            expect(animal.genre).toBe(2);
            expect(animal.name).toBe('photo.animal');
            expect(animal.url).toBe('/photo/animal');
            expect(animal.message).toBe('If you want to be a better animal photographer, stand in front of more animals.');
            expect(animal.image).toBe('/images/cat.jpeg');
        });
        test('genre Portrait', () => {
            const portrait = wrapper.vm.categories.Portrait;
            expect(portrait.genre).toBe(3);
            expect(portrait.name).toBe('photo.portrait');
            expect(portrait.url).toBe('/photo/portrait');
            expect(portrait.message).toBe('The whole point of taking portraits is so that I can see how far people have come.');
            expect(portrait.image).toBe('/images/portrait.jpeg');
        });
        test('genre Others', () => {
            const others = wrapper.vm.categories.Others;
            expect(others.genre).toBeUndefined();
            expect(others.name).toBeUndefined();
            expect(others.url).toBeUndefined();
            expect(others.message).toBe('The Earth is art, The photographer is only a witness.');
            expect(others.image).toBe('/images/wine.jpeg');
        });
        test('genre SnapShot', () => {
            const snapshot = wrapper.vm.categories.SnapShot;
            expect(snapshot.genre).toBe(4);
            expect(snapshot.name).toBe('photo.snapshot');
            expect(snapshot.url).toBe('/photo/others/snapshot');
            expect(snapshot.message).toBe('It is more important to click with people than to click the shutter.');
            expect(snapshot.image).toBe('/images/snapshot.jpeg');
        });
        test('genre Live Composite', () => {
            const liveComposite = wrapper.vm.categories["Live Composite"];
            expect(liveComposite.genre).toBe(5);
            expect(liveComposite.name).toBe('photo.livecomposite');
            expect(liveComposite.url).toBe('/photo/others/livecomposite');
            expect(liveComposite.message).toBe('Since I’m inarticulate, I express myself with images.');
            expect(liveComposite.image).toBe('/images/livecomp.jpeg');
        });
        test('genre Pinhole/Film', () => {
            const film = wrapper.vm.categories["Pinhole/Film"];
            expect(film.genre).toBe(6);
            expect(film.name).toBe('photo.pinfilm');
            expect(film.url).toBe('/photo/others/pinfilm');
            expect(film.message).toBe('Seeing is not enough, you have to feel what you photograph');
            expect(film.image).toBe('/images/film.jpeg');
        });
        test('genre back', () => {
            const back = wrapper.vm.categories["->Back"];
            expect(back.genre).toBeUndefined();
            expect(back.name).toBeUndefined();
            expect(back.url).toBeUndefined();
            expect(back.message).toBe('What you see is what you get.');
            expect(back.image).toBeUndefined();
        });
    });
    test('isVisible is true', () => {
        expect(wrapper.vm.isVisible).toBeTruthy();
    });
    test('width is 1000', () => {
        expect(wrapper.vm.width).toBe(1000);
    });
    test('height is 200', () => {
        expect(wrapper.vm.height).toBe(200);
    });
    test('path is /test/path', () => {
        expect(wrapper.vm.currentPath).toBe('/test/path');
    });
});

describe('Testing methods', () => {
    test('Testing searchPhoto method', () => {
        window = Object.assign(window, {innerWidth: 950, innerHeight: 950});
        const photo = {url: '/test/url', genre: 2, name: 'test.name'};
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/test/path',
                }
            }
        });
        wrapper.vm.searchPhoto(photo);

        expect(wrapper.vm.$store.commit).toHaveBeenCalledTimes(2);
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setUrl', '/test/url');
        expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('photo/setGenre', 2);
        expect(wrapper.vm.$router.push).toHaveBeenCalled();
        expect(wrapper.vm.$router.push).toHaveBeenCalledWith({name: 'test.name'});
    });
    test('Testing setIsOthers method', () => {
        window = Object.assign(window, {innerWidth: 950, innerHeight: 950});
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/test/path',
                }
            }
        });
        expect(wrapper.vm.isOthers).toBeFalsy();

        wrapper.vm.setIsOthers(true);
        expect(wrapper.vm.isOthers).toBeTruthy();

        wrapper.vm.setIsOthers(false);
        expect(wrapper.vm.isOthers).toBeFalsy();
    });
    describe('Testing handleResize method', () => {
        test('if width is more than 950 && current path is not `aboutme` and `bizinq`, card is visible', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/test/path',
                    }
                }
            });
            window.innerWidth = 951;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(951);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is more than 950 && current path is `aboutme`, card is visible', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    }
                }
            });
            window.innerWidth = 951;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(951);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is more than 950 && current path is `/bizinq`, card is visible', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/bizinq',
                    }
                }
            });
            window.innerWidth = 951;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(951);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is equal to 950 && current path is not either `aboutme` or `bizinq`, card is visible', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/test/path',
                    }
                }
            });
            window.innerWidth = 950;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(950);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is equal to 950 && current path is `aboutme`, card is visible', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    }
                }
            });
            window.innerWidth = 950;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(950);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is equal to 950 && current path is `/bizinq`, card is visible', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/bizinq',
                    }
                }
            });
            window.innerWidth = 950;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(950);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is less than 950 && current path is not `aboutme` and `bizinq`, card status is true(visible)', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/test/path',
                    }
                }
            });
            window.innerWidth = 949;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(949);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if width is less than 950 && current path is `aboutme`, card status is false(invisible)', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    }
                }
            });
            window.innerWidth = 949;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(949);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeFalsy();
        });
        test('if width is less than 950 && current path is `/bizinq`, card status is false(invisible)', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/bizinq',
                    }
                }
            });
            window.innerWidth = 949;
            window.innerHeight = 100;
            wrapper.vm.handleResize();

            expect(wrapper.vm.width).toBe(949);
            expect(wrapper.vm.height).toBe(100);
            expect(wrapper.vm.isVisible).toBeFalsy();
        });
    });
    describe('Testing setIsVisible method', () => {
        test('if current path is `/` and width is more than 950, card is visible', () => {
            window = Object.assign(window, {innerWidth: 951, innerHeight: 100});
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/',
                    }
                },
            });

            wrapper.vm.setIsVisible();

            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if current path is `/` and width is less than 950, card is visible', () => {
            window = Object.assign(window, {innerWidth: 949, innerHeight: 100});
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/',
                    }
                },
            });

            wrapper.vm.setIsVisible();

            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if current path is `/` and width is equal to 950, card is visible', () => {
            window = Object.assign(window, {innerWidth: 950, innerHeight: 100});
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/',
                    }
                },
            });

            wrapper.vm.setIsVisible();

            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if current path is not `/` and width is more than 950, card is visible', () => {
            window = Object.assign(window, {innerWidth: 951, innerHeight: 100});
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    }
                },
            });

            wrapper.vm.setIsVisible();

            expect(wrapper.vm.isVisible).toBeTruthy();
        });
        test('if current path is not `/` and width is less than 950, card is invisible', () => {
            window = Object.assign(window, {innerWidth: 949, innerHeight: 100});
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    }
                },
            });

            wrapper.vm.setIsVisible();

            expect(wrapper.vm.isVisible).toBeFalsy();
        });
        test('if current path is not `/` and width is equal to 950, card is invisible', () => {
            window = Object.assign(window, {innerWidth: 950, innerHeight: 100});
            const wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    }
                },
            });

            wrapper.vm.setIsVisible();

            expect(wrapper.vm.isVisible).toBeFalsy();
        });
    });
});

describe('Testing watch', () => {
    let wrapper;
    beforeEach(() => {
        window = Object.assign(window, {innerWidth: 951, innerHeight: 100});
        wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
    });
    test('if $route changed once, setIsVisible method is called once', async () => {
        const setIsVisibleMock = jest.fn();
        wrapper.vm.setIsVisible = setIsVisibleMock;

        wrapper.vm.$options.watch.$route.call(wrapper.vm);

        await wrapper.vm.$nextTick();
        expect(setIsVisibleMock).toHaveBeenCalledTimes(1);
    });
    test('if $route changed twice, setIsVisible method is called twice', async () => {
        const setIsVisibleMock = jest.fn();
        wrapper.vm.setIsVisible = setIsVisibleMock;

        wrapper.vm.$options.watch.$route.call(wrapper.vm);
        wrapper.vm.$options.watch.$route.call(wrapper.vm);

        await wrapper.vm.$nextTick();
        expect(setIsVisibleMock).toHaveBeenCalledTimes(2);
    });
});

describe('Testing created', () => {
    test('when component is created, setIsVisible method is called once.', () => {
        const setIsVisibleSpy = jest.spyOn(Component.methods, 'setIsVisible');
        shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        expect(setIsVisibleSpy).toHaveBeenCalledTimes(1);
    });
});

describe('Testing mounted', () => {
    test('when component is mounted, addEventListener is called once.', () => {
        const addEventListenerSpy = jest.spyOn(window, 'addEventListener');
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        expect(addEventListenerSpy).toHaveBeenCalledTimes(1);
        expect(addEventListenerSpy).toHaveBeenCalledWith('resize', wrapper.vm.handleResize);
    });
});

describe('Testing beforeDestroy', () => {
    test('when component is destroyed, removeEventListener is called once', () => {
        const removeEventListenerSpy = jest.spyOn(window, 'removeEventListener');
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        expect(removeEventListenerSpy).toHaveBeenCalledTimes(0);

        wrapper.destroy();
        expect(removeEventListenerSpy).toHaveBeenCalledTimes(1);
        expect(removeEventListenerSpy).toHaveBeenCalledWith('resize', wrapper.vm.handleResize);
    });
});

describe('Testing v-if', () => {
    test('if isOthers is false and isVisible is true, main cards are visible', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        expect(wrapper.findAll('div.card').length).toBe(1);
        expect(wrapper.find('div.items.head p').text()).toBe('Landscape');
    });
    test('if isOthers is true and isVisible is true, other cards are visible', async () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        wrapper.vm.isOthers = true;

        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('div.card').length).toBe(1);
        expect(wrapper.find('div.items.head p').text()).toBe('SnapShot');
    });
    test('if isOthers is false and isVisible is false, cards are invisible', async () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        wrapper.vm.isVisible = false;

        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('div.card').length).toBe(0);
    });
    test('if isOthers is true and isVisible is false, cards are invisible', async () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
        wrapper.vm.isOthers = true;
        wrapper.vm.isVisible = false;

        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('div.card').length).toBe(0);
    });
});

describe('Testing v-for', () => {
    describe('Testing main cards', () => {
        let wrapper;
        let cards;
        beforeEach(() => {
            wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    },
                },
            });
            wrapper.vm.searchPhoto = jest.fn();
            wrapper.vm.setIsOthers = jest.fn();
            cards = wrapper.findAll('div.card_container');
        });
        afterEach(() => {
            wrapper.destroy();
        });
        test('4 cards are available', () => {
            expect(cards.length).toBe(4);
        });
        test('The content of each card is correct', () => {
            expect(cards.at(0).find('div.items.head p').text()).toBe('Landscape');
            expect(cards.at(1).find('div.items.head p').text()).toBe('Animal');
            expect(cards.at(2).find('div.items.head p').text()).toBe('Portrait');
            expect(cards.at(3).find('div.items.head p').text()).toBe('Others');
        });
        test('searchPhoto method fires when Landscape card is clicked', () => {
            cards.at(0).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.searchPhoto).toHaveBeenCalledWith({
                "genre": 1,
                "name": "photo.landscape",
                "url": "/photo/landscape",
                "message": "The landscape are there, and I just take them thoroughly.",
                "image": "/images/yellow.jpeg"
            });
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(0);
        });
        test('searchPhoto method fires when Animal card is clicked', () => {
            cards.at(1).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.searchPhoto).toHaveBeenCalledWith({
                "genre": 2,
                "name": "photo.animal",
                "url": "/photo/animal",
                "message": "If you want to be a better animal photographer, stand in front of more animals.",
                "image": "/images/cat.jpeg"
            });
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(0)
        });
        test('searchPhoto method fires when Portrait card is clicked', () => {
            cards.at(2).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.searchPhoto).toHaveBeenCalledWith({
                "genre": 3,
                "name": "photo.portrait",
                "url": "/photo/portrait",
                "message": "The whole point of taking portraits is so that I can see how far people have come.",
                "image": "/images/portrait.jpeg"
            });
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(0);
        });
        test('setIsOthers method fires when Others card is clicked', () => {
            cards.at(3).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledWith(true);
        });
    });
    describe('Testing others cards', () => {
        let wrapper;
        let cards;
        beforeEach(() => {
            wrapper = shallowMount(Component, {
                mocks: {
                    $store: {
                        commit: jest.fn(),
                        state: {photo: {card: {}}}
                    },
                    $router: {
                        push: jest.fn(),
                    },
                    $route: {
                        path: '/aboutme',
                    },
                },
            });
            wrapper.vm.searchPhoto = jest.fn();
            wrapper.vm.setIsOthers = jest.fn();
            wrapper.vm.isOthers = true;
            cards = wrapper.findAll('div.card_container');
        });
        afterEach(() => {
            wrapper.destroy();
        });
        test('4 cards are available', () => {
            expect(cards.length).toBe(4);
        });
        test('The content of each card is correct', () => {
            expect(cards.at(0).find('div.items.head p').text()).toBe('SnapShot');
            expect(cards.at(1).find('div.items.head p').text()).toBe('Live Composite');
            expect(cards.at(2).find('div.items.head p').text()).toBe('Pinhole/Film');
            expect(cards.at(3).find('div.items.head p').text()).toBe('->Back');
        });
        test('searchPhoto method fires when SnapShot card is clicked', () => {
            cards.at(0).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.searchPhoto).toHaveBeenCalledWith({
                "genre": 4,
                "name": "photo.snapshot",
                "url": "/photo/others/snapshot",
                "message": "It is more important to click with people than to click the shutter.",
                "image": "/images/snapshot.jpeg"
            });
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(0);
        });
        test('searchPhoto method fires when Live Composite card is clicked', () => {
            cards.at(1).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.searchPhoto).toHaveBeenCalledWith({
                "genre": 5,
                "name": "photo.livecomposite",
                "url": "/photo/others/livecomposite",
                "message": "Since I’m inarticulate, I express myself with images.",
                "image": "/images/livecomp.jpeg"
            });
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(0);
        });
        test('searchPhoto method fires when Pinhole/Film card is clicked', () => {
            cards.at(2).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.searchPhoto).toHaveBeenCalledWith({
                "genre": 6,
                "name": "photo.pinfilm",
                "url": "/photo/others/pinfilm",
                "message": "Seeing is not enough, you have to feel what you photograph",
                "image": "/images/film.jpeg"
            });
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(0);
        });
        test('setIsOthers method fires when Back card is clicked', () => {
            cards.at(3).trigger('click.native');

            expect(wrapper.vm.searchPhoto).toHaveBeenCalledTimes(0);
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.setIsOthers).toHaveBeenCalledWith(false);
        });
    });
});

describe('Testing SnapShot', () => {
    let wrapper;
    beforeEach(() => {
        wrapper = shallowMount(Component, {
            mocks: {
                $store: {
                    commit: jest.fn(),
                    state: {photo: {card: {}}}
                },
                $router: {
                    push: jest.fn(),
                },
                $route: {
                    path: '/aboutme',
                },
            },
        });
    });
    test('SnapShot of main cards', () => {
        wrapper.vm.isOthers = false;
        expect(wrapper.element).toMatchSnapshot();
    });
    test('SnapShot of others cards', async () => {
        wrapper.vm.isOthers = true;
        await wrapper.vm.$nextTick();
        expect(wrapper.element).toMatchSnapshot();
    });
});
