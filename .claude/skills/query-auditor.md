---
name: query-auditor
description: N+1 sorgu dedektörü ve veritabanı performans analizörü. ORM çağrıları, SQL sorguları veya veritabanı işlemleri yazılırken otomatik aktive olur.
---

# Query Auditor Skill

Aktive olma koşulları:
- Prisma, TypeORM, Sequelize, SQLAlchemy, Eloquent kodu yazılırken
- Raw SQL sorguları oluşturulurken
- "findMany", "findAll", "where", "include", "join" ifadeleri geçtiğinde
- Repository veya DAO pattern'i yazılırken

## N+1 Sorgu Tespiti

### Prisma (TypeScript)
```typescript
// 🚨 N+1 SORUNU — Her kullanıcı için ayrı sorgu:
const users = await prisma.user.findMany()
for (const user of users) {
  const orders = await prisma.order.findMany({ where: { userId: user.id } })
  // N sorgu! users.length kadar sorgu gider
}

// ✅ DOĞRU — Tek sorguda include:
const users = await prisma.user.findMany({
  include: { orders: true }
})

// ✅ DAHA İYİ — Sadece gerekli alanlar:
const users = await prisma.user.findMany({
  select: { id: true, name: true, orders: { select: { id: true, total: true } } }
})
```

### Sequelize (Node.js)
```javascript
// 🚨 N+1:
const users = await User.findAll()
for (const user of users) {
  user.orders = await Order.findAll({ where: { userId: user.id } })
}

// ✅ DOĞRU:
const users = await User.findAll({
  include: [{ model: Order, as: 'orders' }]
})
```

### Laravel Eloquent (PHP)
```php
// 🚨 N+1:
$users = User::all();
foreach ($users as $user) {
  echo $user->orders->count(); // Her iterasyonda sorgu!
}

// ✅ DOĞRU — Eager loading:
$users = User::with('orders')->get();
```

### SQLAlchemy (Python)
```python
# 🚨 N+1:
users = session.query(User).all()
for user in users:
    orders = user.orders  # Lazy loading — her kullanıcı için sorgu

# ✅ DOĞRU:
from sqlalchemy.orm import joinedload
users = session.query(User).options(joinedload(User.orders)).all()
```

## Yavaş Sorgu Tespiti

### Eksik Index İpuçları
```sql
-- Bu tür WHERE koşulları index olmadan yavaş olabilir:
-- ❌ Index olmayan kolonda filtreleme:
SELECT * FROM orders WHERE status = 'pending'  -- status'ta index var mı?

-- ❌ Leading wildcard (LIKE '%...') — index kullanamaz:
SELECT * FROM products WHERE name LIKE '%telefon%'

-- ✅ Full-text search kullan veya Elasticsearch
-- ✅ Prefix search (LIKE 'telefon%') index kullanabilir
```

### Önerilen Index'ler
Aşağıdaki pattern'leri görürsen index öner:

```sql
-- Sık filtrelenen kolonlar:
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_user_id ON orders(user_id);

-- Composite index (sıralama önemli):
CREATE INDEX idx_orders_user_status ON orders(user_id, status);

-- Partial index (sadece aktif kayıtlar):
CREATE INDEX idx_active_users ON users(email) WHERE deleted_at IS NULL;
```

## Pagination Kontrolü

```typescript
// 🚨 Offset pagination büyük tablolarda yavaşlar:
await prisma.product.findMany({ skip: 10000, take: 20 })  // Binlerce kaydı say!

// ✅ Cursor-based pagination:
await prisma.product.findMany({
  take: 20,
  skip: 1,  // Cursor'ı atla
  cursor: { id: lastId },
  orderBy: { id: 'asc' }
})
```

## SELECT * Anti-Pattern

```typescript
// 🚨 Gereksiz veri çekme:
const user = await prisma.user.findUnique({ where: { id } })
// Tüm kolonlar geliyor — password_hash dahil!

// ✅ Sadece gerekli alanlar:
const user = await prisma.user.findUnique({
  where: { id },
  select: { id: true, name: true, email: true }
})
```

## Önerilen Araçlar

- **Prisma**: `DEBUG=prisma:query` env var ile sorguları logla
- **TypeORM**: `logging: true` config'i
- **Sequelize**: `logging: console.log` option
- **PostgreSQL**: `EXPLAIN ANALYZE` ile sorgu planı analizi
- **pg-extras**: Yavaş sorgu istatistikleri

## Rapor Formatı

Tespit edilen sorunları şöyle raporla:

```
🔴 Kritik — N+1 Sorgu
  Dosya: src/services/OrderService.ts:45
  Sorun: Her sipariş için ayrı kullanıcı sorgusu
  Çözüm: include: { user: true } ekle
  Tahmini etki: 100 sipariş → 1 sorgu (şu an 101)

🟡 Öneri — Eksik Index
  Tablo: orders, Kolon: status
  Sorgu: WHERE status = 'pending' (checkout akışında)
  Çözüm: CREATE INDEX idx_orders_status ON orders(status);
```
