import {defineStore} from 'pinia';
import {ref} from 'vue';

export const userdataStore = defineStore("userdata", ()=>{
    const accessToken = ref("");
    const userID = ref("");

    function setAccessToken(token){
        accessToken.value = token;
    }
    function getAccessToken(){
        return accessToken.value;
    }

    function setUserID(id) {
        userID.value = id;
    }
    function getUserID() {
        return userID.value;
    }

    return {setAccessToken, getAccessToken, setUserID, getUserID};
});