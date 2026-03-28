---
name: test-generator
description: Test case üreticisi. Test olmayan yeni fonksiyon/sınıf oluşturulduğunda veya "test yaz" talebi geldiğinde otomatik aktive olur.
---

# Test Generator Skill

Aktive olma koşulları:
- Test dosyası olmayan yeni fonksiyon oluşturulduğunda
- "Test yaz", "unit test ekle" talepleri
- Bug fix yapıldığında (regression test)
- API endpoint yazıldığında (integration test)

## Test Çerçevesi Tespiti

```bash
cat package.json | grep -E '"jest"|"vitest"|"mocha"|"playwright"|"cypress"' 2>/dev/null
ls jest.config.* vitest.config.* 2>/dev/null
```

## Unit Test Şablonları

### Jest / Vitest (TypeScript)

```typescript
import { describe, it, expect, beforeEach, vi } from 'vitest'
// veya: import { describe, it, expect, beforeEach, jest } from '@jest/globals'

import { calculateDiscount } from '../src/utils/pricing'

describe('calculateDiscount', () => {
  // Happy path
  it('geçerli kupon ile doğru indirim uygular', () => {
    const result = calculateDiscount(100, 'INDIRIM20')
    expect(result).toBe(80)
  })

  // Edge cases
  it('geçersiz kupon ile orijinal fiyatı döner', () => {
    const result = calculateDiscount(100, 'INVALID')
    expect(result).toBe(100)
  })

  it('sıfır fiyat ile sıfır döner', () => {
    expect(calculateDiscount(0, 'INDIRIM20')).toBe(0)
  })

  it('negatif fiyat için hata fırlatır', () => {
    expect(() => calculateDiscount(-50, 'INDIRIM20')).toThrow('Fiyat negatif olamaz')
  })

  // Mocking
  it('veritabanından kupon kontrolü yapar', async () => {
    const mockDb = { findCoupon: vi.fn().mockResolvedValue({ discount: 0.2 }) }
    const result = await calculateDiscountWithDb(100, 'INDIRIM20', mockDb)
    expect(mockDb.findCoupon).toHaveBeenCalledWith('INDIRIM20')
    expect(result).toBe(80)
  })
})
```

### API Integration Tests (Supertest + Jest)

```typescript
import request from 'supertest'
import app from '../src/app'
import { prisma } from '../src/lib/db'

describe('POST /api/products', () => {
  let authToken: string

  beforeEach(async () => {
    // Test kullanıcısı oluştur ve token al
    authToken = await getTestToken()
    // DB'yi temizle
    await prisma.product.deleteMany()
  })

  afterAll(async () => {
    await prisma.$disconnect()
  })

  it('geçerli veri ile 201 döner', async () => {
    const response = await request(app)
      .post('/api/products')
      .set('Authorization', `Bearer ${authToken}`)
      .send({ name: 'Test Ürün', price: 99.99, categoryId: 'cat-1' })

    expect(response.status).toBe(201)
    expect(response.body).toMatchObject({
      id: expect.any(String),
      name: 'Test Ürün',
      price: 99.99
    })
  })

  it('auth olmadan 401 döner', async () => {
    const response = await request(app)
      .post('/api/products')
      .send({ name: 'Test', price: 99 })

    expect(response.status).toBe(401)
  })

  it('eksik zorunlu alan ile 400 döner', async () => {
    const response = await request(app)
      .post('/api/products')
      .set('Authorization', `Bearer ${authToken}`)
      .send({ name: 'Test' })  // price eksik

    expect(response.status).toBe(400)
    expect(response.body.code).toBe('VALIDATION_ERROR')
  })
})
```

### React Component Tests (Testing Library)

```typescript
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { ProductCard } from '../src/components/ProductCard'

describe('ProductCard', () => {
  const mockProduct = {
    id: '1',
    name: 'Test Ürün',
    price: 99.99,
    imageUrl: '/test.jpg'
  }

  it('ürün bilgilerini doğru gösterir', () => {
    render(<ProductCard product={mockProduct} />)
    expect(screen.getByText('Test Ürün')).toBeInTheDocument()
    expect(screen.getByText('₺99,99')).toBeInTheDocument()
  })

  it('sepete ekle butonuna tıklanınca callback çağrılır', async () => {
    const onAddToCart = vi.fn()
    render(<ProductCard product={mockProduct} onAddToCart={onAddToCart} />)

    await userEvent.click(screen.getByRole('button', { name: /sepete ekle/i }))
    expect(onAddToCart).toHaveBeenCalledWith(mockProduct.id)
  })

  it('stok yoksa buton devre dışı', () => {
    render(<ProductCard product={{ ...mockProduct, stock: 0 }} />)
    expect(screen.getByRole('button', { name: /sepete ekle/i })).toBeDisabled()
  })
})
```

## pytest (Python)

```python
import pytest
from unittest.mock import patch, MagicMock
from app.services.pricing import calculate_discount

class TestCalculateDiscount:
    def test_valid_coupon_applies_discount(self):
        result = calculate_discount(100, 'INDIRIM20')
        assert result == 80

    def test_invalid_coupon_returns_original(self):
        result = calculate_discount(100, 'INVALID')
        assert result == 100

    def test_negative_price_raises_error(self):
        with pytest.raises(ValueError, match="Fiyat negatif olamaz"):
            calculate_discount(-50, 'INDIRIM20')

    @patch('app.services.pricing.db')
    def test_db_coupon_lookup(self, mock_db):
        mock_db.find_coupon.return_value = {'discount': 0.2}
        result = calculate_discount_with_db(100, 'INDIRIM20')
        assert result == 80
        mock_db.find_coupon.assert_called_once_with('INDIRIM20')
```

## Edge Case Checklist

Her fonksiyon için test edilmesi gerekenler:
- [ ] Normal giriş (happy path)
- [ ] Boş değer (`null`, `undefined`, `""`, `[]`, `{}`)
- [ ] Sınır değerleri (min, max, 0, -1, MAX_INT)
- [ ] Geçersiz tip (string beklenirken number)
- [ ] Async hata durumu (ağ hatası, DB hatası)
- [ ] Concurrent çağrılar (race condition)

## Coverage Hedefleri

```json
// package.json veya jest.config.ts
{
  "coverageThreshold": {
    "global": {
      "statements": 80,
      "branches": 75,
      "functions": 80,
      "lines": 80
    }
  }
}
```
