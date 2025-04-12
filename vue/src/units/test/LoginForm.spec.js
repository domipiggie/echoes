import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
    expect(wrapper.find('.alcim').text()).toBe('Jelentkezz be gyorsan!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
  });

  it('emits login event with correct data', async () => {
    const wrapper = mount(LoginForm);
    const testEmail = 'test@test.com';
    const testPassword = 'password123';

    await wrapper.find('input[type="text"]').setValue(testEmail);
    await wrapper.find('input[type="password"]').setValue(testPassword);
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.emitted().login[0]).toEqual([testEmail, testPassword]);
  });

  it('clears error messages when form is valid', async () => {
    const wrapper = mount(LoginForm);
    
    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errorMessage).toBe(false);
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});