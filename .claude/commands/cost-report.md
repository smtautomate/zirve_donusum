# /cost-report — AI API Maliyet Raporu

**Agent**: DAT-08 (AI Observability), YON-01 (bütçe kararları)

AI API kullanım ve maliyet analizi. Sprint bazlı bütçe takibi.

## 1. Claude API Maliyet Referansı (2025-2026)

| Model | Input ($/MTok) | Output ($/MTok) | Kullanım |
|-------|---------------|-----------------|---------|
| claude-opus-4-6 | $15.00 | $75.00 | Karmaşık görevler |
| claude-sonnet-4-6 | $3.00 | $15.00 | Günlük görevler |
| claude-haiku-4-5 | $0.25 | $1.25 | Basit/hızlı görevler |

## 2. Gemini API Maliyet Referansı

| Model | Input ($/MTok) | Output ($/MTok) |
|-------|---------------|-----------------|
| gemini-2.0-flash-exp | $0.075 | $0.30 |
| gemini-1.5-pro | $1.25 | $5.00 |
| gemini-1.5-flash | $0.075 | $0.30 |

## 3. OpenAI Maliyet Referansı

| Model | Input ($/MTok) | Output ($/MTok) |
|-------|---------------|-----------------|
| gpt-4o | $2.50 | $10.00 |
| gpt-4o-mini | $0.15 | $0.60 |
| DALL-E 3 HD | $0.080/resim | — |

## 4. Mevcut Sprint Bütçesi

Mevcut kullanım verilerine göre rapor oluştur. Varsa log dosyalarını kontrol et:

```bash
# Aktivite log varsa:
cat /tmp/ai-team-activity.log 2>/dev/null | tail -50

# Anthropic API kullanımı takibi:
ls -la ~/.anthropic/ 2>/dev/null || echo "Anthropic log dizini bulunamadı"
```

## 5. Sprint Bütçe Tahmini

Ortalama kullanım bazında aylık tahmini maliyet hesapla:

**Örnek Hesap** (tipik sprint):
```
Günlük token kullanımı tahmini:
- Kod yazma: ~500K input, ~200K output (Sonnet) = $3.50
- Araştırma: ~200K input, ~50K output (Sonnet) = $1.35
- Hızlı sorular: ~300K input, ~100K output (Haiku) = $0.20
─────────────────────────────────────────────────────
Günlük toplam: ~$5.05
Aylık toplam: ~$150

Bütçe önerisi:
- Geliştirici başına: $200/ay (Sonnet ağırlıklı)
- Takım (5 geliştirici): $800-1000/ay
- Production AI özellikleri (ayrı): Kullanıma göre
```

## 6. Maliyet Optimizasyon Önerileri

### Model Seçimi Kuralları
```
Görev Türü                          Önerilen Model
─────────────────────────────────────────────────
Basit kod tamamlama                 Haiku
Kısa metin üretme (<500 token)      Haiku
Rutin sorular                       Haiku
Kod yazma, review                   Sonnet ✓ (varsayılan)
Karmaşık refactor                   Sonnet
Araştırma, analiz                   Sonnet
Kritik mimari kararlar              Opus
Uzun doküman analizi (>50K tok)     Opus
```

### Prompt Optimizasyonu
- **Cache kullanımı**: Aynı system prompt tekrar eden sorgularda → prompt caching aktif et
- **Batch işleme**: 10+ benzer istek varsa batch API kullan (%50 indirim)
- **Context penceresi**: Gereksiz context'i budala (eski mesajlar)
- **Streaming**: Uzun output'larda streaming ile daha iyi UX

### Haiku ile Tasarruf Senaryoları
```
Kod format kontrolü:      Haiku (Sonnet yerine %80 tasarruf)
Basit validation:         Haiku
Tür çevirisi:             Haiku
Kısa özet:                Haiku
Karmaşık olmayan çeviri:  Haiku
```

## 7. Aylık Bütçe Takip Şablonu

```markdown
## AI API Maliyet Raporu — [Ay/Yıl]

### Genel Özet
- **Toplam Harcama**: $X.XX
- **Bütçe**: $X.XX
- **Sapma**: +X% / -X%
- **Maliyet/Geliştirici**: $X.XX

### Model Bazlı Kullanım
| Model | Token (MTok) | Maliyet | % Pay |
|-------|-------------|---------|-------|
| claude-sonnet-4-6 | X | $X | X% |
| claude-haiku-4-5 | X | $X | X% |
| claude-opus-4-6 | X | $X | X% |
| Toplam | X | $X | 100% |

### Takım Bazlı Kullanım (tahmini)
| Takım | Model | Kullanım | Maliyet |
|-------|-------|---------|---------|
| ENG | Sonnet | X | $X |
| DAT | Sonnet/Opus | X | $X |
| CRE | Sonnet | X | $X |

### Optimizasyon Fırsatları
1. [Haiku'ya taşınabilecek görevler]
2. [Cache açılabilecek istekler]
3. [Toplu işlenebilecek görevler]

### Gelecek Ay Tahmini
$X.XX (mevcut büyüme oranı: X%)
```

## 8. Uyarı Eşikleri

Bütçe uyarı sistemini hatırlat:
- **Günlük $50+**: YON-01'e bildir
- **Haftalık $200+**: Sprint bütçesi gözden geçir
- **Aylık $800+**: Model optimizasyonu gerekli
