import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import UserProfile from '../../components/UserProfile.vue';

describe('UserProfile', () => {
  it('renders properly', () => {
    const wrapper = mount(UserProfile, {
      props: {
        user: {
          name: 'Teszt Felhasználó',
          email: 'teszt@example.com',
          birthdate: '2000-01-01',
          avatar: 'default-avatar.png'
        }
      }
    });
    expect(wrapper.find('.profile-name').text()).toBe('Teszt Felhasználó');
    expect(wrapper.find('.profile-email').text()).toContain('teszt@example.com');
  });
});