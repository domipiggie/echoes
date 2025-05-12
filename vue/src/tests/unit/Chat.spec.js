import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import Chat from '../../components/Chat.vue';
import { createPinia } from 'pinia';

vi.mock('../../components/SideBar.vue', () => ({ default: { template: '<div class="mock-sidebar"></div>', name: 'SideBar' }}));
vi.mock('../../components/ChatWindow.vue', () => ({ default: { template: '<div class="mock-chatwindow"></div>', name: 'ChatWindow' }}));
vi.mock('../../components/ChatProfile.vue', () => ({ default: { template: '<div class="mock-chatprofile"></div>', name: 'ChatProfile' }}));
vi.mock('../../components/NewChatDialog.vue', () => ({ default: { template: '<div class="mock-newchatdialog"></div>', name: 'NewChatDialog' }}));
vi.mock('../../components/NewGroupDialog.vue', () => ({ default: { template: '<div class="mock-newgroupdialog"></div>', name: 'NewGroupDialog' }}));
vi.mock('../../components/ProfileSettingsDialog.vue', () => ({ default: { template: '<div class="mock-profilesettingsdialog"></div>', name: 'ProfileSettingsDialog' }}));

describe('Chat', () => {
  it('renders main layout and child component stubs', () => {
    const pinia = createPinia();
    const wrapper = mount(Chat, {
      global: {
        plugins: [pinia]
      }
    });
    expect(wrapper.find('.chat-container').exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'SideBar' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'ChatWindow' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'ChatProfile' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'NewChatDialog' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'NewGroupDialog' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'ProfileSettingsDialog' }).exists()).toBe(true);
  });
});
