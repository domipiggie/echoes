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

		if (!response.success) {
			alert('Authentication failed: No access token received');
			return;
		}

		userdata.setAccessToken(response.data.access_token);
		userdata.setUserID(response.data.userID);
		userdata.setRefreshToken(response.data.refresh_token);
		
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

@media (max-width: 768px) {
	.container {
		width: 100%;
		min-height: 100vh;
		margin: 0;
		padding: 15px;
		box-shadow: none;
		border-radius: 0;
		display: flex;
		flex-direction: column;
		background: #F2F0FA;
		gap: 20px;
		justify-content: center;
	}

	.form-container {
		position: static !important;
		width: 100% !important;
		height: auto !important;
		transform: none !important;
	}

	/* Alapértelmezetten a regisztráció látszik */
	.sign-in-container {
		display: block;
		order: 1;
	}

	.sign-up-container {
		display: none;
	}

	/* Bejelentkezés gomb megnyomása után */
	.container.right-panel-active .sign-in-container {
		display: none;
	}

	.container.right-panel-active .sign-up-container {
		display: block;
	}

	.overlay-container {
		position: static;
		width: 100%;
		height: auto;
		transform: none !important;
		order: 2;
		margin-top: 20px;
	}

	.overlay {
		position: static;
		width: 100%;
		transform: none !important;
		border-radius: 15px;
		background: linear-gradient(to right, #5a62d3, #7078e6);
	}

	.overlay-panel {
		position: static;
		width: 100%;
		padding: 30px 20px;
		text-align: center;
		color: white;
	}

	.overlay-left {
		display: none !important;
	}

	.overlay-right {
		display: block !important;
	}

	.container.right-panel-active .overlay-right {
		display: none !important;
	}

	.container.right-panel-active .overlay-left {
		display: block !important;
	}

	form {
		background: white;
		border-radius: 15px;
		box-shadow: 0 4px 15px rgba(112, 120, 230, 0.1);
		padding: 25px;
		max-width: 400px;
		margin: 0 auto;
		width: 100%;
	}

	.ghost {
		background: transparent;
		border: 2px solid white;
		color: white;
		margin-top: 15px;
		padding: 10px 30px;
		border-radius: 20px;
		font-weight: bold;
		transition: all 0.3s ease;
	}

	.ghost:hover {
		background: white;
		color: #7078e6;
	}
}
</style>