<template>
    <transition name="toast">
        <div v-if="show" 
            :class="[
                'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50',
                type === 'success' ? 'bg-green-500' : 'bg-red-500',
                'text-white'
            ]"
        >
            {{ message }}
        </div>
    </transition>
</template>

<script>
export default {
    name: 'Toast',
    props: {
        message: {
            type: String,
            required: true
        },
        type: {
            type: String,
            default: 'success',
            validator: value => ['success', 'error'].includes(value)
        },
        duration: {
            type: Number,
            default: 3000
        }
    },
    data() {
        return {
            show: true
        }
    },
    mounted() {
        setTimeout(() => {
            this.show = false;
            setTimeout(() => {
                this.$emit('close');
            }, 300);
        }, this.duration);
    }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    transform: translateY(100%);
    opacity: 0;
}
</style> 