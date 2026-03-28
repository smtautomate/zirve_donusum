# /review-pr — Pull Request Review

**Agentlar**: QA-01 (QA Lead), ENG-01/ENG-03, QA-03 (Security)

PR numarasını belirt veya mevcut branch'i incele.

## 1. PR Değişikliklerini Al

```bash
git diff main...HEAD --stat
git diff main...HEAD
```

Veya GitHub MCP ile:
```
get_pull_request(owner, repo, pull_number)
get_pull_request_files(owner, repo, pull_number)
```

## 2. Kod Kalite Kontrolü (ENG-01/ENG-03)

### Genel Prensipler
- [ ] Single Responsibility — her fonksiyon/sınıf tek iş yapıyor mu?
- [ ] DRY — tekrarlayan kod var mı?
- [ ] KISS — gereksiz karmaşıklık var mı?
- [ ] Naming — değişken/fonksiyon isimleri açıklayıcı mı?
- [ ] Error handling — tüm hata durumları karşılanmış mı?
- [ ] Edge cases — sınır durumlar düşünülmüş mü?

### TypeScript / JavaScript
- [ ] `any` tipi kullanımı var mı? (kabul edilemez)
- [ ] `!` non-null assertion aşırı mı?
- [ ] async/await hataları yakalanmış mı? (try/catch veya .catch)
- [ ] Promise.all vs sıralı await — doğru kullanılmış mı?
- [ ] Memory leak riski: event listener temizleniyor mu?

## 3. Test Kontrolü (QA-01)

- [ ] Yeni kod için test yazılmış mı?
- [ ] Test isimleri açıklayıcı mı? ("should do X when Y" formatı)
- [ ] Happy path + edge case + error case testleri var mı?
- [ ] Test bağımlılıkları izole mi? (mock/stub doğru kullanılmış)
- [ ] CI'da tüm testler geçiyor mu?

```bash
npm test 2>&1 | tail -20
```

## 4. Güvenlik Kontrolü (QA-03)

- [ ] Kullanıcı inputu validate ediliyor mu?
- [ ] SQL sorguları parameterized mi?
- [ ] Yeni endpoint'lerde auth middleware var mı?
- [ ] Hassas veri response'a dahil edilmemiş mi?
- [ ] Yeni bağımlılıklar güvenli mi?

```bash
npm audit --audit-level=moderate 2>&1
```

## 5. Performans Etkisi (ENG-12)

- [ ] N+1 sorgu riski var mı?
- [ ] Büyük veri setleri için pagination var mı?
- [ ] Gereksiz re-render tetikliyor mu? (React)
- [ ] Bundle size artıyor mu? (büyük import'lar)
- [ ] Cache invalidation doğru mu?

## 6. Breaking Change Analizi

- [ ] API kontratı değişiyor mu? (endpoint, payload, response)
- [ ] Database şeması değişiyor mu? (migration var mı?)
- [ ] Environment variable eklendi/değişti mi? (.env.example güncellendi mi?)
- [ ] Dependency major versiyon artışı var mı?

## 7. Dokümantasyon

- [ ] Yeni public API'ler dokümante edildi mi?
- [ ] README güncellendi mi? (gerekiyorsa)
- [ ] Değişiklik açıklayıcı commit mesajına sahip mi?
- [ ] Karmaşık mantık için inline yorum var mı?

## 8. Review Yorumu Oluştur

Bulguları şu formatta yaz:

```markdown
## PR Review: [Başlık]

### ✅ Onaylandı
- [İyi yapılan şeyler]

### 🔴 Blokaj (Merge edilemez)
- Satır X: [sorun açıklaması]
  ```suggestion
  // düzeltilmiş kod
  ```

### 🟡 Önerilen Değişiklikler
- [Öneri açıklaması]

### 💬 Sorular
- [Açıklama gerektiren noktalar]

**Karar**: Approve / Request Changes / Comment
```
