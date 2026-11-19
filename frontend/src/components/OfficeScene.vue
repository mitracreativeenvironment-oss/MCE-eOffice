<template>
  <div class="relative w-full h-full overflow-hidden select-none bg-slate-950">
    <!-- Three.js Canvas Container -->
    <div ref="canvasContainer" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

    <!-- Loading Overlay -->
    <div
      v-if="isLoading"
      class="absolute inset-0 z-20 bg-slate-950/90 backdrop-blur-md flex flex-col items-center justify-center gap-4 text-white"
    >
      <div class="w-12 h-12 rounded-full border-4 border-sky-500/30 border-t-sky-400 animate-spin"></div>
      <div class="text-center">
        <h3 class="font-bold text-base tracking-wide">Menata Kompleks Perkantoran 3D MCE-eOffice...</h3>
        <p class="text-xs text-slate-400 mt-1">
          Menyesuaikan Ukuran 18 Gedung Divisi Sesuai Jumlah Anggota, Laut Pantai, & Jalan Raya
        </p>
      </div>
    </div>

    <!-- Top Left: Search & Agent Quick Jump -->
    <div class="absolute top-4 left-6 z-10 w-80">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari agen atau gedung divisi..."
          class="w-full bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-sky-500 shadow-xl transition-all"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>

      <!-- Search Suggestions Dropdown -->
      <div
        v-if="filteredAgents.length > 0 && searchQuery.trim()"
        class="mt-2 max-h-64 overflow-y-auto bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl shadow-2xl p-1.5 space-y-1"
      >
        <button
          v-for="char in filteredAgents.slice(0, 10)"
          :key="char.character_id"
          @click="selectAgentFromSearch(char)"
          class="w-full px-3 py-2 text-left rounded-xl hover:bg-slate-800/80 flex items-center justify-between transition-all text-xs"
        >
          <div class="flex items-center gap-2.5">
            <span class="text-base">{{ char.agent_emoji }}</span>
            <div>
              <div class="font-semibold text-white">{{ char.agent_name }}</div>
              <div class="text-[10px] text-slate-400">{{ char.division_label }} • Meja {{ char.desk_code }}</div>
            </div>
          </div>
          <span
            class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full text-white"
            :style="{ backgroundColor: char.agent_color || '#3B82F6' }"
          >
            {{ char.division_id }}
          </span>
        </button>
      </div>
    </div>

    <!-- Bottom Left: Campus Navigation & Building Jump -->
    <div class="absolute bottom-6 left-6 z-10 flex flex-col gap-3">
      <!-- Zone & View Selectors -->
      <div class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 rounded-2xl p-2.5 shadow-2xl flex items-center gap-2">
        <span class="text-xs font-semibold uppercase text-slate-400 px-2">Zona:</span>
        <button
          v-for="zone in campusZones"
          :key="zone.id"
          @click="changeZone(zone)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all duration-300 flex items-center gap-1.5',
            currentZone === zone.id
              ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold'
              : 'text-slate-300 hover:bg-slate-800 hover:text-white'
          ]"
        >
          <span>{{ zone.emoji }} {{ zone.name }}</span>
        </button>
      </div>

      <!-- Quick Building Jump & Global Reset -->
      <div class="flex items-center gap-2 flex-wrap max-w-2xl">
        <button
          @click="resetCamera"
          class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 hover:border-slate-500 text-slate-200 px-3.5 py-2 rounded-xl text-xs font-medium shadow-xl hover:bg-slate-800 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          Tinjauan Udara Kompleks
        </button>

        <!-- Divisional Building Quick Select Dropdown -->
        <select
          @change="onBuildingSelectChange"
          class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 text-slate-200 px-3 py-2 rounded-xl text-xs font-medium shadow-xl focus:outline-none focus:border-sky-500 cursor-pointer"
        >
          <option value="">🏛️ Lompat ke Gedung Kantor Divisi...</option>
          <option v-for="b in divisionalBuildings" :key="b.id" :value="b.id">
            {{ b.emoji }} {{ b.name }} ({{ getDivisionAgentCount(b.id) }} Agen)
          </option>
        </select>
      </div>
    </div>

    <!-- Agent Tooltip on Hover -->
    <div
      v-if="hoveredCharacter"
      class="fixed z-30 pointer-events-none transform -translate-x-1/2 -translate-y-full mb-4 px-3 py-2 rounded-xl bg-slate-900/95 backdrop-blur-md border border-slate-700 shadow-2xl flex items-center gap-2.5"
      :style="{ left: tooltipPos.x + 'px', top: tooltipPos.y + 'px' }"
    >
      <span class="text-xl">{{ hoveredCharacter.agent_emoji }}</span>
      <div>
        <div class="text-xs font-bold text-white flex items-center gap-2">
          {{ hoveredCharacter.agent_name }}
          <span
            class="px-1.5 py-0.2 text-[9px] uppercase font-bold rounded text-white"
            :style="{ backgroundColor: hoveredCharacter.agent_color || '#3B82F6' }"
          >
            {{ hoveredCharacter.division_label }}
          </span>
        </div>
        <div class="text-[10px] text-slate-400 font-mono">
          Meja {{ hoveredCharacter.desk_code }} • Klik untuk berinteraksi
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import * as THREE from 'three'
import gsap from 'gsap'

const emit = defineEmits(['select-agent'])

const canvasContainer = ref(null)
const isLoading = ref(true)
const currentZone = ref('all')
const hoveredCharacter = ref(null)
const tooltipPos = ref({ x: 0, y: 0 })
const searchQuery = ref('')

const charactersData = ref([])

// 4 Campus Zones
const campusZones = [
  { id: 'all', emoji: '🌐', name: 'Semua Area', cam: { x: 0, y: 85, z: 105, tx: 0, ty: 0, tz: 5 } },
  { id: 'beach', emoji: '🏖️', name: 'Tepi Pantai (Belakang)', cam: { x: 0, y: 40, z: -10, tx: 0, ty: 0, tz: -35 } },
  { id: 'creative', emoji: '🎨', name: 'Plaza Kreatif & Bisnis', cam: { x: 0, y: 42, z: 22, tx: 0, ty: 0, tz: -10 } },
  { id: 'tech', emoji: '💻', name: 'Quad Tech & Pertahanan', cam: { x: 0, y: 44, z: 45, tx: 0, ty: 0, tz: 10 } },
  { id: 'avenue', emoji: '🚗', name: 'Jalan Raya & Boulevard (Depan)', cam: { x: 0, y: 35, z: 85, tx: 0, ty: 0, tz: 45 } },
]

// 18 Divisional Standalone Buildings Configuration with calibrated spatial coordinates
const divisionalBuildings = [
  // Zone 1: Front Avenue (Near Highway, Z = +28)
  { id: 'marketing', name: 'Marketing', emoji: '📢', color: 0xf97316, x: -34, z: 28, style: 'billboard', zone: 'Front Avenue' },
  { id: 'support', name: 'Support', emoji: '🛎️', color: 0x84cc16, x: -11, z: 28, style: 'welcome', zone: 'Front Avenue' },
  { id: 'sales', name: 'Sales', emoji: '📈', color: 0x10b981, x: 11, z: 28, style: 'lounge', zone: 'Front Avenue' },
  { id: 'paid-media', name: 'Paid Media', emoji: '🎯', color: 0xeab308, x: 34, z: 28, style: 'ticker', zone: 'Front Avenue' },

  // Zone 2: Tech & Defense Quad (Z = +10)
  { id: 'engineering', name: 'Engineering', emoji: '💻', color: 0x3b82f6, x: -38, z: 10, style: 'cyber_tower', zone: 'Tech Quad' },
  { id: 'security', name: 'Security', emoji: '🛡️', color: 0xef4444, x: -11, z: 10, style: 'hex_fortress', zone: 'Tech Quad' },
  { id: 'testing', name: 'Testing', emoji: '🧪', color: 0xf59e0b, x: 11, z: 10, style: 'stress_lab', zone: 'Tech Quad' },
  { id: 'gis', name: 'GIS', emoji: '🗺️', color: 0x14b8a6, x: 34, z: 10, style: 'observatory', zone: 'Tech Quad' },

  // Zone 3: Creative & Business Plaza (Z = -10)
  { id: 'product', name: 'Product', emoji: '📦', color: 0xd946ef, x: -34, z: -10, style: 'modular_cube', zone: 'Creative Plaza' },
  { id: 'design', name: 'Design', emoji: '🎨', color: 0xec4899, x: -11, z: -10, style: 'curved_glass', zone: 'Creative Plaza' },
  { id: 'game-development', name: 'Game Dev', emoji: '🎮', color: 0xa855f7, x: 11, z: -10, style: 'arcade', zone: 'Creative Plaza' },
  { id: 'finance', name: 'Finance', emoji: '💵', color: 0x22c55e, x: 34, z: -10, style: 'emerald_tower', zone: 'Creative Plaza' },

  // Zone 4: Beachfront Promenade (Oceanfront View, Z = -31)
  { id: 'academic', name: 'Academic', emoji: '🎓', color: 0x8b5cf6, x: -40, z: -31, style: 'rotunda', zone: 'Tepi Pantai' },
  { id: 'research', name: 'Research', emoji: '🔬', color: 0x7c3aed, x: -24, z: -31, style: 'quantum_lab', zone: 'Tepi Pantai' },
  { id: 'spatial-computing', name: 'Spatial XR', emoji: '🥽', color: 0x06b6d4, x: -8, z: -31, style: 'xr_hologram', zone: 'Tepi Pantai' },
  { id: 'specialized', name: 'Specialized', emoji: '✨', color: 0x6366f1, x: 8, z: -31, style: 'spire', zone: 'Tepi Pantai' },
  { id: 'healthcare', name: 'Healthcare', emoji: '🩺', color: 0x0d9488, x: 26, z: -31, style: 'biophilic', zone: 'Tepi Pantai' },
  { id: 'project-management', name: 'Project Mgmt', emoji: '📋', color: 0x0ea5e9, x: 42, z: -31, style: 'grid_tower', zone: 'Tepi Pantai' },
]

function getDivisionAgentCount(divId) {
  return charactersData.value.filter((c) => c.division_id === divId).length || 6
}

/**
 * Calculates dynamic spatial metrics and scaling factors
 * proportional to the number of agents in the division (N: 5 to 66)
 */
function getDivisionMetrics(agentCount) {
  const minCount = 5
  const maxCount = 66
  const count = Math.max(minCount, agentCount)
  const t = Math.min(1.0, Math.max(0.0, (count - minCount) / (maxCount - minCount)))

  // Footprint dimensions: terrace width & depth
  // Small teams (5-6): width ~ 13m, depth ~ 12m
  // Medium teams (7-13): width ~ 16-18m, depth ~ 15-17m
  // Large teams (21): width ~ 23m, depth ~ 20m
  // Mega teams (59-66): width ~ 31-34m, depth ~ 28-30m
  const terraceWidth = 13.0 + Math.pow(t, 0.7) * 21.0
  const terraceDepth = 12.0 + Math.pow(t, 0.7) * 18.0

  // Architecture horizontal scale (scaleXZ) and vertical height scale (scaleY)
  // Height scales up to 2.8x (Engineering & Specialized reach ~32-36m tall landmarks)
  const scaleXZ = 0.82 + Math.pow(t, 0.7) * 1.25
  const scaleY = 0.8 + Math.pow(t, 0.65) * 1.95

  let cols
  if (count <= 6) {
    cols = 3
  } else if (count <= 12) {
    cols = 4
  } else if (count <= 24) {
    cols = 6
  } else if (count <= 45) {
    cols = 7
  } else {
    cols = 9 // 59 and 66 agents form a rich 9-column tech floor
  }

  const rows = Math.ceil(count / cols)

  return {
    count,
    scaleXZ,
    scaleY,
    terraceWidth,
    terraceDepth,
    cols,
    rows,
    buildingHeight: 11 * scaleY,
  }
}

const filteredAgents = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return []
  return charactersData.value.filter((c) => {
    return (
      c.agent_name.toLowerCase().includes(q) ||
      c.agent_slug.toLowerCase().includes(q) ||
      c.division_label.toLowerCase().includes(q) ||
      c.desk_code.toLowerCase().includes(q)
    )
  })
})

let scene, camera, renderer, animationFrameId
const agentMeshes = []
const buildingMeshes = []
const movingVehicles = []
const movingPedestrians = []
const palmTreeHeads = []
let oceanMesh = null
const raycaster = new THREE.Raycaster()
const mouse = new THREE.Vector2()

async function loadSceneData() {
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/office/scene')
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const json = await res.json()
    if (json.data && json.data.characters) {
      charactersData.value = json.data.characters
    }
  } catch (err) {
    console.error('Failed to load office scene from API:', err)
  } finally {
    isLoading.value = false
    initThree()
  }
}

function initThree() {
  const container = canvasContainer.value
  if (!container) return

  // 1. Scene & Atmosphere (Coastal Sunny Day with Horizon)
  scene = new THREE.Scene()
  scene.background = new THREE.Color(0x0f1d33)
  scene.fog = new THREE.FogExp2(0x0f1d33, 0.004)

  // 2. Perspective Camera
  const aspect = container.clientWidth / container.clientHeight
  camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 2000)
  camera.position.set(0, 85, 105)
  camera.lookAt(0, 0, 5)

  // 3. WebGL Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.15
  container.appendChild(renderer.domElement)

  // 4. Lighting Rig (Warm Tropical Sun + Ocean Ambient)
  const ambientLight = new THREE.AmbientLight(0xdbeafe, 0.95)
  scene.add(ambientLight)

  const sunLight = new THREE.DirectionalLight(0xffedd5, 1.6)
  sunLight.position.set(60, 95, 45)
  sunLight.castShadow = true
  sunLight.shadow.mapSize.width = 2048
  sunLight.shadow.mapSize.height = 2048
  sunLight.shadow.camera.near = 10
  sunLight.shadow.camera.far = 400
  sunLight.shadow.camera.left = -120
  sunLight.shadow.camera.right = 120
  sunLight.shadow.camera.top = 120
  sunLight.shadow.camera.bottom = -120
  sunLight.shadow.bias = -0.0005
  scene.add(sunLight)

  // Subtle Ocean Blue Fill Light from the rear (Z < 0)
  const oceanFill = new THREE.DirectionalLight(0x38bdf8, 0.7)
  oceanFill.position.set(-40, 50, -80)
  scene.add(oceanFill)

  // 5. Build Environment Layers
  buildOceanAndBeach()
  buildPalmPlantations()
  buildHighwayAndTraffic()

  // 6. Build the 18 Divisional Standalone Buildings (Dynamically Scaled)
  buildDivisionalBuildings()

  // 7. Spawn Agent Workstations & 3D Characters (Proportionally Placed)
  spawnAgentWorkstations()

  // 8. Event Listeners
  container.addEventListener('mousemove', onMouseMove)
  container.addEventListener('click', onClick)
  window.addEventListener('resize', onWindowResize)

  // 9. Start Smooth 60FPS Animation Loop
  animate()
}

// -------------------------------------------------------------
// BELAKANG: Lautan Samudra & Pantai Pasir (Ocean & Beach)
// -------------------------------------------------------------
function buildOceanAndBeach() {
  // Deep Blue Ocean Plane (Z = -60 to -240)
  const oceanGeo = new THREE.PlaneGeometry(380, 180, 64, 48)
  const oceanMat = new THREE.MeshPhysicalMaterial({
    color: 0x0284c7,
    roughness: 0.1,
    metalness: 0.2,
    transmission: 0.6,
    transparent: true,
    opacity: 0.88,
  })
  oceanMesh = new THREE.Mesh(oceanGeo, oceanMat)
  oceanMesh.rotation.x = -Math.PI / 2
  oceanMesh.position.set(0, -0.6, -145)
  scene.add(oceanMesh)

  // Golden Sand Beach Strip (Z = -46 to -60)
  const beachGeo = new THREE.PlaneGeometry(360, 20)
  const beachMat = new THREE.MeshStandardMaterial({
    color: 0xfef08a,
    roughness: 0.95,
    metalness: 0.05,
  })
  const beach = new THREE.Mesh(beachGeo, beachMat)
  beach.rotation.x = -Math.PI / 2
  beach.position.set(0, -0.2, -54)
  beach.receiveShadow = true
  scene.add(beach)

  // Beach Loungers & Parasol Umbrellas along the shore
  for (let x = -70; x <= 70; x += 18) {
    // Parasol
    const pole = new THREE.Mesh(
      new THREE.CylinderGeometry(0.06, 0.06, 2.8, 8),
      new THREE.MeshStandardMaterial({ color: 0xffffff })
    )
    pole.position.set(x, 1.4, -53)
    pole.castShadow = true
    scene.add(pole)

    const canopy = new THREE.Mesh(
      new THREE.ConeGeometry(1.6, 0.7, 12),
      new THREE.MeshStandardMaterial({ color: (x % 36 === 0) ? 0xef4444 : 0x38bdf8 })
    )
    canopy.position.set(x, 2.7, -53)
    canopy.castShadow = true
    scene.add(canopy)

    // Lounger Bed
    const bed = new THREE.Mesh(
      new THREE.BoxGeometry(0.8, 0.15, 1.8),
      new THREE.MeshStandardMaterial({ color: 0xffffff })
    )
    bed.position.set(x + 0.8, 0.1, -52.2)
    bed.castShadow = true
    scene.add(bed)
  }

  // Campus Ground Plaza Base (Z = -46 to +58)
  const plazaGeo = new THREE.PlaneGeometry(360, 104)
  const plazaMat = new THREE.MeshStandardMaterial({
    color: 0x090d16,
    roughness: 0.85,
    metalness: 0.15,
  })
  const plaza = new THREE.Mesh(plazaGeo, plazaMat)
  plaza.rotation.x = -Math.PI / 2
  plaza.position.set(0, 0, 6)
  plaza.receiveShadow = true
  scene.add(plaza)
}

// -------------------------------------------------------------
// KIRI & KANAN: Perkebunan Pohon Kelapa (Palm Tree Plantations)
// -------------------------------------------------------------
function buildPalmPlantations() {
  const createPalmTree = (px, pz) => {
    const palmGroup = new THREE.Group()
    palmGroup.position.set(px, 0, pz)

    const trunkHeight = 7.5 + Math.random() * 2
    const trunkMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.85 })
    const trunkGeo = new THREE.CylinderGeometry(0.2, 0.38, trunkHeight, 10)
    const trunk = new THREE.Mesh(trunkGeo, trunkMat)
    trunk.position.set(0, trunkHeight / 2, 0)
    trunk.rotation.z = (Math.random() - 0.5) * 0.15
    trunk.rotation.x = (Math.random() - 0.5) * 0.15
    trunk.castShadow = true
    palmGroup.add(trunk)

    const crownGroup = new THREE.Group()
    crownGroup.position.set(0, trunkHeight, 0)

    const frondGeo = new THREE.BoxGeometry(0.35, 0.05, 3.6)
    const frondMat = new THREE.MeshStandardMaterial({ color: 0x16a34a, roughness: 0.5 })

    for (let i = 0; i < 8; i++) {
      const angle = (i / 8) * Math.PI * 2
      const frond = new THREE.Mesh(frondGeo, frondMat)
      frond.rotation.y = angle
      frond.rotation.x = 0.45
      frond.position.set(Math.sin(angle) * 1.4, -0.3, Math.cos(angle) * 1.4)
      frond.castShadow = true
      crownGroup.add(frond)
    }

    const coconutGeo = new THREE.SphereGeometry(0.2, 8, 8)
    const coconutMat = new THREE.MeshStandardMaterial({ color: 0x451a03, roughness: 0.9 })
    ;[[-0.3, -0.2, 0.2], [0.3, -0.2, -0.2], [0.1, -0.25, 0.3]].forEach(([cx, cy, cz]) => {
      const coconut = new THREE.Mesh(coconutGeo, coconutMat)
      coconut.position.set(cx, cy, cz)
      crownGroup.add(coconut)
    })

    palmGroup.add(crownGroup)
    palmTreeHeads.push(crownGroup)
    scene.add(palmGroup)
  }

  // Left Plantation (X: -65 to -115, Z: -42 to 45)
  for (let x = -65; x >= -115; x -= 10) {
    for (let z = -42; z <= 45; z += 11) {
      createPalmTree(x + (Math.random() * 4 - 2), z + (Math.random() * 4 - 2))
    }
  }

  // Right Plantation (X: +65 to +115, Z: -42 to 45)
  for (let x = 65; x <= 115; x += 10) {
    for (let z = -42; z <= 45; z += 11) {
      createPalmTree(x + (Math.random() * 4 - 2), z + (Math.random() * 4 - 2))
    }
  }
}

// -------------------------------------------------------------
// DEPAN: Jalan Raya & Lalu Lintas Bergerak (Highway & Traffic)
// -------------------------------------------------------------
function buildHighwayAndTraffic() {
  const roadGeo = new THREE.PlaneGeometry(360, 16)
  const roadMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 })
  const road = new THREE.Mesh(roadGeo, roadMat)
  road.rotation.x = -Math.PI / 2
  road.position.set(0, 0.02, 65)
  road.receiveShadow = true
  scene.add(road)

  const centerLineGeo = new THREE.PlaneGeometry(360, 0.3)
  const yellowMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 })
  const centerLine = new THREE.Mesh(centerLineGeo, yellowMat)
  centerLine.rotation.x = -Math.PI / 2
  centerLine.position.set(0, 0.03, 65)
  scene.add(centerLine)

  const sidewalkGeo = new THREE.PlaneGeometry(360, 7)
  const sidewalkMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.85 })
  const sidewalk = new THREE.Mesh(sidewalkGeo, sidewalkMat)
  sidewalk.rotation.x = -Math.PI / 2
  sidewalk.position.set(0, 0.05, 53.5)
  sidewalk.receiveShadow = true
  scene.add(sidewalk)

  for (let x = -100; x <= 100; x += 25) {
    const poleGeo = new THREE.CylinderGeometry(0.08, 0.12, 6.5, 8)
    const poleMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.8 })
    const pole = new THREE.Mesh(poleGeo, poleMat)
    pole.position.set(x, 3.25, 56.5)
    pole.castShadow = true
    scene.add(pole)

    const lampHead = new THREE.Mesh(
      new THREE.BoxGeometry(0.8, 0.15, 0.3),
      new THREE.MeshBasicMaterial({ color: 0xfef08a })
    )
    lampHead.position.set(x, 6.4, 57.2)
    scene.add(lampHead)
  }

  const vehicleColors = [0xef4444, 0x3b82f6, 0xffffff, 0x10b981, 0xf59e0b, 0x8b5cf6, 0x06b6d4]

  for (let i = 0; i < 6; i++) {
    const v = createVehicleMesh(vehicleColors[i % vehicleColors.length])
    v.position.set(-140 + i * 50, 0.4, 62)
    v.userData = { speed: 0.35 + Math.random() * 0.15, direction: 1, laneZ: 62 }
    scene.add(v)
    movingVehicles.push(v)
  }

  for (let i = 0; i < 6; i++) {
    const v = createVehicleMesh(vehicleColors[(i + 3) % vehicleColors.length])
    v.position.set(140 - i * 50, 0.4, 68)
    v.rotation.y = Math.PI
    v.userData = { speed: 0.35 + Math.random() * 0.15, direction: -1, laneZ: 68 }
    scene.add(v)
    movingVehicles.push(v)
  }

  for (let i = 0; i < 10; i++) {
    const ped = createPedestrianMesh()
    ped.position.set(-80 + i * 18, 0.7, 52.5 + (i % 2) * 1.5)
    ped.userData = { speed: 0.04 + Math.random() * 0.03, direction: i % 2 === 0 ? 1 : -1 }
    scene.add(ped)
    movingPedestrians.push(ped)
  }
}

function createVehicleMesh(colorHex) {
  const vGroup = new THREE.Group()

  const bodyGeo = new THREE.BoxGeometry(3.6, 0.9, 1.8)
  const bodyMat = new THREE.MeshStandardMaterial({ color: colorHex, metalness: 0.6, roughness: 0.2 })
  const body = new THREE.Mesh(bodyGeo, bodyMat)
  body.position.y = 0.5
  body.castShadow = true
  vGroup.add(body)

  const cabinGeo = new THREE.BoxGeometry(2.0, 0.7, 1.6)
  const cabinMat = new THREE.MeshPhysicalMaterial({ color: 0x0f172a, roughness: 0.1, transmission: 0.7 })
  const cabin = new THREE.Mesh(cabinGeo, cabinMat)
  cabin.position.set(-0.2, 1.25, 0)
  vGroup.add(cabin)

  const headlightMat = new THREE.MeshBasicMaterial({ color: 0xffffff })
  ;[-0.6, 0.6].forEach((hz) => {
    const light = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.2, 0.3), headlightMat)
    light.position.set(1.82, 0.5, hz)
    vGroup.add(light)
  })

  const taillightMat = new THREE.MeshBasicMaterial({ color: 0xef4444 })
  ;[-0.6, 0.6].forEach((tz) => {
    const light = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.2, 0.3), taillightMat)
    light.position.set(-1.82, 0.5, tz)
    vGroup.add(light)
  })

  return vGroup
}

function createPedestrianMesh() {
  const pGroup = new THREE.Group()
  const bodyMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.5 })
  const body = new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.25, 0.8, 8), bodyMat)
  body.position.y = 0.4
  body.castShadow = true
  pGroup.add(body)

  const head = new THREE.Mesh(
    new THREE.SphereGeometry(0.18, 12, 12),
    new THREE.MeshStandardMaterial({ color: 0xfde047 })
  )
  head.position.y = 0.95
  pGroup.add(head)

  return pGroup
}

/**
 * Creates a crisp Canvas Texture for the 3D floating signboard
 * showing the Division Title and Member Headcount Badge
 */
function createSignboardTexture(name, count, colorHex) {
  const canvas = document.createElement('canvas')
  canvas.width = 512
  canvas.height = 140
  const ctx = canvas.getContext('2d')

  // Dark cyber background
  ctx.fillStyle = '#020617'
  ctx.fillRect(0, 0, 512, 140)

  // Neon glowing border
  const hexStr = typeof colorHex === 'string' ? colorHex : '#' + colorHex.toString(16).padStart(6, '0')
  ctx.strokeStyle = hexStr
  ctx.lineWidth = 6
  ctx.strokeRect(4, 4, 504, 132)

  // Division Name
  ctx.fillStyle = '#ffffff'
  ctx.font = 'bold 38px "Segoe UI", sans-serif'
  ctx.textAlign = 'center'
  ctx.fillText(name.toUpperCase(), 256, 56)

  // Headcount pill badge
  ctx.fillStyle = hexStr
  ctx.font = 'bold 26px monospace'
  ctx.fillText(`👥 ${count} AGEN AKTIF`, 256, 106)

  const texture = new THREE.CanvasTexture(canvas)
  texture.needsUpdate = true
  return texture
}

// -------------------------------------------------------------
// KOMPLEKS: 18 Gedung Kantor Divisi Mandiri (Ukuran Berbasis Personil)
// -------------------------------------------------------------
function buildDivisionalBuildings() {
  divisionalBuildings.forEach((b) => {
    const agentCount = getDivisionAgentCount(b.id)
    const metrics = getDivisionMetrics(agentCount)

    b.buildingHeight = metrics.buildingHeight

    const bGroup = new THREE.Group()
    bGroup.position.set(b.x, 0, b.z)

    // 1. Proportional Base Terrace Platform (Teras Berdasarkan Jumlah Orang)
    const terraceGeo = new THREE.BoxGeometry(metrics.terraceWidth, 0.4, metrics.terraceDepth)
    const terraceMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.4 })
    const terrace = new THREE.Mesh(terraceGeo, terraceMat)
    terrace.position.y = 0.2
    terrace.receiveShadow = true
    bGroup.add(terrace)

    // 2. Glowing Neon Perimeter
    const neonBorderGeo = new THREE.BoxGeometry(metrics.terraceWidth + 0.2, 0.1, metrics.terraceDepth + 0.2)
    const neonBorderMat = new THREE.MeshBasicMaterial({ color: b.color, wireframe: true })
    const neonBorder = new THREE.Mesh(neonBorderGeo, neonBorderMat)
    neonBorder.position.y = 0.42
    bGroup.add(neonBorder)

    // 3. Custom Architectural Building Scaled Proportional to Team Size
    buildCustomArchitecture(bGroup, b, metrics)

    // 4. Floating 3D Glowing Signboard with Headcount Badge
    const signGroup = new THREE.Group()
    signGroup.position.set(0, metrics.buildingHeight + 3.2, -metrics.terraceDepth * 0.15)

    const signBoardWidth = Math.max(7.5, metrics.scaleXZ * 6.5)
    const signBoardGeo = new THREE.BoxGeometry(signBoardWidth, 1.8, 0.3)
    const signTexture = createSignboardTexture(b.name, agentCount, b.color)
    const signBoardMat = new THREE.MeshBasicMaterial({ map: signTexture })
    const signBoard = new THREE.Mesh(signBoardGeo, signBoardMat)
    signGroup.add(signBoard)

    const signGlowGeo = new THREE.BoxGeometry(signBoardWidth + 0.25, 2.05, 0.1)
    const signGlowMat = new THREE.MeshBasicMaterial({
      color: b.color,
      wireframe: true,
    })
    const signGlow = new THREE.Mesh(signGlowGeo, signGlowMat)
    signGroup.add(signGlow)

    bGroup.add(signGroup)

    // 5. Interactive Click Box for the Building
    const hitBox = new THREE.Mesh(
      new THREE.BoxGeometry(metrics.terraceWidth, metrics.buildingHeight + 4, metrics.terraceDepth),
      new THREE.MeshBasicMaterial({ visible: false })
    )
    hitBox.position.y = (metrics.buildingHeight + 4) / 2
    hitBox.userData = { building: b, group: bGroup }
    bGroup.add(hitBox)

    buildingMeshes.push(hitBox)
    scene.add(bGroup)
  })
}

/**
 * Builds distinct architectural structures scaled by the division's headcount
 */
function buildCustomArchitecture(group, b, metrics) {
  const { scaleXZ, scaleY, terraceDepth } = metrics

  const mainMat = new THREE.MeshStandardMaterial({
    color: 0x1e293b,
    roughness: 0.3,
    metalness: 0.5,
  })

  const glassMat = new THREE.MeshPhysicalMaterial({
    color: b.color,
    transparent: true,
    opacity: 0.45,
    roughness: 0.1,
    transmission: 0.8,
  })

  const accentMat = new THREE.MeshStandardMaterial({
    color: b.color,
    emissive: b.color,
    emissiveIntensity: 0.55,
  })

  const rearZ = -terraceDepth * 0.22

  switch (b.style) {
    case 'cyber_tower': {
      // Engineering (66 Agents): Colossal Flagship Cyber Tower with Dual Server Wings
      const towerH = 14 * scaleY
      const towerW = 9.5 * scaleXZ
      const towerD = 8 * scaleXZ

      // Central Cyber Tower
      const tower = new THREE.Mesh(new THREE.BoxGeometry(towerW, towerH, towerD), mainMat)
      tower.position.set(0, towerH / 2 + 0.4, rearZ)
      tower.castShadow = true
      group.add(tower)

      // Glass Curtain Wall
      const glassFront = new THREE.Mesh(new THREE.BoxGeometry(towerW * 0.88, towerH * 0.85, 0.2), glassMat)
      glassFront.position.set(0, towerH / 2 + 0.4, rearZ + towerD / 2 + 0.1)
      group.add(glassFront)

      // Dual High-Tech Side Wings (for huge 66-person engineering corps)
      ;[-towerW * 0.75, towerW * 0.75].forEach((wx) => {
        const wing = new THREE.Mesh(new THREE.BoxGeometry(towerW * 0.55, towerH * 0.65, towerD * 0.8), mainMat)
        wing.position.set(wx, (towerH * 0.65) / 2 + 0.4, rearZ)
        wing.castShadow = true
        group.add(wing)

        const wingNeon = new THREE.Mesh(new THREE.BoxGeometry(0.3, towerH * 0.65, 0.3), accentMat)
        wingNeon.position.set(wx, (towerH * 0.65) / 2 + 0.4, rearZ + (towerD * 0.8) / 2 + 0.1)
        group.add(wingNeon)
      })

      // Vertical Server Neon Racks
      ;[-towerW * 0.4, towerW * 0.4].forEach((sx) => {
        const strip = new THREE.Mesh(new THREE.BoxGeometry(0.4, towerH * 0.9, 0.4), accentMat)
        strip.position.set(sx, towerH / 2 + 0.4, rearZ + towerD / 2 + 0.15)
        group.add(strip)
      })

      // High-Frequency Communication Mast & Rooftop Dishes
      const mastH = 6 * scaleY
      const mast = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.3, mastH, 8), accentMat)
      mast.position.set(0, towerH + mastH / 2 + 0.4, rearZ)
      group.add(mast)
      break
    }

    case 'spire': {
      // Specialized (59 Agents): Grand Multi-Tier Crystal Innovation Spire with Orbital Rings
      const spireH = 15 * scaleY
      const baseR = 5.5 * scaleXZ

      // Octagonal Base Tower
      const base = new THREE.Mesh(new THREE.CylinderGeometry(baseR * 0.8, baseR, spireH * 0.65, 8), mainMat)
      base.position.set(0, (spireH * 0.65) / 2 + 0.4, rearZ)
      base.castShadow = true
      group.add(base)

      // Upper Crystal Spire
      const crystal = new THREE.Mesh(new THREE.ConeGeometry(baseR * 0.75, spireH * 0.5, 8), glassMat)
      crystal.position.set(0, spireH * 0.65 + (spireH * 0.5) / 2 + 0.4, rearZ)
      group.add(crystal)

      // Floating Orbital Rings
      ;[spireH * 0.5, spireH * 0.85].forEach((ry, idx) => {
        const ring = new THREE.Mesh(new THREE.TorusGeometry(baseR * (idx === 0 ? 1.25 : 0.85), 0.2, 8, 32), accentMat)
        ring.rotation.x = Math.PI / 2 + 0.15 * (idx === 0 ? 1 : -1)
        ring.position.set(0, ry, rearZ)
        group.add(ring)
      })
      break
    }

    case 'arcade': {
      // Game Development (21 Agents): Interactive Game Studio with Neon Laser Edges
      const w = 12 * scaleXZ
      const h = 9.5 * scaleY
      const d = 8 * scaleXZ

      const studio = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mainMat)
      studio.position.set(0, h / 2 + 0.4, rearZ)
      studio.castShadow = true
      group.add(studio)

      const screenGlass = new THREE.Mesh(new THREE.BoxGeometry(w * 0.85, h * 0.75, 0.2), glassMat)
      screenGlass.position.set(0, h / 2 + 0.4, rearZ + d / 2 + 0.1)
      group.add(screenGlass)

      // Neon Laser Roof Bevel
      const neonRoof = new THREE.Mesh(new THREE.BoxGeometry(w * 1.05, 0.6, d * 1.05), accentMat)
      neonRoof.position.set(0, h + 0.4, rearZ)
      group.add(neonRoof)
      break
    }

    case 'observatory': {
      // GIS (13 Agents): Satellite Tracking Observatory & Geospatial Dome
      const baseR = 5 * scaleXZ
      const baseH = 6 * scaleY

      const base = new THREE.Mesh(new THREE.CylinderGeometry(baseR, baseR * 1.05, baseH, 24), mainMat)
      base.position.set(0, baseH / 2 + 0.4, rearZ)
      base.castShadow = true
      group.add(base)

      // Hemispherical Celestial Dome
      const domeR = baseR * 0.85
      const dome = new THREE.Mesh(new THREE.SphereGeometry(domeR, 24, 16, 0, Math.PI * 2, 0, Math.PI / 2), glassMat)
      dome.position.set(0, baseH + 0.4, rearZ)
      group.add(dome)

      // Dual Angled Satellite Dishes
      ;[-baseR * 0.75, baseR * 0.75].forEach((dx, idx) => {
        const dish = new THREE.Mesh(new THREE.ConeGeometry(1.6 * scaleXZ, 0.5, 16), accentMat)
        dish.rotation.x = -Math.PI / 3.2
        dish.rotation.y = idx === 0 ? 0.4 : -0.4
        dish.position.set(dx, baseH + 2.5 * scaleY, rearZ)
        group.add(dish)
      })
      break
    }

    case 'curved_glass': {
      // Design (10 Agents): Avant-Garde Curved Glass Studio
      const r = 5.2 * scaleXZ
      const h = 8.5 * scaleY

      const studio = new THREE.Mesh(new THREE.CylinderGeometry(r, r * 1.1, h, 32, 1, false, 0, Math.PI), glassMat)
      studio.position.set(0, h / 2 + 0.4, rearZ + 1)
      group.add(studio)

      const roof = new THREE.Mesh(new THREE.CylinderGeometry(r * 1.15, r * 1.15, 0.6, 32), mainMat)
      roof.position.set(0, h + 0.4, rearZ + 1)
      group.add(roof)

      const accentArch = new THREE.Mesh(new THREE.TorusGeometry(r * 1.1, 0.25, 8, 32, Math.PI), accentMat)
      accentArch.rotation.x = Math.PI / 2
      accentArch.position.set(0, h + 0.7, rearZ + 1)
      group.add(accentArch)
      break
    }

    case 'rotunda': {
      // Academic (7 Agents): Neoclassical Modern Rotunda Library
      const r = 4.2 * scaleXZ
      const h = 7.5 * scaleY

      const dome = new THREE.Mesh(new THREE.SphereGeometry(r, 24, 16, 0, Math.PI * 2, 0, Math.PI / 2), mainMat)
      dome.position.set(0, h + 0.4, rearZ)
      group.add(dome)

      // Colonnade Pillars
      for (let i = 0; i < 8; i++) {
        const ang = (i / 8) * Math.PI * 2
        const p = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.28, h, 12), accentMat)
        p.position.set(Math.sin(ang) * (r * 0.95), h / 2 + 0.4, rearZ + Math.cos(ang) * (r * 0.95))
        group.add(p)
      }
      break
    }

    case 'emerald_tower': {
      // Finance (5 Agents): Compact Sleek Emerald Glass Skyscraper
      const w = 6.5 * scaleXZ
      const h = 11.5 * scaleY
      const d = 5.8 * scaleXZ

      const finTower = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), glassMat)
      finTower.position.set(0, h / 2 + 0.4, rearZ)
      finTower.castShadow = true
      group.add(finTower)

      // Gold Accent Vertical Columns
      ;[-w / 2, w / 2].forEach((gx) => {
        const goldCol = new THREE.Mesh(new THREE.BoxGeometry(0.4, h * 1.05, 0.4), accentMat)
        goldCol.position.set(gx, h / 2 + 0.4, rearZ + d / 2)
        group.add(goldCol)
      })
      break
    }

    case 'hex_fortress': {
      // Security (6 Agents): Armored Hexagonal Defense Bunker
      const r = 4.8 * scaleXZ
      const h = 8.5 * scaleY

      const fort = new THREE.Mesh(new THREE.CylinderGeometry(r * 0.85, r, h, 6), mainMat)
      fort.position.set(0, h / 2 + 0.4, rearZ)
      fort.castShadow = true
      group.add(fort)

      const shieldRing = new THREE.Mesh(new THREE.TorusGeometry(r * 1.15, 0.2, 8, 6), accentMat)
      shieldRing.rotation.x = Math.PI / 2
      shieldRing.position.set(0, h * 0.6, rearZ)
      group.add(shieldRing)
      break
    }

    default: {
      // Standard Adaptive Pavilion scaled proportionally to headcount
      const w = 7.5 * scaleXZ
      const h = 8.0 * scaleY
      const d = 6.5 * scaleXZ

      const core = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mainMat)
      core.position.set(0, h / 2 + 0.4, rearZ)
      core.castShadow = true
      group.add(core)

      const glassFacade = new THREE.Mesh(new THREE.BoxGeometry(w * 0.9, h * 0.85, 0.2), glassMat)
      glassFacade.position.set(0, h / 2 + 0.4, rearZ + d / 2 + 0.1)
      group.add(glassFacade)

      const roofBevel = new THREE.Mesh(new THREE.BoxGeometry(w * 1.08, 0.5, d * 1.08), accentMat)
      roofBevel.position.set(0, h + 0.4, rearZ)
      group.add(roofBevel)
      break
    }
  }
}

// -------------------------------------------------------------
// WORKSTATIONS & 3D AGENTS: Spawned at Building Terraces
// -------------------------------------------------------------
function spawnAgentWorkstations() {
  // Group characters by division to calculate dynamic, proportional spatial layout
  const agentsByDivision = {}
  charactersData.value.forEach((char) => {
    if (!agentsByDivision[char.division_id]) {
      agentsByDivision[char.division_id] = []
    }
    agentsByDivision[char.division_id].push(char)
  })

  // Spawn workstations for each division
  Object.keys(agentsByDivision).forEach((divId) => {
    const divAgents = agentsByDivision[divId]
    const building = divisionalBuildings.find((b) => b.id === divId) || { x: 0, z: 0, color: 0x3b82f6 }
    const metrics = getDivisionMetrics(divAgents.length)

    const cols = metrics.cols
    const rows = metrics.rows

    const spacingX = (metrics.terraceWidth * 0.82) / cols
    const spacingZ = Math.min(2.4, (metrics.terraceDepth * 0.55) / Math.max(1, rows))

    divAgents.forEach((char, idx) => {
      const col = idx % cols
      const row = Math.floor(idx / cols)

      // Place desk proportionally on the elevated terrace
      const deskX = building.x + (col - (cols - 1) / 2) * spacingX
      const deskZ = building.z + (metrics.terraceDepth * 0.12) + (row - (rows - 1) / 2) * spacingZ
      const deskY = 0.42

      // Synchronize character data position for search and camera targeting
      char.position_x = deskX
      char.position_y = deskY
      char.position_z = deskZ

      const group = new THREE.Group()
      group.position.set(deskX, deskY, deskZ)

      const agentColor = char.agent_color || '#3B82F6'

      // Desk Surface
      const deskGeo = new THREE.BoxGeometry(2.0, 0.08, 1.2)
      const deskMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.35 })
      const desk = new THREE.Mesh(deskGeo, deskMat)
      desk.position.y = 0.75
      desk.castShadow = true
      desk.receiveShadow = true
      group.add(desk)

      // Legs
      const legGeo = new THREE.CylinderGeometry(0.035, 0.035, 0.75, 8)
      const legMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.9 })
      ;[[-0.9, -0.5], [0.9, -0.5], [-0.9, 0.5], [0.9, 0.5]].forEach(([lx, lz]) => {
        const leg = new THREE.Mesh(legGeo, legMat)
        leg.position.set(lx, 0.375, lz)
        group.add(leg)
      })

      // Glowing Curved Monitor
      const screenGeo = new THREE.BoxGeometry(1.0, 0.6, 0.04)
      const screenMat = new THREE.MeshStandardMaterial({
        color: new THREE.Color(agentColor),
        emissive: new THREE.Color(agentColor),
        emissiveIntensity: 0.7,
      })
      const screen = new THREE.Mesh(screenGeo, screenMat)
      screen.position.set(0, 1.15, -0.25)
      group.add(screen)

      // 3D Stylized Agent Avatar (Torso Capsule + Head Sphere)
      const avatarGroup = new THREE.Group()
      avatarGroup.position.set(0, 0.8, 0.35)

      const bodyGeo = new THREE.CylinderGeometry(0.26, 0.3, 0.7, 16)
      const bodyMat = new THREE.MeshStandardMaterial({
        color: new THREE.Color(agentColor),
        roughness: 0.4,
      })
      const body = new THREE.Mesh(bodyGeo, bodyMat)
      body.position.y = 0.35
      body.castShadow = true
      avatarGroup.add(body)

      const headGeo = new THREE.SphereGeometry(0.24, 20, 20)
      const headMat = new THREE.MeshStandardMaterial({ color: 0xfde047, roughness: 0.3 })
      const head = new THREE.Mesh(headGeo, headMat)
      head.position.y = 0.92
      head.castShadow = true
      avatarGroup.add(head)

      // Floating Holographic Indicator Ring
      const ringGeo = new THREE.RingGeometry(0.42, 0.55, 32)
      const ringMat = new THREE.MeshBasicMaterial({
        color: new THREE.Color(agentColor),
        side: THREE.DoubleSide,
        transparent: true,
        opacity: 0.8,
      })
      const ring = new THREE.Mesh(ringGeo, ringMat)
      ring.rotation.x = -Math.PI / 2
      ring.position.y = 0.04
      avatarGroup.add(ring)

      group.add(avatarGroup)

      // Raycast Bounding Box for Agent
      const hitBox = new THREE.Mesh(
        new THREE.BoxGeometry(2.4, 2.2, 2.2),
        new THREE.MeshBasicMaterial({ visible: false })
      )
      hitBox.position.y = 1.1
      hitBox.userData = { character: char, ring, group }
      group.add(hitBox)

      agentMeshes.push(hitBox)
      scene.add(group)
    })
  })
}

// -------------------------------------------------------------
// INTERACTION & CAMERA ANIMATIONS
// -------------------------------------------------------------
function onMouseMove(event) {
  const container = canvasContainer.value
  if (!container) return
  const rect = container.getBoundingClientRect()
  mouse.x = ((event.clientX - rect.left) / container.clientWidth) * 2 - 1
  mouse.y = -((event.clientY - rect.top) / container.clientHeight) * 2 + 1

  tooltipPos.value = { x: event.clientX, y: event.clientY }

  raycaster.setFromCamera(mouse, camera)
  const intersects = raycaster.intersectObjects(agentMeshes)

  if (intersects.length > 0) {
    const target = intersects[0].object.userData
    hoveredCharacter.value = target.character
    if (target.ring) {
      target.ring.scale.set(1.3, 1.3, 1.3)
    }
    container.style.cursor = 'pointer'
  } else {
    if (hoveredCharacter.value) {
      agentMeshes.forEach((m) => m.userData.ring?.scale.set(1, 1, 1))
    }
    hoveredCharacter.value = null
    container.style.cursor = 'default'
  }
}

function onClick() {
  raycaster.setFromCamera(mouse, camera)

  // 1. Check if an agent workstation was clicked
  const agentHits = raycaster.intersectObjects(agentMeshes)
  if (agentHits.length > 0) {
    const char = agentHits[0].object.userData.character
    focusCameraOnAgent(char)
    emit('select-agent', char)
    return
  }

  // 2. Check if a divisional building was clicked
  const buildingHits = raycaster.intersectObjects(buildingMeshes)
  if (buildingHits.length > 0) {
    const b = buildingHits[0].object.userData.building
    focusCameraOnBuilding(b)
  }
}

function selectAgentFromSearch(char) {
  searchQuery.value = ''
  focusCameraOnAgent(char)
  emit('select-agent', char)
}

function focusCameraOnAgent(char) {
  const targetX = char.position_x
  const targetY = char.position_y || 0.42
  const targetZ = char.position_z

  gsap.to(camera.position, {
    x: targetX,
    y: targetY + 4.5,
    z: targetZ + 7.5,
    duration: 1.5,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(targetX, targetY + 0.8, targetZ),
  })
}

function focusCameraOnBuilding(b) {
  const bHeight = b.buildingHeight || 12
  const camY = Math.max(16, bHeight * 0.75 + 5)
  const camZ = b.z + Math.max(22, bHeight * 0.7 + 14)

  gsap.to(camera.position, {
    x: b.x,
    y: camY,
    z: camZ,
    duration: 1.5,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(b.x, bHeight * 0.35, b.z),
  })
}

function onBuildingSelectChange(event) {
  const bId = event.target.value
  if (!bId) return
  const building = divisionalBuildings.find((b) => b.id === bId)
  if (building) {
    focusCameraOnBuilding(building)
  }
}

function changeZone(zone) {
  currentZone.value = zone.id
  gsap.to(camera.position, {
    x: zone.cam.x,
    y: zone.cam.y,
    z: zone.cam.z,
    duration: 1.6,
    ease: 'power2.inOut',
    onUpdate: () => camera.lookAt(zone.cam.tx, zone.cam.ty, zone.cam.tz),
  })
}

function resetCamera() {
  currentZone.value = 'all'
  gsap.to(camera.position, {
    x: 0,
    y: 85,
    z: 105,
    duration: 1.5,
    ease: 'power2.out',
    onUpdate: () => camera.lookAt(0, 0, 5),
  })
}

function onWindowResize() {
  const container = canvasContainer.value
  if (!container || !renderer || !camera) return
  camera.aspect = container.clientWidth / container.clientHeight
  camera.updateProjectionMatrix()
  renderer.setSize(container.clientWidth, container.clientHeight)
}

// -------------------------------------------------------------
// ANIMATION LOOP (Waves, Traffic, Wind, & Rings)
// -------------------------------------------------------------
function animate() {
  animationFrameId = requestAnimationFrame(animate)

  const time = performance.now() * 0.0015

  // 1. Animated Ocean Waves (Vertex displacement)
  if (oceanMesh && oceanMesh.geometry) {
    const pos = oceanMesh.geometry.attributes.position
    for (let i = 0; i < pos.count; i++) {
      const u = pos.getX(i)
      const v = pos.getY(i)
      const wave = Math.sin(time * 2.0 + u * 0.06) * 0.35 + Math.cos(time * 1.2 + v * 0.08) * 0.25
      pos.setZ(i, wave)
    }
    pos.needsUpdate = true
  }

  // 2. Palm Tree Crowns Swaying Gently in the Coastal Breeze
  palmTreeHeads.forEach((crown, idx) => {
    crown.rotation.z = Math.sin(time * 1.5 + idx) * 0.06
    crown.rotation.x = Math.cos(time * 1.2 + idx) * 0.04
  })

  // 3. Vehicles Moving on Highway (Eastbound & Westbound)
  movingVehicles.forEach((v) => {
    v.position.x += v.userData.speed * v.userData.direction
    if (v.userData.direction === 1 && v.position.x > 150) {
      v.position.x = -150
    } else if (v.userData.direction === -1 && v.position.x < -150) {
      v.position.x = 150
    }
  })

  // 4. Pedestrians Walking on Promenade
  movingPedestrians.forEach((ped) => {
    ped.position.x += ped.userData.speed * ped.userData.direction
    if (ped.position.x > 90) {
      ped.userData.direction = -1
    } else if (ped.position.x < -90) {
      ped.userData.direction = 1
    }
  })

  // 5. Breathing Rotation on Agent Rings
  agentMeshes.forEach((mesh) => {
    if (mesh.userData.ring) {
      mesh.userData.ring.rotation.z = time * 0.6
    }
  })

  renderer.render(scene, camera)
}

onMounted(() => {
  loadSceneData()
})

onBeforeUnmount(() => {
  cancelAnimationFrame(animationFrameId)
  window.removeEventListener('resize', onWindowResize)
  if (renderer && renderer.domElement) {
    renderer.domElement.remove()
  }
})
</script>
