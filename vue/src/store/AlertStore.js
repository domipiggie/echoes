import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAlertStore = defineStore('alert', () => {
    const alerts = ref([]);

    const getAlerts = computed(() => alerts.value);

    const addAlert = (alert) => {
        alert.setIndex(alerts.value.length);
        alerts.value.push(alert);
    }

    const removeAlert = (alert) => {
        alerts.value = alerts.value.filter(a => a !== alert);
    }

    return { getAlerts, addAlert, removeAlert }
})