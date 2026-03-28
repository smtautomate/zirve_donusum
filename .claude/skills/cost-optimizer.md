---
name: cost-optimizer
description: AI API maliyet optimizasyon asistanı. Yüksek token kullanımı, bütçe uyarısı veya "maliyeti düşür" talebi geldiğinde otomatik aktive olur. DAT-08 ile koordineli çalışır.
---

# Cost Optimizer Skill

Aktive olma koşulları:
- Yüksek token kullanımı tespit edildiğinde
- Bütçe uyarı eşiği aşıldığında
- "Maliyeti düşür", "daha ucuz model", "token optimize et" talepleri
- AI özelliği production'a alınmadan önce maliyet analizi

## Model Maliyet Tablosu (2025-2026)

### Anthropic Claude
| Model | Input $/MTok | Output $/MTok | Context | En İyi Kullanım |
|-------|-------------|---------------|---------|-----------------|
| claude-opus-4-6 | $15.00 | $75.00 | 200K | Karmaşık analiz, kritik kararlar |
| claude-sonnet-4-6 | $3.00 | $15.00 | 200K | Günlük geliştirme (varsayılan) |
| claude-haiku-4-5 | $0.25 | $1.25 | 200K | Basit görevler, yüksek hacim |

### Google Gemini
| Model | Input $/MTok | Output $/MTok |
|-------|-------------|---------------|
| gemini-2.0-flash-exp | $0.075 | $0.30 |
| gemini-1.5-flash | $0.075 | $0.30 |
| gemini-1.5-pro | $1.25 | $5.00 |

### OpenAI
| Model | Input $/MTok | Output $/MTok |
|-------|-------------|---------------|
| gpt-4o-mini | $0.15 | $0.60 |
| gpt-4o | $2.50 | $10.00 |

## Model Seçim Rehberi

```
Görev                                  Model Önerisi
─────────────────────────────────────────────────────────
Basit kod tamamlama (<200 tok output)  Haiku      → %83 tasarruf vs Sonnet
Kısa metin üretme (<500 tok)           Haiku      → %83 tasarruf
Sınıflandırma, etiketleme             Haiku
Metin özeti (kısa)                     Haiku
Rutin sorular                          Haiku

Kod yazma, review, açıklama            Sonnet ✓  (varsayılan)
Karmaşık refactor                      Sonnet
Araştırma, analiz (orta uzunluk)       Sonnet
Multi-step reasoning                   Sonnet
API entegrasyon kodu                   Sonnet

Kritik mimari kararlar                 Opus
Uzun doküman analizi (>50K tok)        Opus
Karmaşık matematik/bilim               Opus
Hukuki/finansal analiz                 Opus
Extended thinking gerektiren           Opus
```

## Prompt Optimizasyon Teknikleri

### 1. Prompt Sıkıştırma
```typescript
// ❌ Verimsiz (fazla token):
const prompt = `
Lütfen aşağıdaki kodu dikkatlice analiz etmenizi ve bu kodun ne yaptığını,
hangi kütüphaneleri kullandığını, olası hataları ve iyileştirme önerilerini
detaylı bir şekilde açıklamanızı istiyorum. Cevabınızı Türkçe olarak yazınız.
`

// ✅ Verimli (aynı sonuç, %40 daha az token):
const prompt = `Kodu analiz et (TR): ne yapar, kütüphaneler, hatalar, öneriler.`
```

### 2. Output Uzunluğu Kontrolü
```typescript
const response = await anthropic.messages.create({
  model: 'claude-haiku-4-5-20251001',
  max_tokens: 200,  // Kısa cevap için düşür (varsayılan 4096'dan)
  messages: [{ role: 'user', content: prompt }]
})
```

### 3. Prompt Caching
```typescript
// Aynı system prompt tekrar kullanılıyorsa cache'le:
const response = await anthropic.messages.create({
  model: 'claude-sonnet-4-6',
  system: [
    {
      type: 'text',
      text: longSystemPrompt,
      cache_control: { type: 'ephemeral' }  // 5 dakika cache
    }
  ],
  messages: [{ role: 'user', content: userMessage }]
})
// Cache hit: input token maliyeti %90 düşer!
```

### 4. Batch API
```typescript
// 10+ benzer istek varsa batch kullan (%50 indirim):
const batch = await anthropic.beta.messages.batches.create({
  requests: items.map((item, i) => ({
    custom_id: `req-${i}`,
    params: {
      model: 'claude-haiku-4-5-20251001',
      max_tokens: 100,
      messages: [{ role: 'user', content: `${item}` }]
    }
  }))
})
```

## Maliyet Hesaplama

```typescript
function estimateCost(
  inputTokens: number,
  outputTokens: number,
  model: 'opus' | 'sonnet' | 'haiku' = 'sonnet'
): number {
  const pricing = {
    opus:   { input: 15.00, output: 75.00 },
    sonnet: { input: 3.00,  output: 15.00 },
    haiku:  { input: 0.25,  output: 1.25  }
  }
  const p = pricing[model]
  return (inputTokens / 1_000_000 * p.input) +
         (outputTokens / 1_000_000 * p.output)
}

// Örnek:
// estimateCost(1000, 500, 'haiku')   → $0.0009  (1 kuruş altı)
// estimateCost(1000, 500, 'sonnet')  → $0.0105
// estimateCost(1000, 500, 'opus')    → $0.0525
```

## Bütçe Uyarı Eşikleri

```typescript
const BUDGET_ALERTS = {
  daily: {
    warning: 20,   // $20/gün → uyar
    critical: 50,  // $50/gün → YON-01'e bildir
  },
  monthly: {
    warning: 300,   // $300/ay → gözden geçir
    critical: 800,  // $800/ay → acil optimizasyon
  }
}
```

## Tasarruf Özeti Formatı

```
💰 Maliyet Optimizasyon Raporu

Mevcut durum:
  Model: claude-sonnet-4-6
  Günlük tahmin: $12.50
  Aylık tahmin: $375

Öneriler:
  1. Sınıflandırma görevleri → Haiku  → Tasarruf: $8/gün
  2. Prompt caching (system prompt) → Tasarruf: $3/gün
  3. Batch API (raporlama görevi) → Tasarruf: $1.5/gün

Toplam tasarruf tahmini: $12.5/gün → $375/ay
Önerilen aylık bütçe: $375 → $150 (-%60)
```
