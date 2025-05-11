import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import AppearanceSelector from '../../components/AppearanceSelector.vue';

describe('AppearanceSelector', () => {
  it('renders properly', () => {
    const wrapper = mount(AppearanceSelector);
    expect(wrapper.find('div.appearance-selector-container').exists()).toBe(true);
  });

  it('emits theme-changed event when a theme button is clicked', async () => {
    const wrapper = mount(AppearanceSelector);
    const themeButtons = wrapper.findAll('.theme-button'); 

    if (themeButtons.length > 0) {
      await themeButtons[0].trigger('click');
      expect(wrapper.emitted()['theme-changed']).toBeTruthy();
    }
  });
});