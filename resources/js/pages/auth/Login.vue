<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Mail, LockKeyhole, ArrowRight } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Selamat Datang Kembali',
        description: 'Silakan masuk ke akun Anda untuk melanjutkan',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Masuk" />

    <div
        v-if="status"
        class="mb-6 rounded-xl bg-green-50/50 p-4 text-center text-sm font-medium text-green-600 ring-1 ring-green-500/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30 animate-in fade-in slide-in-from-top-2 duration-500"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <!-- Email Input -->
            <div class="grid gap-2 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-75 fill-mode-both">
                <Label for="email" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                    <Mail class="size-4 text-indigo-500 dark:text-indigo-400" />
                    Alamat email
                </Label>
                <div class="relative group">
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-11 rounded-xl bg-white/50 backdrop-blur-sm transition-all duration-300 group-hover:bg-white/80 focus:bg-white dark:bg-slate-900/50 dark:group-hover:bg-slate-900/80 dark:focus:bg-slate-900 shadow-sm"
                    />
                    <!-- Subtle glow effect on focus -->
                    <div class="pointer-events-none absolute inset-0 -z-10 rounded-xl bg-indigo-500/0 opacity-0 blur-md transition-all duration-500 group-focus-within:bg-indigo-500/20 group-focus-within:opacity-100 dark:group-focus-within:bg-indigo-400/20"></div>
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Input -->
            <div class="grid gap-2 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-150 fill-mode-both">
                <div class="flex items-center justify-between">
                    <Label for="password" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                        <LockKeyhole class="size-4 text-indigo-500 dark:text-indigo-400" />
                        Kata sandi
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                        :tabindex="5"
                    >
                        Lupa sandi?
                    </TextLink>
                </div>
                <div class="relative group">
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="h-11 rounded-xl bg-white/50 backdrop-blur-sm transition-all duration-300 group-hover:bg-white/80 focus:bg-white dark:bg-slate-900/50 dark:group-hover:bg-slate-900/80 dark:focus:bg-slate-900 shadow-sm"
                    />
                     <div class="pointer-events-none absolute inset-0 -z-10 rounded-xl bg-indigo-500/0 opacity-0 blur-md transition-all duration-500 group-focus-within:bg-indigo-500/20 group-focus-within:opacity-100 dark:group-focus-within:bg-indigo-400/20"></div>
                </div>
                <InputError :message="errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200 fill-mode-both">
                <Label for="remember" class="flex cursor-pointer items-center space-x-3 group text-slate-600 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200">
                    <Checkbox id="remember" name="remember" :tabindex="3" class="data-[state=checked]:bg-indigo-600 data-[state=checked]:border-indigo-600 rounded-md" />
                    <span class="text-sm font-medium">Ingat saya</span>
                </Label>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                class="group relative mt-2 w-full h-12 overflow-hidden rounded-xl border-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 bg-[length:200%_auto] font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-500 hover:bg-[position:right_center] hover:shadow-indigo-500/50 hover:-translate-y-0.5 animate-in fade-in slide-in-from-bottom-4 delay-300 fill-mode-both"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <!-- Animated shine effect -->
                <div class="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

                <span class="flex items-center justify-center gap-2">
                    <Spinner v-if="processing" class="text-white" />
                    <span v-else>Masuk Sekarang</span>
                    <ArrowRight v-if="!processing" class="size-4 transition-transform duration-300 group-hover:translate-x-1" />
                </span>
            </Button>
        </div>

        <!-- Registration Link -->
        <div
            class="mt-4 text-center text-sm font-medium text-slate-500 dark:text-slate-400 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-500 fill-mode-both"
            v-if="canRegister"
        >
            Belum punya akun?
            <TextLink
                :href="register()"
                :tabindex="5"
                class="font-semibold text-indigo-600 transition-all hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300 ml-1"
            >
                Daftar Gratis
            </TextLink>
        </div>
    </Form>
</template>

<style>
@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
</style>


