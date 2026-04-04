---
name: video-gen
description: AI video üretim. Video oluşturma, animasyon, ürün videosu, sosyal medya videosu, tanıtım filmi talebi olduğunda aktive olur.
---

# Video Üretim Skill

## Platform Seçimi

| Platform | En İyi Kullanım | API |
|----------|-----------------|-----|
| **Kling AI** | Ürün videosu, sahneli video, lip-sync | `api.klingai.com` |
| **Runway Gen-3** | Yüksek kalite kısa video | `api.runwayml.com` |
| **Canva** | Animasyonlu sunum, sosyal medya video | Canva MCP |

## Kling AI ile Video Üretim

API key gerekli: `KLING_ACCESS_KEY` ve `KLING_SECRET_KEY` ortam değişkenleri.

### Text-to-Video
```bash
# JWT token al
JWT=$(python3 -c "
import jwt, time
payload = {'iss': '$KLING_ACCESS_KEY', 'exp': int(time.time())+1800, 'nbf': int(time.time())-5}
print(jwt.encode(payload, '$KLING_SECRET_KEY', algorithm='HS256'))
")

# Video oluştur
curl -s -X POST "https://api.klingai.com/v1/videos/text2video" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $JWT" \
  -d '{
    "model_name": "kling-v1",
    "prompt": "[VIDEO AÇIKLAMASI - İNGİLİZCE]",
    "duration": "5",
    "aspect_ratio": "16:9"
  }'
```

### Image-to-Video (fotoğraftan video)
```bash
curl -s -X POST "https://api.klingai.com/v1/videos/image2video" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $JWT" \
  -d '{
    "model_name": "kling-v1",
    "image_url": "[GÖRSEL_URL]",
    "prompt": "[HAREKETLENDİRME AÇIKLAMASI]",
    "duration": "5"
  }'
```

### Sonucu Kontrol Et
```bash
curl -s "https://api.klingai.com/v1/videos/text2video/[TASK_ID]" \
  -H "Authorization: Bearer $JWT"
```

Video URL'i yanıtta `works[0].resource.resource` alanında döner.

## Canva ile Animasyonlu İçerik

Kısa animasyonlu sosyal medya içeriği için Canva kullan:
1. `mcp__claude_ai_Canva__generate-design` ile animasyonlu tasarım
2. `mcp__claude_ai_Canva__export-design` ile video olarak dışa aktar

## Prompt Yazım Kuralları

1. İngilizce yaz
2. Kamera hareketini belirt: "slow zoom in", "pan left to right", "static shot"
3. Süreyi belirt: 5s veya 10s
4. Negatif prompt: "no morphing, no distortion, smooth motion"
