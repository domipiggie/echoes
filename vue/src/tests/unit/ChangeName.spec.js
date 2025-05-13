import { mount } from '@vue/test-utils';
import ChangeName from '../../components/groupModals/ChangeName.vue';
import { useWebSocketStore } from '../../store/WebSocketStore';
import { useMessageStore } from '../../store/messageStore';
import { vi } from 'vitest';

vi.mock('../../store/WebSocketStore', () => ({
  useWebSocketStore: vi.fn(() => ({
    isConnected: true,
    send: vi.fn()
  }))
}));

vi.mock('../../store/messageStore', () => ({
  useMessageStore: vi.fn(() => ({
    getCurrentChannelId: 'channel-123',
    getCurrentChannelName: 'Current Channel'
  }))
}));

describe('ChangeName', () => {
  const factory = () => {
    return mount(ChangeName);
  };

  it('renders correctly with initial state', () => {
    const wrapper = factory();
    expect(wrapper.find('h2').text()).toBe('Csoport név módosítása');
    expect(wrapper.find('input').attributes('placeholder')).toBe('Current Channel');
  });

  it('emits close event when clicking overlay or close button', async () => {
    const wrapper = factory();
    
    await wrapper.find('.new-group-overlay').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
    
    await wrapper.find('.close-button').trigger('click');
    expect(wrapper.emitted('close')).toHaveLength(2);
  });

  it('updates groupName ref when input changes', async () => {
    const wrapper = factory();
    await wrapper.find('input').setValue('New Group Name');
    expect(wrapper.vm.groupName).toBe('New Group Name');
  });

  it('disables submit button when groupName is empty', () => {
    const wrapper = factory();
    expect(wrapper.find('.create-button').attributes('disabled')).toBeDefined();
  });

  it('sends websocket message and closes when submitting', async () => {
    const mockSend = vi.fn();
    useWebSocketStore.mockImplementation(() => ({
        isConnected: true,
        send: mockSend
    }));
    
    const wrapper = factory();
    
    await wrapper.find('input').setValue('New Group Name');
    await wrapper.find('.create-button').trigger('click');
    
    expect(mockSend).toHaveBeenCalledWith({
        type: 'group_update_info',
        channelId: 'channel-123', 
        groupName: 'New Group Name'
    });
    expect(wrapper.emitted('close')).toBeTruthy();
});
});