<template>
  <div class="relative w-screen h-screen overflow-hidden bg-slate-950 font-sans">
    <!-- Top Bar Navigation -->
    <OfficeTopBar
      :current-view="currentView"
      @toggle-view="toggleView"
    />

    <!-- Main View: 3D Office Campus Scene OR Admin Management Dashboard -->
    <main class="w-full h-full pt-16">
      <!-- 3D Spatial Canvas (v-show keeps Three.js WebGL context alive) -->
      <OfficeScene
        v-show="currentView === 'office'"
        @select-agent="onAgentSelected"
      />

      <!-- Admin Management Portal -->
      <AdminDashboard
        v-if="currentView === 'admin'"
        @switch-to-office="currentView = 'office'"
      />
    </main>

    <!-- Slide-in Drawer for Selected Agent (in 3D Spatial view) -->
    <AgentChatDrawer
      v-if="currentView === 'office'"
      :agent="selectedAgent"
      @close="selectedAgent = null"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import OfficeTopBar from './components/OfficeTopBar.vue'
import OfficeScene from './components/OfficeScene.vue'
import AgentChatDrawer from './components/AgentChatDrawer.vue'
import AdminDashboard from './components/AdminDashboard.vue'

const currentView = ref('office') // 'office' | 'admin'
const selectedAgent = ref(null)

function toggleView() {
  currentView.value = currentView.value === 'office' ? 'admin' : 'office'
}

function onAgentSelected(agent) {
  selectedAgent.value = agent
}
</script>
