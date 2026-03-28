---
name: video-gen
description: AI video üretim koordinatörü. Video oluşturma, animasyon veya hareket grafikleri talebi olduğunda otomatik aktive olur. CRE-06 agent ile koordineli çalışır.
---

# Video Generation Skill

Aktive olma koşulları:
- "video üret", "animasyon oluştur", "kısa film", "reel" talepleri
- "ürün videosu", "tanıtım filmi", "sosyal medya videosu" istekleri
- "lip sync", "avatar video", "text to video" talepleri

## Platform Seçim Matrisi

| Platform | En İyi Kullanım | Süre | Maliyet |
|----------|-----------------|------|---------|
| **Kling AI v2** | T2V, I2V, lip-sync, ticari | 5-10sn | Kredi/dakika |
| **Runway Gen-3** | Sinematik kalite, kamera hareketleri | 5-10sn | $15+/ay |
| **Pika 2.0** | Hızlı, sosyal medya formatları | 3-5sn | Kredi bazlı |
| **Sora** (OpenAI) | Yüksek kalite, uzun video | 20sn+ | API (sınırlı) |
| **HeyGen** | Konuşan avatar, sunum videosu | Sınırsız | $29+/ay |
| **Minimax** | Müzik videosu, yaratıcı | 6sn | API |

## Platform Seçim Rehberi

```
Ürün tanıtım videosu      → Kling AI v2 (I2V: fotoğraftan video)
Sinematik içerik          → Runway Gen-3
Hızlı sosyal medya        → Pika 2.0
Konuşan avatar/sunum      → HeyGen
Lip-sync                  → Kling AI v2
Uzun form içerik          → Sora (API erişimi varsa)
```

## Kling AI — Temel Kullanım

### Text-to-Video (T2V)
```
Prompt yapısı:
[Kamera açısı] [Konu] [Eylem] [Ortam] [Aydınlatma] [Atmosfer]

Örnek:
"Close-up shot of a steaming coffee cup on a rustic wooden table,
gentle steam rising, warm morning light from left window,
cozy cafe atmosphere, cinematic color grading"
```

### Image-to-Video (I2V)
```
1. Flux Pro ile yüksek kalite başlangıç görsel üret
2. Kling I2V'ye yükle
3. Hareket yönünü belirt:
   - "Kamera yavaşça geriye çekiliyor"
   - "Ürün kendi etrafında dönerek"
   - "Doğal rüzgar hareketi"
```

### Negatif Promptlar
```
blurry, low quality, watermark, text overlay, distorted faces,
unnatural movement, flickering, artifacts, choppy motion
```

## Ses Entegrasyonu

### Seslendirme (ElevenLabs)
```typescript
const audio = await fetch('https://api.elevenlabs.io/v1/text-to-speech/voice_id', {
  method: 'POST',
  headers: {
    'xi-api-key': process.env.ELEVENLABS_API_KEY,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    text: "Merhaba, ürünümüzü tanıtıyoruz...",
    model_id: "eleven_multilingual_v2",
    voice_settings: { stability: 0.5, similarity_boost: 0.75 }
  })
})
```

### Müzik (Suno AI)
```
Prompt şablonu:
"[Tür], [Tempo], [Duygu], [Enstrümanlar], [Süre]"
Örnek: "Upbeat corporate background music, 120 BPM,
energetic and professional, piano and light drums, 30 seconds, no lyrics"
```

## FFmpeg Post-İşlem

```bash
# Video + ses birleştirme:
ffmpeg -i video.mp4 -i audio.mp3 -c:v copy -c:a aac -shortest output.mp4

# GIF'e dönüştürme (sosyal medya):
ffmpeg -i input.mp4 -vf "fps=10,scale=480:-1" -loop 0 output.gif

# Instagram Reels formatı (9:16):
ffmpeg -i input.mp4 -vf "scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2" reels.mp4

# Sıkıştırma (web için):
ffmpeg -i input.mp4 -c:v libx264 -crf 23 -preset medium -c:a aac -b:a 128k output_compressed.mp4
```

## Kalite Standartları

- **Sosyal medya**: 1080x1920 (Reels/TikTok), 1080x1080 (kare), MP4/H.264
- **Web**: max 10MB, 1080p, 30fps
- **Sunum**: 1920x1080, yüksek bitrate
- **GIF**: max 5MB, 480px en, 10-15fps

## Workflow Özeti

```
1. Senaryo → metin planı (YON-01 veya SAL-01 ile)
2. Ses → ElevenLabs ile seslendirme veya Suno AI müzik
3. Görseller → Flux Pro ile anahtar kareler
4. Video → Kling AI I2V veya T2V
5. Montaj → FFmpeg birleştirme
6. Çıktı → Platform'a uygun format ve boyut
```
