---
name: incident-triage
description: Incident severity belirleme ve ilk müdahale asistanı. Hata raporu, sistem çöküşü, alert veya kullanıcı şikayeti geldiğinde otomatik aktive olur. OPS-04 ve QA-02 ile koordineli.
---

# Incident Triage Skill

Aktive olma koşulları:
- "Sistem çöktü", "hata var", "kullanıcılar giremiyort" raporları
- Kritik hata logları
- Monitoring alert'leri
- Müşteri şikayeti eskalasyonu

## Severity Sınıflandırma

| SEV | Tanım | Örnek | Yanıt SLA |
|-----|-------|-------|-----------|
| **SEV1** | Tam çöküş veya veri kaybı | Site erişilemiyor, ödeme çalışmıyor | 15 dakika |
| **SEV2** | Kritik özellik çalışmıyor | Login başarısız, sepet kayboluyor | 1 saat |
| **SEV3** | Önemli özellik bozuk | Arama yavaş, resimler yüklenmiyor | 4 saat |
| **SEV4** | Küçük sorun | Typo, CSS bozukluğu, minor UI hata | Sonraki sprint |

## Triage Karar Ağacı

```
Sorun bildirimi geldi
    │
    ├── Siteye erişim tamamen yok? → SEV1
    │
    ├── Ödeme/satın alma çalışmıyor? → SEV1
    │
    ├── Veri kaybı var mı? → SEV1
    │
    ├── Kullanıcıların >%50'si etkileniyor? → SEV2
    │
    ├── Core özellik (login, sepet) bozuk? → SEV2
    │
    ├── Tek kullanıcı veya nadir durum? → SEV3/SEV4
    │
    └── Estetik veya minor UI? → SEV4
```

## İlk Tanı Adımları

```bash
# 1. Uygulama durumu:
curl -s -o /dev/null -w "%{http_code}" https://app.example.com/api/health

# 2. Son deployment zamanı:
git log --oneline -5

# 3. Error log kontrolü (varsa):
tail -100 /var/log/app/error.log 2>/dev/null | grep -E "ERROR|FATAL|Exception" | tail -20

# 4. Sistem kaynakları:
df -h 2>/dev/null | head -5  # Disk doldu mu?
free -h 2>/dev/null || vm_stat 2>/dev/null | head -5  # RAM durumu

# 5. Veritabanı bağlantısı:
# psql $DATABASE_URL -c "SELECT 1;" 2>&1 | head -3
```

## SEV1 — Hemen Yapılacaklar

```
1. War Room kur:
   - Slack kanal aç: #incident-[tarih]-[konu]
   - Video link paylaş
   - YON-01'i (CEO/PM) bildir

2. Incident Komutanı ata (YON-01 veya OPS-04)

3. İletişim başlat:
   - Status page güncelle: "Sorunu araştırıyoruz"
   - Her 30 dakikada bir güncelleme

4. Rollback değerlendir:
   - Son deploy ne zaman? Sorunla ilgili mi?
   - git revert veya önceki versiyona dön
```

## SEV2 — 1 Saat İçinde

```
1. Etkilenen kullanıcı sayısını tespit et
2. Workaround var mı? (geçici çözüm)
3. Fix estimate al: ENG-01 / ENG-03'ten
4. Müşterileri bilgilendir (eğer çok kişi etkileniyorsa)
```

## İletişim Şablonları

### Status Page — Araştırılıyor
```
[HH:MM UTC] Kullanıcıların [özellik] erişiminde sorun yaşandığı raporlanmaktadır.
Ekibimiz durumu araştırmaktadır. Güncellemeler için bu sayfayı takip edin.
```

### Status Page — Tespit Edildi
```
[HH:MM UTC] Sorunun kaynağı tespit edildi: [kısa açıklama].
Düzeltme üzerinde çalışıyoruz. Tahmini çözüm: [süre].
```

### Status Page — Çözüldü
```
[HH:MM UTC] Sorun çözüldü. Tüm sistemler normal çalışmaktadır.
Etkilenen süre: [X dakika/saat]. Detaylı rapor: [bağlantı]
```

## Yaygın Sorunlar ve Hızlı Çözümler

| Sorun | İlk Kontrol | Hızlı Çözüm |
|-------|-------------|-------------|
| Site açılmıyor | DNS, CDN, SSL | Cloudflare'i kontrol et |
| 500 hataları | App log, env var | Restart veya rollback |
| DB bağlanamıyor | DB sunucu durumu | DB restart, bağlantı havuzu |
| Yavaş sayfa | Cache miss, N+1 | Redis restart, cache warm |
| Login çalışmıyor | JWT expiry, auth servis | Token temizle, auth restart |
| Ödeme başarısız | Stripe/iyzico API | API status sayfasını kontrol |

## Post-Incident

SEV1-2 sonrası 48 saat içinde:
1. `templates/postmortem.md` şablonunu doldur
2. 5 Whys analizi yap
3. Action item listesi çıkar (owner + deadline)
4. Team'e postmortem paylaş

Postmortem → `docs/incidents/[YYYY-MM-DD]-[konu].md` olarak kaydet.
