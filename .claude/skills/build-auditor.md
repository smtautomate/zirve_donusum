---
name: build-auditor
description: Otomatik derleme ve tip hatası dedektörü. TypeScript, Python, PHP, Java, Go projelerinde build hatası veya type error gözlemlendiğinde otomatik aktive olur.
---

# Build Auditor Skill

Bu skill şu durumlarda otomatik olarak aktive olur:
- Compiler/TypeScript hata mesajları görüldüğünde
- "Cannot find module", "Type error", "SyntaxError" gibi ifadeler geçtiğinde
- Build başarısız olduğunda
- `tsc`, `npm run build`, `python`, `go build` komutları hata döndürdüğünde

## Hata Tespiti ve Sınıflandırma

Hataları şu kategorilere ayır:

### TypeScript / JavaScript
```
TS2304: Cannot find name 'X'          → Eksik import veya tanımsız değişken
TS2345: Argument type X not assignable → Tip uyuşmazlığı
TS2339: Property X not exist on type   → Yanlış property erişimi
TS7006: Parameter X implicitly any     → Tip eksik
TS2307: Cannot find module             → Import yolu hatalı
TS2551: Property X not exist, did mean → Yazım hatası
```

### Python
```
ModuleNotFoundError  → pip install gerekli veya import yolu hatalı
IndentationError     → Girinti sorunu
SyntaxError          → Sözdizimi hatası
AttributeError       → Yanlış attribute erişimi
TypeError            → Tip uyumsuzluğu
```

### PHP
```
Fatal error: Class X not found         → Autoload veya namespace sorunu
ParseError: syntax error               → Sözdizimi hatası
Fatal error: Uncaught TypeError        → Tip hatası
```

## Otomatik Düzeltme Süreci

1. **Hatayı analiz et**: Tam hata mesajını oku, dosya ve satır numarasını tespit et
2. **Dosyayı oku**: Hatalı satırın etrafındaki kodu incele (±20 satır)
3. **Kök nedeni bul**: Sadece semptomu değil, asıl sebebi tespit et
4. **Düzeltme öner**: Birden fazla çözüm varsa en basitini seç
5. **Uygula**: Dosyayı düzelt
6. **Doğrula**: Build'i yeniden çalıştır

## Yaygın Düzeltme Kalıpları

### Eksik Import (TypeScript)
```typescript
// Hata: Cannot find name 'useState'
// Düzeltme:
import { useState } from 'react'
```

### Tip Güvensizliği
```typescript
// Hata: Type 'string | undefined' not assignable to 'string'
// Düzeltme (null coalescing):
const value = data?.name ?? 'default'
// veya (non-null assertion — emin olunduğunda):
const value = data.name!
```

### Modül Bulunamıyor
```typescript
// Hata: Cannot find module '../utils/helpers'
// Kontrol: Dosya gerçekten var mı? Büyük/küçük harf uyumu?
// ls src/utils/ ile kontrol et
```

## Cascade Hata Yönetimi

Bir hata birden fazla hatayı tetikleyebilir. İlk hatayı düzelt, diğerlerinin gidip gitmediğini kontrol et. Bağımsız hataları önce düzelt, bağımlı olanları sonra.

## Sınırlar

- Mantıksal hataları (logic bugs) tespit edemez — sadece compiler hatalarını yakalar
- Runtime hatalarını build aşamasında tespit edemez
- Karmaşık circular dependency sorunlarında QA-02'ye eskalasyon öner
