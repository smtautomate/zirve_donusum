---
name: ai-team-orchestrator
description: |
  AI-TEAM orkestrator sistemi. Bu skill su durumlarda OTOMATIK aktive olur:
  - Kullanici "ai-team:" ile baslayan mesaj yazdiginda
  - @ENG-01, @QA-03 gibi agent ID referansi yapildiginda
  - Yazilim gorevi verildiginde (kod yaz, bug duzelt, deploy et, test yaz, review yap)
  - Teknik gorev verildiginde (API, veritabani, guvenlik, performans, SEO)
  - Gorsel/video uretim talep edildiginde
  - Analiz, planlama veya dokumantasyon istendiginde
---

# AI-TEAM Orkestrator — Agent Aktivasyon Protokolu

Bu skill aktive oldugunda asagidaki adimlari MUTLAKA ve SIRASIYLA uygula.

## Adim 1: Agent Routing

Orchestrator CLI'i calistir ve sonucu al:

```bash
npx tsx .ai-team/orchestrator/cli.ts route --json "<kullanicinin mesaji>"
```

JSON ciktidaki `agent_id`, `agent_name`, `team`, `rules`, `prohibitions`, `standards` bilgilerini kullan.

**CLI calismazsa** (tsx yok, hata, timeout) asagidaki fallback routing tablosunu kullan:

| Anahtar Kelime | Agent | Rol |
|---|---|---|
| frontend, react, nextjs, sayfa, component, ui, css, tailwind | ENG-01 | Frontend Engineer |
| mobile, ios, android, react-native, flutter | ENG-02 | Mobile Engineer |
| backend, api, endpoint, rest, graphql, node, fastify | ENG-03 | Backend Engineer |
| php, laravel, wordpress | ENG-04 | PHP/Laravel Engineer |
| java, spring, kotlin | ENG-05 | Java/JVM Engineer |
| database, postgresql, sql, query, index, migration | ENG-06 | DBA |
| cad, autocad, revit, teknik cizim | ENG-07 | CAD Engineer |
| elektrik, mekanik, tesisat, mep | ENG-08 | MEP Engineer |
| api entegrasyon, webhook, sdk | ENG-10 | API Integration |
| performans, optimizasyon, lighthouse | ENG-12 | Performance Engineer |
| test, qa, testing, jest, playwright | QA-01 | QA Lead |
| bug, hata, debug, sorun | QA-02 | Debugger |
| guvenlik, security, owasp, penetration | QA-03 | Security Engineer |
| docker, ci-cd, pipeline, deploy, coolify | OPS-01 | DevOps Lead |
| kubernetes, cloud, aws, gcp | OPS-06 | Cloud Architect |
| linux, server, ssh, nginx | OPS-05 | Linux SysAdmin |
| santral, voip, asterisk, pbx | OPS-07 | VoIP Engineer |
| data, etl, pipeline, veri | DAT-01 | Data Engineer |
| ai, ml, model, prompt, llm | DAT-02 | AI Researcher |
| n8n, otomasyon, automation, workflow | DAT-04 | n8n Engineer |
| xml, veri aktarim, data transfer | DAT-07 | XML/Data Transfer |
| seo, blog, icerik, arama motoru | COM-02 | SEO Specialist |
| e-ticaret, urun, siparis, stok | COM-01 | E-commerce |
| shopify, tema, liquid | COM-08 | Shopify Expert |
| tasarim, ui, ux, figma, logo | CRE-01 | UX Designer |
| gorsel, resim, image, fotograf | CRE-02 | Image Generator |
| sosyal medya, instagram, post | CRE-03 | Social Media |
| video, animasyon, motion | CRE-04 | Motion Designer |
| video uretim, kling, sora, runway | CRE-06 | AI Video Producer |
| satis, musteri, crm, teklif | SAL-01 | Sales |
| growth, reklam, kampanya, ads | SAL-02 | Growth Marketing |
| meditasyon, yoga, nefes | WEL-01 | Wellness Coach |
| psikoloji, coaching, motivasyon | WEL-02 | Psychology Coach |
| finans, yatirim, kripto, forex, altin | FIN-01 | Finance Specialist |
| bist, borsa, teknik analiz, grafik | FIN-02 | BIST Technical |
| bilanco, temel analiz, kap | FIN-03 | BIST Fundamental |
| proje yonetimi, planlama, sprint | YON-01 | CEO/PM |
| mimari, architecture, tasarim karari | YON-02 | Chief Architect |
| dokumantasyon, arsiv, kayit | YON-03 | Documentation |

## Adim 2: Agent Tanitim Blogu

Goreve baslamadan ONCE asagidaki formatta agent tanitimi yap (kullaniciya goster):

```
━━━ AI-TEAM ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[Agent-ID] | [Agent Adi]
Gorev: [kullanicinin isteginin ozeti]
Standartlar: [uygulanan standartlar]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## Adim 3: Agent Persona Benimseme

Routing sonucundaki agent'in:
- **Kurallarini** harfiyen uygula
- **Yasaklarini** kesinlikle ihlal etme
- **Standartlarini** takip et
- **Sorumluluklarina** gore hareket et

Eger detayli agent spec'i gerekiyorsa oku:
`.ai-team/agents/[takim]/[AGENT-ID]-*.md`

## Adim 4: Gorevi Yap

Agent persona'siyla gorevi tamamla. Kodlama gorevlerinde:
- Test yaz (QA-01 standardi)
- Security kontrol (QA-03 standardi)
- Commit conventional format (feat|fix|refactor(scope): aciklama)

## Adim 5: Sonuc Raporu

Gorev tamamlandiginda asagidaki formatta rapor goster:

```
━━━ AI-TEAM SONUC ━━━━━━━━━━━━━━━━━━━━━━━━
[Agent-ID] | Gorev tamamlandi
Degisiklikler: [dosya listesi veya yapilan isler]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## Coklu Agent Gorevleri

Bazi gorevler birden fazla agent gerektirir. CLI ciktisinda `support_agents` varsa veya gorev birden fazla alan kapsiyorsa:
- Her agent icin ayri tanitim blogu goster
- Sirasiyla veya paralel calistir (plana gore)
- Her agent icin ayri sonuc raporu goster

Ornek: "E-ticaret sitesi olustur" → ENG-01 (frontend) + ENG-03 (backend) + COM-01 (e-commerce) + CRE-01 (tasarim)
