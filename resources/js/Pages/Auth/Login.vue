<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar sesión" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="login-head">
            <h1 class="login-title">Bienvenido de nuevo</h1>
            <p class="login-sub">Accede a tu cuenta de PrintCheck AI</p>
        </div>

        <div v-if="status" class="status-msg">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="login-row mt-4">
                <label class="remember-label">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span>Recordarme</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')" class="forgot-link">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Iniciar sesión
                </PrimaryButton>
            </div>

            <p class="register-hint">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="auth-link">Regístrate gratis</Link>
            </p>
        </form>
    </AuthenticationCard>
</template>

<style scoped>
.login-head {
    margin-bottom: 24px;
}
.login-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.02em;
    margin: 0 0 4px;
}
.login-sub {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}
.status-msg {
    margin-bottom: 16px;
    font-size: 14px;
    font-weight: 500;
    color: #16a34a;
}
.login-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.remember-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    cursor: pointer;
}
.forgot-link {
    font-size: 13px;
    color: #16a34a;
    font-weight: 500;
    text-decoration: none;
    transition: color .15s;
}
.forgot-link:hover {
    color: #15803d;
    text-decoration: underline;
}
.register-hint {
    margin-top: 20px;
    text-align: center;
    font-size: 13px;
    color: #64748b;
}
.auth-link {
    color: #16a34a;
    font-weight: 600;
    text-decoration: none;
}
.auth-link:hover {
    text-decoration: underline;
}
</style>
