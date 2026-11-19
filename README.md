# MCE-eOffice (Mitra Creative Environment - Virtual 3D Office)

> **MCE-eOffice** adalah platform ekosistem kantor virtual 3D generasi baru yang menggabungkan visualisasi spatial interaktif, orkestrasi 283 AI Persona agen kerja terintegrasi, dan manajemen multi-gedung divisi mandiri dengan arsitektur backend andal berbasis **CodeIgniter 4** serta AI routing cerdas via **9Router**.

---

## 🏛️ Arsitektur Sistem

Ekosistem MCE-eOffice menggunakan arsitektur tiga lapis yang ramping dan performan:

```text
┌────────────────────────────────────────────────────────┐
│             Frontend (Vue 3 + Three.js)                │
│  - Strategic Coastal Campus (Pantai, Kelapa, Highway)  │
│  - 18 Gedung Divisi Mandiri & 283 Spatial Workstations │
│  - Dashboard Admin Terpadu (6 Tab Manajemen & Audit)   │
└──────────────────────────┬─────────────────────────────┘
                           │ REST API (JSON / SSE)
┌──────────────────────────▼─────────────────────────────┐
│             Backend (CodeIgniter 4 v4.7.6)             │
│  - Office Ecosystem RESTful API v1 (Port 8080)         │
│  - MySQL Database (Divisions, Agents, Desks, Tasks)    │
│  - RouterService (Health Check, Combos, Offline Fallback)│
└──────────────────────────┬─────────────────────────────┘
                           │ OpenAI-Compatible API
┌──────────────────────────▼─────────────────────────────┐
│             AI Gateway (9Router v0.5.x)                │
│  - Port 20128 (Local Inference Gateway)                │
│  - 9 SDLC Combos (Coding, Architecture, DevOps, dll)   │
│  - Multi-Provider Auto-Failover (OpenRouter, Groq, dll)│
└────────────────────────────────────────────────────────┘
```

---

## ✨ Fitur Utama

### 1. 🌴 Visualisasi Kompleks Kampus Pesisir Strategis (3D)
- **Zona Belakang (Coastal Beachfront):** Laut berombak dinamis (*sine-wave vertex displacement*), pantai pasir dengan payung & lounger santai.
- **Sayap Samping (Flanks):** ~80 pohon kelapa prosedural 3D dengan daun bergoyang mengikuti angin.
- **Zona Depan (Front Highway):** Jalan raya beraspal multi-jalur dengan lalu lintas kendaraan (lampu depan/belakang) dan pedestrian yang bergerak dinamis.
- **18 Gedung Divisi Mandiri:** Arsitektur spesifik per divisi (*Engineering Cyber Tower, Security Hex Fortress, Design Pavilion, GIS Observatory Dome, Finance Skyscraper, Academic Rotunda*, dsb).
- **283 Workstation Spatial:** Komputer 3D dengan layar bercahaya neon, cincin holografik, dan interaksi *raycasting click-to-chat*.

### 2. ⚙️ Dashboard Admin Terpadu
- **📊 Ringkasan & Telemetri:** Metrik KPI 283 agen, 18 gedung, 283 meja kerja, status kesehatan gateway 9Router, dan uji coba inferensi cepat.
- **🤖 Manajemen Agen (283 Personas):** Pencarian dan filter cerdas, pengalihan status aktif/nonaktif, penugasan model routing tier SDLC, serta editor System Prompt Markdown.
- **🏢 Gedung & Divisi (18 Buildings):** Pengaturan label divisi, warna tema (*color picker*), dan konfigurasi koordinat 3D $(X, Y, Z)$ kampus.
- **📋 Manajemen Tugas & Review:** Antarmuka delegasi penugasan, inspeksi hasil pekerjaan AI (*Final Deliverables*), form feedback, dan tombol aksi **Approve** / **Reject**.
- **💬 Audit Log Percakapan:** Pelacakan riwayat obrolan pengguna dengan agen, linimasa pesan, serta konsumsi token prompt & completion.
- **⚡ 9Router AI Gateway Diagnostics:** Monitor status port 20128, ringkasan SDLC combos aktif, dan playground inferensi interaktif dengan metrik latensi *real-time*.

### 3. 🎯 Smart AI Routing (9Router SDLC Combos)
- `sdlc-planning`: Lead architect & perencanaan strategis proyek.
- `sdlc-architecture`: Spesialis arsitektur sistem backend, database, & cloud.
- `sdlc-coding`: Eksekusi kode, implementasi fungsi, dan optimasi performa.
- `sdlc-testing`: Pengujian otomatis, QA checklist, dan validasi edge case.
- `sdlc-devops`: Otomasi CI/CD, kontainerisasi, dan infrastruktur cloud.
- `sdlc-maintenance`: Pemeliharaan, patch keamanan, dan refactoring.
- `infografis`, `video`, `music`: Spesialis kreasi konten visual & multimedia.

---

## 🚀 Panduan Menjalankan Sistem

### Persyaratan Sistem
- **PHP:** >= 8.1 dengan ekstensi `mysqli`, `curl`, `mbstring`, `intl`
- **Database:** MySQL / MariaDB (XAMPP / Standalone)
- **Node.js:** >= 18.x
- **9Router:** Port 20128 (opsional, sistem memiliki fallback offline simulation mode jika gateway belum aktif)

### 1. Konfigurasi Backend (CodeIgniter 4)
```bash
cd backend

# Konfigurasi database di file .env (atur database mce_eoffice)
# Jalankan migrasi database
php spark migrate

# Jalankan server API CodeIgniter 4
php spark serve --host 127.0.0.1 --port 8080
```

### 2. Konfigurasi Frontend (Vue 3 + Three.js)
```bash
cd frontend

# Install dependensi
npm install

# Jalankan server pengembangan Vite
npm run dev

# Atau compile untuk rilis produksi
npm run build
```

Buka browser di `http://localhost:5173` untuk mengakses **MCE-eOffice**.

---

## 📡 Dokumentasi Endpoint REST API v1

| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/v1/office/scene` | Mengambil data lengkap 18 gedung, 283 meja, dan karakter 3D |
| `GET` | `/api/v1/office/stats` | Statistik ringkas jumlah agen, gedung, dan koneksi gateway |
| `GET` | `/api/v1/agents` | Katalog agen AI (filter: `division`, `search`, `floor`) |
| `GET` | `/api/v1/agents/{id}` | Detail profil agen, keahlian, dan system prompt |
| `POST` | `/api/v1/characters/{id}/interact` | Memulai sesi obrolan dengan avatar 3D |
| `POST` | `/api/v1/conversations/{id}/send` | Mengirim pesan obrolan ke agen AI melalui 9Router |
| `GET` | `/api/v1/tasks` | Daftar tugas yang didelegasikan |
| `POST` | `/api/v1/tasks` | Membuat delegasi tugas baru |
| `POST` | `/api/v1/tasks/{id}/approve` | Menyetujui deliverable pekerjaan agen |
| `POST` | `/api/v1/tasks/{id}/reject` | Meminta revisi pekerjaan agen disertai catatan |
| `GET` | `/api/v1/admin/overview` | Telemetri komprehensif untuk Dashboard Admin |
| `GET` | `/api/v1/admin/agents` | Manajemen katalog 283 agen dengan status keaktifan |
| `PUT` | `/api/v1/admin/agents/{id}` | Memperbarui persona, model tier, dan prompt agen |
| `PUT` | `/api/v1/admin/divisions/{id}`| Memperbarui tema dan koordinat 3D gedung divisi |
| `GET` | `/api/v1/admin/conversations` | Audit trail sesi percakapan & konsumsi token |
| `POST` | `/api/v1/admin/test-inference` | Uji inferensi langsung ke 9Router Gateway |

---

## 🔒 Lisensi & Hak Cipta
Dikembangkan oleh **Mitra Creative Environment (MCE)**. Open source software di bawah lisensi MIT.

