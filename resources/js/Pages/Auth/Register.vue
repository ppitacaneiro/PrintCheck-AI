<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Crear cuenta" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="reg-head">
            <h1 class="reg-title">Crea tu cuenta</h1>
            <p class="reg-sub">Empieza gratis con PrintCheck AI hoy</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nombre" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
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
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="terms-row">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />
                        <div class="terms-text">
                            Acepto los
                            <a target="_blank" :href="route('terms.show')" class="auth-link">Términos de servicio</a>
                            y la
                            <a target="_blank" :href="route('policy.show')" class="auth-link">Política de privacidad</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Crear cuenta
                </PrimaryButton>
            </div>

            <p class="login-hint">
                ¿Ya tienes cuenta?
                <Link :href="route('login')" class="auth-link">Inicia sesión</Link>
            </p>
        </form>
    </AuthenticationCard>
</template>

<style scoped>
.reg-head {
    margin-bottom: 24px;
}
.reg-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.02em;
    margin: 0 0 4px;
}
.reg-sub {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}
.terms-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}
.terms-text {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}
.login-hint {
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
