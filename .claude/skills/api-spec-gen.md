---
name: api-spec-gen
description: OpenAPI 3.0 ve API dokümantasyon üreticisi. Yeni API endpoint oluşturulduğunda veya mevcut endpoint değiştirildiğinde otomatik aktive olur.
---

# API Spec Generator Skill

Aktive olma koşulları:
- Yeni API endpoint oluşturulduğunda
- Route handler kodu yazılırken
- "API dokümantasyonu yaz" talebi
- Swagger/OpenAPI spec güncelleme isteği

## OpenAPI 3.0 Şablonu

```yaml
openapi: 3.0.3
info:
  title: AI-TEAM API
  version: 1.0.0
  description: |
    [Servis açıklaması]

servers:
  - url: https://api.example.com/v1
    description: Production
  - url: https://staging-api.example.com/v1
    description: Staging

security:
  - BearerAuth: []

components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    Error:
      type: object
      required: [code, message]
      properties:
        code:
          type: string
          example: VALIDATION_ERROR
        message:
          type: string
          example: "Alan geçersiz"
        details:
          type: array
          items:
            type: object

paths:
  /users/{id}:
    get:
      summary: Kullanıcı detayını getir
      operationId: getUserById
      tags: [Users]
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: string
            format: uuid
      responses:
        '200':
          description: Başarılı
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/User'
        '404':
          description: Kullanıcı bulunamadı
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
        '401':
          description: Yetkisiz erişim
```

## Kod'dan Spec Üretme

TypeScript route'undan spec oluştur:

```typescript
// Input (mevcut Express/Hono route):
app.post('/api/products', authMiddleware, async (req, res) => {
  const { name, price, categoryId } = req.body
  const product = await prisma.product.create({
    data: { name, price, categoryId, userId: req.user.id }
  })
  res.status(201).json(product)
})

// Output (OpenAPI spec):
/*
/products:
  post:
    summary: Yeni ürün oluştur
    operationId: createProduct
    tags: [Products]
    security:
      - BearerAuth: []
    requestBody:
      required: true
      content:
        application/json:
          schema:
            type: object
            required: [name, price, categoryId]
            properties:
              name:
                type: string
                minLength: 2
                maxLength: 100
                example: "Laptop"
              price:
                type: number
                minimum: 0
                example: 4999.99
              categoryId:
                type: string
                format: uuid
    responses:
      '201':
        description: Ürün oluşturuldu
      '400':
        description: Doğrulama hatası
      '401':
        description: Yetkisiz
*/
```

## HTTP Durum Kodları Rehberi

| Kod | Kullanım |
|-----|---------|
| 200 | Başarılı GET/PUT |
| 201 | Başarılı POST (kaynak oluşturuldu) |
| 204 | Başarılı DELETE (içerik yok) |
| 400 | Geçersiz istek (doğrulama hatası) |
| 401 | Kimlik doğrulama gerekli |
| 403 | Yetkisiz (kimlik doğrulandı ama izin yok) |
| 404 | Kaynak bulunamadı |
| 409 | Çakışma (zaten var) |
| 422 | İşlenemeyen varlık |
| 429 | Rate limit aşıldı |
| 500 | Sunucu hatası |

## Türkçe API Dokümantasyon Standartları

- `summary` → Türkçe, kısa imperative: "Ürün oluştur", "Kullanıcı getir"
- `description` → Türkçe, detaylı açıklama
- `example` değerleri → Türkçe içerik (isim, adres, ürün adı)
- Error mesajları → Türkçe: `"Alan zorunludur"`, `"Değer geçersiz"`

## Postman Collection Oluşturma

Spec hazır olduğunda:
```bash
# openapi-to-postman CLI ile:
npx openapi-to-postmanv2 -s openapi.yaml -o postman-collection.json

# veya online: app.getpostman.com → Import → OpenAPI
```
