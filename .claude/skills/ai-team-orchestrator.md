---
name: ai-team-orchestrator
description: AI-TEAM orkestrator. /ai-team komutu ile agent routing ve gorev atama.
---

# AI-TEAM Orkestrator

`/ai-team <görev>` ile çağrıldığında:

## 1. Agent Routing

```bash
npx tsx .ai-team/orchestrator/cli.ts route --brief "<görev>"
```

CLI çalışmazsa fallback routing tablosu:

| Anahtar | Agent |
|---------|-------|
| frontend, react, nextjs, sayfa, ui | ENG-01 Frontend |
| backend, api, endpoint, node | ENG-03 Backend |
| php, laravel | ENG-04 PHP |
| database, sql, postgresql | ENG-06 DBA |
| test, qa | QA-01 QA Lead |
| bug, hata, debug | QA-02 Debugger |
| güvenlik, security | QA-03 Security |
| docker, deploy, ci-cd | OPS-01 DevOps |
| seo, blog | COM-02 SEO |
| görsel, resim, image | CRE-02 Image Gen → `/image-gen` skill kullan |
| video, animasyon | CRE-06 Video → `/video-gen` skill kullan |
| tasarım, poster, sunum | CRE-01 UX → `/canva-design` skill kullan |
| finans, kripto, borsa | FIN-01 Finance |
| bist, teknik analiz | FIN-02 BIST TA |

## 2. Agent Tanıtımı

```
━━━ AI-TEAM ━━━━━━━━━━━━━━━━━━━━
[AGENT-ID] | [Agent Adı]
Görev: [özet]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## 3. Görevi Yap

Agent spec'ini oku: `.ai-team/agents/[takım]/[AGENT-ID]-*.md`
Kurallara ve yasaklara uy.

## 4. Sonuç Raporu

```
━━━ AI-TEAM SONUÇ ━━━━━━━━━━━━━━
[AGENT-ID] | Tamamlandı
Değişiklikler: [liste]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```
