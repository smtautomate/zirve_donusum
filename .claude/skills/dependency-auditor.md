---
name: dependency-auditor
description: Bağımlılık güvenlik tarayıcısı. package.json, requirements.txt, Gemfile veya pom.xml değiştirildiğinde ya da "npm install", "pip install" sonrası otomatik aktive olur.
---

# Dependency Auditor Skill

Aktive olma koşulları:
- `package.json`, `requirements.txt`, `Gemfile`, `composer.json`, `go.mod` değiştiğinde
- `npm install`, `pip install`, `composer install` çalıştırıldıktan sonra
- "vulnerability", "CVE", "security advisory" ifadeleri geçtiğinde
- Yeni paket ekleme isteği olduğunda

## Güvenlik Açığı Taraması

### Node.js / npm
```bash
npm audit --json 2>/dev/null | jq '
  .vulnerabilities | to_entries[] |
  select(.value.severity | IN("critical","high")) |
  {name: .key, severity: .value.severity, fixable: .value.fixAvailable}
' 2>/dev/null | head -100

# Hızlı özet:
npm audit --audit-level=moderate 2>&1 | tail -20
```

### Python / pip
```bash
pip-audit 2>/dev/null || pip install pip-audit && pip-audit
# veya:
safety check --json 2>/dev/null | head -50
```

### PHP / Composer
```bash
composer audit 2>/dev/null | head -50
```

## Güncellik Analizi

```bash
# npm — çok eski paketler:
npm outdated --json 2>/dev/null | jq 'to_entries[] | select(.value.type == "dependencies") | {name: .key, current: .value.current, latest: .value.latest}' 2>/dev/null | head -50

# Major versiyon farkı olanlar öncelikli
```

## Yeni Paket Değerlendirmesi

Yeni paket eklemeden önce değerlendir:

| Kriter | Kontrol | Eşik |
|--------|---------|------|
| Haftalık indirme | npm stats | >10K/hafta |
| Son güncelleme | GitHub/npm | <6 ay |
| Açık güvenlik açığı | npm audit | 0 critical/high |
| Maintainer sayısı | npm info | >1 |
| Lisans | MIT, Apache 2.0 | GPL üretimde sorunlu |
| Boyut | bundlephobia | tree-shaking destekliyorsa tamam |

```bash
# Yeni paket info:
npm info [paket-adı] 2>/dev/null | grep -E "latest|license|downloads|maintainers" | head -10
```

## Otomatik Düzeltme

### Güvenlik Açığı Düzeltme
```bash
# Güvenli otomatik düzeltme (minor/patch):
npm audit fix 2>&1 | tail -10

# Major versiyon değişikliği gerekiyorsa (breaking change riski):
# ÖNCE kullanıcıya sor!
npm audit fix --force  # Sadece onaydan sonra
```

### Outdated Paket Güncelleme
Major version için önce CHANGELOG'u kontrol et:
1. Paketin GitHub sayfasına git
2. Breaking changes var mı?
3. Migration guide var mı?
4. Test suite'i çalıştır — güncelleme sonrası

## Lisans Uyumluluğu

Üretim bağımlılıklarında dikkat edilecek lisanslar:
- **Güvenli**: MIT, Apache 2.0, BSD 2/3-Clause, ISC
- **Dikkat**: LGPL (dinamik linking ile sorunsuz)
- **Sorunlu**: GPL, AGPL (kaynak kod paylaşımı gerektirebilir)
- **Ticari**: Commercial lisanslar → YON-04 (Legal) ile görüş

## Özet Rapor Formatı

```
## Bağımlılık Audit — [Tarih]

Güvenlik:
  🔴 Critical: X (hemen düzeltilmeli)
  🟠 High: X (bu sprint)
  🟡 Moderate: X (backlog)

Güncellik:
  Major versiyon geride: X paket
  Minor/patch geride: X paket

Lisans: ✅ Temiz / ⚠️ Kontrol edilmeli

Önerilen Eylemler:
1. npm audit fix (X açığı otomatik düzeltilebilir)
2. Manuel inceleme: [paket-listesi]
```
