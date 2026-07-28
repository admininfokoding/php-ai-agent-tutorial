# CodeIgniter 4 AI Agent Implementation Example

Repositori ini berisi contoh implementasi **AI Agent Otonom** berbasis **PHP 8.3 & CodeIgniter 4** menggunakan pola **ReAct (Reasoning + Acting)** dan **LLM Tool Calling**.

---

## 🛠️ Arsitektur & Komponen

1. **`ToolInterface.php`** (`app/Services/AiAgent/ToolInterface.php`): Interface kontrak standar untuk mendaftarkan tools yang dapat dipanggil oleh LLM.
2. **`DatabaseTool.php`** (`app/Services/AiAgent/Tools/DatabaseTool.php`): Implementasi tool konkret untuk mengambil data transaksi pengguna dari database MySQL dengan Query Builder & Prepared Statements (mencegah SQL Injection).
3. **`AiAgentEngine.php`** (`app/Services/AiAgent/AiAgentEngine.php`): Core engine yang bertugas mengelola siklus ReAct loop (`Observe` ➔ `Think` ➔ `Plan` ➔ `Act` ➔ `Reflect`) dan mengomunikasikannya dengan OpenAI/Gemini REST API.
4. **`AiAgentController.php`** (`app/Controllers/AiAgentController.php`): Controller HTTP yang menerima prompt pengguna, memvalidasi input secara ketat, serta mengembalikan respon JSON.

---

## 🚀 Cara Penggunaan di Projek CodeIgniter 4 Anda

1. **Copy Struktur Folder**:
   Copy folder `app/Services/AiAgent` dan `app/Controllers/AiAgentController.php` ke dalam projek CodeIgniter 4 Anda.

2. **Konfigurasi File `.env`**:
   Tambahkan API Key OpenAI / Gemini Anda ke file `.env`:
   ```env
   OPENAI_API_KEY="sk-proj-xxxxxxxxxxxxxxxxxxxxxxxx"
   ```

3. **Tambahkan Routing di `app/Config/Routes.php`**:
   ```php
   $routes->get('ai-agent', 'AiAgentController::index');
   $routes->post('ai-agent/process', 'AiAgentController::process');
   ```

4. **Jalankan Aplikasi**:
   ```bash
   php spark serve
   ```
   Akses `http://localhost:8080/ai-agent` di browser Anda.

---

## 🔗 Referensi Panduan Complete

Baca artikel panduan lengkapnya di InfoKoding:
[Membuat AI Agent dengan CodeIgniter 4: Panduan Complete & Production-Ready](https://infokoding.com/artikel/membuat-ai-agent-dengan-codeigniter-4)
