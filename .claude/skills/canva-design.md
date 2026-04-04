---
name: canva-design
description: Canva ile profesyonel tasarım. Poster, sosyal medya görseli, sunum, logo, infografik, YouTube thumbnail, CV, davetiye, rapor oluşturma talebi olduğunda aktive olur.
---

# Canva Tasarım Skill

Canva MCP doğrudan bağlı — profesyonel tasarımlar oluşturabilirsin.

## Kullanılabilir Tasarım Tipleri

| Tip | Kullanım |
|-----|----------|
| `instagram_post` | Instagram paylaşımı |
| `facebook_post` | Facebook paylaşımı |
| `twitter_post` | Twitter/X paylaşımı |
| `your_story` | Instagram/Facebook Story |
| `poster` | Etkinlik, tanıtım posteri |
| `flyer` | Broşür, el ilanı |
| `infographic` | Veri görselleştirme |
| `logo` | Marka logosu |
| `presentation` | Sunum (önce `request-outline-review` kullan) |
| `proposal` | İş teklifi |
| `report` | Rapor |
| `doc` | Döküman (memo, makale, plan) |
| `youtube_thumbnail` | YouTube kapak resmi |
| `youtube_banner` | YouTube kanal banner'ı |
| `business_card` | Kartvizit |
| `resume` | CV/Özgeçmiş |
| `invitation` | Davetiye |
| `facebook_cover` | Facebook kapak fotoğrafı |

## Adımlar

1. Kullanıcının isteğine en uygun `design_type` seç
2. Detaylı `query` yaz (İngilizce, stil/renk/tema detayları dahil)
3. `mcp__claude_ai_Canva__generate-design` çağır
4. Adayları kullanıcıya göster
5. Seçilen adayı `mcp__claude_ai_Canva__create-design-from-candidate` ile kaydet
6. Gerekirse `mcp__claude_ai_Canva__export-design` ile dışa aktar

## Sunum Oluşturma (Özel Akış)

Sunumlar için:
1. Önce `mcp__claude_ai_Canva__request-outline-review` ile outline oluştur
2. Kullanıcı onaylayana kadar outline'ı düzenle
3. Onaylandıktan sonra `mcp__claude_ai_Canva__generate-design-structured` ile üret

## Brand Kit Kullanımı

Marka tutarlılığı için:
1. `mcp__claude_ai_Canva__list-brand-kits` ile mevcut kit'leri listele
2. Seçilen `brand_kit_id`'yi `generate-design`'a geç

## İpuçları

- Query'yi İngilizce ve detaylı yaz
- Renk şeması, tipografi stili, hedef kitle belirt
- "Professional, modern, minimalist" gibi stil yönergeleri ekle
- Türkçe metin içermesi gerekiyorsa query'de belirt
