---
name: git-workflow
description: Git branch, commit ve PR workflow rehberi. Git operasyonları sırasında, branch oluştururken veya commit yazarken otomatik aktive olur.
---

# Git Workflow Skill

Aktive olma koşulları:
- Git komutları çalıştırılırken
- Branch oluşturma/silme işlemlerinde
- Commit mesajı yazarken
- PR açma/merge işlemlerinde
- Conflict çözümünde

## Branch Naming Convention

```
feature/   → Yeni özellik
  feature/user-auth
  feature/payment-integration
  feature/ENG-123-cart-redesign  (issue no varsa)

bugfix/    → Hata düzeltme (non-urgent)
  bugfix/login-error-fix
  bugfix/QA-45-cart-total-wrong

hotfix/    → Acil production düzeltmesi
  hotfix/payment-crash-fix
  hotfix/v2.1.1-security-patch

release/   → Release hazırlık
  release/v2.2.0
  release/2025-Q1

refactor/  → Yeniden yapılandırma
  refactor/auth-middleware-cleanup

chore/     → Config, bağımlılık, altyapı
  chore/update-dependencies
  chore/ci-node-upgrade
```

## Commit Conventions (Conventional Commits)

```
<tip>(<kapsam>): <açıklama>

[Opsiyonel gövde]

[Opsiyonel footer]
```

**Tipler**: feat, fix, docs, refactor, test, chore, perf, style, ci, revert

**Kurallar**:
- Açıklama Türkçe, imperative mood: "ekle", "düzelt", "güncelle"
- Max 72 karakter (başlık)
- Scope proje modülüne göre: auth, checkout, api, db, ui

```bash
# İyi örnekler:
git commit -m "feat(checkout): kupon kodu girişi eklendi"
git commit -m "fix(auth): token yenileme sorunu düzeltildi"
git commit -m "perf(api): ürün listesi sorgusu optimize edildi"
git commit -m "chore: TypeScript 5.5'e yükseltildi"
```

## PR Süreci

```bash
# 1. Main'den taze branch:
git checkout main && git pull origin main
git checkout -b feature/my-feature

# 2. Çalış, commit'le
git add -p  # interaktif — sadece ilgili değişiklikleri ekle
git commit -m "feat(modül): açıklama"

# 3. Push:
git push -u origin feature/my-feature

# 4. PR aç (gh CLI):
gh pr create --title "feat(modül): açıklama" \
  --body "## Değişiklikler\n- ...\n\n## Test\n- [ ] ...\n\n## Ekran Görüntüsü (varsa)"
```

## Conflict Çözümü

```bash
# Conflict durumunda:
git status  # Hangi dosyalar çakışıyor?

# Her çakışan dosyada:
# <<<<<<< HEAD (bizim değişikliklerimiz)
# =======
# >>>>>>> branch-name (karşı taraftaki değişiklikler)

# Çözdükten sonra:
git add [dosya]
git commit -m "merge: conflict çözüldü"

# Veya rebase tercih ediliyorsa:
git rebase main
# Her conflict'i çöz, sonra:
git rebase --continue
```

## Güvenli Operasyonlar

```bash
# Commit geçmişini düzeltmeden önce:
git stash  # Çalışmaları sakla
git log --oneline -10  # Ne yapıldığını gör

# Son commit'i değiştir (henüz push edilmediyse):
git commit --amend --no-edit  # Dosya ekle
git commit --amend -m "yeni mesaj"  # Mesajı değiştir

# Staged olmayan değişiklikleri geri al:
git restore [dosya]  # git checkout [dosya] yerine

# Branch sil:
git branch -d feature/tamamlandi  # Güvenli sil (merge edilmediyse hata verir)
git branch -D feature/iptal  # Zorla sil
```

## Tag ve Release

```bash
# Semantic versioning: v[MAJOR].[MINOR].[PATCH]
git tag -a v2.1.0 -m "Release v2.1.0: Ödeme entegrasyonu"
git push origin v2.1.0

# GitHub release:
gh release create v2.1.0 --title "v2.1.0" --notes "Changelog..."
```
