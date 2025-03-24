import {defineStore} from 'pinia';
import {ref} from 'vue';

export const userdataStore = defineStore("userdata", ()=>{
    const accessToken = ref($cookies.get('accessToken') || "");
    const userID = ref($cookies.get('userID') || "");

    function setAccessToken(token){
        accessToken.value = token;
        $cookies.set('accessToken', token);
    }
    
    function getAccessToken(){
        return accessToken.value;
    }

    function setUserID(id) {
        userID.value = id;
        $cookies.set('userID', id);
    }
    
    function getUserID() {
        return userID.value;
    }

    function clearAuth() {
        accessToken.value = "";
        userID.value = "";
        $cookies.remove('accessToken');
        $cookies.remove('userID');
    }

    return {setAccessToken, getAccessToken, setUserID, getUserID, clearAuth};
});