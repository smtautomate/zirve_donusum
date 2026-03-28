# /deploy — Staging Deploy Workflow

**Agentlar**: OPS-01 (DevOps), ENG-01 / ENG-03 (Mühendislik), QA-01 (QA Lead)

Staging ortamına güvenli deploy. Adımları sırasıyla uygula.

## 1. Ön Kontroller (Pre-Deploy Checklist)

### Tests
```bash
npm test -- --watchAll=false 2>&1 | tail -20
```
Testler başarısız → **deploy yapma**, kullanıcıyı uyar.

### Build
```bash
npm run build 2>&1 | tail -30
```
Build hatası → **deploy yapma**.

### Lint
```bash
npm run lint 2>&1 | grep -E "error|Error" | head -20
```

### Type Check
```bash
npx tsc --noEmit 2>&1 | head -30
```

### Git Durumu
```bash
git status
git log --oneline -5
```
Uncommitted değişiklik varsa uyar: "Uncommitted değişiklikler var. Deploy öncesi commit etmek ister misiniz?"

## 2. Environment Variable Kontrolü

Staging için gerekli env var'ların tanımlı olduğunu doğrula:
```bash
# .env.staging veya ortam değişkenlerini kontrol et
echo "DATABASE_URL: ${DATABASE_URL:+SET}"
echo "NEXT_PUBLIC_API_URL: ${NEXT_PUBLIC_API_URL:+SET}"
echo "ANTHROPIC_API_KEY: ${ANTHROPIC_API_KEY:+SET}"
```

Eksik kritik env var varsa deploy'u durdur.

## 3. Database Migration Kontrolü

```bash
# Prisma kullanıyorsa:
npx prisma migrate status 2>&1 | tail -10

# Drizzle kullanıyorsa:
npx drizzle-kit check 2>&1 | tail -10
```

Uygulanmamış migration varsa sor: "Önce migration çalıştırılsın mı?"

## 4. Deploy Yöntemi Tespit Et

Proje yapısına göre deploy yöntemini belirle:

**Vercel projesi** (`vercel.json` veya `.vercel/` varsa):
```bash
npx vercel --prod=false --yes 2>&1 | tail -20
```

**Coolify projesi** (`coolify.json` veya docker-compose varsa):
```bash
# Git push ile otomatik deploy
git push origin main
echo "Coolify webhook tetiklendi — dashboard'dan takip et"
```

**Docker projesi**:
```bash
docker build -t app:staging .
docker tag app:staging registry.example.com/app:staging
docker push registry.example.com/app:staging
```

**Node.js / PM2**:
```bash
npm run build
pm2 reload ecosystem.config.js --env staging
```

## 5. Deploy Sonrası Doğrulama (Smoke Tests)

```bash
# Staging URL'yi belirle (package.json veya .env'den al)
STAGING_URL="${STAGING_URL:-https://staging.example.com}"

# Health check:
curl -s -o /dev/null -w "%{http_code}" "$STAGING_URL/api/health"

# Ana sayfa:
curl -s -o /dev/null -w "%{http_code}" "$STAGING_URL"
```

HTTP 200 alınmazsa uyar: "Deploy başarısız görünüyor — log kontrol edilmeli."

## 6. Deploy Özeti

```markdown
## Deploy Özeti — [Tarih Saat]

- **Ortam**: Staging
- **Branch**: [branch-adı]
- **Commit**: [commit-hash]
- **Build**: ✅/❌
- **Tests**: ✅/❌ (X passed, X failed)
- **Migration**: ✅ uygulandı / ⏭️ gerekmedi
- **URL**: [staging-url]
- **Süre**: Xs

### Kontrol Edilmesi Gerekenler
- [ ] Ana akış manuel test
- [ ] Ödeme akışı (varsa)
- [ ] Email gönderimi (sandbox modda)
```

## Rollback Prosedürü

Staging'de sorun çıkarsa:
```bash
# Son başarılı commit'e dön:
git log --oneline -10  # Hangi commit çalışıyordu?
git checkout <working-commit>
# Yukarıdaki deploy adımlarını tekrar uygula
```
