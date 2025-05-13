import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach } from 'vitest';
import AppearanceSelector from '../../components/AppearanceSelector.vue';

describe('AppearanceSelector', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(AppearanceSelector);
  });

  it('megfelelően renderelődik a téma gombokkal és akciógombokkal', () => {
    expect(wrapper.find('div.appearance-modal').exists()).toBe(true);
    
    const themeButtons = wrapper.findAll('button.theme-button');
    expect(themeButtons.length).toBe(2); 

    expect(wrapper.find('button.action-button.select').exists()).toBe(true); 
    expect(wrapper.find('button.action-button.cancel').exists()).toBe(true);
  });

  it('alapértelmezetten a "messenger" téma van kiválasztva és aktív', () => {
    const messengerButton = wrapper.findAll('button.theme-button').find(b => b.text().includes('Messenger'));
    expect(messengerButton.classes()).toContain('active');
  });

  it('kiválasztja a "discord" témát és "select", valamint "close" eseményt bocsát ki a "Kiválasztás" gombra kattintva', async () => {
    const discordButton = wrapper.findAll('button.theme-button').find(b => b.text().includes('Discord'));
    await discordButton.trigger('click');

    const selectButton = wrapper.find('button.action-button.select');
    await selectButton.trigger('click'); 

    expect(wrapper.emitted().select).toBeTruthy();
    expect(wrapper.emitted().select[0]).toEqual(['discord']);
    expect(wrapper.emitted().close).toBeTruthy(); 
  });

  it('kiválasztja az alapértelmezett "messenger" témát és "select", valamint "close" eseményt bocsát ki a "Kiválasztás" gombra kattintva', async () => {

    const selectButton = wrapper.find('button.action-button.select');
    await selectButton.trigger('click'); 

    expect(wrapper.emitted().select).toBeTruthy();
    expect(wrapper.emitted().select[0]).toEqual(['messenger']);
    expect(wrapper.emitted().close).toBeTruthy();
  });

  it('"close" eseményt bocsát ki a "Bezárás" gombra kattintva', async () => {
    const cancelButton = wrapper.find('button.action-button.cancel');
    await cancelButton.trigger('click');

    expect(wrapper.emitted().close).toBeTruthy();
   
    expect(wrapper.emitted().select).toBeFalsy(); 
  });

  it('a "Messenger" gomb aktívvá válik kattintásra', async () => {
    const messengerButton = wrapper.findAll('button.theme-button').find(b => b.text().includes('Messenger'));
    const discordButton = wrapper.findAll('button.theme-button').find(b => b.text().includes('Discord'));

   
    expect(messengerButton.classes()).toContain('active');
    expect(discordButton.classes()).not.toContain('active');

    await discordButton.trigger('click');
    expect(discordButton.classes()).toContain('active');
    expect(messengerButton.classes()).not.toContain('active');
    
    await messengerButton.trigger('click');
    expect(messengerButton.classes()).toContain('active');
    expect(discordButton.classes()).not.toContain('active');
  });
});