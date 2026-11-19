<template>
  <div class="w-full h-full bg-slate-950 text-slate-100 flex flex-col overflow-hidden font-sans">
    <!-- Sub-Header Navigation Bar -->
    <div class="h-14 border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md px-6 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-1.5 md:gap-3 overflow-x-auto no-scrollbar py-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="currentTab = tab.id"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0',
            currentTab === tab.id
              ? 'bg-gradient-to-r from-blue-600 to-sky-500 text-white shadow-md shadow-blue-500/20'
              : 'bg-slate-800/60 text-slate-400 hover:text-slate-200 hover:bg-slate-800'
          ]"
        >
          <span>{{ tab.icon }}</span>
          <span>{{ tab.label }}</span>
          <span
            v-if="tab.badge !== undefined"
            class="px-1.5 py-0.2 text-[10px] rounded-full bg-white/20 text-white"
          >
            {{ tab.badge }}
          </span>
        </button>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="refreshCurrentTab"
          :disabled="loading"
          class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition-all border border-slate-700/60"
        >
          <span :class="{'animate-spin': loading}">🔄</span>
          <span class="hidden sm:inline">Segarkan Data</span>
        </button>

        <button
          @click="$emit('switch-to-office')"
          class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition-all"
        >
          <span>🎮</span>
          <span>Kembali ke Kantor 3D</span>
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">

      <!-- ============================================================== -->
      <!-- TAB 1: OVERVIEW & TELEMETRY -->
      <!-- ============================================================== -->
      <div v-if="currentTab === 'overview'" class="space-y-6 max-w-7xl mx-auto">
        <!-- Top Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Total Agents Card -->
          <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Agen AI</span>
              <span class="text-2xl">👥</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-black text-white tracking-tight">{{ overview.telemetry?.total_agents || 283 }}</span>
              <span class="text-xs text-emerald-400 font-medium">({{ overview.telemetry?.active_agents || 283 }} Aktif)</span>
            </div>
            <p class="mt-2 text-[11px] text-slate-400">Terdistribusi di 18 Gedung Divisi Mandiri</p>
          </div>

          <!-- Total Divisions Card -->
          <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Gedung Kampus 3D</span>
              <span class="text-2xl">🏢</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-black text-white tracking-tight">{{ overview.telemetry?.total_divisions || 18 }}</span>
              <span class="text-xs text-sky-400 font-medium">Gedung Divisi</span>
            </div>
            <p class="mt-2 text-[11px] text-slate-400">Tepi Pantai, Kebun Kelapa & Jalan Raya</p>
          </div>

          <!-- Total Workstations Card -->
          <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Spatial Workstations</span>
              <span class="text-2xl">💻</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-black text-white tracking-tight">{{ overview.telemetry?.total_desks || 283 }}</span>
              <span class="text-xs text-emerald-400 font-medium">Meja Komputer 3D</span>
            </div>
            <p class="mt-2 text-[11px] text-slate-400">Layar Holografik & Raycasting Aktif</p>
          </div>

          <!-- 9Router Gateway Status Card -->
          <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition-all"></div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">AI Gateway (9Router)</span>
              <span class="text-2xl">⚡</span>
            </div>
            <div class="mt-3 flex items-center gap-2">
              <span
                class="w-3 h-3 rounded-full"
                :class="overview.router_gateway?.online ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"
              ></span>
              <span
                class="text-xl font-bold tracking-tight"
                :class="overview.router_gateway?.online ? 'text-emerald-400' : 'text-amber-400'"
              >
                {{ overview.router_gateway?.online ? 'Online (:20128)' : 'Standby / Local Proxy' }}
              </span>
            </div>
            <p class="mt-2 text-[11px] text-slate-400 font-mono">
              {{ overview.router_gateway?.models?.length || 9 }} SDLC Combos Siap Pakai
            </p>
          </div>
        </div>

        <!-- Middle Row: Task Analytics & Live Quick Inference -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Tasks Status Card -->
          <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span>📋</span>
                  <span>Status Delegasi Tugas (Tasks)</span>
                </h3>
                <span class="text-xs font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                  Total: {{ overview.tasks_breakdown?.total || 0 }}
                </span>
              </div>

              <div class="space-y-3">
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Menunggu (Pending)
                  </span>
                  <span class="font-bold text-white font-mono">{{ overview.tasks_breakdown?.pending || 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Sedang Dikerjakan (In Progress)
                  </span>
                  <span class="font-bold text-white font-mono">{{ overview.tasks_breakdown?.in_progress || 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span> Selesai Dikerjakan (Completed)
                  </span>
                  <span class="font-bold text-white font-mono">{{ overview.tasks_breakdown?.completed || 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Telah Disetujui (Approved)
                  </span>
                  <span class="font-bold text-white font-mono">{{ overview.tasks_breakdown?.approved || 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span> Ditolak / Revisi (Rejected)
                  </span>
                  <span class="font-bold text-white font-mono">{{ overview.tasks_breakdown?.rejected || 0 }}</span>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between">
              <span class="text-xs text-slate-400">Audit Chat Sessions:</span>
              <span class="text-xs font-semibold text-slate-200">
                {{ overview.telemetry?.total_conversations || 0 }} Sesi ({{ overview.telemetry?.total_messages || 0 }} Pesan)
              </span>
            </div>
          </div>

          <!-- Quick Test Inference Box -->
          <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span>⚡</span>
                  <span>Uji Coba Inferensi AI Cepat (9Router Gateway)</span>
                </h3>
                <span class="text-[11px] text-sky-400 font-mono">Port 20128</span>
              </div>
              <p class="text-xs text-slate-400 mb-4">
                Kirim instruksi langsung ke model routing untuk menguji konektivitas AI Gateway, latency, dan token usage.
              </p>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                <div>
                  <label class="block text-[11px] font-medium text-slate-400 mb-1">Pilihan Model / Combo</label>
                  <select
                    v-model="quickTest.model"
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
                  >
                    <option value="sdlc-coding">sdlc-coding (Default Coding)</option>
                    <option value="sdlc-architecture">sdlc-architecture (System Design)</option>
                    <option value="sdlc-planning">sdlc-planning (Project Planning)</option>
                    <option value="sdlc-testing">sdlc-testing (QA & Test Automation)</option>
                    <option value="sdlc-devops">sdlc-devops (CI/CD & Cloud)</option>
                    <option value="deepseek-chat">deepseek-chat (DeepSeek Direct)</option>
                    <option value="claude-3-7-sonnet">claude-3-7-sonnet (Claude 3.7)</option>
                    <option value="gemini-2.5-flash">gemini-2.5-flash (Gemini Flash)</option>
                  </select>
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-[11px] font-medium text-slate-400 mb-1">Prompt Uji Coba</label>
                  <input
                    type="text"
                    v-model="quickTest.prompt"
                    placeholder="Contoh: Buatkan fungsi PHP CodeIgniter 4 untuk validasi UUID"
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
                    @keyup.enter="runQuickTest"
                  />
                </div>
              </div>

              <!-- Result Area -->
              <div
                v-if="quickTest.response"
                class="mt-3 p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono space-y-2 max-h-48 overflow-y-auto"
              >
                <div class="flex items-center justify-between text-[11px] text-slate-400 pb-1 border-b border-slate-800">
                  <span class="text-emerald-400 font-semibold">Latency: {{ quickTest.latency }} ms</span>
                  <span>Model: {{ quickTest.modelUsed }}</span>
                </div>
                <div class="text-slate-200 whitespace-pre-wrap text-[11px] leading-relaxed">
                  {{ quickTest.response }}
                </div>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end">
              <button
                @click="runQuickTest"
                :disabled="quickTest.loading || !quickTest.prompt"
                class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 disabled:opacity-50 text-white text-xs font-bold flex items-center gap-2 shadow-md shadow-sky-500/20 transition-all"
              >
                <span v-if="quickTest.loading" class="animate-spin">⏳</span>
                <span v-else>🚀</span>
                <span>{{ quickTest.loading ? 'Mengirim ke 9Router...' : 'Jalankan Inferensi' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Campus Division Buildings Table -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>🏢</span>
                <span>18 Gedung Divisi Kompleks Kampus 3D</span>
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">
                Setiap gedung memiliki arsitektur unik dengan zona coastal, highway, dan perkebunan kelapa.
              </p>
            </div>
            <button
              @click="currentTab = 'divisions'"
              class="text-xs text-sky-400 hover:text-sky-300 font-semibold flex items-center gap-1"
            >
              Kelola Gedung &rarr;
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                <tr>
                  <th class="py-3 px-4">Gedung / Divisi</th>
                  <th class="py-3 px-4">Zona Kampus</th>
                  <th class="py-3 px-4">Koordinat 3D (X, Y, Z)</th>
                  <th class="py-3 px-4 text-center">Jumlah Agen</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 font-medium">
                <tr
                  v-for="div in overview.divisions"
                  :key="div.id"
                  class="hover:bg-slate-800/30 transition-all"
                >
                  <td class="py-3 px-4 flex items-center gap-2.5">
                    <span
                      class="w-3 h-3 rounded-full shrink-0"
                      :style="{ backgroundColor: div.color_hex }"
                    ></span>
                    <span class="font-bold text-white">{{ div.label }}</span>
                  </td>
                  <td class="py-3 px-4 text-slate-300 text-[11px]">
                    {{ div.description || 'Zona Kampus Modern' }}
                  </td>
                  <td class="py-3 px-4 font-mono text-[11px] text-sky-400">
                    <span v-if="div.building">
                      ({{ div.building.position_x }}, {{ div.building.position_y }}, {{ div.building.position_z }})
                    </span>
                    <span v-else class="text-slate-500">-</span>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-200 font-mono text-[11px]">
                      {{ div.agent_count }} Agen
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="editDivision(div)"
                      class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all text-[11px]"
                    >
                      ✏️ Edit
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 2: MANAJEMEN AGEN (283 AGENTS) -->
      <!-- ============================================================== -->
      <div v-if="currentTab === 'agents'" class="space-y-6 max-w-7xl mx-auto">
        <!-- Control Header: Search & Filters -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
          <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Search Input -->
            <div class="relative flex-1">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔍</span>
              <input
                type="text"
                v-model="agentFilters.search"
                placeholder="Cari agen berdasarkan nama, slug, vibe, atau keahlian..."
                class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-sky-500"
              />
            </div>

            <!-- Division Filter -->
            <select
              v-model="agentFilters.division"
              class="bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
            >
              <option value="">Semua Divisi (18)</option>
              <option v-for="d in overview.divisions" :key="d.id" :value="d.id">
                {{ d.label }} ({{ d.agent_count }})
              </option>
            </select>

            <!-- Tier Filter -->
            <select
              v-model="agentFilters.tier"
              class="bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
            >
              <option value="">Semua Model Tier</option>
              <option value="sdlc-coding">sdlc-coding</option>
              <option value="sdlc-architecture">sdlc-architecture</option>
              <option value="sdlc-planning">sdlc-planning</option>
              <option value="sdlc-testing">sdlc-testing</option>
              <option value="sdlc-devops">sdlc-devops</option>
              <option value="sdlc-maintenance">sdlc-maintenance</option>
              <option value="coding">coding</option>
              <option value="reasoning">reasoning</option>
              <option value="fast">fast</option>
            </select>

            <!-- Status Filter -->
            <select
              v-model="agentFilters.status"
              class="bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
            >
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>

          <div class="text-xs text-slate-400 font-mono flex items-center justify-end gap-2">
            <span>Ditemukan:</span>
            <strong class="text-white text-sm">{{ filteredAgents.length }}</strong>
            <span>dari {{ agentsList.length }} Agen</span>
          </div>
        </div>

        <!-- Agents Table -->
        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden">
          <div class="overflow-x-auto max-h-[640px]">
            <table class="w-full text-left text-xs">
              <thead class="sticky top-0 bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800 z-10">
                <tr>
                  <th class="py-3 px-4">Agen & Persona</th>
                  <th class="py-3 px-4">Divisi</th>
                  <th class="py-3 px-4">Meja Kerja</th>
                  <th class="py-3 px-4">Model Routing</th>
                  <th class="py-3 px-4 text-center">Status</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr
                  v-for="agent in paginatedAgents"
                  :key="agent.id"
                  class="hover:bg-slate-800/30 transition-all"
                >
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shadow-inner shrink-0"
                        :style="{ backgroundColor: (agent.color || '#3B82F6') + '25', border: '1px solid ' + (agent.color || '#3B82F6') + '60' }"
                      >
                        {{ agent.emoji || '🤖' }}
                      </div>
                      <div>
                        <div class="font-bold text-white text-xs flex items-center gap-2">
                          {{ agent.name }}
                        </div>
                        <div class="text-[11px] text-slate-400 line-clamp-1 max-w-sm">
                          {{ agent.vibe || agent.description }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="py-3 px-4">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-white"
                      :style="{ backgroundColor: agent.division_color || '#3B82F6' }"
                    >
                      {{ agent.division_label }}
                    </span>
                  </td>

                  <td class="py-3 px-4 font-mono text-[11px] text-slate-300">
                    {{ agent.desk_code || '-' }}
                  </td>

                  <td class="py-3 px-4 font-mono text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-sky-400">
                      {{ agent.preferred_model_tier || 'coding' }}
                    </span>
                  </td>

                  <td class="py-3 px-4 text-center">
                    <button
                      @click="toggleAgentStatus(agent)"
                      :class="[
                        'px-2.5 py-1 rounded-full text-[10px] font-semibold transition-all',
                        agent.is_active == 1
                          ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/30'
                          : 'bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:bg-rose-500/30'
                      ]"
                    >
                      {{ agent.is_active == 1 ? '● Aktif' : '○ Nonaktif' }}
                    </button>
                  </td>

                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditAgentModal(agent)"
                      class="px-3 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 border border-blue-500/30 hover:text-white transition-all text-xs font-semibold"
                    >
                      ✏️ Kelola Persona
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Bar -->
          <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <div>
              Menampilkan {{ pageStart + 1 }} - {{ Math.min(pageEnd, filteredAgents.length) }} dari {{ filteredAgents.length }}
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 text-slate-200"
              >
                &larr; Prev
              </button>
              <span class="px-2 font-mono text-white">{{ currentPage }} / {{ totalPages || 1 }}</span>
              <button
                @click="currentPage++"
                :disabled="currentPage >= totalPages"
                class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 text-slate-200"
              >
                Next &rarr;
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 3: GEDUNG & DIVISI (18 BUILDINGS) -->
      <!-- ============================================================== -->
      <div v-if="currentTab === 'divisions'" class="space-y-6 max-w-7xl mx-auto">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>🏢</span>
              <span>Tata Letak Kompleks Perkantoran 3D (18 Gedung Divisi)</span>
            </h3>
            <p class="text-xs text-slate-400 mt-1">
              Setiap divisi memiliki gedung kantor tersendiri dengan zona coastal (belakang pantai), perkebunan kelapa (sayap kiri-kanan), dan jalan raya ramai di depan.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="div in overview.divisions"
            :key="div.id"
            class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition-all flex flex-col justify-between group"
          >
            <div>
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                  <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shadow-md"
                    :style="{ backgroundColor: div.color_hex + '25', border: '1px solid ' + div.color_hex + '60' }"
                  >
                    🏢
                  </div>
                  <div>
                    <h4 class="text-sm font-bold text-white">{{ div.label }}</h4>
                    <span class="text-[10px] font-mono text-slate-400">ID: {{ div.id }}</span>
                  </div>
                </div>
                <span
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold text-white shadow-sm"
                  :style="{ backgroundColor: div.color_hex }"
                >
                  {{ div.agent_count }} Agen
                </span>
              </div>

              <p class="text-xs text-slate-300 mb-4 line-clamp-2">
                {{ div.description || 'Gedung kantor modern dengan workstation AI terintegrasi.' }}
              </p>

              <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 space-y-1.5 text-xs font-mono">
                <div class="flex items-center justify-between text-slate-400 text-[11px]">
                  <span>Posisi 3D (X, Y, Z):</span>
                  <span class="text-sky-400 font-bold" v-if="div.building">
                    {{ div.building.position_x }}, {{ div.building.position_y }}, {{ div.building.position_z }}
                  </span>
                  <span v-else class="text-slate-500">Auto</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 text-[11px]">
                  <span>Warna Tema:</span>
                  <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: div.color_hex }"></span>
                    <span class="text-slate-200">{{ div.color_hex }}</span>
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end">
              <button
                @click="editDivision(div)"
                class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-all"
              >
                <span>✏️</span>
                <span>Ubah Gedung</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 4: MANAJEMEN TUGAS & REVIEW -->
      <!-- ============================================================== -->
      <div v-if="currentTab === 'tasks'" class="space-y-6 max-w-7xl mx-auto">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>📋</span>
              <span>Pendelegasian Tugas & Review Deliverables</span>
            </h3>
            <p class="text-xs text-slate-400 mt-1">
              Periksa tugas yang didelegasikan ke para agen, verifikasi output final, setujui (Approve) atau minta revisi (Reject).
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              v-for="st in ['all', 'pending', 'in_progress', 'completed', 'approved', 'rejected']"
              :key="st"
              @click="taskFilterStatus = st"
              :class="[
                'px-2.5 py-1 rounded-lg text-xs font-semibold capitalize transition-all',
                taskFilterStatus === st
                  ? 'bg-blue-600 text-white'
                  : 'bg-slate-800/80 text-slate-400 hover:text-white'
              ]"
            >
              {{ st }}
            </button>
          </div>
        </div>

        <!-- Task List Table -->
        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                <tr>
                  <th class="py-3 px-4">Judul Tugas</th>
                  <th class="py-3 px-4">Lead Agent</th>
                  <th class="py-3 px-4">Tanggal Input</th>
                  <th class="py-3 px-4 text-center">Status</th>
                  <th class="py-3 px-4 text-right">Aksi Review</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-if="filteredTasks.length === 0">
                  <td colspan="5" class="py-8 text-center text-slate-500">
                    Belum ada tugas dalam filter ini.
                  </td>
                </tr>
                <tr
                  v-for="task in filteredTasks"
                  :key="task.id"
                  class="hover:bg-slate-800/30 transition-all"
                >
                  <td class="py-3.5 px-4 font-semibold text-white">
                    {{ task.title }}
                  </td>
                  <td class="py-3.5 px-4">
                    <div class="flex items-center gap-2">
                      <span class="text-base">{{ task.lead_agent_emoji || '🤖' }}</span>
                      <div>
                        <div class="text-slate-200 font-bold">{{ task.lead_agent_name }}</div>
                        <div class="text-[10px] text-slate-400">{{ task.division_label }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                    {{ task.created_at }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span
                      class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                      :class="{
                        'bg-amber-500/20 text-amber-400 border border-amber-500/30': task.status === 'pending',
                        'bg-blue-500/20 text-blue-400 border border-blue-500/30': task.status === 'in_progress',
                        'bg-teal-500/20 text-teal-400 border border-teal-500/30': task.status === 'completed',
                        'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': task.status === 'approved',
                        'bg-rose-500/20 text-rose-400 border border-rose-500/30': task.status === 'rejected',
                      }"
                    >
                      {{ task.status }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <button
                      @click="inspectTask(task)"
                      class="px-3 py-1.5 rounded-xl bg-sky-600/20 hover:bg-sky-600/40 text-sky-400 border border-sky-500/30 hover:text-white transition-all text-xs font-semibold"
                    >
                      🔍 Periksa & Review
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 5: LOG PERCAKAPAN (AUDIT TRAIL) -->
      <!-- ============================================================== -->
      <div v-if="currentTab === 'logs'" class="space-y-6 max-w-7xl mx-auto">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>💬</span>
              <span>Audit Log Percakapan (Audit Trail)</span>
            </h3>
            <p class="text-xs text-slate-400 mt-1">
              Catatan interaksi antara pengguna dan agen AI, riwayat prompt, model yang digunakan, serta konsumsi token.
            </p>
          </div>

          <div class="text-xs text-slate-400 font-mono">
            Total Sesi: <strong class="text-white">{{ conversations.length }}</strong>
          </div>
        </div>

        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                <tr>
                  <th class="py-3 px-4">Sesi Percakapan</th>
                  <th class="py-3 px-4">Agen AI</th>
                  <th class="py-3 px-4">Pesan Terakhir</th>
                  <th class="py-3 px-4 text-center">Pesan</th>
                  <th class="py-3 px-4">Terakhir Diperbarui</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-if="conversations.length === 0">
                  <td colspan="6" class="py-8 text-center text-slate-500">
                    Belum ada riwayat percakapan yang tercatat.
                  </td>
                </tr>
                <tr
                  v-for="conv in conversations"
                  :key="conv.id"
                  class="hover:bg-slate-800/30 transition-all"
                >
                  <td class="py-3.5 px-4 font-semibold text-white">
                    {{ conv.title || 'Sesi Percakapan' }}
                  </td>
                  <td class="py-3.5 px-4">
                    <div class="flex items-center gap-2">
                      <span class="text-lg">{{ conv.agent_emoji || '🤖' }}</span>
                      <div>
                        <div class="font-bold text-slate-200">{{ conv.agent_name }}</div>
                        <div class="text-[10px] text-slate-400">{{ conv.division_label }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-slate-300 text-[11px] max-w-xs truncate">
                    {{ conv.last_message_content || '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-sky-400 font-mono text-[11px]">
                      {{ conv.message_count }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                    {{ conv.updated_at }}
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <button
                      @click="inspectConversation(conv)"
                      class="px-3 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 border border-blue-500/30 hover:text-white transition-all text-xs font-semibold"
                    >
                      📜 Baca Log
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 6: 9ROUTER AI GATEWAY DIAGNOSTICS & PLAYGROUND -->
      <!-- ============================================================== -->
      <div v-if="currentTab === 'gateway'" class="space-y-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Gateway Configuration Info -->
          <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>⚡</span>
              <span>Konfigurasi 9Router Gateway</span>
            </h3>

            <div class="space-y-3 text-xs">
              <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400">Gateway Base URL</span>
                <div class="font-mono text-sky-400">http://127.0.0.1:20128/v1</div>
              </div>

              <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400">Koneksi Gateway</span>
                <div class="flex items-center gap-2">
                  <span
                    class="w-2.5 h-2.5 rounded-full"
                    :class="overview.router_gateway?.online ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"
                  ></span>
                  <span class="font-bold text-white">
                    {{ overview.router_gateway?.online ? 'Online & Terkoneksi' : 'Standby / Local Proxy Mode' }}
                  </span>
                </div>
              </div>

              <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <span class="text-[10px] uppercase font-bold text-slate-400">SDLC Combos Aktif</span>
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="combo in [
                      'sdlc-planning',
                      'sdlc-architecture',
                      'sdlc-coding',
                      'sdlc-testing',
                      'sdlc-devops',
                      'sdlc-maintenance',
                      'infografis',
                      'video',
                      'music'
                    ]"
                    :key="combo"
                    class="px-2 py-0.5 rounded bg-slate-800 text-sky-400 border border-slate-700 text-[10px] font-mono"
                  >
                    {{ combo }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Gateway Interactive Test Playground -->
          <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>🧪</span>
              <span>Interactive AI Inference Playground</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Model / SDLC Combo</label>
                <select
                  v-model="playground.model"
                  class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
                >
                  <option value="sdlc-coding">sdlc-coding (Coding Specialist)</option>
                  <option value="sdlc-architecture">sdlc-architecture (System Architecture)</option>
                  <option value="sdlc-planning">sdlc-planning (Project Lead)</option>
                  <option value="sdlc-testing">sdlc-testing (QA & Tests)</option>
                  <option value="sdlc-devops">sdlc-devops (DevOps & Infrastructure)</option>
                  <option value="deepseek-chat">deepseek-chat (DeepSeek V3)</option>
                  <option value="claude-3-7-sonnet">claude-3-7-sonnet (Claude 3.7)</option>
                  <option value="gemini-2.5-flash">gemini-2.5-flash (Gemini Flash)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Temperature</label>
                <input
                  type="number"
                  step="0.1"
                  min="0"
                  max="1.5"
                  v-model="playground.temperature"
                  class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Prompt / Instruksi Uji Coba</label>
              <textarea
                rows="4"
                v-model="playground.prompt"
                placeholder="Tulis instruksi uji coba untuk menguji respon gateway..."
                class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-sky-500 font-mono"
              ></textarea>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-xs text-slate-400">Endpoint: /v1/chat/completions</span>
              <button
                @click="runPlaygroundTest"
                :disabled="playground.loading || !playground.prompt"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 disabled:opacity-50 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-500/20 transition-all"
              >
                <span v-if="playground.loading" class="animate-spin">⏳</span>
                <span v-else>⚡</span>
                <span>{{ playground.loading ? 'Sedang Memproses...' : 'Uji Inferensi Sekarang' }}</span>
              </button>
            </div>

            <!-- Output Box -->
            <div
              v-if="playground.result"
              class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3 font-mono text-xs"
            >
              <div class="flex items-center justify-between pb-2 border-b border-slate-800 text-[11px] text-slate-400">
                <span class="text-emerald-400 font-bold">Waktu: {{ playground.result.latency_ms }} ms</span>
                <span>Model: {{ playground.result.model }}</span>
                <span>Prompt: {{ playground.result.usage?.prompt_tokens || 0 }} tok</span>
                <span>Comp: {{ playground.result.usage?.completion_tokens || 0 }} tok</span>
              </div>
              <div class="text-slate-200 whitespace-pre-wrap leading-relaxed text-[11px] max-h-72 overflow-y-auto">
                {{ playground.result.content || playground.result.error }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: EDIT AGENT PERSONA -->
    <!-- ============================================================== -->
    <div
      v-if="editingAgent"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
    >
      <div class="w-full max-w-2xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950">
          <div class="flex items-center gap-3">
            <span class="text-2xl">{{ editingAgent.emoji || '🤖' }}</span>
            <div>
              <h3 class="text-sm font-bold text-white">Edit Persona: {{ editingAgent.name }}</h3>
              <p class="text-[11px] text-slate-400 font-mono">Slug: {{ editingAgent.slug }}</p>
            </div>
          </div>
          <button
            @click="editingAgent = null"
            class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all"
          >
            ✕
          </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Nama Agen</label>
              <input
                type="text"
                v-model="editingAgent.name"
                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-sky-500"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-300 mb-1">Emoji Avatar</label>
              <input
                type="text"
                v-model="editingAgent.emoji"
                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-sky-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Model Routing Tier (9Router)</label>
              <select
                v-model="editingAgent.preferred_model_tier"
                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-sky-500 font-mono"
              >
                <option value="sdlc-coding">sdlc-coding (Coding Specialist)</option>
                <option value="sdlc-architecture">sdlc-architecture (System Architecture)</option>
                <option value="sdlc-planning">sdlc-planning (Project Planning)</option>
                <option value="sdlc-testing">sdlc-testing (QA & Test Automation)</option>
                <option value="sdlc-devops">sdlc-devops (CI/CD & Cloud Ops)</option>
                <option value="sdlc-maintenance">sdlc-maintenance (Bug Fix & Patch)</option>
                <option value="infografis">infografis (Visual & Charts)</option>
                <option value="reasoning">reasoning (Deep Logic)</option>
                <option value="fast">fast (Gemini Flash)</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-slate-300 mb-1">Status Keaktifan</label>
              <select
                v-model="editingAgent.is_active"
                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-sky-500"
              >
                <option :value="1">Aktif (Bertugas di Kantor 3D)</option>
                <option :value="0">Nonaktif</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-300 mb-1">Vibe & Karakteristik</label>
            <input
              type="text"
              v-model="editingAgent.vibe"
              class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-sky-500"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-300 mb-1">System Prompt (Markdown)</label>
            <textarea
              rows="8"
              v-model="editingAgent.system_prompt"
              class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-sky-500 font-mono text-[11px]"
            ></textarea>
          </div>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
          <button
            @click="editingAgent = null"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
          >
            Batal
          </button>
          <button
            @click="saveAgent"
            :disabled="savingAgent"
            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-lg shadow-blue-500/20"
          >
            <span v-if="savingAgent" class="animate-spin">⏳</span>
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: EDIT BUILDING & DIVISION -->
    <!-- ============================================================== -->
    <div
      v-if="editingDiv"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
    >
      <div class="w-full max-w-lg bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950">
          <div class="flex items-center gap-3">
            <span class="text-2xl">🏢</span>
            <div>
              <h3 class="text-sm font-bold text-white">Edit Gedung: {{ editingDiv.label }}</h3>
              <p class="text-[11px] text-slate-400 font-mono">Divisi ID: {{ editingDiv.id }}</p>
            </div>
          </div>
          <button
            @click="editingDiv = null"
            class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all"
          >
            ✕
          </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Nama / Label Gedung</label>
            <input
              type="text"
              v-model="editingDiv.label"
              class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-sky-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Warna Hex (#RRGGBB)</label>
              <div class="flex items-center gap-2">
                <input
                  type="color"
                  v-model="editingDiv.color_hex"
                  class="w-10 h-8 rounded cursor-pointer bg-transparent border-0"
                />
                <input
                  type="text"
                  v-model="editingDiv.color_hex"
                  class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-white font-mono"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-300 mb-1">Ikon Lucide</label>
              <input
                type="text"
                v-model="editingDiv.icon_name"
                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono"
              />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-300 mb-1">Deskripsi Arsitektur & Zona</label>
            <textarea
              rows="3"
              v-model="editingDiv.description"
              class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white"
            ></textarea>
          </div>

          <!-- 3D Coordinates -->
          <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
            <span class="block text-[11px] font-bold text-slate-300 uppercase">Koordinat 3D Kampus</span>
            <div class="grid grid-cols-3 gap-2">
              <div>
                <label class="block text-[10px] text-slate-400 mb-0.5">Posisi X</label>
                <input
                  type="number"
                  step="1"
                  v-model.number="editingDiv.position_x"
                  class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white font-mono text-xs"
                />
              </div>
              <div>
                <label class="block text-[10px] text-slate-400 mb-0.5">Posisi Y (Elevasi)</label>
                <input
                  type="number"
                  step="1"
                  v-model.number="editingDiv.position_y"
                  class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white font-mono text-xs"
                />
              </div>
              <div>
                <label class="block text-[10px] text-slate-400 mb-0.5">Posisi Z</label>
                <input
                  type="number"
                  step="1"
                  v-model.number="editingDiv.position_z"
                  class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white font-mono text-xs"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-950 flex items-center justify-end gap-3">
          <button
            @click="editingDiv = null"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
          >
            Batal
          </button>
          <button
            @click="saveDivision"
            :disabled="savingDiv"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-lg shadow-emerald-500/20"
          >
            <span v-if="savingDiv" class="animate-spin">⏳</span>
            <span>Simpan Gedung</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: INSPECT & REVIEW TASK -->
    <!-- ============================================================== -->
    <div
      v-if="selectedTask"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
    >
      <div class="w-full max-w-2xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950">
          <div>
            <span class="text-[10px] font-mono text-sky-400">ID: {{ selectedTask.id }}</span>
            <h3 class="text-sm font-bold text-white">{{ selectedTask.title }}</h3>
          </div>
          <button
            @click="selectedTask = null"
            class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all"
          >
            ✕
          </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
          <!-- Lead Agent info -->
          <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <span class="text-xl">{{ selectedTask.lead_agent_emoji || '🤖' }}</span>
              <div>
                <div class="font-bold text-white">{{ selectedTask.lead_agent_name }}</div>
                <div class="text-[10px] text-slate-400">{{ selectedTask.division_label }}</div>
              </div>
            </div>
            <span
              class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase font-mono"
              :class="{
                'bg-amber-500/20 text-amber-400': selectedTask.status === 'pending',
                'bg-blue-500/20 text-blue-400': selectedTask.status === 'in_progress',
                'bg-teal-500/20 text-teal-400': selectedTask.status === 'completed',
                'bg-emerald-500/20 text-emerald-400': selectedTask.status === 'approved',
                'bg-rose-500/20 text-rose-400': selectedTask.status === 'rejected',
              }"
            >
              {{ selectedTask.status }}
            </span>
          </div>

          <!-- Brief Input -->
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Brief Penugasan</label>
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 whitespace-pre-wrap leading-relaxed">
              {{ selectedTask.brief_input }}
            </div>
          </div>

          <!-- Final Deliverable Output -->
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Hasil Pekerjaan (Final Deliverable)</label>
            <div
              v-if="selectedTask.final_deliverable"
              class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] whitespace-pre-wrap leading-relaxed max-h-60 overflow-y-auto"
            >
              {{ selectedTask.final_deliverable }}
            </div>
            <div v-else class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-slate-500 text-center italic">
              Belum ada deliverable yang dihasilkan oleh agen.
            </div>
          </div>

          <!-- Review Feedback Input -->
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Catatan Evaluasi / Review Feedback</label>
            <textarea
              rows="3"
              v-model="reviewFeedback"
              placeholder="Tuliskan catatan apresiasi atau arahan perbaikan untuk agen..."
              class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white placeholder-slate-500 focus:outline-none focus:border-sky-500"
            ></textarea>
          </div>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-950 flex items-center justify-between">
          <button
            @click="selectedTask = null"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
          >
            Tutup
          </button>

          <div class="flex items-center gap-2">
            <button
              @click="submitTaskReview('reject')"
              :disabled="submittingReview"
              class="px-4 py-2 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 border border-rose-500/30 text-xs font-bold transition-all"
            >
              ❌ Minta Revisi (Reject)
            </button>
            <button
              @click="submitTaskReview('approve')"
              :disabled="submittingReview"
              class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-500/20"
            >
              ✅ Setujui (Approve)
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: AUDIT CONVERSATION LOGS -->
    <!-- ============================================================== -->
    <div
      v-if="selectedConversation"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
    >
      <div class="w-full max-w-2xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950">
          <div>
            <span class="text-[10px] font-mono text-sky-400">ID Sesi: {{ selectedConversation.conversation?.id }}</span>
            <h3 class="text-sm font-bold text-white">
              Log Percakapan dengan {{ selectedConversation.conversation?.agent_name }}
            </h3>
          </div>
          <button
            @click="selectedConversation = null"
            class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all"
          >
            ✕
          </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
          <div
            v-for="msg in selectedConversation.messages"
            :key="msg.id"
            :class="[
              'p-4 rounded-2xl max-w-[85%] space-y-1.5',
              msg.sender_type === 'user'
                ? 'ml-auto bg-blue-600 text-white rounded-br-none shadow-md shadow-blue-500/20'
                : 'mr-auto bg-slate-950 border border-slate-800 text-slate-200 rounded-bl-none'
            ]"
          >
            <div class="flex items-center justify-between text-[10px] opacity-75 font-mono mb-1">
              <span>{{ msg.sender_type === 'user' ? '👤 Pengguna' : '🤖 ' + (selectedConversation.conversation?.agent_name || 'Agen') }}</span>
              <span>{{ msg.created_at }}</span>
            </div>
            <div class="whitespace-pre-wrap leading-relaxed text-xs">
              {{ msg.content }}
            </div>
            <div
              v-if="msg.model_used"
              class="pt-1 border-t border-slate-800/60 text-[10px] font-mono text-sky-400 flex items-center justify-between"
            >
              <span>Model: {{ msg.model_used }}</span>
              <span>{{ msg.tokens_prompt + msg.tokens_completion }} tok</span>
            </div>
          </div>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-950 flex justify-end">
          <button
            @click="selectedConversation = null"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
          >
            Tutup Log
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const emit = defineEmits(['switch-to-office'])

const currentTab = ref('overview')
const loading = ref(false)

const tabs = ref([
  { id: 'overview', label: 'Ringkasan & Telemetri', icon: '📊' },
  { id: 'agents', label: 'Manajemen Agen (283)', icon: '🤖' },
  { id: 'divisions', label: 'Gedung & Divisi (18)', icon: '🏢' },
  { id: 'tasks', label: 'Delegasi Tugas & Review', icon: '📋' },
  { id: 'logs', label: 'Audit Log Percakapan', icon: '💬' },
  { id: 'gateway', label: '9Router Gateway', icon: '⚡' },
])

// Overview Data
const overview = ref({
  telemetry: {},
  tasks_breakdown: {},
  divisions: [],
  recent_tasks: [],
  router_gateway: { online: false },
})

// Agents Data & Filter
const agentsList = ref([])
const agentFilters = ref({
  search: '',
  division: '',
  tier: '',
  status: '',
})
const currentPage = ref(1)
const pageSize = 15

// Tasks Data & Filter
const tasksList = ref([])
const taskFilterStatus = ref('all')
const selectedTask = ref(null)
const reviewFeedback = ref('')
const submittingReview = ref(false)

// Conversations Data
const conversations = ref([])
const selectedConversation = ref(null)

// Editing Modals
const editingAgent = ref(null)
const savingAgent = ref(false)

const editingDiv = ref(null)
const savingDiv = ref(false)

// Quick Test Box
const quickTest = ref({
  model: 'sdlc-coding',
  prompt: 'Buatkan ringkasan arsitektur CodeIgniter 4 dan 9Router',
  response: '',
  modelUsed: '',
  latency: 0,
  loading: false,
})

// Playground Box
const playground = ref({
  model: 'sdlc-coding',
  temperature: 0.7,
  prompt: 'Berikan rekomendasi pembagian tugas SDLC antara agen Frontend, Backend, dan QA.',
  result: null,
  loading: false,
})

// Computed filtered agents
const filteredAgents = computed(() => {
  return agentsList.value.filter(a => {
    if (agentFilters.value.division && a.division_id !== agentFilters.value.division) {
      return false
    }
    if (agentFilters.value.tier && a.preferred_model_tier !== agentFilters.value.tier) {
      return false
    }
    if (agentFilters.value.status !== '' && String(a.is_active) !== String(agentFilters.value.status)) {
      return false
    }
    if (agentFilters.value.search) {
      const q = agentFilters.value.search.toLowerCase()
      const matchName = (a.name || '').toLowerCase().includes(q)
      const matchSlug = (a.slug || '').toLowerCase().includes(q)
      const matchVibe = (a.vibe || '').toLowerCase().includes(q)
      const matchDesc = (a.description || '').toLowerCase().includes(q)
      if (!matchName && !matchSlug && !matchVibe && !matchDesc) return false
    }
    return true
  })
})

const totalPages = computed(() => Math.ceil(filteredAgents.value.length / pageSize))
const pageStart = computed(() => (currentPage.value - 1) * pageSize)
const pageEnd = computed(() => pageStart.value + pageSize)
const paginatedAgents = computed(() => filteredAgents.value.slice(pageStart.value, pageEnd.value))

// Computed filtered tasks
const filteredTasks = computed(() => {
  if (taskFilterStatus.value === 'all') return tasksList.value
  return tasksList.value.filter(t => t.status === taskFilterStatus.value)
})

// Fetch methods
async function fetchOverview() {
  loading.value = true
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/admin/overview')
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        overview.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load overview:', err)
  } finally {
    loading.value = false
  }
}

async function fetchAgents() {
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/admin/agents')
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        agentsList.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load agents:', err)
  }
}

async function fetchTasks() {
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/tasks')
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        tasksList.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load tasks:', err)
  }
}

async function fetchConversations() {
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/admin/conversations')
    if (res.ok) {
      const json = await res.json()
      if (json.data?.conversations) {
        conversations.value = json.data.conversations
      }
    }
  } catch (err) {
    console.error('Failed to load conversations:', err)
  }
}

function refreshCurrentTab() {
  fetchOverview()
  if (currentTab.value === 'agents') fetchAgents()
  if (currentTab.value === 'tasks') fetchTasks()
  if (currentTab.value === 'logs') fetchConversations()
}

// Agent Actions
function openEditAgentModal(agent) {
  editingAgent.value = { ...agent }
}

async function toggleAgentStatus(agent) {
  const newStatus = agent.is_active == 1 ? 0 : 1
  try {
    const res = await fetch(`http://127.0.0.1:8080/api/v1/admin/agents/${agent.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ is_active: newStatus }),
    })
    if (res.ok) {
      agent.is_active = newStatus
    }
  } catch (err) {
    console.error('Failed to toggle status:', err)
  }
}

async function saveAgent() {
  if (!editingAgent.value) return
  savingAgent.value = true
  try {
    const res = await fetch(`http://127.0.0.1:8080/api/v1/admin/agents/${editingAgent.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: editingAgent.value.name,
        emoji: editingAgent.value.emoji,
        vibe: editingAgent.value.vibe,
        preferred_model_tier: editingAgent.value.preferred_model_tier,
        is_active: editingAgent.value.is_active,
        system_prompt: editingAgent.value.system_prompt,
      }),
    })
    if (res.ok) {
      const idx = agentsList.value.findIndex(a => a.id === editingAgent.value.id)
      if (idx !== -1) {
        agentsList.value[idx] = { ...editingAgent.value }
      }
      editingAgent.value = null
    }
  } catch (err) {
    console.error('Failed to save agent:', err)
  } finally {
    savingAgent.value = false
  }
}

// Division Actions
function editDivision(div) {
  editingDiv.value = {
    ...div,
    position_x: div.building?.position_x ?? 0,
    position_y: div.building?.position_y ?? 0,
    position_z: div.building?.position_z ?? 0,
  }
}

async function saveDivision() {
  if (!editingDiv.value) return
  savingDiv.value = true
  try {
    const res = await fetch(`http://127.0.0.1:8080/api/v1/admin/divisions/${editingDiv.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        label: editingDiv.value.label,
        color_hex: editingDiv.value.color_hex,
        icon_name: editingDiv.value.icon_name,
        description: editingDiv.value.description,
        position_x: editingDiv.value.position_x,
        position_y: editingDiv.value.position_y,
        position_z: editingDiv.value.position_z,
      }),
    })
    if (res.ok) {
      await fetchOverview()
      editingDiv.value = null
    }
  } catch (err) {
    console.error('Failed to save division:', err)
  } finally {
    savingDiv.value = false
  }
}

// Task Review Actions
function inspectTask(task) {
  selectedTask.value = task
  reviewFeedback.value = task.review_feedback || ''
}

async function submitTaskReview(decision) {
  if (!selectedTask.value) return
  submittingReview.value = true
  const endpoint = decision === 'approve'
    ? `http://127.0.0.1:8080/api/v1/tasks/${selectedTask.value.id}/approve`
    : `http://127.0.0.1:8080/api/v1/tasks/${selectedTask.value.id}/reject`

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ review_feedback: reviewFeedback.value }),
    })
    if (res.ok) {
      selectedTask.value.status = decision === 'approve' ? 'approved' : 'rejected'
      selectedTask.value.review_feedback = reviewFeedback.value
      await fetchTasks()
      selectedTask.value = null
    }
  } catch (err) {
    console.error('Failed to submit task review:', err)
  } finally {
    submittingReview.value = false
  }
}

// Conversation Actions
async function inspectConversation(conv) {
  try {
    const res = await fetch(`http://127.0.0.1:8080/api/v1/admin/conversations/${conv.id}/messages`)
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        selectedConversation.value = json.data
      }
    }
  } catch (err) {
    console.error('Failed to load conversation messages:', err)
  }
}

// Quick Test Action
async function runQuickTest() {
  if (!quickTest.value.prompt) return
  quickTest.value.loading = true
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/admin/test-inference', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: quickTest.value.model,
        prompt: quickTest.value.prompt,
      }),
    })
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        quickTest.value.response = json.data.content || json.data.error || 'No response.'
        quickTest.value.latency = json.data.latency_ms || 0
        quickTest.value.modelUsed = json.data.model || quickTest.value.model
      }
    }
  } catch (err) {
    quickTest.value.response = 'Connection error to backend: ' + err.message
  } finally {
    quickTest.value.loading = false
  }
}

// Playground Action
async function runPlaygroundTest() {
  if (!playground.value.prompt) return
  playground.value.loading = true
  try {
    const res = await fetch('http://127.0.0.1:8080/api/v1/admin/test-inference', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: playground.value.model,
        prompt: playground.value.prompt,
        temperature: playground.value.temperature,
      }),
    })
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        playground.value.result = json.data
      }
    }
  } catch (err) {
    playground.value.result = { error: 'Network error: ' + err.message }
  } finally {
    playground.value.loading = false
  }
}

onMounted(() => {
  fetchOverview()
  fetchAgents()
  fetchTasks()
  fetchConversations()
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

