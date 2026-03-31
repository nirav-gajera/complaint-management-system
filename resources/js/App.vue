<template>
    <RouterView />
    <ToastStack />
</template>

<script setup>
import { RouterView } from 'vue-router';
import ToastStack from './components/ToastStack.vue';
</script>
