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
        <h3 class="font-bold text-base tracking-wide">Memuat Kompleks Perkantoran 3D MCE-eOffice...</h3>
        <p class="text-xs text-slate-400 mt-1">
          Menata 18 Gedung Divisi, Lautan Pantai, Kebun Kelapa, & Aktivitas Jalan Raya
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
            {{ b.emoji }} Gedung {{ b.name }} ({{ b.zone }})
          </option>
        </select>

        <div class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 text-slate-400 px-3 py-2 rounded-xl text-xs font-mono shadow-xl flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>18 Gedung Kantor • Pantai & Jalan Raya Aktif</span>
        </div>
      </div>
    </div>

    <!-- Active Hover Tooltip -->
    <div
      v-if="hoveredCharacter"
      :style="{ left: tooltipPos.x + 15 + 'px', top: tooltipPos.y + 15 + 'px' }"
      class="pointer-events-none fixed z-30 bg-slate-900/95 backdrop-blur-md border border-sky-500/50 px-4 py-2.5 rounded-2xl shadow-2xl text-xs flex items-center gap-3 transform transition-all duration-75"
    >
      <div
        class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shadow-inner border border-white/10"
        :style="{ backgroundColor: (hoveredCharacter.agent_color || '#3B82F6') + '30' }"
      >
        {{ hoveredCharacter.agent_emoji || '🤖' }}
      </div>
      <div>
        <div class="font-bold text-white flex items-center gap-1.5">
          {{ hoveredCharacter.agent_name }}
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>
        <div class="text-[11px] text-sky-400 font-medium">
          Gedung {{ hoveredCharacter.division_label }} • Meja {{ hoveredCharacter.desk_code }}
        </div>
        <div class="text-[10px] text-slate-400 italic max-w-xs truncate">
          {{ hoveredCharacter.agent_vibe }}
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
  { id: 'all', emoji: '🌐', name: 'Semua Area', cam: { x: 0, y: 75, z: 95, tx: 0, ty: 0, tz: 5 } },
  { id: 'beach', emoji: '🏖️', name: 'Tepi Pantai (Belakang)', cam: { x: 0, y: 35, z: -10, tx: 0, ty: 0, tz: -35 } },
  { id: 'creative', emoji: '🎨', name: 'Plaza Kreatif & Bisnis', cam: { x: 0, y: 38, z: 20, tx: 0, ty: 0, tz: -10 } },
  { id: 'tech', emoji: '💻', name: 'Quad Tech & Pertahanan', cam: { x: 0, y: 38, z: 40, tx: 0, ty: 0, tz: 10 } },
  { id: 'avenue', emoji: '🚗', name: 'Jalan Raya & Boulevard (Depan)', cam: { x: 0, y: 32, z: 80, tx: 0, ty: 0, tz: 45 } },
]

// 18 Divisional Standalone Buildings Configuration
const divisionalBuildings = [
  // Zone 1: Front Avenue (Z = 28)
  { id: 'marketing', name: 'Marketing', emoji: '📢', color: 0xf97316, x: -34, z: 28, style: 'billboard', zone: 'Depan / Boulevard' },
  { id: 'support', name: 'Support', emoji: '🛎️', color: 0x84cc16, x: -11, z: 28, style: 'welcome', zone: 'Depan / Boulevard' },
  { id: 'sales', name: 'Sales', emoji: '📈', color: 0x10b981, x: 11, z: 28, style: 'lounge', zone: 'Depan / Boulevard' },
  { id: 'paid-media', name: 'Paid Media', emoji: '🎯', color: 0xeab308, x: 34, z: 28, style: 'ticker', zone: 'Depan / Boulevard' },

  // Zone 2: Tech & Defense Quad (Z = 10)
  { id: 'engineering', name: 'Engineering', emoji: '💻', color: 0x3b82f6, x: -34, z: 10, style: 'cyber_tower', zone: 'Tech Quad' },
  { id: 'security', name: 'Security', emoji: '🛡️', color: 0xef4444, x: -11, z: 10, style: 'hex_fortress', zone: 'Tech Quad' },
  { id: 'testing', name: 'Testing', emoji: '🧪', color: 0xf59e0b, x: 11, z: 10, style: 'stress_lab', zone: 'Tech Quad' },
  { id: 'gis', name: 'GIS', emoji: '🗺️', color: 0x14b8a6, x: 34, z: 10, style: 'observatory', zone: 'Tech Quad' },

  // Zone 3: Creative & Business Plaza (Z = -10)
  { id: 'product', name: 'Product', emoji: '📦', color: 0xd946ef, x: -34, z: -10, style: 'modular_cube', zone: 'Creative Plaza' },
  { id: 'design', name: 'Design', emoji: '🎨', color: 0xec4899, x: -11, z: -10, style: 'curved_glass', zone: 'Creative Plaza' },
  { id: 'game-development', name: 'Game Dev', emoji: '🎮', color: 0xa855f7, x: 11, z: -10, style: 'arcade', zone: 'Creative Plaza' },
  { id: 'finance', name: 'Finance', emoji: '💵', color: 0x22c55e, x: 34, z: -10, style: 'emerald_tower', zone: 'Creative Plaza' },

  // Zone 4: Beachfront Promenade (Z = -30, facing ocean)
  { id: 'academic', name: 'Academic', emoji: '🎓', color: 0x8b5cf6, x: -36, z: -30, style: 'rotunda', zone: 'Tepi Pantai' },
  { id: 'research', name: 'Research', emoji: '🔬', color: 0x7c3aed, x: -22, z: -30, style: 'quantum_lab', zone: 'Tepi Pantai' },
  { id: 'spatial-computing', name: 'Spatial XR', emoji: '🥽', color: 0x06b6d4, x: -7, z: -30, style: 'xr_hologram', zone: 'Tepi Pantai' },
  { id: 'specialized', name: 'Specialized', emoji: '✨', color: 0x6366f1, x: 7, z: -30, style: 'spire', zone: 'Tepi Pantai' },
  { id: 'healthcare', name: 'Healthcare', emoji: '🩺', color: 0x0d9488, x: 22, z: -30, style: 'biophilic', zone: 'Tepi Pantai' },
  { id: 'project-management', name: 'Project Mgmt', emoji: '📋', color: 0x0ea5e9, x: 36, z: -30, style: 'grid_tower', zone: 'Tepi Pantai' },
]

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
  scene.fog = new THREE.FogExp2(0x0f1d33, 0.005)

  // 2. Perspective Camera
  const aspect = container.clientWidth / container.clientHeight
  camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 2000)
  camera.position.set(0, 75, 95)
  camera.lookAt(0, 0, 5)

  // 3. WebGL Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.35
  container.appendChild(renderer.domElement)

  // 4. Natural Lighting (Sunlight & Sky bounce)
  const ambientLight = new THREE.AmbientLight(0xffffff, 1.1)
  scene.add(ambientLight)

  const sunLight = new THREE.DirectionalLight(0xffedd5, 2.6)
  sunLight.position.set(60, 90, 60)
  sunLight.castShadow = true
  sunLight.shadow.mapSize.width = 2048
  sunLight.shadow.mapSize.height = 2048
  sunLight.shadow.camera.left = -120
  sunLight.shadow.camera.right = 120
  sunLight.shadow.camera.top = 120
  sunLight.shadow.camera.bottom = -120
  scene.add(sunLight)

  // Accent lights for ocean and tech quad
  const oceanRimLight = new THREE.DirectionalLight(0x38bdf8, 1.2)
  oceanRimLight.position.set(-50, 40, -100)
  scene.add(oceanRimLight)

  // 5. Build Environment Components
  buildGroundAndLandscape()
  buildOceanAndBeach()
  buildCoconutPlantations()
  buildHighwayAndTraffic()
  buildDivisionalBuildings()
  spawnAgentWorkstations()

  // 6. Events
  window.addEventListener('resize', onWindowResize)
  container.addEventListener('mousemove', onMouseMove)
  container.addEventListener('click', onClick)

  // 7. Start Render Loop
  animate()
}

// -------------------------------------------------------------
// ENVIRONMENT: Ground & Campus Boulevard
// -------------------------------------------------------------
function buildGroundAndLandscape() {
  // Main Campus Paved Plinth
  const groundGeo = new THREE.PlaneGeometry(100, 90)
  const groundMat = new THREE.MeshStandardMaterial({
    color: 0x1e293b,
    roughness: 0.8,
    metalness: 0.1,
  })
  const ground = new THREE.Mesh(groundGeo, groundMat)
  ground.rotation.x = -Math.PI / 2
  ground.position.set(0, -0.05, 0)
  ground.receiveShadow = true
  scene.add(ground)

  // Elegant Grid Paving Walkways
  const grid = new THREE.GridHelper(96, 48, 0x0284c7, 0x334155)
  grid.position.set(0, 0.01, 0)
  scene.add(grid)

  // Central Holographic Fountain Plaza at (0, 0)
  const fountainBaseGeo = new THREE.CylinderGeometry(5, 5.5, 0.5, 32)
  const fountainMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.2, metalness: 0.8 })
  const fountainBase = new THREE.Mesh(fountainBaseGeo, fountainMat)
  fountainBase.position.set(0, 0.25, 0)
  scene.add(fountainBase)

  const poolWaterGeo = new THREE.CylinderGeometry(4.6, 4.6, 0.2, 32)
  const poolWaterMat = new THREE.MeshStandardMaterial({
    color: 0x06b6d4,
    emissive: 0x0891b2,
    emissiveIntensity: 0.5,
    roughness: 0.1,
  })
  const poolWater = new THREE.Mesh(poolWaterGeo, poolWaterMat)
  poolWater.position.set(0, 0.5, 0)
  scene.add(poolWater)

  // Floating Holographic Campus Monument Ring
  const monumentRingGeo = new THREE.TorusGeometry(3.2, 0.15, 16, 64)
  const monumentRingMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8, wireframe: true })
  const monumentRing = new THREE.Mesh(monumentRingGeo, monumentRingMat)
  monumentRing.rotation.x = Math.PI / 3
  monumentRing.position.set(0, 2.5, 0)
  scene.add(monumentRing)
}

// -------------------------------------------------------------
// BELAKANG: Pantai & Lautan Berombak Animasi (Ocean & Beach)
// -------------------------------------------------------------
function buildOceanAndBeach() {
  // Sandy Beach Shoreline (Z = -45 to -62)
  const sandGeo = new THREE.PlaneGeometry(280, 24)
  const sandMat = new THREE.MeshStandardMaterial({
    color: 0xd4b16a,
    roughness: 0.95,
    metalness: 0.0,
  })
  const sand = new THREE.Mesh(sandGeo, sandMat)
  sand.rotation.x = -Math.PI / 2
  sand.position.set(0, -0.02, -54)
  sand.receiveShadow = true
  scene.add(sand)

  // Animated Ocean Water Surface (Z = -60 to -320)
  const oceanGeo = new THREE.PlaneGeometry(360, 260, 64, 48)
  const oceanMat = new THREE.MeshPhysicalMaterial({
    color: 0x0284c7,
    emissive: 0x0369a1,
    emissiveIntensity: 0.2,
    roughness: 0.15,
    transmission: 0.6,
    thickness: 1.2,
    transparent: true,
    opacity: 0.88,
  })
  oceanMesh = new THREE.Mesh(oceanGeo, oceanMat)
  oceanMesh.rotation.x = -Math.PI / 2
  oceanMesh.position.set(0, -0.15, -180)
  scene.add(oceanMesh)

  // Beach Umbrellas & Sun Loungers along the shore
  const umbrellaGeo = new THREE.ConeGeometry(1.6, 0.8, 16)
  ;[-38, -20, 0, 20, 38].forEach((ux) => {
    const umbMat = new THREE.MeshStandardMaterial({ color: ux % 2 === 0 ? 0xef4444 : 0x38bdf8, roughness: 0.4 })
    const umb = new THREE.Mesh(umbrellaGeo, umbMat)
    umb.position.set(ux, 2.2, -49)
    umb.castShadow = true
    scene.add(umb)

    const pole = new THREE.Mesh(
      new THREE.CylinderGeometry(0.05, 0.05, 2.2, 8),
      new THREE.MeshStandardMaterial({ color: 0xffffff })
    )
    pole.position.set(ux, 1.1, -49)
    scene.add(pole)
  })
}

// -------------------------------------------------------------
// KIRI & KANAN: Perkebunan Pohon Kelapa Tropis (Palm Plantations)
// -------------------------------------------------------------
function buildCoconutPlantations() {
  const createPalmTree = (px, pz) => {
    const palmGroup = new THREE.Group()
    palmGroup.position.set(px, 0, pz)

    // Curved Trunk with 4 segmented rings
    const trunkHeight = 7.5 + Math.random() * 2
    const trunkMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.85 })
    const trunkGeo = new THREE.CylinderGeometry(0.2, 0.38, trunkHeight, 10)
    const trunk = new THREE.Mesh(trunkGeo, trunkMat)
    trunk.position.set(0, trunkHeight / 2, 0)
    trunk.rotation.z = (Math.random() - 0.5) * 0.15
    trunk.rotation.x = (Math.random() - 0.5) * 0.15
    trunk.castShadow = true
    palmGroup.add(trunk)

    // Crown of Palm Fronds (8 curved green blades)
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

    // Coconuts cluster
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

  // Left Plantation (X: -55 to -105, Z: -42 to 45)
  for (let x = -55; x >= -105; x -= 10) {
    for (let z = -42; z <= 45; z += 11) {
      createPalmTree(x + (Math.random() * 4 - 2), z + (Math.random() * 4 - 2))
    }
  }

  // Right Plantation (X: +55 to +105, Z: -42 to 45)
  for (let x = 55; x <= 105; x += 10) {
    for (let z = -42; z <= 45; z += 11) {
      createPalmTree(x + (Math.random() * 4 - 2), z + (Math.random() * 4 - 2))
    }
  }
}

// -------------------------------------------------------------
// DEPAN: Jalan Raya & Lalu Lintas Bergerak (Highway & Traffic)
// -------------------------------------------------------------
function buildHighwayAndTraffic() {
  // Asphalt Multi-Lane Highway (Z = 58 to 72)
  const roadGeo = new THREE.PlaneGeometry(360, 16)
  const roadMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 })
  const road = new THREE.Mesh(roadGeo, roadMat)
  road.rotation.x = -Math.PI / 2
  road.position.set(0, 0.02, 65)
  road.receiveShadow = true
  scene.add(road)

  // Double Yellow Center Line
  const centerLineGeo = new THREE.PlaneGeometry(360, 0.3)
  const yellowMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 })
  const centerLine = new THREE.Mesh(centerLineGeo, yellowMat)
  centerLine.rotation.x = -Math.PI / 2
  centerLine.position.set(0, 0.03, 65)
  scene.add(centerLine)

  // Pedestrian Sidewalk Promenade (Z = 50 to 57)
  const sidewalkGeo = new THREE.PlaneGeometry(360, 7)
  const sidewalkMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.85 })
  const sidewalk = new THREE.Mesh(sidewalkGeo, sidewalkMat)
  sidewalk.rotation.x = -Math.PI / 2
  sidewalk.position.set(0, 0.05, 53.5)
  sidewalk.receiveShadow = true
  scene.add(sidewalk)

  // Street Light Poles along the Sidewalk
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

  // Create Moving Vehicles (Cars, Vans, Buses)
  const vehicleColors = [0xef4444, 0x3b82f6, 0xffffff, 0x10b981, 0xf59e0b, 0x8b5cf6, 0x06b6d4]

  // Lane 1: Heading East (+X) at Z = 62
  for (let i = 0; i < 6; i++) {
    const v = createVehicleMesh(vehicleColors[i % vehicleColors.length])
    v.position.set(-140 + i * 50, 0.4, 62)
    v.userData = { speed: 0.35 + Math.random() * 0.15, direction: 1, laneZ: 62 }
    scene.add(v)
    movingVehicles.push(v)
  }

  // Lane 2: Heading West (-X) at Z = 68
  for (let i = 0; i < 6; i++) {
    const v = createVehicleMesh(vehicleColors[(i + 3) % vehicleColors.length])
    v.position.set(140 - i * 50, 0.4, 68)
    v.rotation.y = Math.PI
    v.userData = { speed: 0.35 + Math.random() * 0.15, direction: -1, laneZ: 68 }
    scene.add(v)
    movingVehicles.push(v)
  }

  // Pedestrians strolling on sidewalk (Z = 53.5)
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

  // Car Body
  const bodyGeo = new THREE.BoxGeometry(3.6, 0.9, 1.8)
  const bodyMat = new THREE.MeshStandardMaterial({ color: colorHex, metalness: 0.6, roughness: 0.2 })
  const body = new THREE.Mesh(bodyGeo, bodyMat)
  body.position.y = 0.5
  body.castShadow = true
  vGroup.add(body)

  // Cabin / Windows
  const cabinGeo = new THREE.BoxGeometry(2.0, 0.7, 1.6)
  const cabinMat = new THREE.MeshPhysicalMaterial({ color: 0x0f172a, roughness: 0.1, transmission: 0.7 })
  const cabin = new THREE.Mesh(cabinGeo, cabinMat)
  cabin.position.set(-0.2, 1.25, 0)
  vGroup.add(cabin)

  // Headlights (White glow)
  const headlightMat = new THREE.MeshBasicMaterial({ color: 0xffffff })
  ;[-0.6, 0.6].forEach((hz) => {
    const light = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.2, 0.3), headlightMat)
    light.position.set(1.82, 0.5, hz)
    vGroup.add(light)
  })

  // Taillights (Red glow)
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

// -------------------------------------------------------------
// KOMPLEKS: 18 Gedung Kantor Divisi Mandiri
// -------------------------------------------------------------
function buildDivisionalBuildings() {
  divisionalBuildings.forEach((b) => {
    const bGroup = new THREE.Group()
    bGroup.position.set(b.x, 0, b.z)

    // Base Terrace Platform for the Division
    const terraceGeo = new THREE.BoxGeometry(16, 0.4, 14)
    const terraceMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.4 })
    const terrace = new THREE.Mesh(terraceGeo, terraceMat)
    terrace.position.y = 0.2
    terrace.receiveShadow = true
    bGroup.add(terrace)

    // Glowing Division Color Neon Perimeter
    const neonBorderGeo = new THREE.BoxGeometry(16.2, 0.1, 14.2)
    const neonBorderMat = new THREE.MeshBasicMaterial({ color: b.color, wireframe: true })
    const neonBorder = new THREE.Mesh(neonBorderGeo, neonBorderMat)
    neonBorder.position.y = 0.42
    bGroup.add(neonBorder)

    // Building Structure customized by Division Style
    buildCustomArchitecture(bGroup, b)

    // Floating 3D Glowing Signboard over the building
    const signGroup = new THREE.Group()
    signGroup.position.set(0, 11, 0)

    const signBoardGeo = new THREE.BoxGeometry(7, 1.4, 0.3)
    const signBoardMat = new THREE.MeshStandardMaterial({
      color: 0x020617,
      roughness: 0.2,
      metalness: 0.8,
    })
    const signBoard = new THREE.Mesh(signBoardGeo, signBoardMat)
    signGroup.add(signBoard)

    const signGlowGeo = new THREE.BoxGeometry(7.2, 1.6, 0.1)
    const signGlowMat = new THREE.MeshBasicMaterial({
      color: b.color,
      wireframe: true,
    })
    const signGlow = new THREE.Mesh(signGlowGeo, signGlowMat)
    signGroup.add(signGlow)

    bGroup.add(signGroup)

    // Interactive Click Box for the Building
    const hitBox = new THREE.Mesh(
      new THREE.BoxGeometry(18, 16, 16),
      new THREE.MeshBasicMaterial({ visible: false })
    )
    hitBox.position.y = 8
    hitBox.userData = { building: b, group: bGroup }
    bGroup.add(hitBox)

    buildingMeshes.push(hitBox)
    scene.add(bGroup)
  })
}

function buildCustomArchitecture(group, b) {
  const mainMat = new THREE.MeshStandardMaterial({
    color: 0x1e293b,
    roughness: 0.3,
    metalness: 0.5,
  })

  const glassMat = new THREE.MeshPhysicalMaterial({
    color: b.color,
    transparent: true,
    opacity: 0.4,
    roughness: 0.1,
    transmission: 0.8,
  })

  const accentMat = new THREE.MeshStandardMaterial({
    color: b.color,
    emissive: b.color,
    emissiveIntensity: 0.5,
  })

  switch (b.style) {
    case 'cyber_tower': { // Engineering: Cyber Tower with Server Neon Racks
      const tower = new THREE.Mesh(new THREE.BoxGeometry(9, 13, 8), mainMat)
      tower.position.set(0, 6.7, -1)
      tower.castShadow = true
      group.add(tower)

      const glassFront = new THREE.Mesh(new THREE.BoxGeometry(8, 11, 0.2), glassMat)
      glassFront.position.set(0, 6.7, 3.1)
      group.add(glassFront)

      // Server vertical neon strips
      ;[-3.5, 3.5].forEach((sx) => {
        const strip = new THREE.Mesh(new THREE.BoxGeometry(0.4, 12, 0.4), accentMat)
        strip.position.set(sx, 6.7, 3.1)
        group.add(strip)
      })
      break
    }

    case 'hex_fortress': { // Security: Hexagonal Armored Fortress
      const fort = new THREE.Mesh(new THREE.CylinderGeometry(5.5, 6.5, 10, 6), mainMat)
      fort.position.set(0, 5.2, -1)
      fort.castShadow = true
      group.add(fort)

      const shieldRing = new THREE.Mesh(new THREE.TorusGeometry(6.8, 0.2, 8, 6), accentMat)
      shieldRing.rotation.x = Math.PI / 2
      shieldRing.position.set(0, 5, -1)
      group.add(shieldRing)
      break
    }

    case 'curved_glass': { // Design: Avant-Garde Curved Glass Studio
      const studio = new THREE.Mesh(new THREE.CylinderGeometry(5, 5.8, 9, 32, 1, false, 0, Math.PI), glassMat)
      studio.position.set(0, 4.7, 1)
      group.add(studio)

      const roof = new THREE.Mesh(new THREE.CylinderGeometry(6, 6, 0.6, 32), mainMat)
      roof.position.set(0, 9.4, 1)
      group.add(roof)
      break
    }

    case 'rotunda': { // Academic: Neoclassical Modern Rotunda Library
      const dome = new THREE.Mesh(new THREE.SphereGeometry(4.5, 24, 16, 0, Math.PI * 2, 0, Math.PI / 2), mainMat)
      dome.position.set(0, 7.5, -1)
      group.add(dome)

      // Colonnade Pillars
      for (let i = 0; i < 8; i++) {
        const ang = (i / 8) * Math.PI * 2
        const p = new THREE.Mesh(new THREE.CylinderGeometry(0.3, 0.3, 7.5, 12), accentMat)
        p.position.set(Math.sin(ang) * 4.2, 3.95, Math.cos(ang) * 4.2 - 1)
        group.add(p)
      }
      break
    }

    case 'observatory': { // GIS: Satellite Dome Observatory
      const base = new THREE.Mesh(new THREE.CylinderGeometry(5, 5, 6, 24), mainMat)
      base.position.set(0, 3.2, -1)
      group.add(base)

      const dome = new THREE.Mesh(new THREE.SphereGeometry(3.5, 20, 16), glassMat)
      dome.position.set(0, 7.5, -1)
      group.add(dome)

      // Satellite dish antenna
      const dish = new THREE.Mesh(new THREE.ConeGeometry(1.8, 0.6, 16), accentMat)
      dish.rotation.x = -Math.PI / 3
      dish.position.set(2, 9.5, -1)
      group.add(dish)
      break
    }

    case 'emerald_tower': { // Finance: Sleek Emerald Glass Skyscraper
      const finTower = new THREE.Mesh(new THREE.BoxGeometry(8, 14, 7), glassMat)
      finTower.position.set(0, 7.2, -1)
      finTower.castShadow = true
      group.add(finTower)

      // Gold Accent Pillars
      const goldPillar = new THREE.Mesh(new THREE.BoxGeometry(0.5, 14.5, 7.2), accentMat)
      goldPillar.position.set(0, 7.2, -1)
      group.add(goldPillar)
      break
    }

    default: { // Standard Sleek Pavilion for other divisions
      const core = new THREE.Mesh(new THREE.BoxGeometry(8.5, 8.5, 7.5), mainMat)
      core.position.set(0, 4.45, -1)
      core.castShadow = true
      group.add(core)

      const glassFacade = new THREE.Mesh(new THREE.BoxGeometry(7.8, 7.5, 0.2), glassMat)
      glassFacade.position.set(0, 4.2, 2.8)
      group.add(glassFacade)

      const roofBevel = new THREE.Mesh(new THREE.BoxGeometry(9.2, 0.6, 8.2), accentMat)
      roofBevel.position.set(0, 8.8, -1)
      group.add(roofBevel)
      break
    }
  }
}

// -------------------------------------------------------------
// WORKSTATIONS & 3D AGENTS: Spawned at Building Terraces
// -------------------------------------------------------------
function spawnAgentWorkstations() {
  charactersData.value.forEach((char) => {
    const group = new THREE.Group()
    group.position.set(char.position_x, char.position_y, char.position_z)

    const agentColor = char.agent_color || '#3B82F6'

    // Desk
    const deskGeo = new THREE.BoxGeometry(2.4, 0.1, 1.4)
    const deskMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.35 })
    const desk = new THREE.Mesh(deskGeo, deskMat)
    desk.position.y = 0.8
    desk.castShadow = true
    desk.receiveShadow = true
    group.add(desk)

    // Legs
    const legGeo = new THREE.CylinderGeometry(0.04, 0.04, 0.8, 8)
    const legMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.9 })
    ;[[-1.1, -0.6], [1.1, -0.6], [-1.1, 0.6], [1.1, 0.6]].forEach(([lx, lz]) => {
      const leg = new THREE.Mesh(legGeo, legMat)
      leg.position.set(lx, 0.4, lz)
      group.add(leg)
    })

    // Glowing Curved Monitor
    const screenGeo = new THREE.BoxGeometry(1.2, 0.7, 0.05)
    const screenMat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(agentColor),
      emissive: new THREE.Color(agentColor),
      emissiveIntensity: 0.7,
    })
    const screen = new THREE.Mesh(screenGeo, screenMat)
    screen.position.set(0, 1.25, -0.3)
    group.add(screen)

    // 3D Stylized Agent Avatar (Torso Capsule + Head Sphere)
    const avatarGroup = new THREE.Group()
    avatarGroup.position.set(0, 0.85, 0.4)

    const bodyGeo = new THREE.CylinderGeometry(0.3, 0.35, 0.8, 16)
    const bodyMat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(agentColor),
      roughness: 0.4,
    })
    const body = new THREE.Mesh(bodyGeo, bodyMat)
    body.position.y = 0.4
    body.castShadow = true
    avatarGroup.add(body)

    const headGeo = new THREE.SphereGeometry(0.28, 20, 20)
    const headMat = new THREE.MeshStandardMaterial({ color: 0xfde047, roughness: 0.3 })
    const head = new THREE.Mesh(headGeo, headMat)
    head.position.y = 1.05
    head.castShadow = true
    avatarGroup.add(head)

    // Floating Holographic Indicator Ring
    const ringGeo = new THREE.RingGeometry(0.5, 0.65, 32)
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
      new THREE.BoxGeometry(3.0, 2.5, 2.5),
      new THREE.MeshBasicMaterial({ visible: false })
    )
    hitBox.position.y = 1.2
    hitBox.userData = { character: char, ring, group }
    group.add(hitBox)

    agentMeshes.push(hitBox)
    scene.add(group)
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
  const targetY = 1.2
  const targetZ = char.position_z

  gsap.to(camera.position, {
    x: targetX,
    y: targetY + 4.5,
    z: targetZ + 7.5,
    duration: 1.5,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(targetX, targetY, targetZ),
  })
}

function focusCameraOnBuilding(b) {
  gsap.to(camera.position, {
    x: b.x,
    y: 18,
    z: b.z + 24,
    duration: 1.5,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(b.x, 5, b.z),
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
    y: 75,
    z: 95,
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
