# /test-run — Test Suite Çalıştırma

**Agentlar**: QA-01 (QA Lead), ENG-01/ENG-03 (test yazma)

Test suite'i katmanlı olarak çalıştır ve analiz et.

## 1. Test Framework'ü Tespit Et

```bash
# package.json'dan tespit:
cat package.json 2>/dev/null | grep -E '"jest"|"vitest"|"mocha"|"cypress"|"playwright"' | head -10

# Config dosyaları:
ls jest.config.* vitest.config.* playwright.config.* cypress.config.* 2>/dev/null
```

## 2. Unit Testler

```bash
# Jest:
npx jest --testPathPattern="unit|spec" --coverage --coverageReporters=text 2>&1 | tail -40

# Vitest:
npx vitest run --coverage 2>&1 | tail -40

# Python (pytest):
python -m pytest tests/unit/ -v --tb=short 2>&1 | tail -40
```

Başarısız test çıktılarını analiz et ve olası sebepleri listele.

## 3. Integration Testler

```bash
# Jest integration:
npx jest --testPathPattern="integration" 2>&1 | tail -30

# Playwright API testing:
npx playwright test tests/api/ 2>&1 | tail -30
```

## 4. E2E Testler (Varsa)

```bash
# Playwright:
npx playwright test --reporter=list 2>&1 | tail -50

# Cypress (headless):
npx cypress run --headless 2>&1 | tail -50
```

**Not**: E2E testler uzun sürebilir. Önce kullanıcıya sor: "E2E testler de çalıştırılsın mı? (~2-10 dakika sürebilir)"

## 5. Coverage Analizi

```bash
# Coverage raporu varsa:
cat coverage/coverage-summary.json 2>/dev/null | \
  python3 -c "import sys,json; d=json.load(sys.stdin)['total']; print(f\"Statements: {d['statements']['pct']}%\nBranches: {d['branches']['pct']}%\nFunctions: {d['functions']['pct']}%\nLines: {d['lines']['pct']}%\")" 2>/dev/null || true
```

Coverage hedefleri (QA-01 standartları):
| Tip | Minimum | Hedef |
|-----|---------|-------|
| Statements | 80% | 90% |
| Branches | 75% | 85% |
| Functions | 80% | 90% |
| Lines | 80% | 90% |

## 6. Başarısız Test Analizi

Test başarısız olursa:

1. **Hata türünü tespit et**:
   - Assertion hatası → Test beklentisi yanlış mı, kod mu?
   - Import/Module hatası → Dependency sorunu
   - Timeout → Async sorun veya yavaş servis
   - Snapshot mismatch → UI değişti, snapshot güncellenmeli mi?

2. **Son değişikliklerle ilişkilendir**:
```bash
git diff HEAD~1 --name-only | head -20
```
Son commit'te değişen dosyalar testle ilgili mi?

3. **Flaky test mi?**:
```bash
# Testi 3 kez çalıştır:
for i in 1 2 3; do npx jest [test-dosyasi] --no-coverage 2>&1 | tail -5; echo "---"; done
```

## 7. Test Raporu

```markdown
## Test Sonuçları — [Tarih Saat]

### Özet
- Unit: X passed, X failed (Xms)
- Integration: X passed, X failed (Xms)
- E2E: X passed, X failed (Xs) / Çalıştırılmadı

### Coverage
| Tip | Mevcut | Hedef | Durum |
|-----|--------|-------|-------|
| Statements | X% | 80% | ✅/⚠️/❌ |
| Branches | X% | 75% | ✅/⚠️/❌ |
| Functions | X% | 80% | ✅/⚠️/❌ |

### Başarısız Testler
| Test | Hata | Sebep | Öncelik |
|------|------|-------|---------|
| ... | ... | ... | P1/P2/P3 |

### Öneriler
- [ ] ...
```

CI'ya entegrasyon: Bu testi `npm run test:ci` komutu ile otomasyon pipeline'ına bağla.
