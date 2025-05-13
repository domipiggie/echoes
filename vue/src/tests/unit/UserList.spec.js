import { mount } from '@vue/test-utils';
import UserList from '../../components/groupModals/UserList.vue';
import Alert from '../../classes/Alert';
import { vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';

vi.mock('../../store/messageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getCurrentChannelId: () => 'test-channel-123'
  }))
}));

vi.mock('../../store/channelStore', () => ({
  useChannelStore: vi.fn(() => ({
    getGroupChannelById: (id) => ({
      getUsers: () => [
        { 
          getUserID: () => 'test-user-123',
          getUserName: () => 'Current User',
          getProfilePicture: () => null
        },
        {
          getUserID: () => 'other-user-456',
          getUserName: () => 'Other User',
          getProfilePicture: () => '/avatar.jpg'
        }
      ],
      getOwnerID: () => 'test-user-123'
    }),
    fetchGroupChannels: vi.fn()
  }))
}));

vi.mock('../../store/messageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getCurrentChannelId: 'test-channel-123'
  }))
}));

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    isConnected: true,
    send: vi.fn()
  }))
}));

const mockAddAlert = vi.fn();
vi.mock('../../store/AlertStore', () => ({
  useAlertStore: vi.fn(() => ({
    addAlert: mockAddAlert
  }))
}));

vi.mock('../../classes/Alert', () => ({
  default: vi.fn().mockImplementation((title, message, type, callback) => ({
    title,
    message,
    type,
    callback
  }))
}));

describe('UserList', () => {
  let pinia;
  
  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    
    mockAddAlert.mockClear();
    vi.mock('../../store/UserdataStore', () => ({
      userdataStore: () => ({
        getUserID: () => 'test-user-123',
        getCurrentTheme: () => 'messenger',
      })
    }));
  });

  const factory = () => {
    return mount(UserList, {
      global: {
        plugins: [pinia],
        stubs: {
          'svg': true
        }
      }
    });
  };

  it('renders user list correctly', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    expect(wrapper.findAll('.friend-item')).toHaveLength(2);
  });

  it('triggers remove user confirmation', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    await wrapper.findAll('.selection-indicator')[1].trigger('click');
    expect(mockAddAlert).toHaveBeenCalled();
  });

  it('triggers ownership transfer confirmation', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    await wrapper.findAll('.selection-indicator')[0].trigger('click');
    
    expect(Alert).toHaveBeenCalledWith(
      'Megerősítés',
      'Biztosan át szeretnéd adni a csoport tulajdonát?',
      'confirm',
      expect.any(Function)
    );
    
    expect(mockAddAlert).toHaveBeenCalledWith(
      expect.objectContaining({
        title: 'Megerősítés',
        message: 'Biztosan át szeretnéd adni a csoport tulajdonát?',
        type: 'confirm',
        callback: expect.any(Function)
      })
    );
  });

  it('only shows action buttons for owner and non-self users', async () => {
    const wrapper = factory();
    await wrapper.vm.$nextTick();
    const buttons = wrapper.findAll('.selection-indicator');
    
    expect(buttons.length).toBe(2);
    
    const transferButton = buttons[0];
    const removeButton = buttons[1];
    
    expect(transferButton.html()).toContain('path');
    expect(removeButton.html()).toContain('line');
  });
});