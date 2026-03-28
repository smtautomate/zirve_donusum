# /commit — Akıllı Git Commit

**Agent**: YON-01 (CEO/PM) koordinasyonu, tüm mühendislik agentları

Aşağıdaki adımları sırasıyla uygula:

## 1. Değişiklikleri Analiz Et

```bash
git status
git diff --staged
git diff
```

Staged ve unstaged değişiklikleri ayrı ayrı incele. Hangi dosyalar değişti, ne tür değişiklikler var?

## 2. Conventional Commits Formatını Belirle

Değişikliğin türünü tespit et:

| Tip | Ne Zaman | Örnek |
|-----|---------|-------|
| `feat` | Yeni özellik | `feat: kullanıcı dashboard bileşeni` |
| `fix` | Hata düzeltme | `fix: ödeme sonrası sepet temizlenmiyordu` |
| `docs` | Dokümantasyon | `docs: API endpoint açıklamaları güncellendi` |
| `refactor` | Yeniden yapılandırma | `refactor: auth middleware sadeleştirildi` |
| `test` | Test ekleme/düzenleme | `test: ödeme akışı E2E testleri` |
| `chore` | Build, config, bağımlılık | `chore: TypeScript 5.4'e yükseltildi` |
| `perf` | Performans iyileştirmesi | `perf: ürün listesi lazy loading eklendi` |
| `style` | Kod formatı (anlam değişmez) | `style: ESLint uyarıları giderildi` |
| `ci` | CI/CD değişikliği | `ci: GitHub Actions node 20'ye güncellendi` |
| `revert` | Geri alma | `revert: feat: dashboard (hatalıydı)` |

## 3. Breaking Change Kontrolü

Değişiklik mevcut API veya davranışı bozuyor mu?
- Evet → Commit tipine `!` ekle: `feat!: ...` veya footer'a `BREAKING CHANGE: açıklama`
- Hayır → Normal devam

## 4. Scope Belirle (Opsiyonel ama Önerilen)

Hangi modülü etkiliyor?
```
feat(auth): ...
fix(checkout): ...
docs(api): ...
```

## 5. Commit Mesajı Formatı

```
<tip>(<kapsam>): <kısa açıklama — Türkçe, imperative, max 72 karakter>

[Opsiyonel gövde — neden bu değişiklik? ne değişti?]

[Opsiyonel footer — BREAKING CHANGE, Fixes #123, Co-authored-by]
```

**Örnekler:**
```
feat(checkout): ödeme adımına kupon kodu girişi eklendi

Kullanıcılar artık ödeme ekranında kupon kodu girebilir.
Geçersiz kuponlar için inline hata mesajı gösteriliyor.

Fixes #234
```

```
fix(api): ürün stoğu negatife düşüyor

Stok azaltma işleminde race condition vardı.
Redis distributed lock ile çözüldü.

BREAKING CHANGE: StockService.decrease() artık async
```

## 6. Staged Kontrolü

Commit edilecek dosyaları bir kez daha doğrula:
```bash
git diff --staged --stat
```

Yanlışlıkla eklenen dosya var mı? (`.env`, `node_modules`, büyük binary dosya)

## 7. Commit Yap

```bash
git add -p  # interaktif staging (gerekirse)
git commit -m "$(cat <<'EOF'
feat(kapsam): açıklama

Gövde metni buraya

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
EOF
)"
```

## 8. Push Kontrolü

```bash
git log --oneline -5  # son commit'leri göster
```

Push yapmak istiyor musun? Onay iste.

## Güvenlik Kontrolleri

Commit öncesi otomatik olarak kontrol et:
- [ ] `.env` dosyası staged'de yok
- [ ] `*.pem`, `*.key`, `*.p12` dosyası yok
- [ ] Kod içinde `password =`, `secret =`, `token =` literal string yok
- [ ] `node_modules/`, `dist/`, `.next/` staged'de değil

Herhangi biri varsa **commit yapma**, kullanıcıyı uyar.
