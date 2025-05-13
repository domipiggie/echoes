import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import ChatHeader from '../../components/chat/ChatHeader.vue';
import { API_CONFIG } from '../../config/api';
import * as MessageStore from '../../store/MessageStore';
import * as ChannelStore from '../../store/ChannelStore';

vi.mock('../../store/MessageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getCurrentChannelId: 'test-channel-123'
  }))
}));

vi.mock('../../store/ChannelStore', () => ({
  useChannelStore: vi.fn(() => ({
    getFriendChannelById: vi.fn((id) => {
      if (id === 'test-channel-123') {
        return {
          getUser1: () => ({
            getUserID: () => 'current-user-123',
            getProfilePicture: () => null
          }),
          getUser2: () => ({
            getUserID: () => 'friend-user-456',
            getProfilePicture: () => '/friend-avatar.jpg'
          })
        };
      }
      return null;
    }),
    getGroupChannelById: vi.fn((id) => {
      if (id === 'test-group-789') {
        return {
          getPicture: () => '/group-avatar.jpg'
        };
      }
      return null;
    })
  }))
}));

vi.mock('../../store/UserdataStore', () => ({
  userdataStore: vi.fn(() => ({
    getUserID: () => 'current-user-123'
  }))
}));

describe('ChatHeader', () => {
  let pinia;
  
  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    API_CONFIG.BASE_URL = 'http://test-api.com';
  });

  const factory = (props = {}) => {
    return mount(ChatHeader, {
      props: {
        currentChannelName: 'Test Channel',
        isMobile: false,
        ...props
      },
      global: {
        plugins: [pinia]
      }
    });
  };

  it('renders channel name correctly', () => {
    const wrapper = factory({ currentChannelName: 'Test Channel Name' });
    expect(wrapper.find('.user-name').text()).toBe('Test Channel Name');
  });

  it('displays back button only on mobile view', () => {
    const desktopWrapper = factory({ isMobile: false });
    expect(desktopWrapper.find('.back-button').exists()).toBe(false);
    
    const mobileWrapper = factory({ isMobile: true });
    expect(mobileWrapper.find('.back-button').exists()).toBe(true);
  });

  it('emits go-back event when back button is clicked on mobile', async () => {
    const wrapper = factory({ isMobile: true });
    await wrapper.find('.back-button').trigger('click');
    expect(wrapper.emitted('go-back')).toBeTruthy();
  });

  it('toggles profile and emits toggle-profile event when more button is clicked', async () => {
    const wrapper = factory();
    await wrapper.find('.more-button').trigger('click');
    
    expect(wrapper.emitted('toggle-profile')).toBeTruthy();
    expect(wrapper.emitted('toggle-profile')[0][0]).toBe(true);
    
    await wrapper.find('.more-button').trigger('click');
    expect(wrapper.emitted('toggle-profile')[1][0]).toBe(false);
  });

  it('displays friend profile picture for friend channels', async () => {
    const messageStore = vi.mocked(MessageStore.useMessageStore);
    messageStore.mockReturnValue({
      getCurrentChannelId: 'test-channel-123'
    });
    
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    const avatar = wrapper.find('.avatar-circle img');
    expect(avatar.exists()).toBe(true);
    expect(avatar.attributes('src')).toBe('http://test-api.com/friend-avatar.jpg');
  });

  it('displays group profile picture for group channels', async () => {
    const messageStore = vi.mocked(MessageStore.useMessageStore);
    const channelStore = vi.mocked(ChannelStore.useChannelStore);
    
    messageStore.mockReturnValue({
      getCurrentChannelId: 'test-group-789'
    });
    
    channelStore.mockReturnValue({
      getFriendChannelById: () => null,
      getGroupChannelById: (id) => ({
        getPicture: () => '/group-avatar.jpg'
      })
    });
    
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    
    const avatar = wrapper.find('.avatar-circle img');
    expect(avatar.attributes('src')).toBe('http://test-api.com/group-avatar.jpg');
  });

  it('displays first letter of channel name when no profile picture is available', async () => {
    const messageStore = vi.mocked(MessageStore.useMessageStore);
    messageStore.mockReturnValue({
      getCurrentChannelId: null
    });
    
    const wrapper = factory({ currentChannelName: 'Test' });
    await wrapper.vm.$nextTick();
    
    expect(wrapper.find('.avatar-circle span').text()).toBe('T');
  });
});