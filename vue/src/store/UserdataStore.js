import {defineStore} from 'pinia';
import {ref} from 'vue';

export const userdataStore = defineStore("userdata", ()=>{
    const accessToken = ref("");

    function setAccessToken(token){
        accessToken.value = token;
    }
    function getAccessToken(){
        return accessToken.value;
    }

    return {setAccessToken, getAccessToken};
});