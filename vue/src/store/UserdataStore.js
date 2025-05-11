import { defineStore } from 'pinia';
import { ref } from 'vue';
import { userService } from '../services/userService';
import { API_CONFIG } from '../config/api';

export const userdataStore = defineStore("userdata", () => {
    const accessToken = ref($cookies.get('accessToken') || "");
    const userID = ref($cookies.get('userID') || "");
    const refreshToken = ref($cookies.get('refreshToken') || "");
    const currentTheme = ref(localStorage.getItem('chatTheme') || "messenger");

    const username = ref("");
    const profilePicture = ref(null);

    function setUsername(name) {
        username.value = name;
    }

    function getUsername() {
        return username.value;
    }

    function setProfilePicture(picture) {
        profilePicture.value = picture;
    }

    function getProfilePicture() {
        if (profilePicture.value === null) {
            return null;
        }
        return API_CONFIG.BASE_URL + profilePicture.value;
    }

    async function fetchUserInfo() {
        try {
            await userService.getUserByID(getUserID()).then((response) => {
                setProfilePicture(response.data.profilePicture);
                setUsername(response.data.username);
            });
        } catch (error) {
            console.error('Error fetching user by userID:', error);
            throw error;
        }
    }

    function setAccessToken(token) {
        accessToken.value = token;
        $cookies.set('accessToken', token);
    }

    function getAccessToken() {
        return accessToken.value;
    }

    function setUserID(id) {
        userID.value = id;
        $cookies.set('userID', id);
    }

    function getUserID() {
        return userID.value;
    }

    function setRefreshToken(token) {
        refreshToken.value = token;
        $cookies.set('refreshToken', token);
    }

    function getCurrentTheme() {
        return currentTheme.value;
    }

    const handleThemeChange = (theme) => {
        currentTheme.value = theme;
        localStorage.setItem('chatTheme', theme);
      };

    function getRefreshToken() {
        return refreshToken.value; 
    }

    function clearAuth() {
        accessToken.value = "";
        userID.value = "";
        refreshToken.value = "";
        $cookies.remove('accessToken');
        $cookies.remove('userID');
        $cookies.remove('refreshToken');
    }

    return { setAccessToken, getAccessToken, setUserID, getUserID, handleThemeChange, getCurrentTheme, setRefreshToken, getRefreshToken, clearAuth, fetchUserInfo, getUsername, getProfilePicture, setUsername, setProfilePicture, getUsername, setUsername, getProfilePicture, setProfilePicture, setProfilePicture };
});