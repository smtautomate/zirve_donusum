# /seo-check — SEO Audit

**Agent**: COM-02 (SEO & Blog), ENG-01 (Frontend — teknik SEO), ENG-12 (Performance)

Teknik SEO ve içerik kalitesi analizi.

## 1. Teknik SEO — Statik Analiz

### Meta Tag Kontrolü
```bash
# Next.js metadata kontrolü:
grep -rn "metadata\|<Head>\|<title>\|description.*content" \
  --include="*.tsx" --include="*.ts" app/ src/ pages/ 2>/dev/null | grep -v "node_modules" | head -30

# OG tag'ları var mı?
grep -rn "og:title\|og:description\|og:image\|twitter:card" \
  --include="*.tsx" --include="*.ts" . 2>/dev/null | grep -v node_modules | head -20
```

### Robots & Sitemap
```bash
ls -la public/robots.txt public/sitemap.xml 2>/dev/null || echo "⚠️  robots.txt veya sitemap.xml eksik"
cat public/robots.txt 2>/dev/null
```

### Yapısal Veri (Structured Data)
```bash
grep -rn "application/ld+json\|@type.*Product\|@type.*Article\|@type.*Organization" \
  --include="*.tsx" --include="*.ts" . 2>/dev/null | grep -v node_modules | head -20
```

## 2. URL Yapısı Kontrolü

```bash
# Türkçe URL slug'ları:
grep -rn "pathname\|href=\|to=\|link=" --include="*.tsx" . 2>/dev/null | \
  grep -v node_modules | grep -E "ş|ç|ğ|ü|ö|ı|İ" | head -20
# Türkçe karakter içeren URL'ler varsa uyar — slugify edilmeli
```

SEO-friendly URL kuralları:
- Küçük harf, tire (-) ayraç, max 60 karakter
- `ş → s`, `ç → c`, `ğ → g`, `ü → u`, `ö → o`, `ı → i`
- Türkçe: `/urunler/kadin-ayakkabi` ✅ `/Ürünler/Kadın Ayakkabı` ❌

## 3. Core Web Vitals Hedefleri

Kod içinde performans sorunlarını kontrol et:

```bash
# Büyük resimler optimize edilmiş mi? (Next.js Image component)
grep -rn "<img " --include="*.tsx" src/ app/ 2>/dev/null | grep -v "node_modules" | head -20
# <img> yerine <Image> (next/image) kullanılmalı

# Lazy loading:
grep -rn "loading.*lazy\|dynamic.*import\|lazy(" \
  --include="*.tsx" --include="*.ts" src/ app/ 2>/dev/null | grep -v node_modules | head -10

# Font optimizasyonu:
grep -rn "next/font\|font-display.*swap" \
  --include="*.tsx" --include="*.ts" . 2>/dev/null | grep -v node_modules | head -10
```

Hedefler (Google Core Web Vitals):
| Metrik | İyi | Düzeltme Gerekli | Kötü |
|--------|-----|------------------|------|
| LCP | <2.5s | 2.5-4s | >4s |
| FID/INP | <100ms | 100-300ms | >300ms |
| CLS | <0.1 | 0.1-0.25 | >0.25 |
| TTFB | <800ms | 800ms-1.8s | >1.8s |

## 4. İçerik SEO Kontrolleri

```bash
# H1 tag'ı tek mi?
grep -rn "<h1\|# " --include="*.tsx" --include="*.mdx" src/ app/ content/ 2>/dev/null | \
  grep -v node_modules | head -20

# Alt text eksik resimler:
grep -rn "<Image\|<img" --include="*.tsx" . 2>/dev/null | \
  grep -v "alt=" | grep -v node_modules | head -20
```

## 5. Sayfa Hızı (Canlı Test)

```bash
# Varsa canlı siteyi test et:
SITE_URL="${SITE_URL:-http://localhost:3000}"
curl -s -o /dev/null -w "TTFB: %{time_starttransfer}s | Toplam: %{time_total}s | Boyut: %{size_download} byte\n" \
  "$SITE_URL" 2>/dev/null || echo "Site erişilemedi"
```

## 6. E-Ticaret SEO (Varsa)

```bash
# Ürün sayfalarında Schema.org Product markup:
grep -rn "@type.*Product\|productId\|sku.*content\|price.*content" \
  --include="*.tsx" . 2>/dev/null | grep -v node_modules | head -20

# Breadcrumb markup:
grep -rn "BreadcrumbList\|breadcrumb" \
  --include="*.tsx" . 2>/dev/null | grep -v node_modules | head -10
```

## 7. SEO Audit Raporu

```markdown
## SEO Audit Raporu — [Tarih]

### Kritik Eksikler 🔴
- [ ] ...

### Önemli İyileştirmeler 🟠
- [ ] ...

### Öneriler 🟡
- [ ] ...

### Kontrol Durumu
- Meta tags: ✅/❌
- Sitemap.xml: ✅/❌
- robots.txt: ✅/❌
- OG tags: ✅/❌
- Structured data: ✅/❌
- Türkçe URL slugs: ✅/❌
- Alt text: ✅/❌
- next/image kullanımı: ✅/❌

### Performans (tahmini)
- LCP hedefi: ...
- CLS riski: ...
```

Daha derin analiz için: Google Search Console ve PageSpeed Insights kullan.
Yeni sayfa oluştururken COM-02 (SEO Agent) ile çalış.
