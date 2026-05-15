# Service A - Katalog Barang Lelang

Service ini menangani katalog barang pada sistem lelang online. User hanya membaca daftar/detail barang, sedangkan admin dapat menambah, mengubah, dan menghapus barang memakai API key.

## Endpoint

User:

- `GET /api/items`
- `GET /api/items/{id}`

Admin:

- `POST /api/admin/items`
- `PUT /api/admin/items/{id}`
- `DELETE /api/admin/items/{id}`

Swagger:

- `GET /api/documentation`

GraphQL Playground:

- `GET /graphql-playground`
- endpoint query: `POST /graphql`

## Menjalankan dengan Docker

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan l5-swagger:generate
```

Base URL lokal:

```text
http://localhost:8080
```

API key admin bawaan seeder:

```text
local-admin-key
```

Pakai header:

```text
Authorization: Bearer local-admin-key
```

## Contoh REST

### GET /api/items

Response `200 OK`:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Laptop ThinkPad X1",
      "description": "Kondisi bekas, normal.",
      "base_price": 5000000,
      "current_price": 5000000,
      "auction_start_at": "2026-05-16T03:00:00.000000Z",
      "auction_end_at": "2026-05-20T03:00:00.000000Z",
      "status": "OPEN"
    }
  ]
}
```

### POST /api/admin/items

Request:

```json
{
  "name": "Kamera Mirrorless",
  "description": "Kondisi baik dan siap lelang.",
  "base_price": 7500000,
  "auction_start_at": "2026-05-16T10:00:00+07:00",
  "auction_end_at": "2026-05-20T10:00:00+07:00",
  "status": "OPEN"
}
```

Response `201 Created`:

```json
{
  "data": {
    "id": 21,
    "name": "Kamera Mirrorless",
    "description": "Kondisi baik dan siap lelang.",
    "base_price": 7500000,
    "current_price": 7500000,
    "status": "OPEN"
  }
}
```

Tanpa API key, admin endpoint akan mengembalikan `401 Unauthorized`.

## Contoh GraphQL

```graphql
query {
  items(first: 10) {
    data {
      id
      name
      base_price
      current_price
      status
    }
  }
}
```

## Kenapa Strukturnya Seperti Ini?

- `routes/api.php` memisahkan endpoint user dan admin agar akses lebih jelas.
- `FormRequest` dipakai untuk validasi supaya controller tetap bersih dan data buruk berhenti sebelum masuk database.
- `JsonResource` dipakai agar format response konsisten.
- `ApiKeyMiddleware` mengunci endpoint admin memakai `Authorization: Bearer <api-key>` atau `X-API-Key`.
- Tabel `items` diberi index pada `status`, `auction_end_at`, `auction_start_at`, dan `name` agar pencarian katalog lebih cepat.
- Cache Redis dipakai pada daftar/detail barang karena katalog sering dibaca dan lebih jarang diubah.
- Swagger dan GraphQL disiapkan untuk memudahkan dokumentasi, eksplorasi data, dan integrasi dengan service lain.
