# /translate — TR/EN Çeviri

**Agent**: DAT-06 (Çeviri & Lokalizasyon), ENG-01 (i18n implementasyonu)

Context-aware teknik çeviri. Kullanım: `/translate [metin veya dosya yolu]`

## 1. Çeviri Türünü Belirle

Kullanıcının neyi çevirmek istediğini tespit et:

| Tür | Örnek | Yöntem |
|-----|-------|--------|
| UI metni | Button label, form, hata mesajı | Kısa, kullanıcı dostu |
| Teknik doküman | README, API docs | Terminoloji odaklı |
| Kod yorumu | `// comment` | Kısa ve net |
| Blog/içerik | Makale, ürün açıklaması | Doğal, SEO-uyumlu |
| Hata mesajı | Error/warning | Teknik açıklık öncelikli |
| E-posta/iletişim | Müşteri bildirimi | Formal, kurumsal |

## 2. Dil Yönü Tespit Et

- Türkçe metin → İngilizce çeviri
- İngilizce metin → Türkçe çeviri
- Belirsiz → Sorun: "TR→EN mi EN→TR mi?"

## 3. Teknik Terim Standardları

### Türkçe → İngilizce Çeviride

Teknik terimleri İngilizce bırak:
- "Deployment", "endpoint", "middleware", "payload", "cache", "token"
- "Authentication", "authorization", "webhook", "API", "SDK"
- Programlama dili isimleri: TypeScript, Python, vb.

### İngilizce → Türkçe Çeviride

Yerleşik Türkçe terimleri kullan:
- "Authentication" → "Kimlik doğrulama"
- "Authorization" → "Yetkilendirme"
- "Dashboard" → "Kontrol paneli" (veya bağlama göre "dashboard")
- "Deploy" → "Dağıtım" (teknik belgelerde "deploy" de kabul edilir)
- "Commit" → Olduğu gibi "commit"
- "Bug" → "Hata" veya "bug"

Karışık kullanım rehberi (TR birincil, EN destek):
- Kullanıcı arayüzü → Türkçe tercih
- Kod yorumları → İngilizce tercih
- Teknik doküman → İngilizce teknik terimler, Türkçe açıklama

## 4. Dosya Çevirisi

Dosya yolu verilmişse:

```bash
# Dosyayı oku:
cat [dosya-yolu] | head -100
```

Sonra çevir ve seçenek sun:
- Orijinal dosyayı güncelle (`-TR.md` veya `-EN.md` suffix ile)
- Yan yana karşılaştırmalı göster
- Sadece ekrana yazdır

## 5. i18n Entegrasyonu

Proje `i18next`, `next-intl` veya benzeri kullanıyorsa:

```bash
# Mevcut i18n dosyalarını bul:
find . -name "*.json" -path "*/locales/*" -o -name "*.json" -path "*/i18n/*" | grep -v node_modules | head -10
find . -name "tr.json" -o -name "en.json" | grep -v node_modules | head -5
```

Çeviriyi JSON format olarak da sun:

```json
{
  "orijinal_key": {
    "tr": "Türkçe metin",
    "en": "English text"
  }
}
```

## 6. Kalite Kontrol

Çeviri sonrası otomatik kontrol:
- [ ] Türkçe karakterler doğru: ş, ç, ğ, ü, ö, ı, İ
- [ ] Resmi/gayriresmi ton tutarlı
- [ ] Teknik terimler standardı korunuyor
- [ ] HTML/JSX tag'leri bozulmadı: `<strong>`, `{variable}`, `%s`
- [ ] Maksimum karakter limitleri (UI için kritik)

## 7. Çeviri Çıktısı

```
Orijinal (TR):
[metin]

Çeviri (EN):
[translated text]

Notlar:
- "X" → "Y" olarak çevrildi (tercih edilebilir alternatif: "Z")
- [varsa özel durumlar]
```

Büyük dosyalar için batch çeviri önerisinde bulun.
