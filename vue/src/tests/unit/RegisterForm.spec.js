import { mount } from '@vue/test-utils'
import RegisterForm from '../../components/RegisterForm.vue'

describe('RegisterForm', () => {
  it('nem engedi a regisztrációt hibás adatokkal', async () => {
    const wrapper = mount(RegisterForm)
    await wrapper.find('form').trigger('submit.prevent')
    expect(wrapper.emitted('register')).toBeFalsy()
    expect(wrapper.html()).toContain('Hibás!')
  })

  it('helyes adatokkal meghívja a register emit-et', async () => {
    const wrapper = mount(RegisterForm)
    await wrapper.find('input[placeholder="Felhasználónév"]').setValue('tesztuser')
    await wrapper.find('input[type="date"]').setValue('2000-01-01')
    await wrapper.find('input[type="email"]').setValue('teszt@example.com')
    const passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('titkosjelszo')
    await passwordInputs[1].setValue('titkosjelszo')
    await wrapper.find('form').trigger('submit.prevent')
    expect(wrapper.emitted('register')).toBeTruthy()
    const args = wrapper.emitted('register')[0]
    expect(args[0]).toBe('tesztuser')
    expect(args[1]).toBe('2000-01-01')
    expect(args[2]).toBe('teszt@example.com')
    expect(args[3]).toBe('titkosjelszo')
  })
})