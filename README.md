# 🤖 Tutorial Lengkap Membangun AI Agent dengan PHP 8.3 & OpenAI API

> **Koleksi Repositori Resmi untuk Seri Tutorial AI Agent & PHP di [InfoKoding.com](https://infokoding.com/tutorial/ai-agent-php)**  
> **Penulis**: [Rusmawan Abdullah Sani](https://www.linkedin.com/in/rusmawan-abdullah-sani-3b015945/)

---

## 📌 Fitur Utama Repositori

- 🚀 **Built for PHP 8.3+**: Memanfaatkan fitur modern seperti `readonly class`, `typed properties`, `enums`, dan `strict types`.
- 🔄 **Agent Loop Otonom**: Siklus 5 tahapan (*Observe → Think → Plan → Act → Reflect*).
- 🛠️ **OpenAI Function Calling (Tools)**: Contoh integrasi Tool Database MySQL PDO, Calculator, REST API, File Reader (TXT/PDF/CSV), & Currency Converter.
- 🔗 **MCP (Model Context Protocol)**: Implementasi arsitektur Model Context Protocol untuk integrasi aman dengan data enterprise.
- 🛡️ **Production-Ready**: Dilengkapi dengan Structured JSON Logging, Authentication Token, Rate Limiting, dan Security Hardening.

---

## 🚀 Panduan Instalasi & Penggunaan

### 1. Clone Repositori
```bash
git clone https://github.com/admininfokoding/php-ai-agent-tutorial.git
cd php-ai-agent-tutorial
```

### 2. Salin Konfigurasi Environment
```bash
cp .env.example .env
```
Edit file `.env` dan masukkan `OPENAI_API_KEY` milik Anda:
```ini
OPENAI_API_KEY=sk-proj-YOUR_ACTUAL_OPENAI_API_KEY
OPENAI_MODEL=gpt-4o-mini
```

### 3. Jalankan Skrip Contoh Bab 1
```bash
php examples/bab-01-apa-itu-ai-agent.php
```

---

## 📚 Struktur Direktori Project

```text
.
├── config/             # File konfigurasi lingkungan & database
├── examples/           # Contoh skrip runnable per bab tutorial
│   └── bab-01-apa-itu-ai-agent.php
├── src/                # Source code utama (PSR-4 App\)
│   ├── Agent/          # Core Agent Loop engine
│   ├── Tools/          # Koleksi Tools (StockTool, CalculatorTool, dll)
│   ├── MCP/            # Client & Server Model Context Protocol
│   └── Logger/         # Logging system
├── .env.example
├── composer.json
├── index.php
└── README.md
```

---

## 📜 Lisensi & Kontribusi

Projek ini berlisensi [MIT License](LICENSE). Bebas digunakan, di-clone, dan dikembangkan untuk keperluan belajar maupun komersial.

Dipublikasikan oleh **[InfoKoding.com](https://infokoding.com)**. Profil LinkedIn Penulis: [Rusmawan Abdullah Sani](https://www.linkedin.com/in/rusmawan-abdullah-sani-3b015945/).
