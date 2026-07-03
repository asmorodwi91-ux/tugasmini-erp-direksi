<script setup lang="ts">
definePageMeta({ layout: 'auth' })
const { login, verifyOtp } = useAuthActions()
const step = ref(1)
const email = ref('')
const password = ref('')
const otp = ref('')
const debugOtp = ref('')
const error = ref('')
const loading = ref(false)

async function submitLogin() {
  error.value = ''; loading.value = true
  try {
    const res: any = await login(email.value, password.value)
    debugOtp.value = res.debug_otp || ''  // demo only
    step.value = 2
  } catch (e: any) {
    error.value = e?.data?.message || 'Login gagal'
  } finally { loading.value = false }
}
async function submitOtp() {
  error.value = ''; loading.value = true
  try {
    await verifyOtp(email.value, otp.value)
    await navigateTo('/')
  } catch (e: any) {
    error.value = 'OTP salah atau kadaluarsa'
  } finally { loading.value = false }
}
</script>
<template>
  <div class="auth-card">
    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center text-lg shadow-sm">🏢</div>
      <div>
        <div class="font-display font-extrabold text-primary-dark text-lg leading-tight tracking-tight">Mini ERP Direksi</div>
        <div class="text-xs text-grey mt-0.5">Masuk ke portal eksekutif</div>
      </div>
    </div>

    <div v-if="step === 1" class="space-y-5">
      <div>
        <label class="form-label">Email</label>
        <input v-model="email" type="email" autocomplete="username" placeholder="nama@perusahaan.co.id"
          class="input-field mt-1.5" @keyup.enter="submitLogin" />
      </div>
      <div>
        <label class="form-label">Password</label>
        <input v-model="password" type="password" autocomplete="current-password" placeholder="Masukkan password"
          class="input-field mt-1.5" @keyup.enter="submitLogin" />
      </div>
      <button @click="submitLogin" :disabled="loading" class="btn-primary w-full">
        {{ loading ? 'Memproses...' : 'Kirim Kode OTP' }}
      </button>
    </div>

    <div v-else class="space-y-5">
      <div class="text-sm text-grey">Masukkan 6 angka OTP (berlaku 5 menit)</div>
      <div v-if="debugOtp" class="text-xs bg-bg-soft rounded-lg px-3 py-2.5 border border-line/60">
        <span class="text-grey">Demo OTP:</span> <span class="font-mono font-bold text-ink">{{ debugOtp }}</span>
      </div>
      <input v-model="otp" maxlength="6" placeholder="______"
        class="input-field text-center font-mono text-xl tracking-widest" />
      <button @click="submitOtp" :disabled="loading" class="btn-primary w-full">
        {{ loading ? 'Memverifikasi...' : 'Masuk' }}
      </button>
    </div>

    <div v-if="error" class="mt-5 text-sm text-status-merah">⚠️ {{ error }}</div>
  </div>
</template>
