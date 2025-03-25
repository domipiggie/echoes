<script setup>
import { ref } from 'vue'
import { userdataStore } from '../store/UserdataStore';
import { useRouter } from 'vue-router';
import axios from 'axios';
import LoginForm from './LoginForm.vue'
import RegisterForm from './RegisterForm.vue'
import OverlayContainer from './OverlayContainer.vue'

const userdata = userdataStore();
const isRightPanelActive = ref(false)
const router = useRouter();

const activateRightPanel = () => {
	isRightPanelActive.value = true
}
const deactivateRightPanel = () => {
	isRightPanelActive.value = false
}

const sendRegisterRequest = (username, birthdate, email, password) => {
	axios.post('http://localhost/auth/register', {
		username: username,
		email: email,
		password: password
	})
		.then(response => {
			alert(response.data['message']);
			activateRightPanel();
		})
		.catch(exception => {
			alert(exception);
		})
}
const sendLoginRequest = (email, password) => {
	axios.post('http://localhost/auth/login', {
		email: email,
		password: password
	})
		.then(response => {
			if (!response.data['access_token']) {
				alert('nuh uh');
				return;
			}
			userdata.setAccessToken(response.data['access_token']);
			userdata.setUserID(response.data.userID);
			alert("Sikeres bejelentkezés!")
			router.push("/chat")
		})
		.catch(exception => {
			alert(exception);
		})
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

/* Add responsive styles */
@media (max-width: 768px) {
	.container {
		width: 100%;
		min-height: 100vh;
		margin: 0;
		padding: 20px;
		box-shadow: none;
		border-radius: 0;
		display: flex;
		flex-direction: column;
		gap: 20px;
		background: #F2F0FA;
	}

	.form-container {
		position: static !important;
		width: 100% !important;
		height: auto !important;
		transform: none !important;
		opacity: 1 !important;
	}

	.overlay-container {
		display: none;
	}

	.sign-in-container,
	.sign-up-container {
		display: block !important;
		background: transparent;
	}

	form {
		background: white;
		border-radius: 15px;
		box-shadow: 0 4px 15px rgba(112, 120, 230, 0.1);
	}
}
</style>