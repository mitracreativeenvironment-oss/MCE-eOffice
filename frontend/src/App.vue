<template>
  <div class="relative w-screen h-screen overflow-hidden bg-slate-950 font-sans">
    <!-- Top Bar Navigation -->
    <OfficeTopBar />

    <!-- 3D Office Interactive Scene -->
    <main class="w-full h-full pt-16">
      <OfficeScene @select-agent="onAgentSelected" />
    </main>

    <!-- Slide-in Drawer for Selected Agent -->
    <AgentChatDrawer
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

const selectedAgent = ref(null)

function onAgentSelected(agent) {
  selectedAgent.value = agent
}
</script>

