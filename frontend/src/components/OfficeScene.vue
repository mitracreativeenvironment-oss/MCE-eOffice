<template>
  <div class="relative w-full h-full overflow-hidden select-none bg-slate-950 font-sans">
    <!-- Three.js Canvas Container -->
    <div ref="canvasContainer" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

    <!-- Loading Overlay -->
    <div
      v-if="isLoading"
      class="absolute inset-0 z-20 bg-slate-950/90 backdrop-blur-md flex flex-col items-center justify-center gap-4 text-white"
    >
      <div class="w-12 h-12 rounded-full border-4 border-amber-500/30 border-t-amber-400 animate-spin"></div>
      <div class="text-center">
        <h3 class="font-bold text-base tracking-wide text-amber-200">Menata Kantor AI 3D Isometrik...</h3>
        <p class="text-xs text-slate-400 mt-1">
          Menyiapkan 18 Kantor Divisi Mandiri, Meja Kayu, Papan Tugas, Sofa, & Agen Animasi
        </p>
      </div>
    </div>

    <!-- Top Left: Office Title & Active Division Info -->
    <div class="absolute top-4 left-6 z-10 flex flex-col gap-2 max-w-sm pointer-events-auto">
      <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-700/80 rounded-2xl p-3 shadow-2xl flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shadow-inner border border-white/10 shrink-0"
            :style="{ backgroundColor: activeDivisionColor + '30', borderColor: activeDivisionColor + '60' }"
          >
            {{ activeDivisionInfo.emoji || '🏢' }}
          </div>
          <div>
            <h2 class="text-xs font-black text-white flex items-center gap-2">
              {{ activeDivisionInfo.name || 'Semua Divisi' }}
              <span
                class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-full text-white"
                :style="{ backgroundColor: activeDivisionColor }"
              >
                {{ activeDivisionAgentCount }} Agen
              </span>
            </h2>
            <p class="text-[10px] text-slate-400 font-medium line-clamp-1">
              {{ activeDivisionInfo.desc || 'Kompleks Kantor AI 3D Isometrik' }}
            </p>
          </div>
        </div>

        <button
          v-if="selectedDivision !== 'all'"
          @click="selectDivision('all')"
          class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[10px] font-semibold text-slate-300 hover:text-white shrink-0 border border-slate-700/60"
        >
          🌐 Kampus
        </button>
      </div>

      <!-- Quick Search Agent -->
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari agen atau ruangan divisi..."
          class="w-full bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-amber-500 shadow-xl transition-all"
        />
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>

        <!-- Search Suggestions Dropdown -->
        <div
          v-if="filteredAgents.length > 0 && searchQuery.trim()"
          class="absolute left-0 right-0 top-full mt-1.5 max-h-56 overflow-y-auto bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-xl shadow-2xl p-1.5 space-y-1 z-30"
        >
          <button
            v-for="char in filteredAgents.slice(0, 8)"
            :key="char.character_id"
            @click="onSearchAgentClick(char)"
            class="w-full px-2.5 py-1.5 text-left rounded-lg hover:bg-slate-800 flex items-center justify-between transition-all text-xs"
          >
            <div class="flex items-center gap-2">
              <span>{{ char.agent_emoji }}</span>
              <div>
                <div class="font-bold text-white text-[11px]">{{ char.agent_name }}</div>
                <div class="text-[9px] text-slate-400">{{ char.division_label }} • Meja {{ char.desk_code }}</div>
              </div>
            </div>
            <span
              class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded text-white"
              :style="{ backgroundColor: char.agent_color || '#3B82F6' }"
            >
              {{ char.division_id }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Top Right Controls: Day/Night Toggle & Gaya 3D Switcher -->
    <div class="absolute top-4 right-6 z-10 flex items-center gap-2 pointer-events-auto">
      <!-- Day / Night Toggle Button -->
      <button
        @click="toggleDayNight"
        class="bg-slate-900/90 backdrop-blur-md border border-slate-700/80 hover:border-slate-500 text-slate-200 px-3 py-2 rounded-xl text-xs font-semibold shadow-xl hover:bg-slate-800 transition-all flex items-center gap-1.5"
      >
        <span>{{ isNightMode ? '🌙 Malam' : '☀️ Siang' }}</span>
      </button>

      <!-- 3D Style Switcher Button -->
      <button
        @click="toggleGaya"
        class="bg-slate-900/90 backdrop-blur-md border border-slate-700/80 hover:border-slate-500 text-slate-200 px-3 py-2 rounded-xl text-xs font-semibold shadow-xl hover:bg-slate-800 transition-all flex items-center gap-1.5"
      >
        <span>🎨</span>
        <span>{{ activeGaya === 'asli' ? 'Gaya Asli' : 'Hologram' }}</span>
      </button>

      <!-- Reset Camera Button -->
      <button
        @click="resetCamera"
        class="bg-slate-900/90 backdrop-blur-md border border-slate-700/80 hover:border-slate-500 text-slate-200 px-3 py-2 rounded-xl text-xs font-semibold shadow-xl hover:bg-slate-800 transition-all flex items-center gap-1.5"
      >
        <span>🎯</span>
        <span class="hidden sm:inline">Pusatkan</span>
      </button>
    </div>

    <!-- Bottom: Division Office Selector Bar (Semua 18 Divisi Memiliki Kantornya Masing-Masing) -->
    <div class="absolute bottom-6 inset-x-6 z-10 flex justify-center pointer-events-none">
      <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-700/80 rounded-2xl p-2 shadow-2xl max-w-5xl overflow-x-auto no-scrollbar pointer-events-auto flex items-center gap-1.5">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-2 shrink-0">
          Kantor Divisi:
        </span>

        <!-- All Divisions Button -->
        <button
          @click="selectDivision('all')"
          :class="[
            'px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shrink-0',
            selectedDivision === 'all'
              ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/30'
              : 'bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-800'
          ]"
        >
          <span>🌐</span>
          <span>Semua Divisi (Kampus)</span>
        </button>

        <!-- 18 Individual Divisions Buttons -->
        <button
          v-for="div in divisionsConfig"
          :key="div.id"
          @click="selectDivision(div.id)"
          :class="[
            'px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shrink-0',
            selectedDivision === div.id
              ? 'text-white shadow-md'
              : 'bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-800'
          ]"
          :style="selectedDivision === div.id ? { backgroundColor: div.color } : {}"
        >
          <span>{{ div.emoji }}</span>
          <span>{{ div.name }}</span>
          <span class="text-[10px] opacity-80 font-mono">({{ getDivisionCount(div.id) }})</span>
        </button>
      </div>
    </div>

    <!-- HTML Floating Agent Speech Bubbles / Labels Layer -->
    <div
      v-if="hoveredCharacter"
      class="fixed z-30 pointer-events-none transform -translate-x-1/2 -translate-y-full mb-3 px-3 py-2 rounded-xl bg-slate-900/95 backdrop-blur-md border border-slate-700 shadow-2xl flex items-center gap-2.5"
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
          Meja {{ hoveredCharacter.desk_code }} • Sedang bekerja • Klik untuk chat
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
const selectedDivision = ref('all') // 'all' | division_id
const isNightMode = ref(false)
const activeGaya = ref('asli') // 'asli' | 'hologram'
const searchQuery = ref('')
const hoveredCharacter = ref(null)
const tooltipPos = ref({ x: 0, y: 0 })

const charactersData = ref([])
const tasksData = ref([])

// 18 Divisional Configuration in the Kantor-AI Template Style
const divisionsConfig = [
  { id: 'engineering', name: 'Engineering', emoji: '💻', color: '#3B82F6', rug: '#2563EB', desc: 'Arsitektur sistem, backend, database, & cloud infrastructure', x: -44, z: -26 },
  { id: 'security', name: 'Security', emoji: '🛡️', color: '#EF4444', rug: '#DC2626', desc: 'Security SOC, threat auditing, & vulnerability defense', x: -14, z: -26 },
  { id: 'testing', name: 'Testing', emoji: '🧪', color: '#F59E0B', rug: '#D97706', desc: 'Quality assurance, automated testing, & regression suites', x: 14, z: -26 },
  { id: 'gis', name: 'GIS', emoji: '🗺️', color: '#14B8A6', rug: '#0D9488', desc: 'Geospatial mapping, satellite telematics, & spatial data', x: 44, z: -26 },

  { id: 'product', name: 'Product', emoji: '📦', color: '#D946EF', rug: '#C026D3', desc: 'Product discovery, requirement specs, & roadmapping', x: -44, z: 0 },
  { id: 'design', name: 'Design', emoji: '🎨', color: '#EC4899', rug: '#DB2777', desc: 'UI/UX craft, typography, design systems, & branding', x: -14, z: 0 },
  { id: 'game-development', name: 'Game Dev', emoji: '🎮', color: '#A855F7', rug: '#9333EA', desc: 'Game engines, procedural logic, & interactive art', x: 14, z: 0 },
  { id: 'finance', name: 'Finance', emoji: '💵', color: '#22C55E', rug: '#16A34A', desc: 'Financial modeling, revenue tracking, & budgeting', x: 44, z: 0 },

  { id: 'marketing', name: 'Marketing', emoji: '📢', color: '#F97316', rug: '#EA580C', desc: 'Content strategy, market research, & brand campaigns', x: -44, z: 26 },
  { id: 'support', name: 'Support', emoji: '🛎️', color: '#84CC16', rug: '#65A30D', desc: 'Customer success, issue escalation, & help center', x: -14, z: 26 },
  { id: 'sales', name: 'Sales', emoji: '📈', color: '#10B981', rug: '#059669', desc: 'B2B enterprise pipeline, deal closure, & pitching', x: 14, z: 26 },
  { id: 'paid-media', name: 'Paid Media', emoji: '🎯', color: '#EAB308', rug: '#CA8A04', desc: 'Performance ad campaigns, conversion, & growth', x: 44, z: 26 },

  { id: 'academic', name: 'Academic', emoji: '🎓', color: '#8B5CF6', rug: '#7C3AED', desc: 'Research council, pedagogy, & whitepaper synthesis', x: -44, z: 52 },
  { id: 'research', name: 'Research', emoji: '🔬', color: '#7C3AED', rug: '#6D28D9', desc: 'Deep AI experimentation, model evals, & algorithms', x: -26, z: 52 },
  { id: 'spatial-computing', name: 'Spatial XR', emoji: '🥽', color: '#06B6D4', rug: '#0891B2', desc: 'Virtual reality, 3D spatial computing, & WebXR', x: -8, z: 52 },
  { id: 'specialized', name: 'Specialized', emoji: '✨', color: '#6366F1', rug: '#4F46E5', desc: 'Strategic cross-functional innovation & execution', x: 10, z: 52 },
  { id: 'healthcare', name: 'Healthcare', emoji: '🩺', color: '#0D9488', rug: '#0F766E', desc: 'Healthtech data protocols, clinical ops, & wellness', x: 28, z: 52 },
  { id: 'project-management', name: 'Project Mgmt', emoji: '📋', color: '#0EA5E9', rug: '#0284C7', desc: 'Sprint coordination, blocker removal, & deliveries', x: 46, z: 52 },
]

function getDivisionCount(divId) {
  return charactersData.value.filter((c) => c.division_id === divId).length || 6
}

const activeDivisionInfo = computed(() => {
  if (selectedDivision.value === 'all') {
    return {
      name: 'Kompleks Kantor AI',
      emoji: '🏛️',
      color: '#F59E0B',
      desc: '18 Kantor Divisi Mandiri • 283 Meja Kerja • Papan Tugas Aktif',
    }
  }
  const conf = divisionsConfig.find((d) => d.id === selectedDivision.value)
  return conf || { name: 'Divisi', emoji: '🏢', color: '#3B82F6', desc: '' }
})

const activeDivisionColor = computed(() => activeDivisionInfo.value.color || '#3B82F6')
const activeDivisionAgentCount = computed(() => {
  if (selectedDivision.value === 'all') return 283
  return getDivisionCount(selectedDivision.value)
})

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

// Three.js State
let scene, camera, renderer, animationFrameId
let officesGroup = null
let lightingGroup = null
const agentMeshes = []
const officeGroups = new Map() // divId -> Group
const animatedActors = [] // { armL, armR, body, head, phase, screenMat, lampMat, pointLight }
const deskLampLights = []
const raycaster = new THREE.Raycaster()
const mouse = new THREE.Vector2()

async function loadSceneData() {
  try {
    const [sceneRes, tasksRes] = await Promise.all([
      fetch('http://127.0.0.1:8080/api/v1/office/scene'),
      fetch('http://127.0.0.1:8080/api/v1/tasks'),
    ])

    if (sceneRes.ok) {
      const json = await sceneRes.json()
      if (json.data && json.data.characters) {
        charactersData.value = json.data.characters
      }
    }
    if (tasksRes.ok) {
      const json = await tasksRes.json()
      if (json.data) {
        tasksData.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load scene data:', err)
  } finally {
    isLoading.value = false
    initThree()
  }
}

function initThree() {
  const container = canvasContainer.value
  if (!container) return

  // 1. Scene with Warm Cozy Background Gradient
  scene = new THREE.Scene()
  updateSceneBackground()

  // 2. Orthographic Isometric Camera (Pure Kantor-AI Perspective)
  const aspect = container.clientWidth / container.clientHeight
  const frustumSize = 42
  camera = new THREE.OrthographicCamera(
    (-frustumSize * aspect) / 2,
    (frustumSize * aspect) / 2,
    frustumSize / 2,
    -frustumSize / 2,
    0.1,
    2000
  )
  camera.position.set(-70, 75, 70)
  camera.lookAt(0, 0, 10)

  // 3. WebGL Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.1
  container.appendChild(renderer.domElement)

  // 4. Lighting Rig
  setupLighting()

  // 5. Build Environment and All 18 Divisional Offices
  buildCampusEnvironment()
  buildAllDivisionalOffices()

  // 6. Listeners
  container.addEventListener('mousemove', onMouseMove)
  container.addEventListener('click', onClick)
  window.addEventListener('resize', onWindowResize)

  // 7. Start Animation Loop
  animate()
}

function updateSceneBackground() {
  if (isNightMode.value) {
    scene.background = new THREE.Color(0x0b0d19)
    scene.fog = new THREE.Fog(0x0b0d19, 90, 300)
  } else if (activeGaya.value === 'hologram') {
    scene.background = new THREE.Color(0x040c1a)
    scene.fog = new THREE.Fog(0x040c1a, 80, 260)
  } else {
    scene.background = new THREE.Color(0xf6ede2)
    scene.fog = new THREE.Fog(0xf6ede2, 100, 320)
  }
}

function setupLighting() {
  if (lightingGroup) scene.remove(lightingGroup)
  lightingGroup = new THREE.Group()

  const isNight = isNightMode.value
  const isHolo = activeGaya.value === 'hologram'

  if (isNight || isHolo) {
    const hemi = new THREE.HemisphereLight(0x38bdf8, 0x0f172a, 0.45)
    lightingGroup.add(hemi)

    const moon = new THREE.DirectionalLight(0x93c5fd, 0.6)
    moon.position.set(-50, 90, 50)
    lightingGroup.add(moon)

    const amb = new THREE.AmbientLight(0x1e1b4b, 0.5)
    lightingGroup.add(amb)
  } else {
    const hemi = new THREE.HemisphereLight(0xfffbeb, 0xe2e8f0, 0.85)
    lightingGroup.add(hemi)

    const sun = new THREE.DirectionalLight(0xfff7ed, 1.35)
    sun.position.set(-60, 95, 60)
    sun.castShadow = true
    sun.shadow.mapSize.width = 2048
    sun.shadow.mapSize.height = 2048
    sun.shadow.camera.left = -120
    sun.shadow.camera.right = 120
    sun.shadow.camera.top = 120
    sun.shadow.camera.bottom = -120
    sun.shadow.camera.near = 10
    sun.shadow.camera.far = 300
    lightingGroup.add(sun)

    const amb = new THREE.AmbientLight(0xffffff, 0.35)
    lightingGroup.add(amb)
  }

  scene.add(lightingGroup)
}

// -------------------------------------------------------------
// ENVIRONMENT: Island Lawn, Stepping Stones, Puffy Clouds
// -------------------------------------------------------------
function buildCampusEnvironment() {
  // Broad Island Base (Soft Green Lawn)
  const lawnGeo = new THREE.PlaneGeometry(320, 260)
  const lawnColor = isNightMode.value ? 0x14281d : activeGaya.value === 'hologram' ? 0x030a16 : 0x8cc672
  const lawnMat = new THREE.MeshStandardMaterial({ color: lawnColor, roughness: 0.9 })
  const lawn = new THREE.Mesh(lawnGeo, lawnMat)
  lawn.rotation.x = -Math.PI / 2
  lawn.position.set(0, -0.15, 12)
  lawn.receiveShadow = true
  scene.add(lawn)

  // Stylized Round Puffy Trees around the campus boundary
  const trunkMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.8 })
  const leavesMat = new THREE.MeshStandardMaterial({ color: 0x4f9d4a, roughness: 0.6 })
  const flowerMat = new THREE.MeshStandardMaterial({ color: 0xf472b6, roughness: 0.5 })

  const treePositions = [
    [-75, -45], [-75, 0], [-75, 45], [-75, 80],
    [75, -45], [75, 0], [75, 45], [75, 80],
    [-45, -50], [0, -50], [45, -50],
    [-45, 85], [0, 85], [45, 85],
  ]

  treePositions.forEach(([tx, tz], i) => {
    const tg = new THREE.Group()
    tg.position.set(tx, 0, tz)

    const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.35, 0.5, 3.2, 8), trunkMat)
    trunk.position.y = 1.6
    trunk.castShadow = true
    tg.add(trunk)

    const crown = new THREE.Mesh(new THREE.SphereGeometry(2.4, 16, 16), i % 3 === 0 ? flowerMat : leavesMat)
    crown.position.y = 4.2
    crown.castShadow = true
    tg.add(crown)

    const crownMini = new THREE.Mesh(new THREE.SphereGeometry(1.6, 12, 12), leavesMat)
    crownMini.position.set(1.2, 3.6, 0.8)
    crownMini.castShadow = true
    tg.add(crownMini)

    scene.add(tg)
  })

  // Floating Puffy White Clouds in the Isometric Sky
  const cloudMat = new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.85 })
  ;[[-60, 35, -40], [40, 42, -50], [70, 38, 20], [-50, 40, 60]].forEach(([cx, cy, cz]) => {
    const cg = new THREE.Group()
    cg.position.set(cx, cy, cz)

    const b1 = new THREE.Mesh(new THREE.SphereGeometry(4.5, 12, 12), cloudMat)
    const b2 = new THREE.Mesh(new THREE.SphereGeometry(3.2, 12, 12), cloudMat)
    b2.position.set(3.5, -0.6, 1)
    const b3 = new THREE.Mesh(new THREE.SphereGeometry(3.0, 12, 12), cloudMat)
    b3.position.set(-3.2, -0.8, -0.8)

    cg.add(b1, b2, b3)
    scene.add(cg)
  })
}

// -------------------------------------------------------------
// SETIAP DEVISI MEMILIKI KANTORNYA MASING-MASING (Kantor-AI Style)
// -------------------------------------------------------------
function buildAllDivisionalOffices() {
  officesGroup = new THREE.Group()
  scene.add(officesGroup)

  divisionsConfig.forEach((div) => {
    const divAgents = charactersData.value.filter((c) => c.division_id === div.id)
    const agentCount = divAgents.length || 6

    const office = createDivisionalOffice(div, divAgents, agentCount)
    officeGroups.set(div.id, office)
    officesGroup.add(office)
  })
}

/**
 * Creates one standalone isometric office in the exact Kantor-AI template style:
 * - Parquet wooden floor with plank seams
 * - Division-colored carpet rug
 * - Cutaway back and side architectural walls with windows and wood trims
 * - Desks with wooden tops, monitors, keyboard pads, coffee mugs, and desk lamps
 * - Mission Board on the wall with colorful sticky notes
 * - Cozy lounge sofa, coffee table, espresso coffee maker station
 * - Bookshelf filled with colorful books, and terracotta potted plants
 */
function createDivisionalOffice(div, divAgents, agentCount) {
  const g = new THREE.Group()
  g.position.set(div.x, 0, div.z)

  // 1. Proportional Dimensions based on headcount
  let cols, rows
  if (agentCount <= 6) {
    cols = 3
    rows = Math.ceil(agentCount / cols)
  } else if (agentCount <= 12) {
    cols = 4
    rows = Math.ceil(agentCount / cols)
  } else if (agentCount <= 24) {
    cols = 6
    rows = Math.ceil(agentCount / cols)
  } else {
    cols = 8 // For 59 and 66 agents
    rows = Math.ceil(agentCount / cols)
  }

  // Office width and depth scaling
  const roomW = Math.max(16, cols * 2.8 + 6)
  const roomD = Math.max(14, rows * 2.6 + 6.5)
  const wallH = 4.8

  const isNight = isNightMode.value
  const isHolo = activeGaya.value === 'hologram'

  // Materials
  const floorWood = isHolo ? 0x041022 : isNight ? 0x4a3d31 : 0xe9d9c4
  const floorMat = new THREE.MeshStandardMaterial({ color: floorWood, roughness: 0.6 })
  const rugMat = new THREE.MeshStandardMaterial({
    color: isHolo ? 0x0e7490 : div.rug || div.color,
    roughness: 0.85,
  })
  const wallMat = new THREE.MeshStandardMaterial({
    color: isHolo ? 0x082f49 : isNight ? 0x1e1b2e : 0xfbf6ef,
    roughness: 0.7,
  })
  const wallSideMat = new THREE.MeshStandardMaterial({
    color: isHolo ? 0x0369a1 : isNight ? 0x181528 : 0xf1e8dc,
    roughness: 0.75,
  })
  const trimWood = new THREE.MeshStandardMaterial({ color: isHolo ? 0x22d3ee : 0xa87c56, roughness: 0.5 })
  const darkBaseMat = new THREE.MeshStandardMaterial({ color: 0x1b1330, roughness: 0.5 })

  // 2. Heavy Raised Plinth Base
  const base = new THREE.Mesh(new THREE.BoxGeometry(roomW + 1.2, 0.45, roomD + 1.2), darkBaseMat)
  base.position.y = 0.22
  base.receiveShadow = true
  g.add(base)

  // 3. Parquet Floor with Planks Seams
  const floor = new THREE.Mesh(new THREE.BoxGeometry(roomW, 0.1, roomD), floorMat)
  floor.position.y = 0.5
  floor.receiveShadow = true
  g.add(floor)

  // Division-Colored Carpet Rug in the central workstation zone
  const rugW = roomW * 0.85
  const rugD = roomD * 0.72
  const rug = new THREE.Mesh(new THREE.BoxGeometry(rugW, 0.02, rugD), rugMat)
  rug.position.set(0, 0.56, 1.2)
  rug.receiveShadow = true
  g.add(rug)

  // 4. Back Wall with Glass Windows & Wood Baseboard
  const backWallZ = -roomD / 2
  const backWall = new THREE.Mesh(new THREE.BoxGeometry(roomW, wallH, 0.2), wallMat)
  backWall.position.set(0, wallH / 2 + 0.5, backWallZ)
  backWall.receiveShadow = true
  backWall.castShadow = true
  g.add(backWall)

  // Wood Baseboard
  const baseboard = new THREE.Mesh(new THREE.BoxGeometry(roomW, 0.25, 0.25), trimWood)
  baseboard.position.set(0, 0.62, backWallZ + 0.12)
  g.add(baseboard)

  // Left Wall (Cutaway Architecture)
  const leftWallX = -roomW / 2
  const leftWall = new THREE.Mesh(new THREE.BoxGeometry(0.2, wallH, roomD), wallSideMat)
  leftWall.position.set(leftWallX, wallH / 2 + 0.5, 0)
  leftWall.receiveShadow = true
  leftWall.castShadow = true
  g.add(leftWall)

  // Left Baseboard
  const baseboardL = new THREE.Mesh(new THREE.BoxGeometry(0.25, 0.25, roomD), trimWood)
  baseboardL.position.set(leftWallX + 0.12, 0.62, 0)
  g.add(baseboardL)

  // Glass Windows on Back Wall
  const glassMat = new THREE.MeshPhysicalMaterial({
    color: isNight ? 0x1e1b4b : 0xbfe3ff,
    transparent: true,
    opacity: 0.5,
    roughness: 0.1,
    transmission: 0.8,
  })
  const windowFrameMat = new THREE.MeshStandardMaterial({ color: 0x271b3f, roughness: 0.4 })

  const windowCount = Math.max(2, Math.floor(roomW / 6))
  const windowSpacing = roomW / (windowCount + 1)

  for (let i = 1; i <= windowCount; i++) {
    const wx = -roomW / 2 + i * windowSpacing
    const win = new THREE.Mesh(new THREE.BoxGeometry(2.4, 1.8, 0.08), glassMat)
    win.position.set(wx, 3.2, backWallZ + 0.12)
    g.add(win)

    const frame = new THREE.Mesh(new THREE.BoxGeometry(2.55, 1.95, 0.12), windowFrameMat)
    frame.position.set(wx, 3.2, backWallZ + 0.08)
    g.add(frame)
  }

  // 5. Mission Board on the Back Wall with Colorful Sticky Notes
  const boardW = Math.min(6.5, roomW * 0.4)
  const boardH = 2.0
  const boardFrame = new THREE.Mesh(new THREE.BoxGeometry(boardW, boardH, 0.1), trimWood)
  boardFrame.position.set(0, 3.0, backWallZ + 0.12)
  g.add(boardFrame)

  const boardSurface = new THREE.Mesh(
    new THREE.BoxGeometry(boardW - 0.2, boardH - 0.2, 0.04),
    new THREE.MeshStandardMaterial({ color: isHolo ? 0x0284c7 : 0xf7f2ea, roughness: 0.8 })
  )
  boardSurface.position.set(0, 3.0, backWallZ + 0.18)
  g.add(boardSurface)

  // Sticky Notes on Mission Board
  const noteColors = [0xffd166, 0xf3355b, 0x7bdff2, 0xb2f7ef, 0xf7aef8, 0xcdb4db, 0x10b981, 0x38bdf8]
  for (let ni = 0; ni < 6; ni++) {
    const noteMat = new THREE.MeshBasicMaterial({ color: noteColors[ni % noteColors.length] })
    const note = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.4, 0.02), noteMat)
    const nx = -boardW / 2 + 0.8 + (ni % 3) * 0.85
    const ny = 3.3 - Math.floor(ni / 3) * 0.6
    note.position.set(nx, ny, backWallZ + 0.22)
    note.rotation.z = (Math.random() - 0.5) * 0.15
    g.add(note)
  }

  // 6. Bookshelf & Storage Credenza (Left Wall)
  const shelf = createBookshelf()
  shelf.position.set(leftWallX + 0.45, 0.55, -roomD * 0.2)
  shelf.rotation.y = Math.PI / 2
  g.add(shelf)

  // 7. Lounge & Break Station: Sofa, Coffee Table, Espresso Maker
  const sofaGroup = createLoungeArea(div.color)
  sofaGroup.position.set(roomW * 0.35, 0.55, roomD * 0.32)
  g.add(sofaGroup)

  // Potted Indoor Plants
  const plant1 = createPottedPlant()
  plant1.position.set(leftWallX + 0.8, 0.55, roomD * 0.35)
  g.add(plant1)

  const plant2 = createPottedPlant()
  plant2.position.set(roomW * 0.42, 0.55, backWallZ + 0.8)
  g.add(plant2)

  // 8. Workstation Desks & Animated Agents
  const desksGroup = new THREE.Group()
  g.add(desksGroup)

  const startX = -((cols - 1) * 2.8) / 2
  const startZ = -roomD * 0.15

  divAgents.forEach((char, idx) => {
    const col = idx % cols
    const row = Math.floor(idx / cols)

    const deskX = startX + col * 2.8
    const deskZ = startZ + row * 2.5

    // Absolute position in scene for search & camera focus
    char.position_x = div.x + deskX
    char.position_y = 0.55
    char.position_z = div.z + deskZ

    const deskActor = createWorkstationActor(char, div.color, idx)
    deskActor.root.position.set(deskX, 0.55, deskZ)
    desksGroup.add(deskActor.root)

    animatedActors.push(deskActor)
  })

  // 9. Floating 3D Glowing Signboard over the office
  const signGroup = createOfficeSignboard(div.name, agentCount, div.color, roomW)
  signGroup.position.set(0, wallH + 1.8, backWallZ + 0.5)
  g.add(signGroup)

  // Hitbox for Clicking Office
  const hitBox = new THREE.Mesh(
    new THREE.BoxGeometry(roomW, wallH + 2, roomD),
    new THREE.MeshBasicMaterial({ visible: false })
  )
  hitBox.position.set(0, wallH / 2, 0)
  hitBox.userData = { divisionId: div.id, group: g }
  g.add(hitBox)

  return g
}

/**
 * Creates an animated workstation actor (Desk, Chair, Laptop, Desk Lamp, Stylized Agent)
 * with animated arms typing on keyboard and glowing screen!
 */
function createWorkstationActor(char, divColor, idx) {
  const root = new THREE.Group()

  const deskWoodMat = new THREE.MeshStandardMaterial({ color: 0xc9a27c, roughness: 0.45 })
  const metalLegMat = new THREE.MeshStandardMaterial({ color: 0x4b3b62, metalness: 0.8 })
  const chairMat = new THREE.MeshStandardMaterial({ color: 0x3a2a5c, roughness: 0.6 })
  const paperMat = new THREE.MeshStandardMaterial({ color: 0xefe9f6 })
  const potMat = new THREE.MeshStandardMaterial({ color: new THREE.Color(divColor) })

  // Desk Surface
  const deskTop = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.08, 0.85), deskWoodMat)
  deskTop.position.set(0, 0.78, 0)
  deskTop.castShadow = true
  deskTop.receiveShadow = true
  root.add(deskTop)

  // Desk Legs
  ;[[-0.72, -0.36], [0.72, -0.36], [-0.72, 0.36], [0.72, 0.36]].forEach(([lx, lz]) => {
    const leg = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.76, 0.06), metalLegMat)
    leg.position.set(lx, 0.38, lz)
    root.add(leg)
  })

  // Laptop / Curved Monitor
  const screenMat = new THREE.MeshStandardMaterial({
    color: 0x1b1330,
    emissive: 0x66e3a5,
    emissiveIntensity: 0.75,
  })
  const screen = new THREE.Mesh(new THREE.BoxGeometry(0.68, 0.42, 0.02), screenMat)
  screen.position.set(0, 1.15, -0.25)
  root.add(screen)

  const screenStand = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.25, 0.12), metalLegMat)
  screenStand.position.set(0, 0.92, -0.25)
  root.add(screenStand)

  // Keyboard / Paper Sheet
  const keyboard = new THREE.Mesh(new THREE.BoxGeometry(0.55, 0.02, 0.22), paperMat)
  keyboard.position.set(0, 0.83, 0.1)
  root.add(keyboard)

  // Mini Desk Plant / Mug in division color
  const mug = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 0.12, 8), potMat)
  mug.position.set(0.55, 0.88, 0.15)
  root.add(mug)

  // Cozy Desk Lamp with point light at night
  const lampMat = new THREE.MeshStandardMaterial({
    color: 0xfff3b0,
    emissive: 0xffb35c,
    emissiveIntensity: isNightMode.value ? 1.0 : 0.2,
  })
  const lampHead = new THREE.Mesh(new THREE.SphereGeometry(0.1, 8, 8), lampMat)
  lampHead.position.set(-0.55, 1.12, -0.2)
  root.add(lampHead)

  const lampPole = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.3, 0.04), metalLegMat)
  lampPole.position.set(-0.55, 0.98, -0.2)
  root.add(lampPole)

  let pointLight = null
  if (isNightMode.value) {
    pointLight = new THREE.PointLight(0xffb35c, 1.8, 3.5, 1.5)
    pointLight.position.set(-0.55, 1.3, -0.1)
    root.add(pointLight)
    deskLampLights.push(pointLight)
  }

  // Ergonomic Office Chair
  const chairSeat = new THREE.Mesh(new THREE.BoxGeometry(0.6, 0.08, 0.6), chairMat)
  chairSeat.position.set(0, 0.52, 0.72)
  root.add(chairSeat)

  const chairBack = new THREE.Mesh(new THREE.BoxGeometry(0.6, 0.65, 0.08), chairMat)
  chairBack.position.set(0, 0.88, 1.0)
  root.add(chairBack)

  // Stylized Humanoid Agent (Chibi proportions from kantor-ai-template)
  const agentColor = char.agent_color || divColor
  const skinTones = [0xf1c7a5, 0xd9a07b, 0xc68863, 0xe8b896, 0xb97a56]
  const hairTones = [0x2b1d16, 0x1b1330, 0x5a3825, 0x7a4a2a]

  const agentGroup = new THREE.Group()
  agentGroup.position.set(0, 0, 0.68)

  const shirtMat = new THREE.MeshStandardMaterial({ color: new THREE.Color(agentColor), roughness: 0.5 })
  const pantsMat = new THREE.MeshStandardMaterial({ color: 0x2b2340, roughness: 0.6 })
  const skinMat = new THREE.MeshStandardMaterial({ color: skinTones[idx % skinTones.length], roughness: 0.4 })
  const hairMat = new THREE.MeshStandardMaterial({ color: hairTones[idx % hairTones.length], roughness: 0.4 })

  // Torso
  const body = new THREE.Mesh(new THREE.CylinderGeometry(0.24, 0.28, 0.5, 12), shirtMat)
  body.position.y = 0.92
  agentGroup.add(body)

  // Head with Hair
  const head = new THREE.Mesh(new THREE.SphereGeometry(0.25, 16, 16), skinMat)
  head.position.y = 1.38
  agentGroup.add(head)

  const hair = new THREE.Mesh(new THREE.SphereGeometry(0.265, 16, 16, 0, Math.PI * 2, 0, Math.PI / 1.7), hairMat)
  hair.rotation.x = 0.3
  hair.position.y = 0.02
  head.add(hair)

  // Seated Legs (Bent forward)
  const legL = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.08, 0.42, 8), pantsMat)
  legL.position.set(-0.12, 0.55, 0)
  legL.rotation.x = -1.35
  agentGroup.add(legL)

  const legR = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.08, 0.42, 8), pantsMat)
  legR.position.set(0.12, 0.55, 0)
  legR.rotation.x = -1.35
  agentGroup.add(legR)

  // Arms typing on keyboard
  const armL = new THREE.Mesh(new THREE.CylinderGeometry(0.065, 0.065, 0.36, 8), shirtMat)
  armL.position.set(-0.28, 1.05, 0)
  armL.rotation.set(-1.15, 0, 0.1)
  agentGroup.add(armL)

  const armR = new THREE.Mesh(new THREE.CylinderGeometry(0.065, 0.065, 0.36, 8), shirtMat)
  armR.position.set(0.28, 1.05, 0)
  armR.rotation.set(-1.15, 0, -0.1)
  agentGroup.add(armR)

  root.add(agentGroup)

  // Raycast Hitbox for Clicking Agent
  const hitBox = new THREE.Mesh(
    new THREE.BoxGeometry(1.8, 1.8, 1.8),
    new THREE.MeshBasicMaterial({ visible: false })
  )
  hitBox.position.set(0, 0.9, 0.3)
  hitBox.userData = { character: char, root }
  root.add(hitBox)

  agentMeshes.push(hitBox)

  return {
    key: char.agent_slug,
    root,
    armL,
    armR,
    body,
    head,
    screenMat,
    lampMat,
    pointLight,
    phase: idx * 1.5,
  }
}

/**
 * Creates Bookshelf with Rows of Colorful Books
 */
function createBookshelf() {
  const g = new THREE.Group()
  const woodMat = new THREE.MeshStandardMaterial({ color: 0xa87c56, roughness: 0.5 })
  const frame = new THREE.Mesh(new THREE.BoxGeometry(1.4, 2.2, 0.4), woodMat)
  frame.position.y = 1.1
  g.add(frame)

  const bookColors = [0xe4572e, 0x2a9d8f, 0xf2b134, 0x3a86ff, 0x8e6bd8, 0xff5d8f]
  for (let row = 0; row < 3; row++) {
    for (let i = 0; i < 5; i++) {
      const bMat = new THREE.MeshStandardMaterial({ color: bookColors[(i + row) % bookColors.length] })
      const book = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.42, 0.28), bMat)
      book.position.set(-0.45 + i * 0.22, 0.5 + row * 0.65, 0.05)
      g.add(book)
    }
  }
  return g
}

/**
 * Creates Lounge Area: Modular Sofa, Coffee Table, Espresso Station
 */
function createLoungeArea(accentColor) {
  const g = new THREE.Group()
  const sofaMat = new THREE.MeshStandardMaterial({ color: 0x5b4a72, roughness: 0.7 })
  const sofaBaseMat = new THREE.MeshStandardMaterial({ color: 0x3a2a5c })
  const woodMat = new THREE.MeshStandardMaterial({ color: 0xa87c56, roughness: 0.4 })
  const metalMat = new THREE.MeshStandardMaterial({ color: 0x271b3f, metalness: 0.8 })

  // 2-Seater Sofa
  const sofaSeat = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.35, 0.8), sofaMat)
  sofaSeat.position.set(0, 0.25, 0)
  g.add(sofaSeat)

  const sofaBack = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.55, 0.22), sofaBaseMat)
  sofaBack.position.set(0, 0.55, 0.4)
  g.add(sofaBack)

  // Coffee Table
  const table = new THREE.Mesh(new THREE.BoxGeometry(1.2, 0.3, 0.7), woodMat)
  table.position.set(0, 0.15, -0.9)
  g.add(table)

  // Espresso Coffee Maker Station on Credenza
  const credenza = new THREE.Mesh(new THREE.BoxGeometry(1.0, 0.85, 0.45), woodMat)
  credenza.position.set(1.8, 0.42, 0)
  g.add(credenza)

  const coffeeMachine = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.45, 0.3), metalMat)
  coffeeMachine.position.set(1.8, 1.05, 0)
  g.add(coffeeMachine)

  const cup = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.05, 0.12, 8), new THREE.MeshBasicMaterial({ color: 0xffffff }))
  cup.position.set(1.6, 0.9, -0.05)
  g.add(cup)

  return g
}

/**
 * Creates Terracotta Potted Indoor Plant
 */
function createPottedPlant() {
  const g = new THREE.Group()
  const potMat = new THREE.MeshStandardMaterial({ color: 0xc26b3b, roughness: 0.85 })
  const leafMat = new THREE.MeshStandardMaterial({ color: 0x3f9b4f, roughness: 0.6 })
  const leaf2Mat = new THREE.MeshStandardMaterial({ color: 0x5bbf5e, roughness: 0.5 })

  const pot = new THREE.Mesh(new THREE.CylinderGeometry(0.3, 0.22, 0.55, 12), potMat)
  pot.position.y = 0.28
  g.add(pot)

  const leaves = new THREE.Mesh(new THREE.ConeGeometry(0.48, 1.1, 8), leafMat)
  leaves.position.y = 1.0
  g.add(leaves)

  const leavesSphere = new THREE.Mesh(new THREE.SphereGeometry(0.35, 8, 8), leaf2Mat)
  leavesSphere.position.set(0.15, 0.8, 0.1)
  g.add(leavesSphere)

  return g
}

/**
 * Creates Floating 3D Glowing Signboard with Headcount Badge
 */
function createOfficeSignboard(name, count, colorHex, roomW) {
  const signGroup = new THREE.Group()
  const boardW = Math.max(8.5, roomW * 0.45)

  const canvas = document.createElement('canvas')
  canvas.width = 512
  canvas.height = 140
  const ctx = canvas.getContext('2d')

  // Background
  ctx.fillStyle = '#0f172a'
  ctx.fillRect(0, 0, 512, 140)

  // Colored border
  ctx.strokeStyle = colorHex
  ctx.lineWidth = 8
  ctx.strokeRect(4, 4, 504, 132)

  // Title
  ctx.fillStyle = '#ffffff'
  ctx.font = 'bold 36px "Plus Jakarta Sans", sans-serif'
  ctx.textAlign = 'center'
  ctx.fillText(name.toUpperCase(), 256, 56)

  // Badge
  ctx.fillStyle = colorHex
  ctx.font = 'bold 26px monospace'
  ctx.fillText(`👥 ${count} AGEN • TERINTEGRASI 9ROUTER`, 256, 106)

  const texture = new THREE.CanvasTexture(canvas)
  const signBoard = new THREE.Mesh(
    new THREE.BoxGeometry(boardW, 1.8, 0.15),
    new THREE.MeshBasicMaterial({ map: texture })
  )
  signGroup.add(signBoard)

  return signGroup
}

// -------------------------------------------------------------
// NAVIGATION & DIVISION FOCUS
// -------------------------------------------------------------
function selectDivision(divId) {
  selectedDivision.value = divId

  if (divId === 'all') {
    resetCamera()
    return
  }

  const div = divisionsConfig.find((d) => d.id === divId)
  if (div) {
    focusOnDivision(div)
  }
}

function focusOnDivision(div) {
  gsap.to(camera.position, {
    x: div.x - 22,
    y: 24,
    z: div.z + 22,
    duration: 1.6,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(div.x, 2, div.z),
  })

  // Adjust zoom for intimate isometric view of the selected office
  gsap.to(camera, {
    zoom: 1.85,
    duration: 1.6,
    ease: 'power3.inOut',
    onUpdate: () => camera.updateProjectionMatrix(),
  })
}

function resetCamera() {
  selectedDivision.value = 'all'
  gsap.to(camera.position, {
    x: -70,
    y: 75,
    z: 70,
    duration: 1.6,
    ease: 'power2.out',
    onUpdate: () => camera.lookAt(0, 0, 10),
  })
  gsap.to(camera, {
    zoom: 1.0,
    duration: 1.6,
    ease: 'power2.out',
    onUpdate: () => camera.updateProjectionMatrix(),
  })
}

function toggleDayNight() {
  isNightMode.value = !isNightMode.value
  setupLighting()
  updateSceneBackground()

  // Update Desk Lamps
  deskLampLights.forEach((light) => {
    light.intensity = isNightMode.value ? 1.8 : 0
  })

  // Re-build all offices to apply day/night materials
  if (officesGroup) {
    scene.remove(officesGroup)
    buildAllDivisionalOffices()
  }
}

function toggleGaya() {
  activeGaya.value = activeGaya.value === 'asli' ? 'hologram' : 'asli'
  setupLighting()
  updateSceneBackground()

  if (officesGroup) {
    scene.remove(officesGroup)
    buildAllDivisionalOffices()
  }
}

function onSearchAgentClick(char) {
  searchQuery.value = ''
  selectedDivision.value = char.division_id

  const div = divisionsConfig.find((d) => d.id === char.division_id)
  if (div) {
    focusOnDivision(div)
  }

  emit('select-agent', char)
}

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
    container.style.cursor = 'pointer'
  } else {
    hoveredCharacter.value = null
    container.style.cursor = 'default'
  }
}

function onClick() {
  raycaster.setFromCamera(mouse, camera)
  const intersects = raycaster.intersectObjects(agentMeshes)

  if (intersects.length > 0) {
    const char = intersects[0].object.userData.character
    emit('select-agent', char)
  }
}

function onWindowResize() {
  const container = canvasContainer.value
  if (!container || !renderer || !camera) return
  const aspect = container.clientWidth / container.clientHeight
  const frustumSize = 42

  camera.left = (-frustumSize * aspect) / 2
  camera.right = (frustumSize * aspect) / 2
  camera.top = frustumSize / 2
  camera.bottom = -frustumSize / 2
  camera.updateProjectionMatrix()

  renderer.setSize(container.clientWidth, container.clientHeight)
}

// -------------------------------------------------------------
// ANIMATION LOOP (Typing hands, breathing, screen glow)
// -------------------------------------------------------------
function animate() {
  animationFrameId = requestAnimationFrame(animate)

  const time = performance.now() * 0.001

  // Animate Agents: Typing on Keyboard & Subtle Breathing
  animatedActors.forEach((actor) => {
    const phase = actor.phase
    // Typing Arms Alternating Kinematics
    actor.armL.rotation.x = -1.15 + 0.14 * Math.sin(time * 14 + phase)
    actor.armR.rotation.x = -1.15 + 0.14 * Math.sin(time * 14 + phase + 2.2)

    // Subtle breathing on torso
    actor.body.position.y = 0.92 + 0.012 * Math.abs(Math.sin(time * 3 + phase))

    // Subtle head tilt
    actor.head.rotation.x = 0.08 + 0.03 * Math.sin(time * 2.5 + phase)
    actor.head.rotation.y = 0.06 * Math.sin(time * 1.2 + phase)

    // Screen light pulse
    if (actor.screenMat) {
      actor.screenMat.emissiveIntensity = 0.7 + 0.2 * Math.sin(time * 8 + phase)
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

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
