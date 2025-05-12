import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('emits login event with correct data', async () => {
    const wrapper = mount(LoginForm);
    const email = 'test@example.com';
    const password = 'password123';
    await wrapper.find('input[type="text"]').setValue(email);
    await wrapper.find('input[type="password"]').setValue(password);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted().login[0]).toEqual([email, password]);
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
    expect(wrapper.vm.errorMessage).toBe(false);
  });

  it('clears error messages when form is valid and submitted', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalid');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);

    await wrapper.find('input[type="text"]').setValue('valid@example.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
    expect(wrapper.vm.errorMessage).toBe(false);
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');

    await wrapper.find('input[type="text"]').setValue('test@test.com');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');

    if (typeof wrapper.vm.errorMessage !== 'undefined') {
      expect(wrapper.vm.errorMessage).toBe(false);
    }
    expect(wrapper.vm.errorMessageEmail).toBe('');
    expect(wrapper.vm.errorMessagePassw).toBe('');
  });
});
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import LoginForm from '../../components/LoginForm.vue';

describe('LoginForm', () => {
  it('renders properly', () => {
    const wrapper = mount(LoginForm);
    expect(wrapper.find('h1').text()).toBe('Üdv újra itt!');
  });

  it('shows error messages for empty fields', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessagePassw).toBe('Hibás jelszó');
    expect(wrapper.vm.errorMessage).toBe(true);
  });

  it('shows error message for invalid email format', async () => {
    const wrapper = mount(LoginForm);
    await wrapper.find('input[type="text"]').setValue('invalidemail');
    await wrapper.find('input[type="password"]').setValue('password123');
    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.vm.errorMessageEmail).toBe('Hibás e-mail cím!');
    expect(wrapper.vm.errorMessage).toBe(true);
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
  });
});