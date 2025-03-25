<script setup>
import { ref } from 'vue'
import { userdataStore } from '../store/UserdataStore';
import { useRouter } from 'vue-router';
import LoginForm from './LoginForm.vue'
import RegisterForm from './RegisterForm.vue'
import OverlayContainer from './OverlayContainer.vue'
import { authService } from '../services/authService';

const userdata = userdataStore();
const isRightPanelActive = ref(false)
const router = useRouter();

const activateRightPanel = () => {
	isRightPanelActive.value = true
}
const deactivateRightPanel = () => {
	isRightPanelActive.value = false
}

const sendRegisterRequest = async (username, birthdate, email, password) => {
	try {
		const response = await authService.register(username, email, password);
		alert(response.message);
		activateRightPanel();
	} catch (error) {
		alert(error.response?.data?.message || 'Registration failed');
	}
}

const sendLoginRequest = async (email, password) => {
	try {
		const response = await authService.login(email, password);
		if (!response.access_token) {
			alert('Authentication failed: No access token received');
			return;
		}
		userdata.setAccessToken(response.access_token);
		userdata.setUserID(response.userID);
		alert("Sikeres bejelentkezés!")
		router.push("/chat")
	} catch (error) {
		alert(error.response?.data?.message || 'Login failed');
	}
}
</script>
<script>
export default {
	name: 'Login'
}
</script>

<template>
	<div class="container" :class="{ 'right-panel-active': isRightPanelActive }">
		<LoginForm @login="sendLoginRequest" />
		<RegisterForm @register="sendRegisterRequest" />
		<OverlayContainer @activate-right-panel="activateRightPanel" @deactivate-right-panel="deactivateRightPanel" />
	</div>
</template>

<style scoped>
.container {
	background-color: #fff;
	border-radius: 10px;
	box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25),
		0 10px 10px rgba(0, 0, 0, 0.22);
	position: relative;
	overflow: hidden;
	width: 900px;
	max-width: 100%;
	min-height: 600px;
}

.form-container {
	position: absolute;
	top: 0;
	height: 100%;
	transition: all 0.6s ease-in-out;
}
</style>