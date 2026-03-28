---
name: image-gen
description: AI görsel üretim koordinatörü. Görsel oluşturma, resim üretme veya image generation talebi olduğunda otomatik aktive olur. CRE-02 agent ile koordineli çalışır.
---

# Image Generation Skill

Aktive olma koşulları:
- "görsel üret", "resim oluştur", "fotoğraf üret" talepleri
- "ürün görseli", "banner", "sosyal medya görseli" istekleri
- Logo, illüstrasyon veya tasarım talepleri

## Platform Seçim Matrisi

| Platform | En İyi Kullanım | Maliyet |
|----------|-----------------|---------|
| **Flux Pro 1.1** (fal.ai) | Fotorealistik ürün, portre | ~$0.05/görsel |
| **Flux Dev** (fal.ai) | Hızlı iterasyon, prototip | ~$0.025/görsel |
| **Midjourney v6.1** | Yaratıcı, sanatsal | $10-60/ay |
| **DALL-E 3** (OpenAI) | Metin içeren görseller | $0.04-0.08/görsel |
| **Ideogram 2.0** | Logo, metin-görsel entegrasyonu | ~$0.05/görsel |
| **Leonardo AI** | Oyun asset, concept art | Kredi bazlı |
| **Adobe Firefly** | Ticari güvenli, marka uyumlu | CC aboneliği |
| **SDXL** (Replicate) | Fine-tune, ControlNet | ~$0.002/görsel |

## Platform Seçim Rehberi

```
Ticari ürün fotoğrafı     → Flux Pro 1.1 veya Adobe Firefly
Yaratıcı/sanatsal         → Midjourney v6.1
Metin içeren görsel       → Ideogram 2.0 veya DALL-E 3
Hızlı prototip            → Flux Dev veya DALL-E 3
Oyun/karakter asset       → Leonardo AI
Fine-tune gerekli         → SDXL on Replicate
Ticari güvenli            → Adobe Firefly
```

## Prompt Şablonları

### Flux Pro — Ürün Fotoğrafı
```
[Ürün adı ve özellikleri], white seamless background, studio lighting,
45-degree angle, product photography, sharp focus, 8K resolution,
commercial quality, no shadows, no reflections
```

### Midjourney — Yaratıcı
```
/imagine [konu], [stil referansı], [atmosfer] --ar 1:1 --style raw --v 6.1
```

### DALL-E 3 — Metin İçeren
```
Türkçe metin içeren görseller için İngilizce prompt + metin belirt:
"A banner with the text 'BÜYÜK İNDİRİM' in bold red letters..."
```

### Negatif Promptlar (SDXL/Flux)
```
Genel:   blurry, low quality, distorted, watermark, ugly
Ürün:    background clutter, harsh shadows, people
Portre:  extra fingers, bad anatomy, deformed hands
```

## Kalite Standartları

- **E-ticaret**: min 1024x1024px, beyaz/transparan arka plan, WebP formatı
- **Sosyal medya**: Instagram 1:1 (1080x1080), Stories 9:16 (1080x1920)
- **Print**: min 2048px en kısa kenar, PNG veya TIFF
- **Web banner**: 1200x628 (OG image), 728x90 (leaderboard)

## fal.ai API (Flux)

```typescript
const response = await fetch('https://fal.run/fal-ai/flux-pro', {
  method: 'POST',
  headers: {
    'Authorization': `Key ${process.env.FAL_KEY}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    prompt: "product photo of...",
    image_size: "square_hd",
    num_images: 1,
    enable_safety_checker: true
  })
})
const result = await response.json()
// result.images[0].url → üretilen görsel URL
```

## Batch Üretim

10+ görsel için:
1. 3-5 test görseli üret, en iyiyi seç
2. `seed` numarasını sabitle (tutarlılık)
3. Aynı seed ile varyasyonlar üret
4. Sonuçları `public/generated/[proje]/` altına kaydet

## Marka Tutarlılığı

```
Renk paleti:   Prompt'ta hex kod belirt: "#2563EB blue accent"
Font tarzı:    "minimalist sans-serif typography"
Logo alanı:    "leave empty space in bottom-right corner for logo overlay"
Genel ton:     "professional and clean" / "warm and friendly" / "bold and energetic"
```
