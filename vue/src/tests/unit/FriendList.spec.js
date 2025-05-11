import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import FriendList from '../../components/FriendList.vue';

describe('FriendList', () => {
  it('renders properly with no friends', () => {
    const wrapper = mount(FriendList, {
      props: {
        friends: []
      }
    });
    expect(wrapper.find('h4').text()).toBe('Barátok');
    expect(wrapper.find('.no-friends').text()).toBe('Nincsenek barátaid.');
  });

  it('renders list of friends', () => {
    const friends = [
      { id: 1, name: 'Barát 1', avatar: 'avatar1.png' },
      { id: 2, name: 'Barát 2', avatar: 'avatar2.png' },
    ];
    const wrapper = mount(FriendList, {
      props: {
        friends: friends
      }
    });
    expect(wrapper.findAll('.friend-item').length).toBe(2);
    expect(wrapper.find('.friend-item .friend-name').text()).toBe('Barát 1');
  });
});