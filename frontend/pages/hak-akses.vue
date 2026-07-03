<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()

type AksesRow = { modul: string; lihat: boolean; ubah: boolean; setujui: boolean }
type User = { id: number; nama: string; role: string; status: string; akses?: Record<string, string> }

const MODULES = ['Dashboard', 'Keuangan', 'Approval', 'Operasional', 'Ekspor']

const users = ref<User[]>([])
const roles = ref<any[]>([])
const loading = ref(true)
const search = ref('')
const selected = ref<User | null>(null)
const matrix = ref<AksesRow[]>([])
const saving = ref(false)
const msg = ref('')
const msgType = ref<'ok' | 'err'>('ok')
const showAdd = ref(false)
const addForm = ref({ nama: '', email: '', id_role: '', password: '' })
const addSaving = ref(false)
const addMsg = ref('')
const addErr = ref('')

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return users.value
  return users.value.filter(
    (u) => u.nama.toLowerCase().includes(q) || (u.role || '').toLowerCase().includes(q),
  )
})

async function load() {
  loading.value = true
  try {
    const [resUsers, resRoles]: any[] = await Promise.all([
      api('/access/users'),
      api('/access/roles'),
    ])
    users.value = resUsers.data || []
    roles.value = resRoles.data || []
  } catch (e: any) {
    msg.value = 'Gagal memuat data pengguna.'
    msgType.value = 'err'
  } finally {
    loading.value = false
  }
}

async function tambahPengguna() {
  addMsg.value = ''; addErr.value = ''
  if (!addForm.value.nama.trim() || !addForm.value.email.trim() || !addForm.value.id_role) {
    addErr.value = 'Nama, email, dan role wajib diisi.'
    return
  }
  addSaving.value = true
  try {
    const res: any = await api('/access/users', {
      method: 'POST',
      body: {
        nama: addForm.value.nama,
        email: addForm.value.email,
        id_role: Number(addForm.value.id_role),
        password: addForm.value.password || undefined,
      },
    })
    addMsg.value = `Pengguna "${res.nama}" (${res.role}) berhasil ditambahkan. Password awal: ${addForm.value.password || 'password'}.`
    addForm.value = { nama: '', email: '', id_role: '', password: '' }
    await load()
  } catch (e: any) {
    const errs = e?.data?.errors
    addErr.value = errs ? Object.values(errs).flat().join(' ') : (e?.data?.message || 'Gagal menambah pengguna.')
  } finally {
    addSaving.value = false
  }
}

// Bangun matriks dari hak akses tersimpan (level: lihat|ubah|setujui)
function matrixFromAkses(akses: Record<string, string> = {}): AksesRow[] {
  return MODULES.map((m) => {
    const level = akses?.[m]
    return {
      modul: m,
      lihat: !!level,
      ubah: level === 'ubah' || level === 'setujui',
      setujui: level === 'setujui',
    }
  })
}

function edit(u: any) {
  selected.value = u
  matrix.value = matrixFromAkses(u.akses)
  msg.value = ''
  showAdd.value = false
}

function toggle(row: AksesRow, col: 'lihat' | 'ubah' | 'setujui') {
  row[col] = !row[col]
  // Level bersifat hierarkis: Setujui > Ubah > Lihat
  if (col === 'setujui' && row.setujui) {
    row.ubah = true
    row.lihat = true
  }
  if (col === 'ubah' && row.ubah) row.lihat = true
  if (col === 'lihat' && !row.lihat) {
    row.ubah = false
    row.setujui = false
  }
  if (col === 'ubah' && !row.ubah) row.setujui = false
}

async function save() {
  if (!selected.value) return
  saving.value = true
  msg.value = ''
  try {
    await api('/access/save', {
      method: 'POST',
      body: {
        user_id: selected.value.id,
        akses: matrix.value.map((r) => ({
          modul: r.modul,
          lihat: r.lihat,
          ubah: r.ubah,
          setujui: r.setujui,
        })),
      },
    })
    msg.value = `Hak akses ${selected.value.nama} tersimpan & berlaku langsung.`
    msgType.value = 'ok'
    // Muat ulang agar daftar & editor mencerminkan hak akses terbaru
    const sid = selected.value.id
    await load()
    const refreshed = users.value.find((u) => u.id === sid)
    if (refreshed) { selected.value = refreshed; matrix.value = matrixFromAkses(refreshed.akses) }
  } catch (e: any) {
    msg.value = e?.data?.message || 'Gagal menyimpan hak akses.'
    msgType.value = 'err'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex flex-col lg:flex-row gap-5 items-start">
      <!-- Kolom daftar pengguna -->
      <div class="flex-[1.3] w-full">
        <div class="flex items-center justify-between mb-4 gap-3">
          <h1 class="page-title">Manajemen Hak Akses</h1>
          <button class="btn-primary rounded-full whitespace-nowrap" @click="showAdd = true; selected = null">
            + Tambah Pengguna
          </button>
        </div>

        <div class="card">
          <div class="relative mb-3 max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-grey text-sm">🔍</span>
            <input
              v-model="search"
              type="text"
              placeholder="Cari pengguna…"
              class="w-full border border-line rounded-full pl-8 pr-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/15 focus:border-primary"
            />
          </div>

          <div v-if="loading" class="loading-text">Memuat data...</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-grey text-left border-b" style="border-color:#eef1f4">
                <th class="py-2.5 px-2 font-medium text-xs uppercase tracking-wide">Nama</th>
                <th class="py-2.5 px-2 font-medium text-xs uppercase tracking-wide">Role</th>
                <th class="py-2.5 px-2 font-medium text-xs uppercase tracking-wide">Status</th>
                <th class="py-2.5 px-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="u in filtered"
                :key="u.id"
                class="border-b last:border-0 transition-colors"
                style="border-color:#eef1f4"
                :class="selected?.id === u.id ? 'bg-primary-soft/40' : 'hover:bg-gray-50'"
              >
                <td class="py-2.5 px-2 font-medium text-ink">{{ u.nama }}</td>
                <td class="py-2.5 px-2 text-grey">{{ u.role }}</td>
                <td class="py-2.5 px-2">
                  <StatusBadge :status="u.status === 'Aktif' ? 'hijau' : 'merah'">
                    {{ u.status }}
                  </StatusBadge>
                </td>
                <td class="py-2.5 px-2 text-right">
                  <button class="text-xs font-semibold text-primary hover:underline" @click="edit(u)">
                    Edit
                  </button>
                </td>
              </tr>
              <tr v-if="!filtered.length">
                <td colspan="4" class="empty-text text-center">Tidak ada pengguna ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="text-xs text-grey mt-4 leading-relaxed">
          Catatan: perubahan hak akses (POST /access/save) tercatat di audit log &amp; berlaku langsung.
        </p>
      </div>

      <!-- Kolom edit hak akses -->
      <div class="flex-1 w-full lg:sticky lg:top-6">
        <!-- Panel tambah pengguna -->
        <div v-if="showAdd" class="card">
          <div class="text-xs text-grey font-semibold mb-3 uppercase tracking-wide">
            Tambah Pengguna
          </div>
          <label class="text-xs font-semibold text-ink">Nama Lengkap</label>
          <input v-model="addForm.nama" class="input-field mt-1.5 mb-3" placeholder="Nama pengguna" />

          <label class="text-xs font-semibold text-ink">Email</label>
          <input v-model="addForm.email" type="email" class="input-field mt-1.5 mb-3" placeholder="nama@perusahaan.co.id" />

          <label class="text-xs font-semibold text-ink">Role / Jabatan</label>
          <select v-model="addForm.id_role" class="input-field mt-1.5 mb-1">
            <option value="" disabled>— Pilih role —</option>
            <option v-for="r in roles" :key="r.id_role" :value="r.id_role">
              {{ r.nama_role }} (akses {{ r.level_akses }})
            </option>
          </select>
          <p class="text-[11px] text-grey mb-3">Hak akses awal mengikuti level default role terpilih.</p>

          <label class="text-xs font-semibold text-ink">Password Awal</label>
          <input v-model="addForm.password" class="input-field mt-1.5" placeholder="default: password" />

          <div class="mt-4 flex gap-2">
            <button class="btn-primary flex-1" :disabled="addSaving" @click="tambahPengguna">
              {{ addSaving ? 'Menyimpan...' : 'Simpan Pengguna' }}
            </button>
            <button class="btn-outline" @click="showAdd = false">Batal</button>
          </div>
          <div v-if="addMsg" class="text-xs mt-3 font-medium text-status-hijau">✅ {{ addMsg }}</div>
          <div v-if="addErr" class="text-xs mt-3 font-medium text-status-merah">⚠️ {{ addErr }}</div>
        </div>

        <!-- Panel matriks hak akses -->
        <div v-else-if="selected" class="card">
          <div class="text-xs text-grey font-semibold mb-3 uppercase tracking-wide">
            Edit Hak Akses — {{ selected.nama }}
          </div>

          <label class="text-xs font-semibold text-ink block mb-1.5">Role / Jabatan</label>
          <div class="border border-line rounded-btn px-3 py-2.5 text-sm bg-bg-soft text-ink">
            {{ selected.role || '—' }}
          </div>

          <label class="text-xs font-semibold text-ink block mt-4 mb-1.5">Akses per Modul</label>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-grey border-b" style="border-color:#eef1f4">
                <th class="py-2 px-1 text-left font-medium text-xs uppercase">Modul</th>
                <th class="py-2 px-1 text-center font-medium text-xs uppercase">Lihat</th>
                <th class="py-2 px-1 text-center font-medium text-xs uppercase">Ubah</th>
                <th class="py-2 px-1 text-center font-medium text-xs uppercase">Setujui</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in matrix" :key="row.modul" class="border-b last:border-0" style="border-color:#eef1f4">
                <td class="py-2.5 px-1 text-left text-ink">{{ row.modul }}</td>
                <td v-for="col in (['lihat','ubah','setujui'] as const)" :key="col" class="py-2.5 px-1 text-center">
                  <button
                    type="button"
                    class="w-4 h-4 rounded border inline-flex items-center justify-center text-[11px] leading-none transition-colors"
                    :class="row[col]
                      ? 'bg-primary border-primary text-white'
                      : 'bg-white border-line text-transparent hover:border-grey'"
                    @click="toggle(row, col)"
                  >
                    ✓
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <button class="btn-primary w-full mt-4" :disabled="saving" @click="save">
            {{ saving ? 'Menyimpan...' : 'Simpan (berlaku langsung)' }}
          </button>

          <div
            v-if="msg"
            class="text-xs mt-3 font-medium"
            :class="msgType === 'ok' ? 'text-status-hijau' : 'text-status-merah'"
          >
            {{ msgType === 'ok' ? '✅' : '⚠️' }} {{ msg }}
          </div>
          <div class="text-xs text-grey mt-2 leading-relaxed">
            🛡️ Perubahan berlaku langsung &amp; tercatat di audit · pengguna diberi notifikasi.
          </div>
        </div>

        <!-- Placeholder -->
        <div v-else class="card text-center text-grey text-sm py-10">
          Pilih pengguna dan klik <span class="text-primary font-semibold">Edit</span> untuk mengatur hak akses.
        </div>
      </div>
    </div>
  </div>
</template>
