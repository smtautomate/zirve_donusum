# /ai-team — AI-TEAM Orkestrator

**Kullanim**: `/ai-team <gorev aciklamasi>`

Bu komut AI-TEAM orkestratoru uzerinden dogru agent'i secip gorevi baslatir.

## Adimlar

1. Orchestrator CLI calistir:
   ```bash
   npx tsx .ai-team/orchestrator/cli.ts route --json "$ARGUMENTS"
   ```

2. JSON ciktidan agent bilgilerini oku

3. Agent tanitim blogu goster:
   ```
   ━━━ AI-TEAM ━━━━━━━━━━━━━━━━━━━━━━━━━━
   [AGENT-ID] | [Agent Adi]
   Gorev: $ARGUMENTS
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   ```

4. Agent spec dosyasini oku: `.ai-team/agents/[takim]/[AGENT-ID]-*.md`

5. Agent persona'siyla gorevi tamamla — kurallara ve yasaklara uy

6. Sonuc raporu goster:
   ```
   ━━━ AI-TEAM SONUC ━━━━━━━━━━━━━━━━━━━━
   [AGENT-ID] | Gorev tamamlandi
   Degisiklikler: [liste]
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   ```
