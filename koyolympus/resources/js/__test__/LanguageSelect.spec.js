import {shallowMount} from '@vue/test-utils';
import Component from '@/LanguageSelectComponent.vue';

describe('Testing method', () => {
    test('Testing changeLang method', () => {
        const wrapper = shallowMount(Component);
        wrapper.vm.changeLang('japanese');
        expect(wrapper.emitted('update')).not.toBeUndefined();
        expect(wrapper.emitted('update')[0][0]).toBe('japanese');
    });
});

describe('Testing click', () => {
    let wrapper;
    let selects;
    beforeEach(() => {
        wrapper = shallowMount(Component);
        wrapper.vm.changeLang = jest.fn();
        selects = wrapper.findAll('a.lang_detail');
    });
    test('click Japanese', () => {
        const target = selects.at(0);
        target.trigger('click');
        expect(target.text()).toBe('日本語');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click English', () => {
        const target = selects.at(1);
        target.trigger('click');
        expect(target.text()).toBe('English');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click French', () => {
        const target = selects.at(2);
        target.trigger('click');
        expect(target.text()).toBe('French');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click Korean', () => {
        const target = selects.at(3);
        target.trigger('click');
        expect(target.text()).toBe('한국말');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
    test('click Chinese', () => {
        const target = selects.at(4);
        target.trigger('click');
        expect(target.text()).toBe('中文');
        expect(wrapper.vm.changeLang).toHaveBeenCalled();
    });
});

describe('Testing snapshot', () => {
    test('default', () => {
        const wrapper = shallowMount(Component);
        expect(wrapper.element).toMatchSnapshot();
    });
});
