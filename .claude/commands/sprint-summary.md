# /sprint-summary — Sprint Durum Raporu

**Agent**: YON-01 (CEO/PM), tüm takım liderlerine özet sunar

Sprint'in anlık durumunu analiz et ve kapsamlı bir rapor oluştur.

## 1. Git Aktivitesi Analizi

```bash
# Son 2 haftadaki commit'ler:
git log --oneline --since="2 weeks ago" --all

# Branch durumu:
git branch -a | grep -v "HEAD"

# Bu sprint açık PR sayısı (GitHub CLI varsa):
gh pr list --state open --json title,author,createdAt 2>/dev/null | head -50
```

## 2. Tamamlanan İşler

Son 2 haftadaki git geçmişini analiz et:
- `feat:` commit'leri → Yeni özellikler
- `fix:` commit'leri → Düzeltilen hatalar
- `docs:` commit'leri → Dokümentasyon
- `refactor:` commit'leri → Teknik iyileştirmeler

## 3. Devam Eden İşler

```bash
# Açık branch'ler (aktif geliştirme):
git branch -r | grep -v "main\|develop\|HEAD" | head -20

# Stalened branch'ler (7+ gün commit yok):
git for-each-ref --format='%(refname:short) %(committerdate:relative)' refs/remotes | grep -v "HEAD\|main" | head -20
```

## 4. Blokajlar ve Riskler

Kontrol et:
```bash
# Uzun süredir review bekleyen PR'lar:
gh pr list --state open --json title,createdAt,author 2>/dev/null

# Çakışmalı branch'ler:
git log --merges --oneline --since="1 week ago" | head -10
```

## 5. Teknik Borç

```bash
# TODO/FIXME sayısı:
grep -r "TODO\|FIXME\|HACK" --include="*.ts" --include="*.tsx" --include="*.js" src/ 2>/dev/null | wc -l

# Test coverage (varsa):
npm test -- --coverage --coverageReporters=text 2>/dev/null | grep -E "All files|Statements" | head -3
```

## 6. Sprint Raporu Formatı

Aşağıdaki formatta Markdown rapor oluştur:

```markdown
# Sprint Raporu — [Hafta/Sprint No] — [Tarih]

## Özet
- **Sprint**: [No]
- **Dönem**: [Başlangıç] → [Bitiş]
- **Takım**: [Aktif üye sayısı]

## Tamamlananlar ✅
| # | İş | Tip | Tamamlayan |
|---|-----|-----|------------|
| 1 | ... | feat | ... |

## Devam Edenler 🔄
| # | İş | Branch | Durum | Tahmini Bitiş |
|---|-----|--------|-------|---------------|
| 1 | ... | feature/... | %70 | ... |

## Blokajlar 🚧
| Blokaj | Sebep | Çözüm Önerisi |
|--------|-------|---------------|

## Metrikler
- Tamamlanan commit: X
- Açık PR: X
- Kapatılan issue: X
- Teknik borç (TODO): X

## Sonraki Sprint İçin
### Öncelikli İşler
1. ...

### Riskler
- ...

## Takım Notları
[Varsa önemli duyurular, kararlar, notlar]
```

## 7. Raporu Paylaş

Raporu şu konumlarda oluştur:
- Ekrana yazdır (kullanıcı için)
- `docs/sprint-reports/sprint-[N]-[YYYY-MM-DD].md` dosyasına kaydet (onay al)
- Slack'e göndermek ister misin? (opsiyonel, onay gerekli)
