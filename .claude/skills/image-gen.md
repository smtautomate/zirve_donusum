---
name: image-gen
description: AI görsel üretim. Görsel oluşturma, resim üretme, fotoğraf, banner, logo, ürün görseli, sosyal medya görseli talep edildiğinde aktive olur.
---

# Görsel Üretim Skill

Bu skill aktive olduğunda aşağıdaki adımları uygula.

## Platform Seçimi

| Durum | Platform | Nasıl |
|-------|----------|-------|
| **Profesyonel tasarım** (poster, sosyal medya, logo, sunum) | **Canva** | `mcp__claude_ai_Canva__generate-design` aracını kullan |
| **Fotorealistik görsel** (ürün fotoğrafı, sahne) | **Gemini** | Bash ile `curl` komutu çalıştır (aşağıya bak) |
| **Hızlı basit görsel** | **Canva** | `mcp__claude_ai_Canva__generate-design` |

## Canva ile Tasarım (ÖNCELİKLİ — DOĞRUDAN BAĞLI)

Canva MCP doğrudan bağlı. Kullanılabilir tasarım tipleri:
- `instagram_post`, `facebook_post`, `twitter_post`, `your_story`
- `poster`, `flyer`, `infographic`, `logo`
- `presentation`, `proposal`, `report`, `doc`
- `youtube_thumbnail`, `youtube_banner`
- `business_card`, `resume`, `invitation`

Kullanım:
1. `mcp__claude_ai_Canva__generate-design` ile tasarım üret
2. Kullanıcıya adayları göster
3. `mcp__claude_ai_Canva__create-design-from-candidate` ile seçileni kaydet

## Gemini ile Fotorealistik Görsel

GOOGLE_AI_KEY ortam değişkeni gerekli. Bash ile çalıştır:

```bash
curl -s "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=$GOOGLE_AI_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "contents": [{"parts": [{"text": "Generate a photorealistic image: [PROMPT]"}]}],
    "generationConfig": {"responseModalities": ["IMAGE", "TEXT"]}
  }'
```

Yanıttaki base64 görsel verisini dosyaya kaydet:
```bash
echo "<base64_data>" | base64 -d > output.png
```

## Prompt Yazım Kuralları

1. İngilizce yaz (AI modelleri İngilizce prompt'ta daha iyi sonuç verir)
2. Detaylı ol: renk, ışık, açı, stil, ortam belirt
3. Negatif prompt ekle: "no text, no watermark, no distortion"
4. Marka tutarlılığı: Her seferinde aynı stil referansı ver
