import {shallowMount} from '@vue/test-utils';
import Component from '@/AboutMeComponent.vue';
import aboutMe from '../json/aboutMe.json';
import LanguageSelectComponent from "../components/LanguageSelectComponent";

let wrapper;

beforeEach(() => {
    wrapper = shallowMount(Component)
})

describe('Testing data', () => {
    test('language has aboutMe.japanese', () => {
        expect(wrapper.vm.language).toBe(aboutMe.japanese);
    });
    test('question has string which is `question`', () => {
        expect(wrapper.vm.question).toBe('question');
    });
    test('answer has string which is `answer`', () => {
        expect(wrapper.vm.answer).toBe('answer');
    });
});

describe('Testing methods', () => {
    describe('updateLang function', () => {
        test('Japanese ver', () => {
            wrapper.vm.updateLang('japanese');
            expect(wrapper.vm.language.length).toBe(12);
            expect(wrapper.vm.language).toBe(aboutMe.japanese);
        });
        test('English ver', () => {
            wrapper.vm.updateLang('english');
            expect(wrapper.vm.language.length).toBe(12);
            expect(wrapper.vm.language).toBe(aboutMe.english);
        });
        test('French ver', () => {
            wrapper.vm.updateLang('french');
            expect(wrapper.vm.language.length).toBe(12);
            expect(wrapper.vm.language).toBe(aboutMe.french);
        });
        test('Korean ver', () => {
            wrapper.vm.updateLang('korean');
            expect(wrapper.vm.language.length).toBe(12);
            expect(wrapper.vm.language).toBe(aboutMe.korean);
        });
        test('Chinese ver', () => {
            wrapper.vm.updateLang('chinese');
            expect(wrapper.vm.language.length).toBe(12);
            expect(wrapper.vm.language).toBe(aboutMe.chinese);
        });
    });
});

describe('Testing top button', () => {
    test('never click top button', () => {
        const routerPush = jest.fn();
        wrapper = shallowMount(Component, {
            mocks: {
                $router: {
                    push: routerPush
                },
            }
        });
        expect(routerPush.mock.calls.length).toBe(0);
    });
    test('click top button once', () => {
        const routerPush = jest.fn();
        wrapper = shallowMount(Component, {
            mocks: {
                $router: {
                    push: routerPush
                }
            }
        });
        wrapper.find('button.top_button').trigger('click');
        expect(routerPush.mock.calls.length).toBe(1);
        expect(routerPush).toHaveBeenCalledWith('/');
    });
});

describe('Testing language select component', () => {
    test('called updateLang function', () => {
        const updateLangSpy = jest.spyOn(Component.methods, 'updateLang')
        wrapper = shallowMount(Component);
        wrapper.findComponent(LanguageSelectComponent).vm.$emit('update')
        expect(updateLangSpy).toHaveBeenCalled()
    });
});

describe('Testing f-for', () => {
    test('rendered self-introduction correctly', () => {
        const intro = wrapper.findAll('p');
        expect(intro.at(0).classes()).toContain('question');
        expect(intro.at(1).classes()).toContain('answer');
        expect(intro.at(10).classes()).toContain('question');
        expect(intro.at(11).classes()).toContain('answer');
    });
});

describe('Testing snapshot', () => {
    test('default [japanese introduction]', () => {
        expect(wrapper.element).toMatchSnapshot();
    });
    test('english introduction', async () => {
        await wrapper.vm.updateLang('english');
        expect(wrapper.element).toMatchSnapshot();
    });
    test('french introduction', async () => {
        await wrapper.vm.updateLang('french');
        expect(wrapper.element).toMatchSnapshot();
    });
    test('korean introduction', async () => {
        await wrapper.vm.updateLang('korean');
        expect(wrapper.element).toMatchSnapshot();
    });
    test('chinese introduction', async () => {
        await wrapper.vm.updateLang('chinese');
        expect(wrapper.element).toMatchSnapshot();
    });
});
