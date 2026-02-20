import { reactive } from 'vue';

export const toastState = reactive({
    show: false,
    message: '',
    type: 'success'
});

export const showToast = (message, type = 'success') => {
    toastState.message = message;
    toastState.type = type;
    toastState.show = true;
    setTimeout(() => {
        toastState.show = false;
    }, 3000);
};

export const cleanCardName = (name) => {
    return name;
};