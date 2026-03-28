---
name: deploy-staging
description: Staging ortam deploy asistanı. Deploy talebi, "staging'e at", "test ortamını güncelle" veya release hazırlığı geldiğinde aktive olur. OPS-01 ile koordineli çalışır.
---

# Deploy Staging Skill

Aktive olma koşulları:
- "Deploy et", "staging'e at", "test ortamını güncelle" talepleri
- Release branch oluşturulduğunda
- Hotfix acil deploy ihtiyacı

## Pre-Deploy Kontrol Listesi

Sırasıyla kontrol et. Herhangi biri başarısız olursa deploy'u durdur ve kullanıcıyı bildir.

### 1. Kod Kontrolleri
```bash
# Build başarılı mı?
npm run build 2>&1 | tail -5
[ $? -eq 0 ] || echo "❌ BUILD BAŞARISIZ — DEPLOY DURDU"

# TypeScript hata yok mu?
npx tsc --noEmit 2>&1 | head -20

# Lint temiz mi?
npm run lint 2>&1 | grep -c "error" || echo "✅ Lint temiz"

# Testler geçiyor mu?
npm test -- --watchAll=false 2>&1 | tail -10
```

### 2. Git Kontrolleri
```bash
# Hangi branch'teyiz?
git branch --show-current

# Uncommitted değişiklik var mı?
git status --short

# Son commit:
git log --oneline -3
```

### 3. Environment Kontrolleri
```bash
# Kritik env var'lar tanımlı mı?
echo "DATABASE_URL: ${DATABASE_URL:+✅ SET}"
echo "NEXT_PUBLIC_API_URL: ${NEXT_PUBLIC_API_URL:+✅ SET}"
echo "ANTHROPIC_API_KEY: ${ANTHROPIC_API_KEY:+✅ SET}"

# Secrets var mı kontrol et:
grep -r "password\s*=\s*['\"]" --include="*.ts" src/ 2>/dev/null | grep -v node_modules | wc -l
```

## Deploy Yöntemleri

### Vercel
```bash
# Preview deploy (staging):
npx vercel --yes 2>&1 | tail -10
# Çıktıdan URL'yi al

# Production deploy (onay gerekli):
# npx vercel --prod --yes
```

### Coolify (Self-hosted)
```bash
# Git push ile otomatik deploy tetikle:
git push origin main
echo "🚀 Coolify webhook tetiklendi"
echo "📊 Deploy takibi: https://your-coolify-instance/projects"
```

### Docker + Registry
```bash
VERSION=$(git rev-parse --short HEAD)
docker build -t app:$VERSION -t app:staging .
docker push registry.example.com/app:staging
echo "✅ Image push edildi: app:$VERSION"

# Coolify/K8s'e deploy:
# kubectl set image deployment/app app=registry.example.com/app:$VERSION
```

### PM2 (Node.js)
```bash
npm run build
pm2 reload ecosystem.config.js --env staging
pm2 status
```

## Post-Deploy Doğrulama

```bash
STAGING_URL="${NEXT_PUBLIC_API_URL:-http://localhost:3000}"

# Health check:
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$STAGING_URL/api/health" 2>/dev/null)
if [ "$HTTP_STATUS" = "200" ]; then
  echo "✅ Health check: OK ($HTTP_STATUS)"
else
  echo "❌ Health check BAŞARISIZ: $HTTP_STATUS"
fi

# Ana sayfa:
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$STAGING_URL" 2>/dev/null)
echo "Ana sayfa: $HTTP_STATUS"

# API ping:
curl -s "$STAGING_URL/api/ping" 2>/dev/null | head -3
```

## Smoke Test Kontrol Listesi

Deploy sonrası manuel/otomatik kontrol:
- [ ] Giriş sayfası yükleniyor
- [ ] Login akışı çalışıyor
- [ ] Ana dashboard yükleniyor
- [ ] API endpoint'leri cevap veriyor
- [ ] Database bağlantısı çalışıyor
- [ ] Email gönderimi (sandbox/test mode)
- [ ] Ödeme akışı (test card ile)

## Rollback Prosedürü

Sorun tespit edilirse:

```bash
# Son başarılı versiyona dön:
git log --oneline -10  # Hangi commit çalışıyordu?

# Vercel:
# vercel rollback [deployment-url] --yes

# Docker:
# docker pull registry.example.com/app:[önceki-versiyon]
# kubectl set image deployment/app app=registry.example.com/app:[önceki-versiyon]

# Git revert:
git revert HEAD  # Son commit'i geri al
git push origin main  # Auto-deploy tetiklenir
```

## Deploy Özeti Formatı

```
🚀 Deploy Tamamlandı — [Tarih Saat]

Branch:  [branch-adı]
Commit:  [hash] [mesaj]
Ortam:   Staging
URL:     [staging-url]

Kontroller:
  ✅ Build
  ✅ Tests (X passed)
  ✅ Health check
  ⚠️  Smoke tests — manuel kontrol gerekli

Notlar: [varsa]
```
