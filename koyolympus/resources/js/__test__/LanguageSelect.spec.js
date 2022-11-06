import {shallowMount} from '@vue/test-utils';
import Component from '@/LanguageSelectComponent.vue';

describe('Testing method', () => {
    test('Testing changeLang method', () => {
        const wrapper = shallowMount(Component);
        wrapper.vm.changeLang();
        expect(wrapper.emitted('update')).not.toBeUndefined();
        expect(wrapper.emitted('update')[0][0]).toBe('japanese');
    });
});

describe('Testing @ event', () => {
    let wrapper;
    let options;
    beforeEach(() => {
        wrapper = shallowMount(Component);
        wrapper.vm.changeLang = jest.fn();
        options = wrapper.find('select').findAll('option');
    });
    describe('Testing change event', () => {
        test('click Japanese', async () => {
            await options.at(1).setSelected();
            await options.at(0).setSelected();
            expect(wrapper.vm.language).toBe('japanese');
            expect(wrapper.vm.changeLang).toHaveBeenCalled();
        });
        test('click English', async () => {
            await options.at(1).setSelected();
            expect(wrapper.vm.language).toBe('english');
            expect(wrapper.vm.changeLang).toHaveBeenCalled();
        });
        test('click French', async () => {
            await options.at(2).setSelected();
            expect(wrapper.vm.language).toBe('french');
            expect(wrapper.vm.changeLang).toHaveBeenCalled();
        });
        test('click Korean', async () => {
            await options.at(3).setSelected();
            expect(wrapper.vm.language).toBe('korean');
            expect(wrapper.vm.changeLang).toHaveBeenCalled();
        });
        test('click Chinese', async () => {
            await options.at(4).setSelected();
            expect(wrapper.vm.language).toBe('chinese');
            expect(wrapper.vm.changeLang).toHaveBeenCalled();
        });
    });
});

describe('Testing snapshot', () => {
    test('default', () => {
        const wrapper = shallowMount(Component);
        expect(wrapper.element).toMatchSnapshot();
    });
});
