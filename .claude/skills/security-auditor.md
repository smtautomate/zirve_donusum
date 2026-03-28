---
name: security-auditor
description: OWASP Top 10 güvenlik denetçisi. Auth kodu, API endpoint, kullanıcı girdi işleme veya güvenlikle ilgili kod yazılırken otomatik aktive olur.
---

# Security Auditor Skill

Aktive olma koşulları:
- Authentication / authorization kodu yazılırken
- Kullanıcı inputu işleyen kod
- API endpoint oluştururken
- Veritabanı sorgusu oluştururken
- File upload, CORS, CSP konfigürasyonu
- JWT, session, token işlemleri

## OWASP Top 10 Kontrolleri

### A01 — Broken Access Control

```typescript
// 🚨 SORUN — Kaynak sahibi kontrolü yok:
app.get('/api/invoices/:id', async (req, res) => {
  const invoice = await Invoice.findById(req.params.id)
  res.json(invoice)  // Başkasının faturasına erişilebilir!
})

// ✅ DOĞRU — Ownership check:
app.get('/api/invoices/:id', authMiddleware, async (req, res) => {
  const invoice = await Invoice.findById(req.params.id)
  if (!invoice || invoice.userId !== req.user.id) {
    return res.status(403).json({ error: 'Erişim reddedildi' })
  }
  res.json(invoice)
})
```

### A02 — Cryptographic Failures

```typescript
// 🚨 Zayıf hash:
const hash = crypto.createHash('md5').update(password).digest('hex')

// ✅ Güçlü hash:
import bcrypt from 'bcrypt'
const hash = await bcrypt.hash(password, 12)

// 🚨 HTTP URL (TLS yok):
const apiUrl = 'http://api.example.com'

// ✅ HTTPS zorunlu:
const apiUrl = 'https://api.example.com'
```

### A03 — Injection

```typescript
// 🚨 SQL injection:
const query = `SELECT * FROM users WHERE email = '${email}'`

// ✅ Parameterized (Prisma otomatik korur):
const user = await prisma.user.findUnique({ where: { email } })

// ✅ Raw SQL ile parameterized:
const user = await prisma.$queryRaw`SELECT * FROM users WHERE email = ${email}`
```

```typescript
// 🚨 XSS — HTML'e unsanitized veri:
element.innerHTML = userInput

// ✅ Güvenli alternatifler:
element.textContent = userInput  // Text olarak render et
// veya DOMPurify ile sanitize:
import DOMPurify from 'dompurify'
element.innerHTML = DOMPurify.sanitize(userInput)
```

### A05 — Security Misconfiguration

```typescript
// 🚨 CORS wildcard production'da:
app.use(cors({ origin: '*' }))

// ✅ Whitelist:
app.use(cors({
  origin: process.env.NODE_ENV === 'production'
    ? ['https://app.example.com', 'https://www.example.com']
    : 'http://localhost:3000'
}))
```

### A07 — Authentication Failures

```typescript
// 🚨 Weak JWT algoritması:
jwt.sign(payload, secret, { algorithm: 'HS256' })  // Symmetric

// ✅ Asymmetric (daha güvenli):
jwt.sign(payload, privateKey, { algorithm: 'RS256', expiresIn: '15m' })

// ✅ Refresh token pattern:
// Access token: 15 dakika
// Refresh token: 7 gün, HttpOnly cookie olarak sakla
```

### A09 — Logging Failures

```typescript
// 🚨 PII loglama:
console.log('Kullanıcı login:', { email, password, creditCard })

// ✅ Sadece non-sensitive veri:
console.log('Kullanıcı login:', { userId: user.id, ip: req.ip })
```

## Otomatik Kontrol Listesi

Her API endpoint için:
- [ ] Authentication middleware uygulanmış mı?
- [ ] Authorization (yetki) kontrol ediliyor mu?
- [ ] Input validation (zod, joi, yup) var mı?
- [ ] Rate limiting tanımlı mı?
- [ ] Hata mesajı bilgi sızdırmıyor mu? (`Internal Server Error` yeterli, stack trace değil)

## Güvenlik Header'ları (Next.js)

```javascript
// next.config.js
const securityHeaders = [
  { key: 'X-DNS-Prefetch-Control', value: 'on' },
  { key: 'X-XSS-Protection', value: '1; mode=block' },
  { key: 'X-Frame-Options', value: 'SAMEORIGIN' },
  { key: 'X-Content-Type-Options', value: 'nosniff' },
  { key: 'Referrer-Policy', value: 'origin-when-cross-origin' },
  { key: 'Content-Security-Policy', value: "default-src 'self'..." },
]
```

## Acil Eskalasyon

Aşağıdaki durumlarda QA-03 (Security) ve YON-01'e hemen bildir:
- Production'da SQL injection açığı
- Kullanıcı veri sızıntısı riski
- Authentication bypass
- Hardcoded credential
