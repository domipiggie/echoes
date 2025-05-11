import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import SideBar from '../../components/SideBar.vue';

describe('SideBar', () => {
  it('renders properly', () => {
    const wrapper = mount(SideBar, {
      global: {
        stubs: {
          ChatList: true,
          FriendList: true
        }
      }
    });
    expect(wrapper.find('.sidebar-header input[type="text"]').exists()).toBe(true);
  });

  it('emits open-settings event when settings button is clicked', async () => {
    const wrapper = mount(SideBar, {
      global: {
        stubs: {
          ChatList: true,
          FriendList: true
        }
      }
    });
    await wrapper.find('.settings-button').trigger('click');
    expect(wrapper.emitted()['open-settings']).toBeTruthy();
  });
});