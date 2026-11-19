<template>
  <div class="relative w-full h-full overflow-hidden">
    <!-- Three.js Canvas Container -->
    <div ref="canvasContainer" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

    <!-- 3D Scene Controls Overlay -->
    <div class="absolute bottom-6 left-6 z-10 flex flex-col gap-3">
      <!-- Floor Selector -->
      <div class="bg-slate-900/80 backdrop-blur-md border border-slate-700/60 rounded-2xl p-2.5 shadow-2xl flex items-center gap-2">
        <span class="text-xs font-semibold uppercase text-slate-400 px-2">Lantai:</span>
        <button
          v-for="floor in floors"
          :key="floor.level"
          @click="changeFloor(floor.level)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all duration-300 flex items-center gap-1.5',
            currentFloor === floor.level
              ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold'
              : 'text-slate-300 hover:bg-slate-800 hover:text-white'
          ]"
        >
          <span>{{ floor.label }}</span>
        </button>
      </div>

      <!-- Camera Reset Button -->
      <div class="flex gap-2">
        <button
          @click="resetCamera"
          class="bg-slate-900/80 backdrop-blur-md border border-slate-700/60 hover:border-slate-500 text-slate-200 px-3.5 py-2 rounded-xl text-xs font-medium shadow-xl hover:bg-slate-800 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </svg>
          Reset Kamera Global
        </button>
      </div>
    </div>

    <!-- Active Hover Tooltip -->
    <div
      v-if="hoveredAgent"
      :style="{ left: tooltipPos.x + 15 + 'px', top: tooltipPos.y + 15 + 'px' }"
      class="pointer-events-none fixed z-30 bg-slate-900/90 backdrop-blur-md border border-sky-500/40 px-3.5 py-2 rounded-xl shadow-2xl text-xs flex items-center gap-2.5 transform transition-all"
    >
      <span class="text-base">{{ hoveredAgent.emoji }}</span>
      <div>
        <div class="font-bold text-white flex items-center gap-1.5">
          {{ hoveredAgent.name }}
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>
        <div class="text-[11px] text-sky-400">{{ hoveredAgent.division }} • Meja {{ hoveredAgent.deskCode }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import * as THREE from 'three'
import gsap from 'gsap'

const emit = defineEmits(['select-agent'])

const canvasContainer = ref(null)
const currentFloor = ref(3) // Default Lantai 3: Engineering
const hoveredAgent = ref(null)
const tooltipPos = ref({ x: 0, y: 0 })

const floors = [
  { level: 1, label: 'Lt 1: Lobby & Ops', y: 0 },
  { level: 2, label: 'Lt 2: Product & Design', y: 8 },
  { level: 3, label: 'Lt 3: Engineering & QA', y: 16 },
  { level: 4, label: 'Lt 4: Spatial & Executive', y: 24 },
]

// Sample Initial Roster from agency-agents
const agentRoster = [
  // Lantai 3: Engineering & QA
  { id: 'agt_arch', name: 'Software Architect', slug: 'engineering-software-architect', division: 'Engineering', emoji: '🏛️', deskCode: 'ENG-01', floor: 3, color: '#3B82F6', pos: { x: -6, z: -4 }, model: 'claude-3-7-sonnet' },
  { id: 'agt_backend', name: 'Backend Architect', slug: 'engineering-backend-architect', division: 'Engineering', emoji: '🏗️', deskCode: 'ENG-02', floor: 3, color: '#3B82F6', pos: { x: -2, z: -4 }, model: 'deepseek-chat' },
  { id: 'agt_frontend', name: 'Frontend Developer', slug: 'engineering-frontend-developer', division: 'Engineering', emoji: '💻', deskCode: 'ENG-03', floor: 3, color: '#3B82F6', pos: { x: 2, z: -4 }, model: 'deepseek-chat' },
  { id: 'agt_db', name: 'Database Optimizer', slug: 'engineering-database-optimizer', division: 'Engineering', emoji: '🗄️', deskCode: 'ENG-04', floor: 3, color: '#3B82F6', pos: { x: 6, z: -4 }, model: 'deepseek-chat' },
  { id: 'agt_qa', name: 'Reality Checker (QA)', slug: 'testing-reality-checker', division: 'Testing', emoji: '🧐', deskCode: 'QA-01', floor: 3, color: '#F59E0B', pos: { x: -4, z: 4 }, model: 'deepseek-r1' },
  { id: 'agt_sec', name: 'Security Architect', slug: 'security-architect', division: 'Security', emoji: '🛡️', deskCode: 'SEC-01', floor: 3, color: '#EF4444', pos: { x: 4, z: 4 }, model: 'claude-3-7-sonnet' },

  // Lantai 2: Product & Design
  { id: 'agt_pm', name: 'Senior Project Manager', slug: 'project-manager-senior', division: 'Project Management', emoji: '📋', deskCode: 'PM-01', floor: 2, color: '#0EA5E9', pos: { x: -3, z: -3 }, model: 'claude-3-7-sonnet' },
  { id: 'agt_prod', name: 'Product Manager', slug: 'product-manager', division: 'Product', emoji: '📦', deskCode: 'PRD-01', floor: 2, color: '#D946EF', pos: { x: 3, z: -3 }, model: 'claude-3-7-sonnet' },
  { id: 'agt_uiux', name: 'UI Designer', slug: 'design-ui-designer', division: 'Design', emoji: '🎨', deskCode: 'DSG-01', floor: 2, color: '#EC4899', pos: { x: 0, z: 3 }, model: 'claude-3-5-sonnet' },

  // Lantai 1: Lobby
  { id: 'agt_recep', name: 'Office Receptionist', slug: 'support-support-responder', division: 'Support', emoji: '🛎️', deskCode: 'LOBBY-01', floor: 1, color: '#10B981', pos: { x: 0, z: 0 }, model: 'gemini-2.5-flash' },

  // Lantai 4: Executive & Spatial
  { id: 'agt_orch', name: 'Master Orchestrator', slug: 'agents-orchestrator', division: 'Specialized', emoji: '🧙‍♂️', deskCode: 'EXEC-01', floor: 4, color: '#6366F1', pos: { x: 0, z: -2 }, model: 'claude-3-7-sonnet' },
  { id: 'agt_xr', name: 'XR Interface Architect', slug: 'spatial-computing-xr-interface-architect', division: 'Spatial Computing', emoji: '🥽', deskCode: 'XR-01', floor: 4, color: '#06B6D4', pos: { x: 4, z: 2 }, model: 'claude-3-7-sonnet' },
]

let scene, camera, renderer, animationFrameId
const agentMeshes = []
const raycaster = new THREE.Raycaster()
const mouse = new THREE.Vector2()

function initThree() {
  const container = canvasContainer.value
  if (!container) return

  // 1. Scene & Background
  scene = new THREE.Scene()
  scene.background = new THREE.Color(0x0a0f1d)
  scene.fog = new THREE.FogExp2(0x0a0f1d, 0.02)

  // 2. Camera
  const aspect = container.clientWidth / container.clientHeight
  camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000)
  camera.position.set(0, 32, 28)
  camera.lookAt(0, 16, 0)

  // 3. Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.2
  container.appendChild(renderer.domElement)

  // 4. Lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.8)
  scene.add(ambientLight)

  const dirLight = new THREE.DirectionalLight(0x93c5fd, 2.5)
  dirLight.position.set(20, 40, 20)
  dirLight.castShadow = true
  dirLight.shadow.mapSize.width = 2048
  dirLight.shadow.mapSize.height = 2048
  scene.add(dirLight)

  const warmAccentLight = new THREE.PointLight(0xf59e0b, 3, 50)
  warmAccentLight.position.set(-15, 25, 10)
  scene.add(warmAccentLight)

  // 5. Office Architecture & Floor Plans
  buildOfficeFloors()

  // 6. Spawn Desks & Agent Avatars
  spawnAgents()

  // 7. Event Listeners
  window.addEventListener('resize', onWindowResize)
  container.addEventListener('mousemove', onMouseMove)
  container.addEventListener('click', onClick)

  // 8. Start Render Loop
  animate()
}

function buildOfficeFloors() {
  floors.forEach((f) => {
    const floorGroup = new THREE.Group()
    floorGroup.position.y = f.y

    // Main Floor Slab
    const slabGeo = new THREE.BoxGeometry(26, 0.5, 20)
    const slabMat = new THREE.MeshStandardMaterial({
      color: 0x1e293b,
      roughness: 0.3,
      metalness: 0.2,
    })
    const slab = new THREE.Mesh(slabGeo, slabMat)
    slab.position.y = -0.25
    slab.receiveShadow = true
    floorGroup.add(slab)

    // Sleek Grid Wireframe on Floor
    const grid = new THREE.GridHelper(26, 26, 0x38bdf8, 0x334155)
    grid.position.y = 0.01
    floorGroup.add(grid)

    // Glass Railings
    const railingMat = new THREE.MeshPhysicalMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.25,
      roughness: 0.1,
      transmission: 0.8,
      thickness: 0.5,
    })
    const northRail = new THREE.Mesh(new THREE.BoxGeometry(26, 1.2, 0.2), railingMat)
    northRail.position.set(0, 0.6, -10)
    floorGroup.add(northRail)

    const southRail = new THREE.Mesh(new THREE.BoxGeometry(26, 1.2, 0.2), railingMat)
    southRail.position.set(0, 0.6, 10)
    floorGroup.add(southRail)

    // Floating Room Pillars
    const pillarGeo = new THREE.CylinderGeometry(0.3, 0.3, 7.5, 16)
    const pillarMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.8, roughness: 0.2 })
    ;[[-12.5, -9.5], [12.5, -9.5], [-12.5, 9.5], [12.5, 9.5]].forEach(([px, pz]) => {
      const pillar = new THREE.Mesh(pillarGeo, pillarMat)
      pillar.position.set(px, 3.75, pz)
      pillar.castShadow = true
      floorGroup.add(pillar)
    })

    scene.add(floorGroup)
  })
}

function spawnAgents() {
  agentRoster.forEach((agent) => {
    const floorY = floors.find((f) => f.level === agent.floor)?.y || 0
    const group = new THREE.Group()
    group.position.set(agent.pos.x, floorY, agent.pos.z)

    // 1. Sleek Workstation Desk
    const deskGeo = new THREE.BoxGeometry(2.4, 0.1, 1.4)
    const deskMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.4 })
    const desk = new THREE.Mesh(deskGeo, deskMat)
    desk.position.y = 0.8
    desk.castShadow = true
    desk.receiveShadow = true
    group.add(desk)

    // Desk Legs
    const legGeo = new THREE.CylinderGeometry(0.04, 0.04, 0.8, 8)
    const legMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.9 })
    ;[[-1.1, -0.6], [1.1, -0.6], [-1.1, 0.6], [1.1, 0.6]].forEach(([lx, lz]) => {
      const leg = new THREE.Mesh(legGeo, legMat)
      leg.position.set(lx, 0.4, lz)
      group.add(leg)
    })

    // Glowing Monitor
    const screenGeo = new THREE.BoxGeometry(1.2, 0.7, 0.05)
    const screenMat = new THREE.MeshStandardMaterial({
      color: 0x0284c7,
      emissive: 0x0369a1,
      emissiveIntensity: 0.8,
    })
    const screen = new THREE.Mesh(screenGeo, screenMat)
    screen.position.set(0, 1.25, -0.3)
    group.add(screen)

    // 2. Stylized 3D Avatar (Head & Body Capsule)
    const avatarGroup = new THREE.Group()
    avatarGroup.position.set(0, 0.85, 0.4)

    // Avatar Body
    const bodyGeo = new THREE.CylinderGeometry(0.3, 0.35, 0.8, 16)
    const bodyMat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(agent.color),
      roughness: 0.5,
    })
    const body = new THREE.Mesh(bodyGeo, bodyMat)
    body.position.y = 0.4
    body.castShadow = true
    avatarGroup.add(body)

    // Avatar Head
    const headGeo = new THREE.SphereGeometry(0.28, 24, 24)
    const headMat = new THREE.MeshStandardMaterial({ color: 0xfde047, roughness: 0.3 })
    const head = new THREE.Mesh(headGeo, headMat)
    head.position.y = 1.05
    head.castShadow = true
    avatarGroup.add(head)

    // Floating Holographic Indicator Ring
    const ringGeo = new THREE.RingGeometry(0.5, 0.65, 32)
    const ringMat = new THREE.MeshBasicMaterial({
      color: new THREE.Color(agent.color),
      side: THREE.DoubleSide,
      transparent: true,
      opacity: 0.7,
    })
    const ring = new THREE.Mesh(ringGeo, ringMat)
    ring.rotation.x = -Math.PI / 2
    ring.position.y = 0.05
    avatarGroup.add(ring)

    group.add(avatarGroup)

    // Bounding Box for Raycasting
    const hitBox = new THREE.Mesh(
      new THREE.BoxGeometry(3, 2.5, 2.5),
      new THREE.MeshBasicMaterial({ visible: false })
    )
    hitBox.position.y = 1.2
    hitBox.userData = { agent, ring }
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
    hoveredAgent.value = target.agent
    if (target.ring) {
      target.ring.scale.set(1.2, 1.2, 1.2)
    }
    container.style.cursor = 'pointer'
  } else {
    if (hoveredAgent.value) {
      agentMeshes.forEach((m) => m.userData.ring?.scale.set(1, 1, 1))
    }
    hoveredAgent.value = null
    container.style.cursor = 'default'
  }
}

function onClick() {
  if (hoveredAgent.value) {
    const agent = hoveredAgent.value
    focusCameraOnAgent(agent)
    emit('select-agent', agent)
  }
}

function focusCameraOnAgent(agent) {
  const floorY = floors.find((f) => f.level === agent.floor)?.y || 0
  const targetX = agent.pos.x
  const targetY = floorY + 1.2
  const targetZ = agent.pos.z

  gsap.to(camera.position, {
    x: targetX,
    y: targetY + 3.5,
    z: targetZ + 6,
    duration: 1.5,
    ease: 'power3.inOut',
    onUpdate: () => camera.lookAt(targetX, targetY, targetZ),
  })
}

function changeFloor(level) {
  currentFloor.value = level
  const targetFloor = floors.find((f) => f.level === level)
  if (!targetFloor) return

  gsap.to(camera.position, {
    x: 0,
    y: targetFloor.y + 16,
    z: 22,
    duration: 1.2,
    ease: 'power2.inOut',
    onUpdate: () => camera.lookAt(0, targetFloor.y, 0),
  })
}

function resetCamera() {
  const targetFloor = floors.find((f) => f.level === currentFloor.value)
  const targetY = targetFloor ? targetFloor.y : 16

  gsap.to(camera.position, {
    x: 0,
    y: targetY + 16,
    z: 24,
    duration: 1.2,
    ease: 'power2.out',
    onUpdate: () => camera.lookAt(0, targetY, 0),
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

  // Gentle breathing idle rotation for active rings
  const time = performance.now() * 0.002
  agentMeshes.forEach((mesh) => {
    if (mesh.userData.ring) {
      mesh.userData.ring.rotation.z = time * 0.5
    }
  })

  renderer.render(scene, camera)
}

onMounted(() => {
  initThree()
})

onBeforeUnmount(() => {
  cancelAnimationFrame(animationFrameId)
  window.removeEventListener('resize', onWindowResize)
  if (renderer && renderer.domElement) {
    renderer.domElement.remove()
  }
})
</script>
