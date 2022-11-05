import {RouterLinkStub, shallowMount} from "@vue/test-utils";
import Component from '@/PaginateComponent.vue';

describe('Testing methods', () => {
    test('Testing moveMainPage method', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 1,
                lastPage: 2
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });

        wrapper.vm.moveMainPage();
        expect(wrapper.vm.$router.push).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.$router.push).toHaveBeenCalledWith('/');
    });
});

describe('Testing computed', () => {
    describe('Testing isFirstPage', () => {
        test('isFirstPage is true when currentPage is 1', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $router: {push: jest.fn()},
                    $store: {state: {photo: {url: '/test'}}},
                },
                propsData: {
                    currentPage: 1,
                    lastPage: 2
                },
                stubs: {
                    RouterLink: RouterLinkStub
                }
            });
            expect(wrapper.vm.isFirstPage).toBeTruthy();
        });
        test('isFirstPage is false when currentPage is 2', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $router: {push: jest.fn()},
                    $store: {state: {photo: {url: '/test'}}},
                },
                propsData: {
                    currentPage: 2,
                    lastPage: 3
                },
                stubs: {
                    RouterLink: RouterLinkStub
                }
            });
            expect(wrapper.vm.isFirstPage).toBeFalsy();
        });
    });
    describe('Testing isLastPage', () => {
        test('isLastPage is true when currentPage and lastPage is same', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $router: {push: jest.fn()},
                    $store: {state: {photo: {url: '/test'}}},
                },
                propsData: {
                    currentPage: 1,
                    lastPage: 1
                },
                stubs: {
                    RouterLink: RouterLinkStub
                }
            });
            expect(wrapper.vm.isLastPage).toBeTruthy();
        });
        test('isLastPage is false when currentPage and lastPage is different', () => {
            const wrapper = shallowMount(Component, {
                mocks: {
                    $router: {push: jest.fn()},
                    $store: {state: {photo: {url: '/test'}}},
                },
                propsData: {
                    currentPage: 1,
                    lastPage: 2
                },
                stubs: {
                    RouterLink: RouterLinkStub
                }
            });
            expect(wrapper.vm.isLastPage).toBeFalsy();
        });
    });
    test('Testing url method', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 1,
                lastPage: 2
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.vm.url).toBe('/test');
    });
});

describe('Testing v-if', () => {
    test('home and next button exist when currentPage is 1 and lastPage is more than 1', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 1,
                lastPage: 2
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.find('button.prev').exists()).toBeFalsy();
        expect(wrapper.find('button.home').exists()).toBeTruthy();
        expect(wrapper.find('button.next').exists()).toBeTruthy();
    });
    test('only home button exist when both currentPage and lastPage is 1', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 1,
                lastPage: 1
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.find('button.prev').exists()).toBeFalsy();
        expect(wrapper.find('button.home').exists()).toBeTruthy();
        expect(wrapper.find('button.next').exists()).toBeFalsy();
    });
    test('home and prev button exist when currentPage is more than 1 and is as same as lastPage', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 2,
                lastPage: 2
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.find('button.prev').exists()).toBeTruthy();
        expect(wrapper.find('button.home').exists()).toBeTruthy();
        expect(wrapper.find('button.next').exists()).toBeFalsy();
    });
    test('all three button exist when currentPage is more than 1 and lastPage is more than currentPage', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 2,
                lastPage: 3
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.find('button.prev').exists()).toBeTruthy();
        expect(wrapper.find('button.home').exists()).toBeTruthy();
        expect(wrapper.find('button.next').exists()).toBeTruthy();
    });
});

describe('Testing router-link', () => {
    test('Testing v-on:to in router-link', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 2,
                lastPage: 3
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        const routerLinks = wrapper.findAllComponents(RouterLinkStub);
        expect(routerLinks.at(0).props('to')).toBe('/test/?page=1');
        expect(routerLinks.at(1).props('to')).toBe('/test/?page=3');
    });
});

describe('Testing @ event', () => {
    test('moveMainPage method called when home button is clicked', async () => {
        const spy = jest.spyOn(Component.methods, 'moveMainPage');
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 2,
                lastPage: 3
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });

        wrapper.find('button.home').trigger('click');
        await wrapper.vm.$nextTick();
        expect(spy).toHaveBeenCalled();
    });
});

describe('Testing SnapShot', () => {
    test('only home button exist', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 1,
                lastPage: 1
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.element).toMatchSnapshot();
    });
    test('home and prev button exist', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 2,
                lastPage: 2
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.element).toMatchSnapshot();
    });
    test('home and next button exist', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 1,
                lastPage: 2
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.element).toMatchSnapshot();
    });
    test('all three button exist', () => {
        const wrapper = shallowMount(Component, {
            mocks: {
                $router: {push: jest.fn()},
                $store: {state: {photo: {url: '/test'}}},
            },
            propsData: {
                currentPage: 2,
                lastPage: 3
            },
            stubs: {
                RouterLink: RouterLinkStub
            }
        });
        expect(wrapper.element).toMatchSnapshot();
    });
});
