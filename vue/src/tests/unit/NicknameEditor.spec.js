import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import NicknameEditor from '../../components/NicknameEditor.vue';

describe('NicknameEditor', () => {
  it('renders properly', () => {
    const wrapper = mount(NicknameEditor, {
      props: {
        currentNickname: 'TesztElek'
      }
    });
    expect(wrapper.find('h3').text()).toBe('Becenév Szerkesztése');
    expect(wrapper.find('input[type="text"]').element.value).toBe('TesztElek');
  });

  it('emits save-nickname event with the new nickname', async () => {
    const wrapper = mount(NicknameEditor, {
      props: {
        currentNickname: 'TesztElek'
      }
    });
    const newNickname = 'UjBecenev';
    await wrapper.find('input[type="text"]').setValue(newNickname);
    await wrapper.find('button').trigger('click');
    expect(wrapper.emitted()['save-nickname']).toBeTruthy();
    expect(wrapper.emitted()['save-nickname'][0]).toEqual([newNickname]);
  });
});