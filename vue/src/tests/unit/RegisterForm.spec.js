import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import RegisterForm from '../../components/RegisterForm.vue';

describe('RegisterForm', () => {
  it('renders properly', () => {
    const wrapper = mount(RegisterForm);
    expect(wrapper.find('h1').text()).toBe('Mielőtt chatelnél...');
    expect(wrapper.find('.alcim').text()).toBe('Regisztrálj egy fiókot pár kattintással!');
  });

  it('validates empty fields', async () => {
    const wrapper = mount(RegisterForm);
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errors.username).toBe(true);
    expect(wrapper.vm.errors.email).toBe(true);
    expect(wrapper.vm.errors.birthdate).toBe(true);
    expect(wrapper.vm.errors.password1).toBe(true);
  });

  it('validates email format', async () => {
    const wrapper = mount(RegisterForm);

    await wrapper.find('input[type="email"]').setValue('invalidEmail');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errors.email).toBe(true);

    await wrapper.find('input[type="email"]').setValue('valid@email.com');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errors.email).toBe(false);
  });

  it('validates password match', async () => {
    const wrapper = mount(RegisterForm);

    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.findAll('input[type="password"]')[1].setValue('differentPassword');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errors.password2).toBe(true);

    await wrapper.findAll('input[type="password"]')[1].setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.vm.errors.password2).toBe(false);
  });

  it('emits register event with correct data when form is valid', async () => {
    const wrapper = mount(RegisterForm);
    const testData = {
      username: 'testuser',
      email: 'test@test.com',
      password: 'password123',
      birthdate: '2000-01-01'
    };

    await wrapper.find('input[type="text"]').setValue(testData.username);
    await wrapper.find('input[type="email"]').setValue(testData.email);
    await wrapper.find('input[type="date"]').setValue(testData.birthdate);
    await wrapper.findAll('input[type="password"]')[0].setValue(testData.password);
    await wrapper.findAll('input[type="password"]')[1].setValue(testData.password);

    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.emitted().register[0]).toEqual([
      testData.username,
      testData.birthdate,
      testData.email,
      testData.password
    ]);
  });
});