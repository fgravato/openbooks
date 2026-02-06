<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
  enabled: boolean;
  qr_code_svg: string;
  backup_codes: string[];
}>();

const enableForm = useForm({
  totp_code: '',
  password: '',
});

const disableForm = useForm({
  totp_code: '000000',
  password: '',
});

const enable = (): void => {
  enableForm.post(route('2fa.enable'));
};

const disable = (): void => {
  disableForm.delete(route('2fa.disable'));
};
</script>

<template>
  <Head title="Two-Factor Setup" />

  <div class="mx-auto mt-10 w-full max-w-2xl rounded-xl2 border border-brand-100 bg-white p-8 shadow-panel">
    <h1 class="font-display text-3xl font-semibold text-slate-900">Two-factor authentication</h1>
    <p class="mt-2 text-sm text-slate-600">Use an authenticator app to secure your account.</p>

    <div class="mt-6 rounded-lg border border-slate-200 p-4">
      <p class="text-sm font-medium text-slate-700">Scan this QR code:</p>
      <div class="mt-3" v-html="qr_code_svg" />
    </div>

    <div class="mt-4 rounded-lg border border-slate-200 p-4">
      <p class="text-sm font-medium text-slate-700">Backup codes</p>
      <ul class="mt-2 space-y-1 text-sm text-slate-600">
        <li v-for="code in backup_codes" :key="code" class="font-mono">{{ code }}</li>
      </ul>
    </div>

    <form class="mt-6 grid gap-3 md:grid-cols-[1fr_auto]" @submit.prevent="enable">
      <input v-model="enableForm.totp_code" maxlength="6" placeholder="6-digit code" class="rounded-lg border border-slate-300 px-3 py-2" />
      <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Enable</button>
    </form>

    <form v-if="enabled" class="mt-6 grid gap-3 md:grid-cols-[1fr_auto]" @submit.prevent="disable">
      <input v-model="disableForm.password" type="password" placeholder="Confirm with password" class="rounded-lg border border-slate-300 px-3 py-2" />
      <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Disable</button>
    </form>
  </div>
</template>
