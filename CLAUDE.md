# zirve_donusum

## Proje
- **Stack**: belirtilmedi
- **Dil**: TR
- **AI-TEAM**: `.ai-team/` (68 agent, 11 takim, v5.0 orkestrator)

## Model Kullanim Kurali (ZORUNLU)
- **Planlama / Mimari / Analiz / Spec / Orkestrasyon**: `claude-opus-4-7` (Opus 4.7)
- **Uygulama / Kod / Refactor / Test / Deploy**: `claude-sonnet-4-6` (Sonnet 4.6)
- Plan Mode'da `/model opus`, `ExitPlanMode` sonrasi `/model sonnet`.
- `.claude/settings.json` default = `claude-sonnet-4-6`, `availableModels` sadece bu iki modeli allow'lar.

## Mod Kurallari (v6.0)

**Varsayilan mod**: Claude Code direkt cevaplar — hicbir komut gerekmez, orchestrator devreye girmez.

| Mod | Tetikleyici | Ne Yapar |
|-----|-------------|----------|
| **Claude Max** | Komut yok veya `/ai-team` | Sadece Claude Code, agent persona ile gorev tamamlar |
| **Full Orchestrator** | `/orchestrator` | Claude Max + Gemini + Higgsfield + Claude API — tum sistemler aktif |

### Claude Max Modu (varsayilan)
- Komut yazmadan gelen her istek: **direkt Claude Code cevaplar**, hicbir skill/CLI devreye girmez
- `/ai-team <gorev>`: Claude agent persona secer ve gorevi tamamlar, CLI cagrilmaz
- `@AGENT-ID <gorev>`: o agent'in spec'ini okuyarak gorev tamamlar

### Full Orchestrator Modu
- Sadece `/orchestrator` komutu ile tetiklenir
- Multi-AI Council, Claude API kuyrugu, Gemini, Higgsfield aktif olur

## Direkt Agent Secimi

`@AGENT-ID` ile direkt agent secebilirsin:
- `@ENG-01 responsive navbar yap`
- `@QA-03 güvenlik taraması`
- `@FIN-02 BIST teknik analiz`

## Temel Kurallar
1. Testsiz kod merge edilmez
2. Kodda secret yok (`.env` kullan)
3. Commit: `feat|fix|refactor(scope): aciklama`
4. Turkce karakterler dogru kullanilmali (UTF-8)

## Kaynaklar
- **68 Agent**: `.ai-team/agents/` (11 takim)
- **25 Skill**: `.ai-team/.claude/skills/`
- **Standartlar**: `.ai-team/standards/`
- **Workflow'lar**: `.ai-team/workflows/`
- **Orkestrator**: `.ai-team/orchestrator/`
