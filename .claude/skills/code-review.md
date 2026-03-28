---
name: code-review
description: Kapsamlı kod review asistanı. PR review, kod değişikliği analizi veya "kodu gözden geçir" talepleri geldiğinde otomatik aktive olur. QA-01 standartlarını uygular.
---

# Code Review Skill

Aktive olma koşulları:
- PR review talebi
- "Bu kodu incele", "review et", "gözden geçir" talepleri
- Commit diff analizi
- Kod kalite değerlendirmesi

## Review Kontrol Listesi

### 1. Doğruluk ve Mantık
- [ ] Fonksiyon beklenen sonucu üretiyor mu?
- [ ] Edge case'ler ele alınmış mı? (null, undefined, boş array, negatif sayı)
- [ ] Hata durumları yönetiliyor mu?
- [ ] Async/await doğru kullanılmış mı? (unhandled promise rejection?)

### 2. Güvenlik (QA-03 standartları)
- [ ] Kullanıcı girdisi doğrulanıyor mu? (Zod/Joi/Yup)
- [ ] SQL injection, XSS riski yok mu?
- [ ] Hassas veri loglanmıyor mu?
- [ ] Yetkilendirme kontrolleri var mı?

### 3. Performans (ENG-12 standartları)
- [ ] N+1 sorgu riski var mı?
- [ ] Gereksiz re-render (React) var mı?
- [ ] Büyük veri setleri için pagination/virtual scroll?
- [ ] Ağır işlemler için memoization veya cache?

### 4. Kod Kalitesi
- [ ] DRY (Don't Repeat Yourself) — tekrar eden kod var mı?
- [ ] Tek sorumluluk ilkesi uygulanmış mı?
- [ ] Değişken/fonksiyon isimleri açıklayıcı mı?
- [ ] Magic number/string yerine sabit kullanılmış mı?
- [ ] Fonksiyon max 50 satır mı?

### 5. Test Coverage
- [ ] Yeni kod için test yazılmış mı?
- [ ] Düzeltilen bug için regression test var mı?
- [ ] Happy path + error path test edilmiş mi?

### 6. Tip Güvenliği (TypeScript)
- [ ] `any` kullanımından kaçınılmış mı?
- [ ] Interface/type tanımlanmış mı?
- [ ] Non-null assertion (`!`) gerekçeli mi?

## Review Yorum Formatı

```markdown
<!-- Kritik — mutlaka düzeltilmeli -->
🔴 **[Kritik]** Satır 45: SQL injection riski
```typescript
// Mevcut (HATALI):
const query = `SELECT * FROM users WHERE id = ${userId}`

// Düzeltilmiş:
const user = await prisma.user.findUnique({ where: { id: userId } })
```

<!-- Önemli — düzeltilmeli -->
🟠 **[Önemli]** Satır 78: N+1 sorgu
Her sipariş için ayrı kullanıcı sorgusu gidiyor. `include: { user: true }` kullan.

<!-- Öneri — tartışılabilir -->
🟡 **[Öneri]** Satır 120: `any` tipi
`UserResponse` interface'i zaten var, onu kullan.

<!-- Bilgi — opsiyonel -->
ℹ️ **[Bilgi]** Satır 55: Bu pattern `useMemo` ile optimize edilebilir ama şu anki yük için gerekmeyebilir.

<!-- Onay -->
✅ Auth middleware doğru uygulanmış.
✅ Error handling kapsamlı.
```

## Review Kararı

**Onay (`LGTM`)** → Tüm kritik ve önemli sorunlar yok
**Değişiklik İste** → 1+ kritik sorun var
**Yorum** → Sadece öneriler, merge engellenmiyor

## Öncelik Matrisi

| Kategori | Ağırlık | Örnek |
|----------|---------|-------|
| Güvenlik açığı | 🔴 Kritik | SQL injection, XSS, auth bypass |
| Veri kaybı riski | 🔴 Kritik | Transaction olmadan silme |
| N+1 sorgu (production) | 🟠 Önemli | ORM include eksik |
| Test eksikliği | 🟠 Önemli | Kritik fonksiyon test edilmemiş |
| TypeScript `any` | 🟡 Öneri | Tip güvenliği zayıf |
| Naming convention | ℹ️ Bilgi | Küçük stil farklılıkları |

## Otomatik Kontroller

```bash
# Diff'i al:
git diff main...HEAD --stat
git diff main...HEAD -- '*.ts' '*.tsx'

# Değişen dosyalarda hızlı kontrol:
git diff main...HEAD -- '*.ts' | grep -E "^\+" | grep -v "^+++" | \
  grep -iE "console\.log|TODO|FIXME|any\b|eval\(|\$\{" | head -20
```
