import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import OverlayContainer from '../../components/OverlayContainer.vue';

describe('OverlayContainer', () => {
  it('rendereli az overlay konténert és a fő elemeket', () => {
    const wrapper = mount(OverlayContainer);
    expect(wrapper.find('.overlay-container').exists()).toBe(true);
    expect(wrapper.find('.overlay-panel.overlay-left').exists()).toBe(true);
    expect(wrapper.find('.overlay-panel.overlay-right').exists()).toBe(true);
    expect(wrapper.html()).toContain('Regisztráció');
    expect(wrapper.html()).toContain('Bejelentkezés');
  });

  it('kibocsátja a deactivate-right-panel eseményt a Regisztráció gombra kattintva', async () => {
    const wrapper = mount(OverlayContainer);
    const regButton = wrapper.find('.overlay-panel.overlay-left .ghost');
    await regButton.trigger('click');
    expect(wrapper.emitted('deactivate-right-panel')).toBeTruthy();
  });

  it('kibocsátja az activate-right-panel eseményt a Bejelentkezés gombra kattintva', async () => {
    const wrapper = mount(OverlayContainer);
    const loginButton = wrapper.find('.overlay-panel.overlay-right .ghost');
    await loginButton.trigger('click');
    expect(wrapper.emitted('activate-right-panel')).toBeTruthy();
  });
});