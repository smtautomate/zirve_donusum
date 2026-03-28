---
name: gemini-vision
description: Gemini Pro Vision ile görsel analiz, OCR ve dokuman anlama. Görsel analiz, ekran görüntüsü okuma, fotoğraf anlama veya belge tarama talepleri geldiğinde aktive olur.
---

# Gemini Vision Skill

Aktive olma koşulları:
- Görsel/fotoğraf analiz talebi
- Ekran görüntüsü okuma veya açıklama
- PDF/belge tarama ve metin çıkarma
- UI/UX accessibility analizi
- Ürün fotoğrafı kalite kontrolü
- Çoklu görsel karşılaştırma

## Model Seçimi

| Model | Kullanım | Hız | Maliyet |
|-------|----------|-----|---------|
| `gemini-2.0-flash-exp` | Hızlı analiz, basit görevler | ⚡⚡⚡ | En düşük |
| `gemini-1.5-pro` | Derin analiz, karmaşık görevler | ⚡⚡ | Orta |
| `gemini-1.5-flash` | Denge: hız + kalite | ⚡⚡⚡ | Düşük |

Varsayılan: `gemini-2.0-flash-exp` (çoğu görev için yeterli)

## Temel Kullanım

```typescript
import { GoogleGenerativeAI } from '@google/generative-ai'

const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY)
const model = genAI.getGenerativeModel({ model: 'gemini-2.0-flash-exp' })

// URL'den görsel analiz:
const result = await model.generateContent([
  {
    inlineData: {
      mimeType: 'image/jpeg',
      data: base64ImageData  // Buffer.from(imageBytes).toString('base64')
    }
  },
  'Bu görselde ne görüyorsunuz? Detaylı açıklayın.'
])
console.log(result.response.text())
```

## Kullanım Senaryoları

### 1. UI/UX Accessibility Audit (QA-01, CRE-01)
```
Prompt: "Bu ekran görüntüsünü WCAG 2.1 AA standartlarına göre değerlendir.
Kontrol et:
- Metin kontrast oranları (min 4.5:1)
- Tıklanabilir alan boyutları (min 44x44px)
- Alt text eksik görseller
- Form label ilişkilendirmeleri
- Klavye navigasyonu sırası
Sorunları kritiklik sırasına göre listele."
```

### 2. Ürün Görseli Kalite Kontrolü (COM-01, CRE-02)
```
Prompt: "Bu ürün fotoğrafını e-ticaret standartları için değerlendir:
- Arka plan temizliği (beyaz/nötr mü?)
- Ürün odak keskinliği
- Aydınlatma kalitesi
- Ürünün görünürlüğü ve açısı
- Trendyol/Hepsiburada gereksinimleri uyumu
Puanlama: 1-10, eksik kısımlar listesi"
```

### 3. Ekran Görüntüsü → Kod (ENG-01)
```
Prompt: "Bu UI mockup'ını analiz et ve Tailwind CSS + React bileşeni olarak
uygula. Renkleri, tipografiyi, spacing'i ve layout'u mümkün olduğunca
birebir yansıt."
```

### 4. Belge OCR ve Veri Çıkarma (DAT-01, YON-07)
```
Prompt: "Bu faturadaki şu bilgileri JSON formatında çıkar:
fatura_no, tarih, satici_adi, satici_vergi_no, kalemler (aciklama, miktar, birim_fiyat, toplam), ara_toplam, kdv, genel_toplam"
```

### 5. Rakip Ürün Analizi (COM-09)
```
Prompt: "Bu ekran görüntüsü rakip bir e-ticaret sitesinden.
Analiz et: fiyatlandırma stratejisi, ürün kategorileri,
UI/UX kararları, kullanıcı deneyimi güçlü/zayıf yönleri.
Bizim sitemize göre fırsatları listele."
```

### 6. Çoklu Görsel Karşılaştırma
```typescript
const result = await model.generateContent([
  'Bu iki tasarımı karşılaştır ve hangisinin daha iyi kullanıcı deneyimi sunduğunu açıkla:',
  { inlineData: { mimeType: 'image/png', data: design1Base64 } },
  'Tasarım 1 yukarıda. Tasarım 2:',
  { inlineData: { mimeType: 'image/png', data: design2Base64 } }
])
```

## Dosyadan Görsel Okuma

```typescript
import fs from 'fs'
import path from 'path'

async function analyzeImage(imagePath: string, prompt: string) {
  const imageBytes = fs.readFileSync(imagePath)
  const base64 = Buffer.from(imageBytes).toString('base64')
  const mimeType = imagePath.endsWith('.png') ? 'image/png' : 'image/jpeg'

  const model = genAI.getGenerativeModel({ model: 'gemini-2.0-flash-exp' })
  const result = await model.generateContent([
    { inlineData: { mimeType, data: base64 } },
    prompt
  ])
  return result.response.text()
}
```

## Sınırlar ve Dikkat Edilecekler

- Max görsel boyutu: 20MB per görsel
- Max 100 görsel per istek (1.5 Pro)
- Kişisel veri (yüz, kimlik) → KVKK uyumluluğu için dikkat
- Tıbbi görüntü analizi → Uzman onayı şart
- Bant genişliği: büyük görselleri önce compress et
