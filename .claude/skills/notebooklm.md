---
name: notebooklm
description: NotebookLM araştırma sentezi asistanı. Araştırma sentezi, çoklu kaynak analizi veya bilgi tabanı oluşturma talepleri olduğunda aktive olur. DAT-02 ve DAT-03 ile koordineli çalışır.
---

# NotebookLM Skill

Aktive olma koşulları:
- Çok sayıda doküman/kaynak analizi
- Araştırma sentezi ve özetleme
- "Şu kaynaklara göre..." tarzı sorular
- Akademik veya teknik literatür tarama
- Meeting transcript analizi
- Rakip analizi için çoklu rapor sentezi

## NotebookLM Nedir?

Google'ın AI destekli araştırma asistanı. Yüklediğiniz kaynaklara dayalı soru-cevap, özet ve podcast (Audio Overview) üretir. Halüsinasyon riski düşük — sadece yüklenen kaynaklardan yanıt verir.

## Desteklenen Kaynak Türleri

- PDF dosyaları (maks 500MB/kaynak)
- Google Docs
- Web URL'leri
- YouTube video URL'leri (transkript)
- Google Slides
- Düz metin / Markdown
- Audio dosyaları

## Workflow

### 1. Kaynak Hazırlama

```bash
# PDF'leri klasöre topla:
mkdir -p research/[konu]
# Dokümanları buraya koy

# Web sayfalarını PDF'e çevir (Puppeteer MCP ile):
# "example.com/rapor sayfasını PDF'e çevir"
```

### 2. Notebook Oluşturma

NotebookLM'de yeni notebook oluştur:
- notebooklm.google.com → New Notebook
- Kaynakları yükle (drag & drop veya URL)
- Kaynak işleme: 1-5 dakika

### 3. Araştırma Sorguları

**Özet sorguları:**
```
"Bu kaynakları özetle: ana temalar, önemli bulgular, tutarsızlıklar"
"[Konu] hakkında bu kaynakların ortak görüşü nedir?"
"Bu raporların söylemediği ama ima ettiği nedir?"
```

**Karşılaştırma sorguları:**
```
"Kaynak A ve Kaynak B'nin [konu] hakkındaki görüşlerini karşılaştır"
"Hangi kaynak [iddia]'yı destekliyor, hangisi çürütüyor?"
```

**Spesifik bilgi çıkarma:**
```
"Tüm kaynaklarda geçen istatistikleri tablo olarak çıkar"
"[Şirket adı] hakkında ne söyleniyor?"
"Hangi tarihler önemli?"
```

### 4. Audio Overview (Podcast)

NotebookLM konuları 2 AI host arasında tartışma formatında özetleyebilir:
- "Generate Audio Overview" butonuna tıkla
- ~5-10 dakikalık podcast üretir
- Karar alıcılara hızlı brief için ideal

### 5. Citation-Aware Çıktı

NotebookLM her cevap için kaynak referansı gösterir:
```
"Bu bilgi Kaynak 3, sayfa 12'den gelmektedir [⬡]"
```

Çıktıyı alırken citation'ları koru — DAT-03'ün citation management workflow'u ile entegre et.

## Entegrasyon Örnekleri

### Rakip Analizi (COM-09 + DAT-03)
```
1. Rakip şirketlerin yıllık raporlarını, basın bültenlerini PDF'e çevir
2. NotebookLM'e yükle
3. Sor: "Bu şirketlerin 2024-2025 stratejik öncelikleri neler?
   Fiyatlandırma stratejilerini karşılaştır."
4. Çıktıyı COM-09'un rekabet analizi şablonuna dönüştür
```

### Teknik Standart Analizi (YON-02 + ENG-01)
```
1. RFC dokümanları, W3C standartları, MDN referansları yükle
2. Sor: "Next.js 15 App Router için önerilen cache stratejisi nedir?"
3. Kaynak-tabanlı yanıtı architecture kararına dönüştür
4. ADR (Architecture Decision Record) olarak kaydet
```

### Müşteri Geri Bildirim Analizi (SAL-03 + COM-01)
```
1. Müşteri destek ticket'larını, NPS yorumlarını, Trendyol yorumlarını yükle
2. Sor: "En sık şikayet edilen konular neler? Öncelik sırası?"
3. Tema analizi → Ürün geliştirme backlog'una dönüştür
```

## Sınırlar

- İnternet'ten gerçek zamanlı veri çekemez (yüklenen kaynaklar ile sınırlı)
- Max 50 kaynak/notebook
- Görsel/tablo anlama: PDF'de iyi, görüntülerde sınırlı
- Türkçe kaynak desteği: iyi (çok dilli)
- Hassas/gizli dokümanlar → Google'a yükleme → KVKK dikkat
