<template>
  <div
    v-if="agent"
    class="fixed inset-y-0 right-0 z-40 w-full max-w-xl bg-slate-900/95 backdrop-blur-2xl border-l border-slate-700/70 shadow-2xl flex flex-col transform transition-all duration-300"
  >
    <!-- Header -->
    <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
      <div class="flex items-center gap-3.5">
        <div
          class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-white/10"
          :style="{ backgroundColor: agent.color + '25', borderColor: agent.color + '60' }"
        >
          {{ agent.emoji }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base font-bold text-white tracking-tight">{{ agent.name }}</h2>
            <span
              class="px-2 py-0.5 text-[10px] font-semibold uppercase rounded-full text-white"
              :style="{ backgroundColor: agent.color }"
            >
              {{ agent.division }}
            </span>
          </div>
          <div class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
            <span>Meja: <strong class="text-slate-200">{{ agent.deskCode }}</strong></span>
            <span>•</span>
            <span class="flex items-center gap-1 text-emerald-400 font-mono text-[11px]">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              9Router: {{ agent.model }}
            </span>
          </div>
        </div>
      </div>

      <button
        @click="$emit('close')"
        class="w-8 h-8 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all"
      >
        ✕
      </button>
    </div>

    <!-- Mode Tabs -->
    <div class="flex border-b border-slate-800 px-5 bg-slate-950/30">
      <button
        @click="activeTab = 'chat'"
        :class="[
          'py-3 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2',
          activeTab === 'chat'
            ? 'border-sky-500 text-sky-400'
            : 'border-transparent text-slate-400 hover:text-slate-200'
        ]"
      >
        💬 Obrolan Langsung
      </button>
      <button
        @click="activeTab = 'task'"
        :class="[
          'py-3 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2',
          activeTab === 'task'
            ? 'border-sky-500 text-sky-400'
            : 'border-transparent text-slate-400 hover:text-slate-200'
        ]"
      >
        📋 Pendelegasian Tugas (Task)
      </button>
      <button
        @click="activeTab = 'profile'"
        :class="[
          'py-3 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2',
          activeTab === 'profile'
            ? 'border-sky-500 text-sky-400'
            : 'border-transparent text-slate-400 hover:text-slate-200'
        ]"
      >
        🔍 SOP & Persona
      </button>
    </div>

    <!-- Tab 1: Chat Messages -->
    <div v-if="activeTab === 'chat'" class="flex-1 overflow-y-auto p-5 space-y-4">
      <div
        v-for="(msg, index) in messages"
        :key="index"
        :class="[
          'flex flex-col max-w-[88%] rounded-2xl p-4 text-xs leading-relaxed shadow-md',
          msg.sender === 'user'
            ? 'ml-auto bg-blue-600 text-white rounded-br-sm'
            : 'mr-auto bg-slate-800/90 border border-slate-700 text-slate-200 rounded-bl-sm'
        ]"
      >
        <div class="flex items-center justify-between text-[10px] opacity-75 mb-1.5 font-medium">
          <span>{{ msg.sender === 'user' ? 'Anda' : agent.name }}</span>
          <span>{{ msg.time }}</span>
        </div>
        <div class="whitespace-pre-wrap font-sans">{{ msg.text }}</div>
      </div>

      <div v-if="isThinking" class="mr-auto bg-slate-800/80 border border-slate-700/80 text-sky-300 rounded-2xl rounded-bl-sm p-3.5 text-xs flex items-center gap-2.5">
        <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
        <span class="italic font-mono text-[11px]">{{ agent.name }} sedang berpikir via 9Router...</span>
      </div>
    </div>

    <!-- Tab 2: Task Delegation -->
    <div v-else-if="activeTab === 'task'" class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
      <div class="bg-blue-950/30 border border-blue-500/30 rounded-2xl p-4 text-blue-200">
        <h4 class="font-bold text-white mb-1 flex items-center gap-2">
          <span>🎯 Format Deliverables Terstandar</span>
        </h4>
        <p class="text-[11px] text-slate-300 leading-relaxed">
          Tugas yang Anda delegasikan ke <strong>{{ agent.name }}</strong> akan dikerjakan sesuai SOP repositori <em>agency-agents</em> dan menghasilkan artefak nyata (kode, skema, atau laporan).
        </p>
      </div>

      <div class="space-y-2">
        <label class="font-semibold text-slate-300">Judul Tugas / Fitur:</label>
        <input
          v-model="taskTitle"
          type="text"
          placeholder="Misal: Buat Filter Autentikasi JWT RS256 di CI4"
          class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-sky-500"
        />
      </div>

      <div class="space-y-2">
        <label class="font-semibold text-slate-300">Deskripsi Brief & Batasan (Scope):</label>
        <textarea
          v-model="taskBrief"
          rows="5"
          placeholder="Jelaskan kebutuhan fungsional, batasan teknologi, dan output yang diharapkan..."
          class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-sky-500 resize-none font-mono"
        ></textarea>
      </div>

      <button
        @click="submitTask"
        :disabled="isThinking || !taskTitle.trim()"
        class="w-full py-3 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg shadow-sky-500/20 transition-all flex items-center justify-center gap-2"
      >
        <span>🚀 Delegasikan Tugas ke Meja {{ agent.deskCode }}</span>
      </button>
    </div>

    <!-- Tab 3: SOP & Persona -->
    <div v-else class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
      <div class="space-y-1.5">
        <h4 class="font-bold text-sky-400 uppercase text-[10px] tracking-wider">Filosofi & Vibe</h4>
        <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl italic text-slate-300">
          "{{ agent.slug.replace(/-/g, ' ') }}" — Spesialisasi domain tingkat tinggi di lingkungan Kantor 3D MCE-eOffice.
        </div>
      </div>

      <div class="space-y-1.5">
        <h4 class="font-bold text-sky-400 uppercase text-[10px] tracking-wider">Pola Komunikasi & Guardrails</h4>
        <ul class="list-disc list-inside space-y-1 text-slate-300 bg-slate-950 p-3.5 border border-slate-800 rounded-xl">
          <li>Berbicara langsung ke pokok permasalahan teknis.</li>
          <li>Menyajikan kode produksi utuh (zero pseudo-code).</li>
          <li>Mematuhi batas peran divisi dan protokol 9Router.</li>
        </ul>
      </div>
    </div>

    <!-- Chat Input Area (Active on Chat Tab) -->
    <div v-if="activeTab === 'chat'" class="p-4 border-t border-slate-800 bg-slate-950/80">
      <form @submit.prevent="sendMessage" class="flex gap-2.5 items-center">
        <input
          v-model="inputMessage"
          type="text"
          :placeholder="'Tanyakan sesuatu pada ' + agent.name + '...'"
          :disabled="isThinking"
          class="flex-1 bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-sky-500 transition-all"
        />
        <button
          type="submit"
          :disabled="isThinking || !inputMessage.trim()"
          class="px-5 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-40 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-500/20"
        >
          Kirim
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  agent: {
    type: Object,
    default: null,
  },
})

defineEmits(['close'])

const activeTab = ref('chat')
const inputMessage = ref('')
const isThinking = ref(false)
const taskTitle = ref('')
const taskBrief = ref('')

const messages = ref([])

watch(
  () => props.agent,
  (newAgent) => {
    if (newAgent) {
      messages.value = [
        {
          sender: 'agent',
          text: `Halo! Saya ${newAgent.name} (${newAgent.division}). Saya bertugas di meja ${newAgent.deskCode}. Ada instruksi atau masalah teknis yang bisa saya selesaikan untuk Anda hari ini?`,
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        },
      ]
    }
  },
  { immediate: true }
)

function sendMessage() {
  const text = inputMessage.value.trim()
  if (!text) return

  messages.value.push({
    sender: 'user',
    text,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
  })

  inputMessage.value = ''
  isThinking.value = true

  // Simulate Smart Response through 9Router gateway
  setTimeout(() => {
    isThinking.value = false
    messages.value.push({
      sender: 'agent',
      text: `[9Router: ${props.agent.model}]\n\nPesan Anda: "${text}" telah diterima. Berdasarkan persona ${props.agent.name}, saya akan menyusun solusi teknis terstruktur sesuai standar CodeIgniter 4 dan Three.js.`,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    })
  }, 1200)
}

function submitTask() {
  if (!taskTitle.value.trim()) return
  activeTab.value = 'chat'
  messages.value.push({
    sender: 'user',
    text: `📋 [DELEGASI TUGAS]\nJudul: ${taskTitle.value}\nBrief: ${taskBrief.value || 'Sesuai standar arsitektur.'}`,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
  })

  isThinking.value = true
  setTimeout(() => {
    isThinking.value = false
    messages.value.push({
      sender: 'agent',
      text: `✅ Tugas "${taskTitle.value}" telah dimasukkan ke dalam antrean eksekusi meja ${props.agent.deskCode}. Deliverables akan disajikan begitu selesai diproses via model ${props.agent.model}.`,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    })
    taskTitle.value = ''
    taskBrief.value = ''
  }, 1500)
}
</script>
