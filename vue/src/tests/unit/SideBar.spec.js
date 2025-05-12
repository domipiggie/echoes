import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import SideBar from '../../components/SideBar.vue';
import { createPinia } from 'pinia';
import { useRouter } from 'vue-router';

vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}));

vi.mock('../../components/icons/LogoutIcon.vue', () => ({ default: { template: '<svg class="mock-logout-icon"></svg>' } }));
vi.mock('../../components/icons/SettingsIcon.vue', () => ({ default: { template: '<svg class="mock-settings-icon"></svg>' } }));
vi.mock('../../components/icons/NewChatIcon.vue', () => ({ default: { template: '<svg class="mock-newchat-icon"></svg>' } }));
vi.mock('../../components/icons/NewGroupIcon.vue', () => ({ default: { template: '<svg class="mock-newgroup-icon"></svg>' } }));


describe('SideBar', () => {
  const pinia = createPinia();
  
  it('renders properly', () => {
    const wrapper = mount(SideBar, {
      global: {
        plugins: [pinia],
        stubs: {
          LogoutIcon: true,
          SettingsIcon: true,
          NewChatIcon: true,
          NewGroupIcon: true,
          RouterLink: { template: '<a><slot></slot></a>' }
        }
      }
    });
    expect(wrapper.find('.sidebar').exists()).toBe(true);
    expect(wrapper.find('.logo-container').exists()).toBe(true);
  });
});