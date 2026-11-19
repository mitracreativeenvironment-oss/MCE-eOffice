# DOKUMENTASI LENGKAP EKOSISTEM AGEN KANTOR 3D (MCE-eOffice)
## ADAPTASI DAN ENSIKLOPEDIA AGENCY-AGENTS DENGAN CODEIGNITER 4 & 9ROUTER

**Platform:** Web-based 3D Virtual Office & Multi-Agent Ecosystem  
**Dokumen Induk:** `master arsitektur .md`  
**Versi Dokumen:** 2.0 (Arsitektur Ramping: CI4 + 9Router)  
**Tanggal Rilis:** 19 November 2026  
**Status:** Dokumen Arsitektur & Katalog Operasional Resmi  
**Repositori Proyek:** `https://github.com/mitracreativeenvironment-oss/MCE-eOffice`  

---

## DAFTAR ISI
1. [BAB 1: KONSEPSI ARSITEKTUR RAMPING (CI4 + 9ROUTER)](#bab-1-konsepsi-arsitektur-ramping-ci4--9router)
2. [BAB 2: TAKSONOMI LENGKAP DIVISI & KATALOG 283 AGEN](#bab-2-taksonomi-lengkap-divisi--katalog-283-agen)
   - [2.1 Divisi Engineering](#21-divisi-engineering)
   - [2.2 Divisi Design](#22-divisi-design)
   - [2.3 Divisi Project Management](#23-divisi-project-management)
   - [2.4 Divisi Product](#24-divisi-product)
   - [2.5 Divisi Testing & QA](#25-divisi-testing--qa)
   - [2.6 Divisi Security](#26-divisi-security)
   - [2.7 Divisi Spatial Computing](#27-divisi-spatial-computing)
   - [2.8 Divisi Finance](#28-divisi-finance)
   - [2.9 Divisi Paid Media & Marketing](#29-divisi-paid-media--marketing)
   - [2.10 Divisi Sales & Revenue](#210-divisi-sales--revenue)
   - [2.11 Divisi Support & Operations](#211-divisi-support--operations)
   - [2.12 Divisi GIS & Spatial Data](#212-divisi-gis--spatial-data)
   - [2.13 Divisi Game Development](#213-divisi-game-development)
   - [2.14 Divisi Specialized & Strategic](#214-divisi-specialized--strategic)
   - [2.15 Divisi Academic, Research, & Healthcare](#215-divisi-academic-research--healthcare)
3. [BAB 3: INJEKSI PERSONA DARI AGENCY-AGENTS KE CI4 AGENT ENGINE](#bab-3-injeksi-persona-dari-agency-agents-ke-ci4-agent-engine)
4. [BAB 4: STRATEGI SMART ROUTING & FAILOVER PADA 9ROUTER](#bab-4-strategi-smart-routing--failover-pada-9router)
5. [BAB 5: PENATAAN SPASIAL 3D (ZONING KANTOR 4 LANTAI & MEJA)](#bab-5-penataan-spasial-3d-zoning-kantor-4-lantai--meja)
6. [BAB 6: SPESIFIKASI BACKEND CI4 & SKEMA DATABASE MYSQL](#bab-6-spesifikasi-backend-ci4--skema-database-mysql)
7. [BAB 7: ORKESTRASI MULTI-AGEN DI CODEIGNITER 4](#bab-7-orkestrasi-multi-agen-di-codeigniter-4)
8. [BAB 8: PROTOKOL DELIVERABLES & BUKTI AKSEPTANSI](#bab-8-protokol-deliverables--bukti-akseptansi)
9. [BAB 9: KEAMANAN, RBAC, DAN TELEMETRI BIAYA](#bab-9-keamanan-rbac-dan-telemetri-biaya)
10. [BAB 10: AUTOMATION SEEDER DARI DATA AGENCY-AGENTS KE MYSQL](#bab-10-automation-seeder-dari-data-agency-agents-ke-mysql)

---

# BAB 1: KONSEPSI ARSITEKTUR RAMPING (CI4 + 9ROUTER)

## 1.1 Filosofi Penyederhanaan
Keputusan arsitektur resmi Kantor 3D menetapkan alur sistem yang **ramping, cepat, dan zero-friction**:

```
[ Frontend: Vue 3 + Three.js ]
              │ (REST API & SSE Token Streaming)
              ▼
[ Backend: CodeIgniter 4 + MySQL ]  ◄── Injeksi Persona 283 Agen dari "agency-agents"
              │ (OpenAI-Compatible HTTP: http://127.0.0.1:20128/v1)
              ▼
[ Central AI Gateway: 9Router ]
              │ (Smart Routing & Auto-Failover)
              ▼
[ Foundation Models: Claude 3.7 / DeepSeek V3 / OpenAI o3-mini / Gemini Flash ]
```

### Keuntungan Dibandingkan Menggunakan Middleware Eksternal:
1. **Zero Daemon Complexity:** Tidak memerlukan manajemen proses terpisah Python atau container runtime tambahan di luar stack web standar.
2. **Latensi Minimal:** Aliran token mengalir langsung dari 9Router ke CI4, lalu diteruskan ke Three.js menggunakan Server-Sent Events (SSE) tanpa hambatan hop ganda.
3. **Pusat Kendali Tunggal di CI4:** State percakapan, hak akses pengguna, status karakter 3D, dan riwayat memori agen berada dalam database MySQL yang sama.
4. **Fleksibilitas 9Router Penuh:** CI4 bebas meminta model apa pun melalui 9Router dengan jaminan *failover* instan jika terkena rate limit (429).

---

# BAB 2: TAKSONOMI LENGKAP DIVISI & KATALOG 283 AGEN

Repositori `agency-agents` yang telah di-clone ke direktori proyek (`agency-agents/`) memuat **283 agen spesialis** di dalam **18 divisi fungsional** (berdasarkan `divisions.json`):

---

## 2.1 Divisi Engineering
- **Ikon UI:** `Code` | **Warna Aksen:** `#3B82F6` (Biru Terang) | **Lokasi:** Lantai 3 — Ruang Engineering & DevOps Lab  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `engineering-software-architect.md` | Software Architect | 🏛️ | Desain sistem makro, microservices vs modular monolith, non-functional requirements. | `claude-3-7-sonnet` (Tier 1 Reasoning) |
| `engineering-backend-architect.md` | Backend Architect | 🏗️ | Arsitektur server, API high-throughput, isolasi failure, circuit breaker, caching layer. | `deepseek-chat` / `claude-3-7-sonnet` |
| `engineering-frontend-developer.md` | Frontend Developer | 💻 | Arsitektur SPA Vue 3/React, state management, render optimization, bundle shrinking. | `deepseek-chat` / `claude-3-5-sonnet` |
| `engineering-senior-developer.md` | Senior Developer | ⚡ | Implementasi fitur kritis, clean code, design patterns, refactoring logic kompleks. | `deepseek-chat` / `claude-3-7-sonnet` |
| `engineering-code-reviewer.md` | Code Reviewer | 🧐 | Audit correctness, anti-pattern, maintainability, memory leaks, convention check. | `claude-3-7-sonnet` |
| `engineering-database-optimizer.md` | Database Optimizer | 🗄️ | Tuning query SQL, skema indexing B-Tree/GiST, partitioning, eliminasi lock contention. | `deepseek-chat` / `claude-3-5-sonnet` |
| `engineering-database-reliability-engineer.md` | Database SRE | 🛡️ | High availability, replikasi master-replica, disaster recovery, failover validation. | `claude-3-5-sonnet` |
| `engineering-data-engineer.md` | Data Engineer | 📊 | Pipeline ETL/ELT, streaming Kafka, data warehousing, Delta Lake, data validation. | `deepseek-chat` / `qwen-coder` |
| `engineering-devops-automator.md` | DevOps Automator | 🚀 | CI/CD pipelines, Docker, Kubernetes manifests, zero-downtime deployment. | `claude-3-5-sonnet` |
| `engineering-sre.md` | Site Reliability Engineer | ⏱️ | SLO/SLI, observability (Prometheus/Grafana), chaos engineering, error budgets. | `claude-3-7-sonnet` |
| `engineering-incident-response-commander.md` | Incident Commander | 🚨 | Triage insiden produksi, RCA (Root Cause Analysis), post-mortem documentation. | `claude-3-7-sonnet` |
| `engineering-api-platform-engineer.md` | API Platform Engineer | 🔌 | Kontrak OpenAPI/Swagger, rate limiting, GraphQL schemas, gateway routing. | `deepseek-chat` |
| `engineering-ai-engineer.md` | AI Engineer | 🤖 | Integrasi LLM, function calling schemas, model evaluations, context window optimization. | `claude-3-7-sonnet` |
| `engineering-prompt-engineer.md` | Prompt Engineer | ✍️ | Structured output schemas, system instructions, XML/JSON parsing guardrails. | `claude-3-5-sonnet` |
| `engineering-rag-pipeline-engineer.md` | RAG Pipeline Engineer | 📚 | Chunking strategies, hybrid search (BM25 + dense), re-ranking, vector retrieval. | `claude-3-7-sonnet` |
| `engineering-multi-agent-systems-architect.md` | Multi-Agent Architect | 🕸️ | Desain protokol komunikasi agen, mitigasi circular loops, handoff payload schemas. | `claude-3-7-sonnet` |
| `engineering-codebase-onboarding-engineer.md` | Codebase Onboarding | 🧭 | Membedah arsitektur repositori warisan, membuat peta jalan komponen dan flow data. | `claude-3-5-sonnet` |
| `engineering-git-workflow-master.md` | Git Workflow Master | 🔀 | Trunk-based vs GitFlow, merge conflict resolution, rebase clean history. | `fast-tier` (Gemini Flash) |
| `engineering-technical-writer.md` | Technical Writer | 📝 | Dokumentasi arsitektur, panduan API, developer manuals, changelog otomatis. | `claude-3-5-sonnet` |
| `engineering-realtime-collaboration-engineer.md` | Realtime Engineer | 🔄 | WebSocket, SSE streaming, CRDTs, optimistic UI updates, low-latency syncing. | `deepseek-chat` |

---

## 2.2 Divisi Design
- **Ikon UI:** `PenTool` | **Warna Aksen:** `#EC4899` (Pink Modern) | **Lokasi:** Lantai 2 — Creative & UX Studio  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `design-ux-architect.md` | UX Architect | 📐 | Information architecture, user flows, cognitive load mapping, navigation hierarchy. | `claude-3-7-sonnet` |
| `design-ui-designer.md` | UI Designer | 🎨 | Layout grid, komponen antarmuka, micro-interactions, responsive states, typography. | `claude-3-5-sonnet` / `gpt-4o` |
| `design-brand-guardian.md` | Brand Guardian | 🛡️ | Konsistensi palet warna, tipografi, tone of voice, visual identity standards. | `gpt-4o` / `claude-3-5` |
| `design-persona-walkthrough.md` | Persona Walkthrough | 🧠 | Simulasi uji empati pengguna, cognitive walkthrough dari perspektif audiens spesifik. | `claude-3-7-sonnet` |
| `design-ui-finish-gate-reviewer.md` | UI Finish Gatekeeper | 🔍 | Audit visual pixel-perfect, whitespace balance, optical alignment, WCAG contrast. | `gpt-4o` (Multimodal) |
| `design-ux-researcher.md` | UX Researcher | 🔬 | Perumusan hipotesis user test, panduan wawancara kualitatif, sintesis data temuan. | `claude-3-5-sonnet` |
| `design-visual-storyteller.md` | Visual Storyteller | 📖 | Narasi visual produk, pitch slides, customer journey visualization. | `claude-3-5-sonnet` |
| `design-image-prompt-engineer.md` | Image Prompt Specialist | 🖼️ | Pembuatan prompt generatif (Midjourney/Flux) beresolusi tinggi dan terarah. | `gpt-4o` |
| `design-whimsy-injector.md` | Whimsy Injector | ✨ | Menambahkan sentuhan kejutan, mikro-copy humoris, easter eggs, elemen emosional UI. | `claude-3-5-sonnet` |

---

## 2.3 Divisi Project Management
- **Ikon UI:** `ClipboardList` | **Warna Aksen:** `#0EA5E9` (Sky Blue) | **Lokasi:** Lantai 2 — War Room & Sprint Planning Hall  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `project-manager-senior.md` | Senior Project Manager | 📋 | Perencanaan timeline, alokasi beban kerja multi-agen, mitigasi risiko keterlambatan. | `claude-3-7-sonnet` |
| `project-management-project-shepherd.md` | Project Shepherd | 🐑 | Mengawal tugas dari konsep hingga rilis, memastikan tidak ada tugas yang terabaikan. | `claude-3-5-sonnet` |
| `project-management-studio-producer.md` | Studio Producer | 🎬 | Koordinasi deliverables lintas divisi (Desain + Dev + QA), pelaporan sprint. | `claude-3-5-sonnet` |
| `project-management-jira-workflow-steward.md` | Jira Workflow Steward | 🎫 | Struktur tiket terstandar, acceptance criteria baku, otomatisasi status transisi. | `fast-tier` (Gemini Flash) |
| `project-management-meeting-notes-specialist.md` | Meeting Notes Specialist | 📝 | Ekstraksi ringkasan rapat, keputusan kunci, dan action items berpemilik jelas. | `gemini-2.5-flash` |
| `project-management-experiment-tracker.md` | Experiment Tracker | 🧪 | Pelacakan hipotesis A/B testing, matriks evaluasi, pencatatan hasil eksperimen. | `claude-3-5-sonnet` |

---

## 2.4 Divisi Product
- **Ikon UI:** `Box` | **Warna Aksen:** `#D946EF` (Fuchsia) | **Lokasi:** Lantai 2 — Product Strategy Zone  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `product-manager.md` | Product Manager | 📦 | PRD (Product Requirements Document), product-market fit, value proposition. | `claude-3-7-sonnet` |
| `product-sprint-prioritizer.md` | Sprint Prioritizer | ⚖️ | RICE scoring (Reach, Impact, Confidence, Effort), pemangkasan scope yang membengkak. | `claude-3-7-sonnet` |
| `product-feedback-synthesizer.md` | Feedback Synthesizer | 💬 | Mengelompokkan masukan pengguna menjadi pola insight dan ide fitur konkrit. | `claude-3-5-sonnet` |
| `product-dx-engineer.md` | Developer Experience (DX) | 🛠️ | Memastikan kemudahan SDK, dokumentasi API interaktif, dan onboarding developer. | `deepseek-chat` |
| `product-trend-researcher.md` | Product Trend Analyst | 📈 | Analisis tren pasar teknologi, benchmarking kompetitor, rekomendasi fitur masa depan. | `claude-3-5-sonnet` |

---

## 2.5 Divisi Testing & QA
- **Ikon UI:** `FlaskConical` | **Warna Aksen:** `#F59E0B` (Amber) | **Lokasi:** Lantai 3 — QA & Stress Testing Zone  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `testing-reality-checker.md` | Reality Checker | 🧐 | Skeptis profesional: menguji apakah klaim kode sesuai bukti nyata eksekusi terminal. | `deepseek-r1` / `claude-3-7` |
| `testing-test-automation-engineer.md` | Test Automation Lead | 🤖 | Penulisan skrip Unit (PHPUnit/Vitest), Integration, dan E2E (Playwright). | `deepseek-chat` / `claude-3-5` |
| `testing-api-tester.md` | API Tester | 📮 | Validasi payload boundary, status code, idempotensi, race conditions REST API. | `deepseek-chat` |
| `testing-accessibility-auditor.md` | Accessibility Auditor | ♿ | Verifikasi kepatuhan WCAG 2.2 Level AA/AAA, navigasi keyboard, screen reader. | `claude-3-5-sonnet` |
| `testing-evidence-collector.md` | Evidence Collector | 📸 | Mengumpulkan log eksekusi, bukti uji, dan laporan verifikasi sebelum rilis. | `fast-tier` (Gemini Flash) |
| `testing-performance-benchmarker.md` | Benchmark Specialist | ⏱️ | Pengujian beban (Load testing), latensi p95/p99, throughput req/sec, profiling CPU/RAM. | `deepseek-chat` |

---

## 2.6 Divisi Security
- **Ikon UI:** `ShieldCheck` | **Warna Aksen:** `#EF4444` (Merah Siaga) | **Lokasi:** Lantai 3 — Security Operations Center (SOC)  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `security-architect.md` | Security Architect | 🏰 | Zero-trust architecture, threat modeling (STRIDE), otentikasi aman, enkripsi. | `claude-3-7-sonnet` |
| `security-appsec-engineer.md` | AppSec Engineer | 🛡️ | Pencegahan OWASP Top 10 (SQL Injection, XSS, CSRF, IDOR), sanitasi input. | `claude-3-7-sonnet` |
| `security-penetration-tester.md` | Penetration Tester | 🥷 | Simulasi serangan eksploitasi, fuzzing payload, penemuan celah bypass otorisasi. | `claude-3-7-sonnet` |
| `security-ai-generated-code-auditor.md` | AI Code Auditor | 🤖 | Mendeteksi celah halus yang dihasilkan AI (halusinasi package, insecure defaults). | `claude-3-7-sonnet` |
| `security-secrets-credential-engineer.md` | Secrets Auditor | 🔑 | Deteksi kebocoran hardcoded credentials, konfigurasi Vault, rotasi token otomatis. | `fast-tier` / `deepseek` |
| `security-compliance-auditor.md` | Compliance Auditor | 📜 | Kepatuhan regulasi data (GDPR, ISO 27001, SOC 2, HIPAA, UU PDP). | `claude-3-5-sonnet` |

---

## 2.7 Divisi Spatial Computing
- **Ikon UI:** `Boxes` | **Warna Aksen:** `#06B6D4` (Cyan) | **Lokasi:** Lantai 4 — Spatial & WebXR R&D Lab  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `xr-interface-architect.md` | XR Interface Architect | 🥽 | Desain antarmuka spasial 3D, depth ergonomics, foveated interaction, 3D UI hierarchy. | `claude-3-7-sonnet` |
| `xr-immersive-developer.md` | WebXR Developer | 🌐 | Implementasi Three.js/WebGPU, canvas rendering, spatial shaders, interactive raycasting. | `deepseek-chat` / `claude-3-5` |
| `visionos-spatial-engineer.md` | VisionOS Engineer | 👓 | RealityKit, SwiftUI spasial, spatial gestures, eye tracking interactions. | `claude-3-7-sonnet` |
| `macos-spatial-metal-engineer.md` | Metal 3D Engineer | ⚡ | Pemrograman shader grafis Metal, optimasi rendering tingkat rendah, performa 60+ FPS. | `deepseek-chat` |

---

## 2.8 Divisi Finance
- **Ikon UI:** `DollarSign` | **Warna Aksen:** `#22C55E` (Hijau Finansial) | **Lokasi:** Lantai 1 — Finance & Budgeting Office  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `finance-fpa-analyst.md` | FP&A Analyst | 📊 | Peramalan arus kas, run rate, alokasi anggaran, varians biaya operasional. | `claude-3-7-sonnet` |
| `finance-bookkeeper-controller.md` | Bookkeeper Controller | 📒 | Rekonsiliasi transaksi, kepatuhan pembukuan ganda (double-entry), ledger audit. | `deepseek-chat` |
| `finance-tax-strategist.md` | Tax Strategist | 🏛️ | Strategi efisiensi pajak, kepatuhan yurisdiksi, depresiasi aset teknologi. | `claude-3-7-sonnet` |
| `finance-investment-researcher.md` | Investment Analyst | 💼 | Evaluasi ROI proyek perangkat lunak, perbandingan CAPEX vs OPEX infrastruktur cloud. | `claude-3-5-sonnet` |

---

## 2.9 Divisi Paid Media & Marketing
- **Ikon UI:** `Target` | **Warna Aksen:** `#EAB308` (Emas) | **Lokasi:** Lantai 1 — Growth & Acquisition Pods  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `paid-media-ppc-strategist.md` | PPC Strategist | 🎯 | Struktur kampanye Google Search/Shopping, bid optimization, cost-per-click efficiency. | `claude-3-5-sonnet` |
| `paid-media-paid-social-strategist.md` | Paid Social Strategist | 📱 | Kampanye Meta/LinkedIn/TikTok, audience targeting, funnel retargeting. | `claude-3-5-sonnet` |
| `paid-media-tracking-specialist.md` | Tracking Specialist | 📡 | GTM, GA4 server-side tagging, conversion API (CAPI), atribusi akurat. | `deepseek-chat` |
| `paid-media-auditor.md` | Ad Account Auditor | 🔍 | Audit 200+ poin kesehatan akun iklan, eliminasi pemborosan anggaran. | `claude-3-7-sonnet` |

---

## 2.10 Divisi Sales & Revenue
- **Ikon UI:** `TrendingUp` | **Warna Aksen:** `#10B981` (Emerald) | **Lokasi:** Lantai 1 — Business Development Lounge  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `sales-engineer.md` | Sales Engineer | 🤝 | Menjembatani kapabilitas teknis dengan kebutuhan klien, PoC teknis, arsitektur demo. | `claude-3-5-sonnet` |
| `sales-proposal-strategist.md` | Proposal Strategist | 📄 | Penyusunan RFP/RFI, penulisan proposal tender, kalkulasi nilai tambah solusi. | `claude-3-5-sonnet` |
| `sales-pipeline-analyst.md` | Pipeline Analyst | 📈 | Pemantauan siklus deal, identifikasi hambatan negosiasi, proyeksi kuartal. | `gemini-2.5-flash` |

---

## 2.11 Divisi Support & Operations
- **Ikon UI:** `LifeBuoy` | **Warna Aksen:** `#84CC16` (Lime Green) | **Lokasi:** Lantai 1 — Helpdesk & Customer Experience Center  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `support-support-responder.md` | Customer Support Lead | 🎧 | Eskalasi tiket pengguna, penanganan masalah empati, resolusi first-contact. | `gemini-2.5-flash` |
| `support-analytics-reporter.md` | Analytics Reporter | 📊 | Pembuatan laporan rutin performa aplikasi, visualisasi metrik kesehatan sistem. | `claude-3-5-sonnet` |
| `support-legal-compliance-checker.md` | Legal Compliance | ⚖️ | Pengecekan klausul privasi, ToS, kepatuhan lisensi open-source. | `claude-3-7-sonnet` |

---

## 2.12 Divisi GIS & Spatial Data
- **Ikon UI:** `Map` | **Warna Aksen:** `#14B8A6` (Teal) | **Lokasi:** Lantai 4 — GeoSpatial Data Center  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `gis-3d-scene-developer.md` | 3D GIS Scene Developer | 🗺️ | Integrasi data geospasial 3D ke Three.js/Cesium, digital twin perkotaan. | `deepseek-chat` / `claude-3-5` |
| `gis-spatial-data-engineer.md` | Spatial Data Engineer | 🌐 | PostGIS queries, GeoJSON processing, transformasi koordinat (WGS84 ke UTM). | `deepseek-chat` |
| `gis-drone-reality-mapping.md` | Reality Mapping Specialist | 🛸 | Fotogrametri, point cloud processing (LiDAR), mesh reconstruction 3D. | `claude-3-7-sonnet` |

---

## 2.13 Divisi Game Development
- **Ikon UI:** `Gamepad2` | **Warna Aksen:** `#A855F7` (Ungu Interaktif) | **Lokasi:** Lantai 4 — Game Tech & Simulation Wing  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `technical-artist.md` | Technical Artist | 🎨 | Rigging karakter 3D, shader visual GLSL, pipeline ekspor .GLB efisien untuk Three.js. | `claude-3-5-sonnet` |
| `level-designer.md` | Level Designer | 🏢 | Penataan denah lingkungan 3D, pencahayaan dramatis, occlusion culling, flow navigasi. | `claude-3-5-sonnet` |
| `game-audio-engineer.md` | Spatial Audio Engineer | 🔊 | Desain soundscape kantor 3D, positional audio WebAudio, audio cues status agen. | `fast-tier` (Gemini Flash) |

---

## 2.14 Divisi Specialized & Strategic
- **Ikon UI:** `Sparkles` | **Warna Aksen:** `#6366F1` (Indigo) | **Lokasi:** Lantai 4 — Executive Command Suite  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `agents-orchestrator.md` | Master Orchestrator | 🧙‍♂️ | Koordinator tertinggi multi-agen: menganalisis instruksi besar, memecah delegasi. | `claude-3-7-sonnet` |
| `specialized-mcp-builder.md` | MCP Builder Specialist | 🔌 | Membangun server dan tools Model Context Protocol (MCP) untuk memperluas akses agen. | `claude-3-7-sonnet` |
| `specialized-codebase-archaeologist.md` | Codebase Archaeologist | 🏺 | Menganalisis git blame bertahun-tahun, mengungkap alasan historis arsitektur kuno. | `claude-3-5-sonnet` |
| `specialized-chief-of-staff.md` | Chief of Staff | 🎖️ | Menjaga keselarasan strategi seluruh departemen, menyaring prioritas utama pimpinan. | `claude-3-7-sonnet` |

---

## 2.15 Divisi Academic, Research, & Healthcare
- **Ikon UI:** `GraduationCap` | **Warna Aksen:** `#8B5CF6` (Ungu Riset) | **Lokasi:** Lantai 4 — Research Library & Think Tank  

| File Markdown Agen | Nama Agen | Emoji | Tanggung Jawab Utama | Rekomendasi Model (9Router) |
|---|---|---|---|---|
| `academic-statistician.md` | Statistician | 📐 | Analisis regresi, signifikansi statistik p-value, verifikasi bias data. | `deepseek-r1` / `claude-3-7` |
| `academic-psychologist.md` | Behavioral Psychologist | 🧠 | Analisis psikologi kognitif interaksi antarmuka pengguna, kebiasaan digital. | `claude-3-5-sonnet` |
| `research-synthesist.md` | Research Synthesist | 🔬 | Sintesis puluhan jurnal dan whitepaper ilmiah menjadi rekomendasi praktis. | `claude-3-7-sonnet` |

---

# BAB 3: INJEKSI PERSONA DARI AGENCY-AGENTS KE CI4 AGENT ENGINE

Di CodeIgniter 4, setiap agen diaktifkan melalui penyusunan `system prompt` dinamis yang mengambil data persona langsung dari tabel MySQL `agent_profiles`.

## 3.1 Template Kompilasi Prompt CI4
```php
public function buildSystemPrompt(object $agent): string
{
    return <<<PROMPT
You are {$agent->name}, operating as a specialized AI agent in the Kantor 3D (MCE-eOffice) workspace.

=== IDENTITY & VIBE ===
Vibe: "{$agent->vibe}"
{$agent->identity_prompt}

=== PRIMARY MISSION ===
{$agent->core_mission}

=== STRICT OPERATIONAL GUARDRAILS ===
1. You MUST operate strictly within your domain and follow professional standards.
2. NEVER produce vague or hand-waving explanations when concrete code or data is requested.
{$agent->critical_rules}

=== WORKFLOW METHODOLOGY ===
{$agent->workflow_steps}

=== DELIVERABLE FORMAT ===
{$agent->deliverables_spec}

=== COMMUNICATION STYLE ===
- Tone: Professional, structured, direct, and zero fluff.
- Language: Follow user preference (default: Bahasa Indonesia formal/teknis).
PROMPT;
}
```

---

# BAB 4: STRATEGI SMART ROUTING & FAILOVER PADA 9ROUTER

CI4 mengirimkan request ke 9Router (`http://127.0.0.1:20128/v1/chat/completions`) menggunakan format standar OpenAI.

## 4.1 Penetapan Model Dinamis
CI4 menentukan model tujuan berdasarkan `preferred_model_tier` milik agen:
- **`high_reasoning`:** Ditujukan ke `claude-3-7-sonnet` (Auto-fallback ke `deepseek-r1` atau `o3-mini`).
- **`coding`:** Ditujukan ke `deepseek-chat` (Auto-fallback ke `claude-3-5-sonnet` atau `qwen2.5-coder`).
- **`fast_triage`:** Ditujukan ke `gemini-2.5-flash` (Auto-fallback ke `ollama/llama3.3`).

## 4.2 Auto-Failover di Layer 9Router
Jika penyedia AI utama mengalami *HTTP 429 (Rate Limit)* atau *503 (Service Unavailable)*:
- 9Router langsung mengalihkan request ke penyedia cadangan secara otomatis dalam waktu < 500 milidetik.
- CI4 dan antarmuka Three.js tidak mengalami *disconnect*, aliran teks SSE tetap berjalan lancar.

---

# BAB 5: PENATAAN SPASIAL 3D (ZONING KANTOR 4 LANTAI & MEJA)

| Lantai | Tema Lantai | Divisi yang Menempati | Target Kamera GSAP (Y) |
|---|---|---|---|
| **Lantai 1** | Client Experience & Operations | Reception, Sales, Finance, Support, Marketing | `Y = 0.0` |
| **Lantai 2** | Product, Design & Agile Pods | Product, UI/UX Design, Project Management (War Room) | `Y = 10.0` |
| **Lantai 3** | Engineering & Cyber Security | Engineering Lab, QA Sandbox, Security SOC | `Y = 20.0` |
| **Lantai 4** | Innovation & Strategic Command | Executive Suite, Spatial R&D, GIS Center, Research Think Tank | `Y = 30.0` |

---

# BAB 6: SPESIFIKASI BACKEND CI4 & SKEMA DATABASE MYSQL

Database MySQL bertindak sebagai penyimpan state terpusat:
- **`divisions`**: Menyimpan 18 divisi (label, ikon Lucide, warna hex, lantai).
- **`agent_profiles`**: Menyimpan 283 profil persona hasil ekstraksi `agency-agents`.
- **`office_rooms` & `office_desks`**: Menyimpan koordinat ruang dan meja kerja 3D.
- **`characters`**: Menghubungkan model 3D avatar (.glb) dengan profil agen dan meja kerja.
- **`agent_tasks`**: Menyimpan penugasan terstruktur dari pengguna ke agen pimpinan.
- **`conversation_messages`**: Riwayat interaksi obrolan dan pencatatan token dari 9Router.

---

# BAB 7: ORKESTRASI MULTI-AGEN DI CODEIGNITER 4

Tanpa memerlukan agent daemon eksternal, CI4 mengorkestrasi pendelegasian tugas secara berantai melalui `TaskService`:

```
[ PENGGUNA MEMBERIKAN BRIEF TUGAS ]
              │
              ▼
[ Marcus - Senior PM ] (Lead Agent)
  ── Mengurai User Story & Kriteria Akseptansi
  ── CI4 mengirim konteks ke 9Router
              │
              ├──────> [ Victor - Software Architect ]
              │        ── Membuat desain skema & arsitektur API
              │
              ├──────> [ Devon - Backend Engineer ]
              │        ── Menulis Controller & Migration CI4
              │
              └──────> [ Quinn - Reality QA ]
                       ── Mengaudit edge-case & verifikasi fungsionalitas
              │
              ▼
[ CI4 Menggabungkan Hasil Deliverables Menjadi Laporan Akhir di Meja Pengguna ]
```

---

# BAB 8: PROTOKOL DELIVERABLES & BUKTI AKSEPTANSI

Setiap tugas yang diselesaikan agen disajikan ke pengguna dalam struktur deliverables:
1. **Executive Brief:** Ringkasan solusi dalam 2–3 kalimat.
2. **Artifacts Utama:** Kode sumber utuh (tanpa placeholder), skema SQL, atau diagram arsitektur.
3. **Trade-offs & Security Check:** Alasan pemilihan solusi dan mitigasi risiko keamanan.
4. **Actionable Button:** Tombol **"Approve & Apply"** atau **"Request Revision"** di antarmuka 3D.

---

# BAB 9: KEAMANAN, RBAC, DAN TELEMETRI BIAYA

1. **Zero Secret Exposure:** Kunci API (Anthropic, DeepSeek, OpenAI) hanya tersimpan pada konfigurasi `9Router`. Tidak ada string kunci pada kode CI4 maupun database.
2. **Local Loopback:** 9Router hanya mendengarkan koneksi lokal (`127.0.0.1`), terlindungi dari akses jaringan publik.
3. **Audit Token Terpusat:** CI4 mencatat konsumsi token (input/output) per agen dan per departemen untuk memantau efisiensi biaya.

---

# BAB 10: AUTOMATION SEEDER DARI DATA AGENCY-AGENTS KE MYSQL

Backend CI4 telah dilengkapi dengan skrip Spark Seeder yang membaca berkas kompilasi `agency-agents/integrations/hermes/agency-agents-router/data/agents.json` (4.27 MB, 283 agen) dan mengisinya ke tabel `agent_profiles` secara otomatis dalam sekali perintah:

```bash
php spark agency:sync
```

---

**DOKUMEN INI ADALAH ENSIKLOPEDIA & SPESIFIKASI RESMI KANTOR 3D (MCE-eOffice).**  
*Menerapkan arsitektur ramping: Vue 3 (Three.js 3D) ⟷ CodeIgniter 4 (Full API + MySQL) ⟷ 9Router (AI Gateway).*
