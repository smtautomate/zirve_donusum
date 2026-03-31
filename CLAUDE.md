# Proje Talimatları

## AI-TEAM Entegrasyonu

Bu proje AI-TEAM OS v5.0 ile entegredir. Agent specleri, standartlar ve orchestrator `ai-team/` symlink'i üzerinden erişilebilir.

- **AI-TEAM**: `ai-team/` (66 agent, 11 takım, 18 workflow)
- **Orchestrator**: `ai-team/orchestrator/` (TypeScript, 10.490 satır)
- **Standartlar**: `ai-team/standards/` (24 standart)

### `ai-team:` — AI Orkestratör

Kullanıcı `ai-team:` ile başlayan mesaj yazdığında, ilgili agent spec'ini oku ve o agent'ın rolüne bürünerek yanıt ver:

1. `ai-team:` sonrasındaki mesajı analiz et
2. `ai-team/orchestrator/router/routing-matrix.ts` üzerinden doğru agent'ı belirle
3. `ai-team/agents/[takım]/[AGENT-ID]-*.md` spec dosyasını oku
4. Agent'ın uzmanlık alanına göre yanıt ver

## AI-TEAM OS Entegrasyonu

Bu proje **AI-TEAM OS** (66 agent, 11 takım, 18 workflow) tarafından desteklenir.
Symlink: `ai-team/` → `/Volumes/macOS-SSD/GitHub/ai-team`

### ai-team: Komutu

Kullanıcı `ai-team:` ile başlayan bir mesaj yazdığında, orkestratörü çalıştır:

```bash
npx tsx ai-team/orchestrator/cli.ts "kullanıcı isteği"
```

Çıktıdaki agent rolünü benimseyerek o agent'ın kurallarına, yasaklarına ve standartlarına uy. Çıktıdaki system prompt'u sessizce uygula — kullanıcıya teknik detay gösterme.

### Nasıl Çalışır

1. Kullanıcı yazar: `ai-team: Login sayfası oluştur`
2. Sen CLI'ı çalıştırırsın → ENG-01 Frontend Engineer seçilir
3. ENG-01'in kurallarına göre kodu yazarsın (framework, test dahil)
4. Sonucu kullanıcıya normal şekilde sunarsın

### Örnek Komutlar

| Komut | Agent |
|-------|-------|
| `ai-team: Login sayfası oluştur` | ENG-01 (Frontend) |
| `ai-team: REST API endpoint ekle` | ENG-03 (Backend) |
| `ai-team: PostgreSQL sorgu optimize et` | ENG-06 (DBA) |
| `ai-team: güvenlik taraması yap` | QA-03 (Security) |
| `ai-team: Docker pipeline kur` | OPS-01 (DevOps) |
| `ai-team: test yaz` | QA-01 (QA Lead) |
| `ai-team: bug düzelt` | QA-02 (Debugger) |
| `ai-team: deploy et` | OPS-01 + OPS-02 |
| `ai-team: SEO optimize et` | COM-02 (SEO) |
| `ai-team: performans analizi` | ENG-12 (Performance) |
| `ai-team: n8n workflow oluştur` | DAT-04 (n8n) |

### Diğer CLI Komutları

```bash
npx tsx ai-team/orchestrator/cli.ts agents        # Tüm agent listesi
npx tsx ai-team/orchestrator/cli.ts status        # Orkestratör durumu
npx tsx ai-team/orchestrator/cli.ts context ENG-01  # Tek agent context
```

### Kaynaklar

- **66 Agent**: `ai-team/agents/` (11 takım)
- **Standartlar**: `ai-team/standards/`
- **Workflow'lar**: `ai-team/workflows/`
- **Şablonlar**: `ai-team/templates/`
- **Orkestratör**: `ai-team/orchestrator/`
- **OS Spec**: `ai-team/operating-system.md`

### Temel Kurallar (ai-team/operating-system.md)

1. Testsiz kod merge edilmez
2. Kodda secret yok (`.env` kullan)
3. Türkçe karakterler (ğüşöçıİ) korunmalı — ASCII dönüşümü yasak (OS 2.5)
4. Commit formatı: `feat|fix|refactor(scope): açıklama`
