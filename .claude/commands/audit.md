# /audit — Kod Kalite Audit

**Agentlar**: QA-01 (QA Lead), QA-03 (Security), ENG-12 (Performance)

Kapsamlı kod kalite analizi. Tüm kontrolleri sırasıyla uygula.

## 1. Build & Type Check

```bash
# TypeScript projelerde:
npx tsc --noEmit 2>&1 | head -50

# veya:
npm run build 2>&1 | tail -30
```

Hataları kategorize et: type errors, import errors, syntax errors.

## 2. Linting

```bash
npm run lint 2>&1 | head -100
# veya:
npx eslint . --ext .ts,.tsx,.js,.jsx 2>&1 | head -50
```

## 3. Güvenlik Taraması (QA-03)

### Secret/Credential Leak Kontrolü
Kod içinde hardcoded secret ara:
```bash
grep -r "password\s*=" --include="*.ts" --include="*.js" --include="*.env" . | grep -v "node_modules" | grep -v ".git" | head -20
grep -r "api_key\|apikey\|API_KEY" --include="*.ts" --include="*.js" . | grep -v "node_modules" | grep -v "\.env\." | head -20
grep -r "Bearer [A-Za-z0-9]" --include="*.ts" --include="*.js" . | grep -v "node_modules" | head -10
```

### OWASP Top 10 Hızlı Kontrol
- **A01 — Broken Access Control**: Her endpoint'te authorization middleware var mı?
- **A02 — Cryptographic Failures**: MD5/SHA1 kullanımı var mı? (zayıf hash)
- **A03 — Injection**: SQL sorguları parameterized mi? ORM kullanılıyor mu?
- **A04 — Insecure Design**: Hassas veri loglanıyor mu?
- **A05 — Security Misconfiguration**: Debug mode production'da mı?
- **A06 — Vulnerable Components**: npm audit sonucu
- **A07 — Identification & Auth**: JWT algoritması RS256 mi?
- **A08 — Integrity Failures**: Dependency pinning var mı?
- **A09 — Logging Failures**: Error logları PII içeriyor mu?
- **A10 — SSRF**: Kullanıcı inputu URL olarak kullanılıyor mu?

```bash
# npm güvenlik audit:
npm audit --audit-level=high 2>&1 | head -50
```

## 4. Bağımlılık Analizi

```bash
# Outdated paketler:
npm outdated 2>&1 | head -30

# Lisans kontrolü:
npx license-checker --summary 2>&1 | head -20
```

## 5. Test Coverage (QA-01)

```bash
npm test -- --coverage --coverageReporters=text 2>&1 | tail -30
```

Coverage hedefleri:
- Statements: ≥80%
- Branches: ≥75%
- Functions: ≥80%
- Lines: ≥80%

## 6. Performans İpuçları (ENG-12)

Kod içinde ara:
```bash
# N+1 sorgu riski:
grep -r "\.find\|\.findAll\|\.where" --include="*.ts" . | grep -v "node_modules" | head -20

# Senkron dosya operasyonları (Node.js):
grep -r "readFileSync\|writeFileSync" --include="*.ts" . | grep -v "node_modules" | head -10

# console.log production'da:
grep -r "console\.log" --include="*.ts" --include="*.tsx" src/ | head -20
```

## 7. Kod Kalite Metrikleri

Kontrol et:
- [ ] Dosya başına max 300 satır (büyük dosyalar bölünmeli)
- [ ] Fonksiyon başına max 50 satır
- [ ] Cyclomatic complexity < 10
- [ ] TODO/FIXME sayısı (teknik borç)

```bash
# TODO/FIXME sayısı:
grep -r "TODO\|FIXME\|HACK\|XXX" --include="*.ts" --include="*.tsx" src/ | wc -l
```

## 8. Audit Raporu Oluştur

Bulguları şu formatta özetle:

```markdown
## Kod Audit Raporu — [Tarih]

### Kritik (Hemen Düzeltilmeli)
- [ ] ...

### Önemli (Bu Sprint)
- [ ] ...

### İyileştirme (Backlog)
- [ ] ...

### Metrikler
- Build: ✅/❌
- Lint hataları: X
- Test coverage: X%
- Güvenlik açıkları: X (critical: X, high: X)
- Outdated paket: X
```

Raporu `docs/audit-[YYYY-MM-DD].md` dosyasına kaydet önerisinde bulun.
