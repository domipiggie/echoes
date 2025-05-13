import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('nem engedi a bejelentkezést üres mezőkkel', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted('login')).toBeFalsy();
    expect(wrapper.html()).toContain('Hibás e-mail cím!');
    expect(wrapper.html()).toContain('Hibás jelszó');
  });

  it('csak email megadásával nem engedi a bejelentkezést', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[placeholder="Email cím"]').setValue('teszt@example.com');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted('login')).toBeFalsy();
    expect(wrapper.html()).toContain('Hibás jelszó');
  });

  it('csak jelszó megadásával nem engedi a bejelentkezést', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[placeholder="Jelszó"]').setValue('titkosjelszo');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted('login')).toBeFalsy();
    expect(wrapper.html()).toContain('Hibás e-mail cím!');
  });

  it('helyes adatokkal meghívja a login emit-et', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[placeholder="Email cím"]').setValue('teszt@example.com');
    await wrapper.find('input[placeholder="Jelszó"]').setValue('titkosjelszo');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted('login')).toBeTruthy();
    const args = wrapper.emitted('login')[0];
    expect(args[0]).toBe('teszt@example.com');
    expect(args[1]).toBe('titkosjelszo');
  });
});