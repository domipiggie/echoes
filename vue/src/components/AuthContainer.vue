<script setup>
import { ref } from 'vue'
import { userdataStore } from '../store/UserdataStore';
import axios from 'axios';
import LoginForm from './LoginForm.vue'
import RegisterForm from './RegisterForm.vue'
import OverlayContainer from './OverlayContainer.vue'

const userdata = userdataStore();
const isRightPanelActive = ref(false)

const activateRightPanel = () => {
	isRightPanelActive.value = true
}
const deactivateRightPanel = () => {
	isRightPanelActive.value = false
}

const sendRegisterRequest = (username, birthdate, email, password) => {
	axios.post('http://localhost/register', {
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
	axios.post('http://localhost/login', {
		email: email,
		password: password
	})
		.then(response => {
			userdata.setAccessToken(response.data['access_token']);
			alert("Sikeres bejelentkezés!")
		})
		.catch(exception => {
			alert(exception);
		})
}
const testAccessToken = () => {
	axios.get('http://localhost/protected', { headers: { Authorization: 'Bearer ' + userdata.getAccessToken() } })
		.then(response => {
			alert(response.data['user']);
		})
		.catch(exception => {
			alert(exception);
		})
}
</script>

<template>
	<!--<button @click="testAccessToken">Login test</button>-->
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
	width: 768px;
	max-width: 100%;
	min-height: 480px;
}

.form-container {
	position: absolute;
	top: 0;
	height: 100%;
	transition: all 0.6s ease-in-out;
}
</style>