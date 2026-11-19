<template>
  <div
    v-if="agent"
    class="fixed inset-y-0 right-0 z-40 w-full max-w-xl bg-slate-900/95 backdrop-blur-2xl border-l border-slate-700/70 shadow-2xl flex flex-col transform transition-all duration-300"
  >
    <!-- Header -->
    <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
      <div class="flex items-center gap-3.5">
        <div
          class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-white/10"
          :style="{ backgroundColor: (agent.agent_color || '#3B82F6') + '25', borderColor: (agent.agent_color || '#3B82F6') + '60' }"
        >
          {{ agent.agent_emoji || '🤖' }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base font-bold text-white tracking-tight">{{ agent.agent_name }}</h2>
            <span
              class="px-2 py-0.5 text-[10px] font-semibold uppercase rounded-full text-white"
              :style="{ backgroundColor: agent.agent_color || '#3B82F6' }"
            >
              {{ agent.division_label }}
            </span>
          </div>
          <div class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
            <span>Meja: <strong class="text-slate-200">{{ agent.desk_code }}</strong> (Lt {{ agent.floor_level }})</span>
            <span>•</span>
            <span class="flex items-center gap-1 text-emerald-400 font-mono text-[11px]">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              Tier: {{ agent.preferred_model_tier || 'coding' }}
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
    <div class="flex border-b border-slate-800 px-5 bg-slate-950/40">
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
      <div v-if="isLoadingMessages" class="py-8 text-center text-slate-400 text-xs flex flex-col items-center gap-2">
        <span class="w-5 h-5 rounded-full border-2 border-sky-400 border-t-transparent animate-spin"></span>
        <span>Membuka sesi obrolan dengan {{ agent.agent_name }}...</span>
      </div>

      <div
        v-for="(msg, index) in messages"
        :key="index"
        :class="[
          'flex flex-col max-w-[88%] rounded-2xl p-4 text-xs leading-relaxed shadow-md',
          msg.sender_type === 'user'
            ? 'ml-auto bg-blue-600 text-white rounded-br-sm'
            : 'mr-auto bg-slate-800/90 border border-slate-700 text-slate-200 rounded-bl-sm'
        ]"
      >
        <div class="flex items-center justify-between text-[10px] opacity-75 mb-1.5 font-medium">
          <span>{{ msg.sender_type === 'user' ? 'Anda' : agent.agent_name }}</span>
          <span v-if="msg.model_used" class="font-mono text-[9px] text-sky-400 px-1.5 py-0.2 bg-slate-900 rounded">
            {{ msg.model_used }}
          </span>
          <span>{{ formatTime(msg.created_at) }}</span>
        </div>
        <div class="whitespace-pre-wrap font-sans">{{ msg.content }}</div>
      </div>

      <div v-if="isThinking" class="mr-auto bg-slate-800/80 border border-slate-700/80 text-sky-300 rounded-2xl rounded-bl-sm p-3.5 text-xs flex items-center gap-2.5">
        <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
        <span class="italic font-mono text-[11px]">{{ agent.agent_name }} sedang memproses via 9Router Gateway...</span>
      </div>
    </div>

    <!-- Tab 2: Task Delegation -->
    <div v-else-if="activeTab === 'task'" class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
      <div class="bg-blue-950/30 border border-blue-500/30 rounded-2xl p-4 text-blue-200">
        <h4 class="font-bold text-white mb-1 flex items-center gap-2">
          <span>🎯 Pendelegasian Tugas & Deliverables Formal</span>
        </h4>
        <p class="text-[11px] text-slate-300 leading-relaxed">
          Tugas yang Anda delegasikan ke <strong>{{ agent.agent_name }}</strong> akan diproses langsung oleh engine AI di 9Router dan menghasilkan artefak nyata yang disimpan di basis data MySQL.
        </p>
      </div>

      <div class="space-y-2">
        <label class="font-semibold text-slate-300">Judul Tugas / Fitur:</label>
        <input
          v-model="taskTitle"
          type="text"
          placeholder="Misal: Buat Arsitektur Skema Database MySQL untuk Modul Billing"
          class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-sky-500"
        />
      </div>

      <div class="space-y-2">
        <label class="font-semibold text-slate-300">Deskripsi Brief & Batasan (Scope):</label>
        <textarea
          v-model="taskBrief"
          rows="5"
          placeholder="Jelaskan kebutuhan fungsional, spesifikasi teknis, dan output deliverable yang diharapkan..."
          class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-sky-500 resize-none font-mono"
        ></textarea>
      </div>

      <button
        @click="submitTask"
        :disabled="isThinking || !taskTitle.trim() || !taskBrief.trim()"
        class="w-full py-3 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg shadow-sky-500/20 transition-all flex items-center justify-center gap-2"
      >
        <span>🚀 Delegasikan Tugas ke Meja {{ agent.desk_code }}</span>
      </button>

      <!-- Task Result Preview -->
      <div v-if="taskResult" class="mt-4 p-4 bg-slate-950 border border-emerald-500/40 rounded-2xl space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-emerald-400 font-bold uppercase text-[10px]">Deliverable Siap:</span>
          <span class="text-[10px] text-slate-400">Status: {{ taskResult.status }}</span>
        </div>
        <div class="text-slate-200 font-mono text-[11px] whitespace-pre-wrap max-h-60 overflow-y-auto p-3 bg-slate-900 rounded-xl border border-slate-800">
          {{ taskResult.final_deliverable }}
        </div>
      </div>
    </div>

    <!-- Tab 3: SOP & Persona -->
    <div v-else class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
      <div v-if="isLoadingProfile" class="py-8 text-center text-slate-400 flex flex-col items-center gap-2">
        <span class="w-5 h-5 rounded-full border-2 border-sky-400 border-t-transparent animate-spin"></span>
        <span>Memuat profil lengkap & SOP...</span>
      </div>

      <div v-else class="space-y-4">
        <div class="space-y-1.5">
          <h4 class="font-bold text-sky-400 uppercase text-[10px] tracking-wider">Filosofi & Vibe</h4>
          <div class="p-3.5 bg-slate-950 border border-slate-800 rounded-xl italic text-slate-300">
            "{{ agentProfile?.vibe || agent.agent_vibe }}"
          </div>
        </div>

        <div class="space-y-1.5">
          <h4 class="font-bold text-sky-400 uppercase text-[10px] tracking-wider">Deskripsi Peran</h4>
          <div class="p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300">
            {{ agentProfile?.description || agent.agent_description }}
          </div>
        </div>

        <div class="space-y-1.5">
          <h4 class="font-bold text-sky-400 uppercase text-[10px] tracking-wider">Instruksi Sistem (System Prompt)</h4>
          <pre class="p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 font-mono text-[11px] whitespace-pre-wrap max-h-72 overflow-y-auto">{{ agentProfile?.system_prompt }}</pre>
        </div>
      </div>
    </div>

    <!-- Chat Input Area (Active on Chat Tab) -->
    <div v-if="activeTab === 'chat'" class="p-4 border-t border-slate-800 bg-slate-950/80">
      <form @submit.prevent="sendMessage" class="flex gap-2.5 items-center">
        <input
          v-model="inputMessage"
          type="text"
          :placeholder="'Tanyakan sesuatu pada ' + agent.agent_name + '...'"
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

const API_BASE = 'http://127.0.0.1:8080/api/v1'

const activeTab = ref('chat')
const inputMessage = ref('')
const isThinking = ref(false)
const isLoadingMessages = ref(false)
const isLoadingProfile = ref(false)

const currentConversation = ref(null)
const messages = ref([])
const agentProfile = ref(null)

const taskTitle = ref('')
const taskBrief = ref('')
const taskResult = ref(null)

function formatTime(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// When agent is selected, initiate chat session with character
watch(
  () => props.agent,
  async (newAgent) => {
    if (newAgent) {
      activeTab.value = 'chat'
      taskResult.value = null
      taskTitle.value = ''
      taskBrief.value = ''
      await initChatSession(newAgent)
      loadAgentProfile(newAgent.agent_id)
    }
  },
  { immediate: true }
)

async function initChatSession(char) {
  isLoadingMessages.value = true
  try {
    const res = await fetch(`${API_BASE}/characters/${char.character_id}/interact`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    })
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        currentConversation.value = json.data.conversation
        messages.value = json.data.messages || []
      }
    }
  } catch (err) {
    console.error('Failed to init chat session:', err)
  } finally {
    isLoadingMessages.value = false
  }
}

async function loadAgentProfile(agentId) {
  isLoadingProfile.value = true
  try {
    const res = await fetch(`${API_BASE}/agents/${agentId}`)
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        agentProfile.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load agent profile:', err)
  } finally {
    isLoadingProfile.value = false
  }
}

async function sendMessage() {
  const text = inputMessage.value.trim()
  if (!text || !currentConversation.value) return

  const userTime = new Date().toISOString()
  messages.value.push({
    sender_type: 'user',
    content: text,
    created_at: userTime,
  })

  inputMessage.value = ''
  isThinking.value = true

  try {
    const res = await fetch(`${API_BASE}/conversations/${currentConversation.value.id}/send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text }),
    })

    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        messages.value.push({
          sender_type: 'agent',
          content: json.data.reply,
          model_used: json.data.model,
          created_at: json.data.created_at || new Date().toISOString(),
        })
      }
    } else {
      messages.value.push({
        sender_type: 'agent',
        content: `Maaf, terjadi kesalahan saat menghubungi server API (HTTP ${res.status}).`,
        created_at: new Date().toISOString(),
      })
    }
  } catch (err) {
    messages.value.push({
      sender_type: 'agent',
      content: `Koneksi ke backend CodeIgniter 4 gagal: ${err.message}`,
      created_at: new Date().toISOString(),
    })
  } finally {
    isThinking.value = false
  }
}

async function submitTask() {
  if (!taskTitle.value.trim() || !taskBrief.value.trim()) return

  isThinking.value = true
  try {
    const res = await fetch(`${API_BASE}/tasks`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        lead_agent_id: props.agent.agent_id,
        title: taskTitle.value,
        brief_input: taskBrief.value,
      }),
    })

    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        taskResult.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to submit task:', err)
  } finally {
    isThinking.value = false
  }
}
</script>
