<template>
  <div class="relative w-full h-full overflow-hidden select-none">
    <!-- Three.js Canvas Container -->
    <div ref="canvasContainer" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

    <!-- Loading Overlay -->
    <div
      v-if="isLoading"
      class="absolute inset-0 z-20 bg-slate-950/90 backdrop-blur-md flex flex-col items-center justify-center gap-4 text-white"
    >
      <div class="w-12 h-12 rounded-full border-4 border-sky-500/30 border-t-sky-400 animate-spin"></div>
      <div class="text-center">
        <h3 class="font-bold text-sm tracking-wide">Memuat Kantor 3D MCE-eOffice...</h3>
        <p class="text-xs text-slate-400 mt-1">Mengambil 283 workstation agen dari CodeIgniter 4 API</p>
      </div>
    </div>

    <!-- Search & Quick Navigation Overlay (Top Left) -->
    <div class="absolute top-4 left-6 z-10 w-72">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari agen (misal: architect, qa, rust)..."
          class="w-full bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-sky-500 shadow-xl transition-all"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>

      <!-- Search Suggestions Dropdown -->
      <div
        v-if="filteredAgents.length > 0 && searchQuery.trim()"
        class="mt-2 max-h-60 overflow-y-auto bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl shadow-2xl p-1.5 space-y-1"
      >
        <button
          v-for="char in filteredAgents.slice(0, 8)"
          :key="char.character_id"
          @click="selectAgentFromSearch(char)"
          class="w-full px-3 py-2 text-left rounded-xl hover:bg-slate-800/80 flex items-center justify-between transition-all text-xs"
        >
          <div class="flex items-center gap-2">
            <span class="text-sm">{{ char.agent_emoji }}</span>
            <div>
              <div class="font-semibold text-white">{{ char.agent_name }}</div>
              <div class="text-[10px] text-slate-400">{{ char.division_label }} • Lt {{ char.floor_level }}</div>
            </div>
          </div>
          <span class="text-[10px] font-mono text-sky-400 font-bold">{{ char.desk_code }}</span>
        </button>
      </div>
    </div>

    <!-- 3D Scene Controls Overlay (Bottom Left) -->
    <div class="absolute bottom-6 left-6 z-10 flex flex-col gap-3">
      <!-- Floor Selector -->
      <div class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 rounded-2xl p-2.5 shadow-2xl flex items-center gap-2">
        <span class="text-xs font-semibold uppercase text-slate-400 px-2">Lantai:</span>
        <button
          v-for="floor in floorList"
          :key="floor.level"
          @click="changeFloor(floor.level)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all duration-300 flex items-center gap-1.5',
            currentFloor === floor.level
              ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold'
              : 'text-slate-300 hover:bg-slate-800 hover:text-white'
          ]"
        >
          <span>Lt {{ floor.level }}: {{ floor.shortLabel }}</span>
        </button>
      </div>

      <!-- Camera Reset Button & Active Floor Info -->
      <div class="flex items-center gap-2">
        <button
          @click="resetCamera"
          class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 hover:border-slate-500 text-slate-200 px-3.5 py-2 rounded-xl text-xs font-medium shadow-xl hover:bg-slate-800 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          Reset Kamera
        </button>

        <div class="bg-slate-900/85 backdrop-blur-md border border-slate-700/70 text-slate-400 px-3.5 py-2 rounded-xl text-xs font-mono shadow-xl flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span>{{ currentFloorInfo?.name || 'Kantor 3D' }}</span>
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
          {{ hoveredCharacter.division_label }} • Meja {{ hoveredCharacter.desk_code }} (Lt {{ hoveredCharacter.floor_level }})
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
const currentFloor = ref(2) // Default Lantai 2: Engineering
const hoveredCharacter = ref(null)
const tooltipPos = ref({ x: 0, y: 0 })
const searchQuery = ref('')

const charactersData = ref([])
const floorList = [
  { level: 1, shortLabel: 'Ops & Client', name: 'Lantai 1: Public, Operations & Client Facing', heightY: 0 },
  { level: 2, shortLabel: 'Engineering', name: 'Lantai 2: Engineering & Deep Infrastructure', heightY: 14 },
  { level: 3, shortLabel: 'Product & Design', name: 'Lantai 3: Product, Design & Creative Studio', heightY: 28 },
  { level: 4, shortLabel: 'Executive & XR', name: 'Lantai 4: Strategy, Spatial & Innovation', heightY: 42 },
]

const currentFloorInfo = computed(() => {
  return floorList.find((f) => f.level === currentFloor.value)
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

let scene, camera, renderer, animationFrameId
const agentMeshes = []
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
    console.error('Failed to load office scene from API, using fallback data:', err)
  } finally {
    isLoading.value = false
    initThree()
  }
}

function initThree() {
  const container = canvasContainer.value
  if (!container) return

  // 1. Scene & Environment
  scene = new THREE.Scene()
  scene.background = new THREE.Color(0x060b16)
  scene.fog = new THREE.FogExp2(0x060b16, 0.012)

  // 2. Camera Setup
  const aspect = container.clientWidth / container.clientHeight
  camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 2000)
  camera.position.set(0, 32, 55)
  camera.lookAt(0, 14, 0)

  // 3. High Fidelity WebGL Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.3
  container.appendChild(renderer.domElement)

  // 4. Lighting Rig
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.9)
  scene.add(ambientLight)

  const dirLight = new THREE.DirectionalLight(0xbae6fd, 2.8)
  dirLight.position.set(35, 60, 45)
  dirLight.castShadow = true
  dirLight.shadow.mapSize.width = 2048
  dirLight.shadow.mapSize.height = 2048
  scene.add(dirLight)

  const accentCyan = new THREE.PointLight(0x06b6d4, 4, 100)
  accentCyan.position.set(-30, 30, 20)
  scene.add(accentCyan)

  const accentPurple = new THREE.PointLight(0x8b5cf6, 4, 100)
  accentPurple.position.set(30, 45, -20)
  scene.add(accentPurple)

  // 5. Build 4 Architectural Floors
  buildOfficeFloors()

  // 6. Spawn Workstation Desks and Avatars
  spawnCharacters()

  // 7. Event Listeners
  window.addEventListener('resize', onWindowResize)
  container.addEventListener('mousemove', onMouseMove)
  container.addEventListener('click', onClick)

  // 8. Start Loop
  animate()
}

function buildOfficeFloors() {
  floorList.forEach((f) => {
    const floorGroup = new THREE.Group()
    floorGroup.position.y = f.heightY

    // Main Architectural Floor Slab (W: 76, H: 0.8, D: 56)
    const slabGeo = new THREE.BoxGeometry(76, 0.8, 56)
    const slabMat = new THREE.MeshStandardMaterial({
      color: 0x0f172a,
      roughness: 0.35,
      metalness: 0.15,
    })
    const slab = new THREE.Mesh(slabGeo, slabMat)
    slab.position.y = -0.4
    slab.receiveShadow = true
    floorGroup.add(slab)

    // Floor Grid Surface
    const grid = new THREE.GridHelper(76, 38, 0x0284c7, 0x1e293b)
    grid.position.y = 0.02
    floorGroup.add(grid)

    // Perimeter Neon Light Strip
    const borderGeo = new THREE.BoxGeometry(76.2, 0.1, 56.2)
    const borderMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8, wireframe: true })
    const border = new THREE.Mesh(borderGeo, borderMat)
    border.position.y = 0.05
    floorGroup.add(border)

    // Modern Glass Railings
    const railingMat = new THREE.MeshPhysicalMaterial({
      color: 0xe0f2fe,
      transparent: true,
      opacity: 0.25,
      roughness: 0.1,
      transmission: 0.85,
      thickness: 0.4,
    })

    const northRail = new THREE.Mesh(new THREE.BoxGeometry(76, 1.4, 0.2), railingMat)
    northRail.position.set(0, 0.7, -28)
    floorGroup.add(northRail)

    const southRail = new THREE.Mesh(new THREE.BoxGeometry(76, 1.4, 0.2), railingMat)
    southRail.position.set(0, 0.7, 28)
    floorGroup.add(southRail)

    const westRail = new THREE.Mesh(new THREE.BoxGeometry(0.2, 1.4, 56), railingMat)
    westRail.position.set(-38, 0.7, 0)
    floorGroup.add(westRail)

    const eastRail = new THREE.Mesh(new THREE.BoxGeometry(0.2, 1.4, 56), railingMat)
    eastRail.position.set(38, 0.7, 0)
    floorGroup.add(eastRail)

    // Structural Corner Pillars
    const pillarGeo = new THREE.CylinderGeometry(0.5, 0.5, 13.5, 16)
    const pillarMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.8, roughness: 0.3 })
    ;[
      [-37, -27], [37, -27], [-37, 27], [37, 27],
      [0, -27], [0, 27]
    ].forEach(([px, pz]) => {
      const pillar = new THREE.Mesh(pillarGeo, pillarMat)
      pillar.position.set(px, 6.75, pz)
      pillar.castShadow = true
      floorGroup.add(pillar)
    })

    scene.add(floorGroup)
  })
}

function spawnCharacters() {
  charactersData.value.forEach((char) => {
    const group = new THREE.Group()
    group.position.set(char.position_x, char.position_y, char.position_z)

    const agentColor = char.agent_color || '#3B82F6'

    // 1. Modern Minimalist Workstation Desk
    const deskGeo = new THREE.BoxGeometry(3.2, 0.12, 1.8)
    const deskMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3 })
    const desk = new THREE.Mesh(deskGeo, deskMat)
    desk.position.y = 0.9
    desk.castShadow = true
    desk.receiveShadow = true
    group.add(desk)

    // Desk Frame Legs
    const legGeo = new THREE.CylinderGeometry(0.05, 0.05, 0.9, 8)
    const legMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.9 })
    ;[[-1.5, -0.8], [1.5, -0.8], [-1.5, 0.8], [1.5, 0.8]].forEach(([lx, lz]) => {
      const leg = new THREE.Mesh(legGeo, legMat)
      leg.position.set(lx, 0.45, lz)
      group.add(leg)
    })

    // Glowing Curved Monitor
    const screenGeo = new THREE.BoxGeometry(1.6, 0.9, 0.06)
    const screenMat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(agentColor),
      emissive: new THREE.Color(agentColor),
      emissiveIntensity: 0.6,
    })
    const screen = new THREE.Mesh(screenGeo, screenMat)
    screen.position.set(0, 1.45, -0.4)
    group.add(screen)

    // 2. Stylized 3D Character Avatar
    const avatarGroup = new THREE.Group()
    avatarGroup.position.set(0, 0.95, 0.5)

    // Torso Capsule
    const bodyGeo = new THREE.CylinderGeometry(0.35, 0.42, 0.9, 16)
    const bodyMat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(agentColor),
      roughness: 0.4,
    })
    const body = new THREE.Mesh(bodyGeo, bodyMat)
    body.position.y = 0.45
    body.castShadow = true
    avatarGroup.add(body)

    // Head Sphere
    const headGeo = new THREE.SphereGeometry(0.32, 24, 24)
    const headMat = new THREE.MeshStandardMaterial({ color: 0xfde047, roughness: 0.25 })
    const head = new THREE.Mesh(headGeo, headMat)
    head.position.y = 1.2
    head.castShadow = true
    avatarGroup.add(head)

    // Floating Holographic Indicator Ring
    const ringGeo = new THREE.RingGeometry(0.65, 0.85, 32)
    const ringMat = new THREE.MeshBasicMaterial({
      color: new THREE.Color(agentColor),
      side: THREE.DoubleSide,
      transparent: true,
      opacity: 0.75,
    })
    const ring = new THREE.Mesh(ringGeo, ringMat)
    ring.rotation.x = -Math.PI / 2
    ring.position.y = 0.04
    avatarGroup.add(ring)

    group.add(avatarGroup)

    // Raycast Bounding Box
    const hitBox = new THREE.Mesh(
      new THREE.BoxGeometry(3.6, 3.0, 3.0),
      new THREE.MeshBasicMaterial({ visible: false })
    )
    hitBox.position.y = 1.4
    hitBox.userData = { character: char, ring, group }
    group.add(hitBox)

    agentMeshes.push(hitBox)
    scene.add(group)
  })
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
    if (target.ring) {
      target.ring.scale.set(1.25, 1.25, 1.25)
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
  if (hoveredCharacter.value) {
    const char = hoveredCharacter.value
    focusCameraOnAgent(char)
    emit('select-agent', char)
  }
}

function selectAgentFromSearch(char) {
  searchQuery.value = ''
  currentFloor.value = char.floor_level
  focusCameraOnAgent(char)
  emit('select-agent', char)
}

function focusCameraOnAgent(char) {
  const targetX = char.position_x
  const targetY = char.position_y + 1.2
  const targetZ = char.position_z

  gsap.to(camera.position, {
    x: targetX,
    y: targetY + 4.5,
    z: targetZ + 7.5,
    duration: 1.4,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(targetX, targetY, targetZ),
  })
}

function changeFloor(level) {
  currentFloor.value = level
  const targetFloor = floorList.find((f) => f.level === level)
  if (!targetFloor) return

  gsap.to(camera.position, {
    x: 0,
    y: targetFloor.heightY + 24,
    z: 42,
    duration: 1.3,
    ease: 'power2.inOut',
    onUpdate: () => camera.lookAt(0, targetFloor.heightY + 4, 0),
  })
}

function resetCamera() {
  const targetFloor = floorList.find((f) => f.level === currentFloor.value)
  const targetY = targetFloor ? targetFloor.heightY : 14

  gsap.to(camera.position, {
    x: 0,
    y: targetY + 24,
    z: 48,
    duration: 1.2,
    ease: 'power2.out',
    onUpdate: () => camera.lookAt(0, targetY + 2, 0),
  })
}

function onWindowResize() {
  const container = canvasContainer.value
  if (!container || !renderer || !camera) return
  camera.aspect = container.clientWidth / container.clientHeight
  camera.updateProjectionMatrix()
  renderer.setSize(container.clientWidth, container.clientHeight)
}

function animate() {
  animationFrameId = requestAnimationFrame(animate)

  // Subtle breathing idle pulse on all rings
  const time = performance.now() * 0.002
  agentMeshes.forEach((mesh) => {
    if (mesh.userData.ring) {
      mesh.userData.ring.rotation.z = time * 0.4
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
