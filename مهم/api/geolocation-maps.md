# Geolocation & Maps API

Public endpoints for property search by coordinates and map integration with Leaflet, OpenStreetMap, and Google Maps.

Base path: `/api/v1/estates`

## Coordinates

| Table   | Columns              |
|---------|----------------------|
| cities  | `latitude`, `longitude` |
| places  | `latitude`, `longitude` |
| estates | `latitude`, `longitude` |

Estates without coordinates are excluded from geo queries.

---

## GET `/estates/nearby`

Find the nearest active properties to a point.

### Query parameters

| Parameter   | Required | Description                          |
|-------------|----------|--------------------------------------|
| `latitude`  | yes      | WGS-84 latitude (-90 to 90)          |
| `longitude` | yes      | WGS-84 longitude (-180 to 180)       |
| `limit`     | no       | Max results (1–50, default 10)       |
| `radius_km` | no       | Max search radius (default 25 km)    |

### Example

```http
GET /api/v1/estates/nearby?latitude=33.5138&longitude=36.2765&limit=5&radius_km=10
```

### Response

```json
{
  "success": true,
  "message": "Nearby estates retrieved.",
  "data": {
    "origin": { "latitude": 33.5138, "longitude": 36.2765 },
    "estates": [
      {
        "id": 1,
        "name": "Near Estate",
        "price": 450000,
        "latitude": 33.514,
        "longitude": 36.277,
        "distance_km": 0.042,
        "place": { "id": 1, "name": "...", "city": { "id": 1, "name": "..." } }
      }
    ]
  }
}
```

---

## GET `/estates/in-radius`

Paginated search for active properties within a radius.

### Query parameters

| Parameter   | Required | Description                    |
|-------------|----------|--------------------------------|
| `latitude`  | yes      | Origin latitude                |
| `longitude` | yes      | Origin longitude               |
| `radius_km` | yes      | Radius in km (max 100)         |
| `per_page`  | no       | Page size (default 15)         |
| `type_text` | no       | Filter by property type        |
| `kind_text` | no       | Filter by listing kind         |
| `min_price` | no       | Minimum price                  |
| `max_price` | no       | Maximum price                  |

### Example

```http
GET /api/v1/estates/in-radius?latitude=33.5138&longitude=36.2765&radius_km=5&per_page=20
```

---

## GET `/estates/map`

Returns map configuration and property markers for frontend map libraries.

### Query parameters (optional bounding box)

| Parameter | Description        |
|-----------|--------------------|
| `north`   | Northern latitude  |
| `south`   | Southern latitude  |
| `east`    | Eastern longitude  |
| `west`    | Western longitude  |

When all four bounds are provided, only markers inside the viewport are returned (useful for Leaflet `getBounds()`).

### Response structure

```json
{
  "success": true,
  "data": {
    "providers": {
      "leaflet": {
        "library": "leaflet",
        "tile_url": "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        "attribution": "..."
      },
      "openstreetmap": { "library": "leaflet", "tile_url": "...", "attribution": "..." },
      "google_maps": {
        "library": "google_maps",
        "requires_api_key": true,
        "api_key_configured": false,
        "api_key_env": "GOOGLE_MAPS_API_KEY"
      }
    },
    "center": { "latitude": 33.5138, "longitude": 36.2765 },
    "default_zoom": 12,
    "markers": [ { "id": 1, "latitude": 33.514, "longitude": 36.277, "price": 450000 } ],
    "total_markers": 1
  }
}
```

---

## Frontend integration

### Leaflet / OpenStreetMap

```javascript
const { data } = await fetch('/api/v1/estates/map').then(r => r.json());
const map = L.map('map').setView([data.center.latitude, data.center.longitude], data.default_zoom);

L.tileLayer(data.providers.leaflet.tile_url, {
  attribution: data.providers.leaflet.attribution,
}).addTo(map);

data.markers.forEach(m => {
  L.marker([m.latitude, m.longitude]).addTo(map).bindPopup(m.name);
});
```

### Google Maps

Set `GOOGLE_MAPS_API_KEY` in `.env`. The API indicates whether a key is configured; load the Google Maps JS SDK on the client and plot `data.markers` using `google.maps.Marker`.

---

## Configuration

Environment variables in `config/realestate.php`:

| Variable                    | Default | Description              |
|-----------------------------|---------|--------------------------|
| `MAP_DEFAULT_LAT`           | 33.5138 | Default map center lat   |
| `MAP_DEFAULT_LNG`           | 36.2765 | Default map center lng   |
| `MAP_DEFAULT_ZOOM`          | 12      | Default zoom level       |
| `GEO_DEFAULT_NEARBY_RADIUS_KM` | 25   | Default nearby radius    |
| `GEO_MAX_RADIUS_KM`         | 100     | Max allowed radius       |
| `GOOGLE_MAPS_API_KEY`       | —       | Google Maps API key      |

---

## Service layer

`App\Services\GeoSearchService` provides:

- `calculateDistanceKm()` — Haversine distance between two points
- `searchNearby()` — nearest properties
- `searchWithinRadius()` — paginated radius search
- `getMapData()` — markers + provider config
- `mapProviders()` — Leaflet/OSM/Google metadata

Distance is computed with the Haversine formula (Earth radius 6371 km).
