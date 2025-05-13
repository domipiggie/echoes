import { mount } from '@vue/test-utils';
import AlertBox from '../../components/AlertBox.vue';
import Alert from '../../classes/Alert';
import { vi } from 'vitest';

describe('AlertBox', () => {
  it('renders alert title and message', () => {
    const testAlert = new Alert('Test Title', 'Test Message', 'info');
    const wrapper = mount(AlertBox, {
      props: { alert: testAlert }
    });

    expect(wrapper.text()).toContain('Test Title');
    expect(wrapper.text()).toContain('Test Message');
  });

  it('emits close event when confirm button is clicked', async () => {
    const mockCallback = vi.fn();
    const testAlert = new Alert('Confirm', 'Are you sure?', 'confirm', mockCallback);
    const wrapper = mount(AlertBox, {
      props: { alert: testAlert }
    });

    await wrapper.find('.create-button').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
    expect(mockCallback).toHaveBeenCalled();
  });

  it('shows correct buttons for confirm type', () => {
    const testAlert = new Alert('Confirm', '', 'confirm');
    const wrapper = mount(AlertBox, {
      props: { alert: testAlert }
    });

    expect(wrapper.findAll('.button-container button').length).toBe(2);
  });

  it('shows single button for non-confirm type', () => {
    const testAlert = new Alert('Info', '', 'info');
    const wrapper = mount(AlertBox, {
      props: { alert: testAlert }
    });

    expect(wrapper.findAll('.button-container button').length).toBe(1);
  });
});