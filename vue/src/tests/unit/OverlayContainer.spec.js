import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import OverlayContainer from '../../components/OverlayContainer.vue';

describe('OverlayContainer', () => {
  it('renders properly', () => {
    const wrapper = mount(OverlayContainer);
    expect(wrapper.find('.overlay-left h1').text()).toBe('Üdv újra itt!');
    expect(wrapper.find('.overlay-right h1').text()).toBe('Szia Barátom!');
    expect(wrapper.findAll('button.ghost').length).toBe(2);
  });

  it('emits events when buttons are clicked', async () => {
    const wrapper = mount(OverlayContainer);

    const loginButton = wrapper.find('.overlay-left button.ghost');
    await loginButton.trigger('click');
    expect(wrapper.emitted()['deactivate-right-panel']).toBeTruthy();
    expect(wrapper.emitted()['deactivate-right-panel'].length).toBe(1);

    const registerButton = wrapper.find('.overlay-right button.ghost');
    await registerButton.trigger('click');
    expect(wrapper.emitted()['activate-right-panel']).toBeTruthy();
    expect(wrapper.emitted()['activate-right-panel'].length).toBe(1);
  });
});