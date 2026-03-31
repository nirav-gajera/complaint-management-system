import { defineStore } from 'pinia';

let nextId = 1;

export const useToastStore = defineStore('toasts', {
    state: () => ({
        items: [],
    }),
    actions: {
        push(message, type = 'success') {
            const id = nextId++;
            this.items.push({ id, message, type });
            setTimeout(() => {
                this.items = this.items.filter((item) => item.id !== id);
            }, 4000);
        },
        success(message) {
            this.push(message, 'success');
        },
        error(message) {
            this.push(message, 'error');
        },
    },
});
