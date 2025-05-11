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

    const leftButton = wrapper.find('.overlay-left button.ghost');
    await leftButton.trigger('click');
    expect(wrapper.emitted()['deactivate-right-panel']).toBeTruthy();

    const rightButton = wrapper.find('.overlay-right button.ghost');
    await rightButton.trigger('click');
    expect(wrapper.emitted()['activate-right-panel']).toBeTruthy();
  });
});