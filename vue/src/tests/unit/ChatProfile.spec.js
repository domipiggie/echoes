import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import ChatProfile from '../../components/ChatProfile.vue';

describe('ChatProfile', () => {
  it('renders properly with default props', () => {
    const wrapper = mount(ChatProfile, {
      props: {
        user: { name: 'Teszt Felhasználó', status: 'Online', avatar: 'default-avatar.png' }
      }
    });
    expect(wrapper.find('.user-name').text()).toBe('Teszt Felhasználó');
    expect(wrapper.find('.user-status').text()).toBe('Online');
  });
});