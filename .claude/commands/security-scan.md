# /security-scan — Güvenlik Taraması

**Agent**: QA-03 (Security Engineer), OPS-08 (DevSecOps)

Kapsamlı güvenlik taraması. OWASP Top 10 + dependency + secret taraması.

## 1. Secret / Credential Leak Taraması

```bash
# Hardcoded şifre/token ara:
grep -rn "password\s*=\s*['\"][^'\"]\|api_key\s*=\s*['\"][^'\"]\|secret\s*=\s*['\"][^'\"]" \
  --include="*.ts" --include="*.js" --include="*.py" --include="*.php" \
  --exclude-dir=node_modules --exclude-dir=.git . 2>/dev/null | head -30

# AWS key formatı:
grep -rn "AKIA[0-9A-Z]{16}" --include="*.ts" --include="*.js" --include="*.env" . 2>/dev/null | grep -v node_modules

# Bearer token hardcoded:
grep -rn "Bearer [A-Za-z0-9\-_\.]{20,}" --include="*.ts" --include="*.js" . 2>/dev/null | grep -v node_modules | grep -v test | head -10

# .env dosyaları git'te mi?
git ls-files | grep "\.env$\|\.env\."
```

## 2. Dependency Güvenlik Açıkları

```bash
# npm audit:
npm audit --audit-level=moderate 2>&1

# Kritik açıklar varsa detay:
npm audit --json 2>/dev/null | jq '.vulnerabilities | to_entries[] | select(.value.severity == "critical") | {name: .key, severity: .value.severity, via: .value.via}' 2>/dev/null | head -50
```

## 3. OWASP Top 10 Statik Analiz

### A01 — Broken Access Control
```bash
# Middleware olmayan route'lar (Express/Next.js):
grep -rn "router\.\(get\|post\|put\|delete\)\|app\.\(get\|post\|put\|delete\)" \
  --include="*.ts" --include="*.js" src/ 2>/dev/null | grep -v "auth\|middleware\|protect" | head -20
```

### A02 — Cryptographic Failures
```bash
# Zayıf hash algoritmaları:
grep -rn "md5\|sha1\|createHash.*md5\|createHash.*sha1" \
  --include="*.ts" --include="*.js" . 2>/dev/null | grep -v node_modules | head -10

# HTTP (non-HTTPS) kullanımı:
grep -rn "http://" --include="*.ts" --include="*.js" src/ 2>/dev/null | grep -v "localhost\|127.0.0.1\|//schemas\|:///" | head -10
```

### A03 — Injection
```bash
# Raw SQL sorguları (Prisma/TypeORM dışı):
grep -rn "\$queryRaw\|\.query\s*(\|\.execute\s*(" \
  --include="*.ts" --include="*.js" src/ 2>/dev/null | grep -v node_modules | head -20

# eval() kullanımı:
grep -rn "\beval\s*(" --include="*.ts" --include="*.js" src/ 2>/dev/null | head -10
```

### A05 — Security Misconfiguration
```bash
# Debug mode kontrolü:
grep -rn "debug.*true\|NODE_ENV.*development" --include="*.ts" --include="*.js" src/ 2>/dev/null | head -10

# CORS wildcard:
grep -rn "origin.*\*\|cors.*\*" --include="*.ts" --include="*.js" src/ 2>/dev/null | head -10
```

### A09 — Security Logging Failures
```bash
# PII loglama kontrolü:
grep -rn "console\.log.*password\|logger.*email\|log.*token" \
  --include="*.ts" --include="*.js" src/ 2>/dev/null | head -10
```

## 4. Güvenlik Header Kontrolü (HTTP)

Uygulama HTTP server'ı varsa doğrula:
```bash
# Varsa çalışan uygulamayı test et:
curl -s -I "http://localhost:3000" 2>/dev/null | grep -iE "x-content-type|x-frame|strict-transport|content-security|permissions-policy"
```

Eksik header'lar:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Strict-Transport-Security` (HSTS)
- `Content-Security-Policy`

## 5. JWT Güvenlik Kontrolü

```bash
# HS256 yerine RS256 kullanımı var mı?
grep -rn "algorithm.*HS256\|sign.*HS256\|jwt.*secret" \
  --include="*.ts" --include="*.js" src/ 2>/dev/null | head -10

# JWT expiry kontrolü:
grep -rn "expiresIn\|exp:" --include="*.ts" --include="*.js" src/ 2>/dev/null | head -10
```

## 6. Docker / Container Güvenliği (varsa)

```bash
# Dockerfile varsa kontrol et:
if [ -f Dockerfile ]; then
  echo "=== Dockerfile Güvenlik Kontrolleri ==="
  grep -n "FROM\|USER\|RUN\|COPY\|ADD" Dockerfile
  # Root olarak çalışıyor mu?
  grep "USER root\|^USER 0" Dockerfile || echo "⚠️  USER direktifi yok — root olarak çalışıyor olabilir"
fi
```

## 7. Güvenlik Raporu

```markdown
## Güvenlik Tarama Raporu — [Tarih]

### Kritik 🔴
- [ ] ...

### Yüksek 🟠
- [ ] ...

### Orta 🟡
- [ ] ...

### Bilgi ℹ️
- [ ] ...

### Temiz ✅
- Secret leak: Bulunamadı
- Dependency: X critical, X high
- OWASP Top 10: X kontrol geçti

### Öncelikli Eylemler
1. ...
2. ...

### Sonraki Tarama
[Tarih — haftalık önerilir]
```

Kritik bulgu varsa QA-03 ve YON-01'e eskalasyon öner.
