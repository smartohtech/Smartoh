# Smartoh Technologies — Real-Time Non-Custodial Multitenant ISP Billing & Network Activation Platform

## 1. Architectural Overview & Non-Custodial Principles

Smartoh Technologies acts strictly as a **non-custodial billing, reconciliation, and network activation orchestration platform**:
- **Direct ISP Merchant Settlement**: Every ISP tenant connects its own authorized Safaricom M-Pesa Daraja Paybill, Buy Goods Till, Pesapal v3 merchant account, PayPal Commerce v2 account, or direct Bank IPN/Statement feed. Subscriber payments settle directly into the ISP's own merchant utility or bank account.
- **Zero Smartoh Holding Period**: Smartoh never pools funds across ISPs, routes subscriber collections into a Smartoh-owned wallet, or imposes an artificial waiting window before notifying the ISP or activating a subscriber.
- **Immediate Payment-to-Connectivity Pipeline**:
  1. Receive provider webhook/callback (`/api/webhooks/payments/{provider}/{tenantId?}`) or STK callback.
  2. Validate provider webhook signature (`X-Mpesa-Signature`, `X-Pesapal-Signature`, `PAYPAL-TRANSMISSION-SIG`, or `X-Bank-Signature`) and enforce replay/idempotency locking via `payment_webhook_events` and `payments.idempotency_key`.
  3. Resolve the exact ISP tenant, subscriber (`Client`), unpaid/overdue `Invoice`(s), and `BillingPlan`.
  4. Record durable `Payment` and `PaymentAllocation` entries inside an atomic database transaction (supporting partial payments, multi-invoice allocation, and wallet overpayments).
  5. Immediately enqueue and execute a `NetworkActivationJob` via `NetworkActivationOrchestrator` and `MikroTikService` without waiting for a scheduled batch.
  6. Apply targeted PPPoE, Hotspot, RADIUS CoA, or Static-IP updates on the ISP's router and verify the resulting network state.
  7. Stream real-time updates to the ISP dashboard (`/billing/payments/live-feed` via SSE/JSON) and Customer Portal (`/portal/live-status`).

---

## 2. Accurate Lifecycle & Status Reporting

Smartoh strictly separates payment verification, provider settlement, and router connectivity states:
- `Payment pending verification` — STK Push initiated or checkout opened; awaiting trusted provider confirmation.
- `Unverified manual reference — pending review` — Customer submitted a manual reference/screenshot claim; queued in `payment_reconciliation_exceptions` and **never** auto-activates service.
- `Payment verified — activation queued` — Provider confirmed payment; network job queued.
- `Payment received — activation retrying` — Payment verified and preserved, but the router/RADIUS server is temporarily offline; retrying automatically with exponential backoff (`30s, 60s, 120s, 240s, 480s`) and raising a critical `NOCIncident` if retries exhaust.
- `Payment verified — activation applied` — Router configuration updated; awaiting session verification.
- `Connected` (`connected_verified`) — Only displayed when router/RADIUS session or queue state is confirmed active.

---

## 3. Provider-Specific Capabilities & Limitations Disclosure

| Provider / Account Type | Settlement Destination | Real-Time Webhook API | Disclosed Limitations & Reconciliation Mode |
| :--- | :--- | :--- | :--- |
| **M-Pesa Daraja (Paybill & Buy Goods Till)** | Direct to ISP's M-Pesa Merchant Utility Account | Yes (STK Callback & C2B Confirmation) | Instant settlement & activation. Requires Safaricom Daraja C2B/STK production credentials. |
| **Pochi la Biashara** | Direct to ISP Owner's Pochi Wallet | Only if registered on Daraja Business API | Standard personal Pochi la Biashara numbers do **not** offer public real-time webhooks. Supported via verified M-Pesa CSV Statement Import or manual exception review. |
| **Pesapal API v3.0** | ISP's Pesapal Merchant Account | Yes (Instant IPN v3 Callback) | Real-time IPN triggers immediate subscriber activation, but Pesapal settles funds to the ISP's bank/mobile account on a **T+1 provider settlement cycle**. Smartoh marks callbacks as `pending_provider_settlement` until settled. |
| **PayPal Commerce v2** | Direct to ISP's PayPal Business Account | Yes (Signed Webhook Events) | Subject to PayPal merchant reserve holds on new accounts or international cards. |
| **Bank Accounts (Equity, KCB, Co-op, NCBA)** | Direct to ISP's Corporate Bank Account | Supported where Bank IPN is enabled | For bank accounts without an API/IPN agreement, Smartoh provides controlled CSV Statement Import (`/billing/payments/import-statement`) and an Exception Queue. |

---

## 4. Network Activation & Router Safety Rules

Implemented in `App\Services\MikroTik\MikroTikService` and `App\Services\Network\NetworkActivationOrchestrator`:
- **PPPoE**: Enables the subscriber's `/ppp secret`, assigns the purchased `BillingPlan` profile, removes only the subscriber's IP from `Smartoh_Suspended` address-list, and refreshes only restricted/walled-garden sessions.
- **Hotspot**: Restores the user/voucher profile and removes blocked MAC bindings without resetting legitimate data quotas.
- **Static-IP / DHCP**: Removes only the subscriber's IP from `Smartoh_Suspended` and updates the subscriber's `/queue simple` rate-limit while preserving IP assignments and firewall policies.
- **RADIUS CoA**: Dispatches targeted `CoA-Request` / rate-limit updates (`Mikrotik-Rate-Limit`).
- **Safety Guarantees**: Smartoh never reboots a router or disables unrelated firewall rules to activate or restrict a subscriber. Payment reversals trigger a controlled reassessment of that single subscriber's eligibility—never an indiscriminate network-wide disconnection.

---

## 5. Strict Multitenancy, Security & Separate SaaS Billing

- **Tenant Isolation**: Enforced server-side via `TenantResolutionMiddleware`, `BelongsToTenant` global scopes, encrypted `credentials_encrypted` in `payment_gateways` (with masked display via `masked_credentials`), and tenant-bound webhook verification.
- **Super Admin Privacy Guard**: Ordinary Super Admin views cannot browse individual ISP subscriber payment ledgers (`/billing/payments` returns `403 Forbidden` unless an explicit, audited support session is initiated via `/platform/tenants/{tenant}/impersonate`, which records a tamper-evident SHA-256 hash chain entry in `audit_logs`).
- **Separate SaaS Billing**: ISP subscription payments to Smartoh (`SaasPayment` / `SubscriptionLifecycleService`) update only that ISP's `SaasSubscription` state (`ACTIVE_PAID`) and never alter or reconnect individual ISP subscriber accounts.

---

## 6. Deployment, Monitoring & Rollback Procedures

### Deployment & Setup
```bash
composer install --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install && npm run build
```

### Background Worker & Scheduled Recovery
Run the queue listener and schedule the activation retry & expiry recovery command (`ReconcileAndActivateRetriesCommand`):
```bash
php artisan queue:listen --tries=3 --timeout=60
php artisan smartoh:reconcile-and-activate
```

### Rollback Procedure
To roll back the real-time non-custodial schema additions safely without losing core tenant/subscriber tables:
```bash
php artisan migrate:rollback --step=1
```

---

## 7. Kenya Network Map & GIS Fiber Infrastructure (`EPSG:4326` / `WGS 84`)

### 7.1 Authoritative Geographic Datasets & Licensing
- **Coordinate Reference System (CRS)**: `EPSG:4326` (`WGS 84` decimal degrees), bounded to the Republic of Kenya (`Latitude -4.80°S to +5.20°N`, `Longitude 33.85°E to 42.00°E`).
- **Kenya 47 Constitutional Counties & National Boundary (`KenyaGeospatialService`)**:
  - **Source**: Independent Electoral and Boundaries Commission (IEBC) / Kenya National Bureau of Statistics (KNBS) / Humanitarian Data Exchange (`HDX COD-AB-KEN` Administrative Boundaries).
  - **License**: Creative Commons Attribution for Intergovernmental Organizations (`CC BY-IGO 3.0`) / Open Government Data Kenya.
  - **Update Date**: 2024-10 (aligned with the 47 Counties under the First Schedule of the Constitution of Kenya, County Codes `001` Mombasa through `047` Nairobi).
- **Major Towns, Estates & National Trunk Highway Corridors (`A104-A109`, `A2`, `B1`, `B8`)**:
  - **Source**: Kenya National Highways Authority (KeNHA) / Survey of Kenya Gazetteer & OpenStreetMap Kenya contributors (`ODbL 1.0`).
- **Map Tiles & Geocoding Service**:
  - **Default Basemap Tiles**: OpenStreetMap (`ODbL 1.0`), CARTO Basemaps (`CC BY 4.0`), and OpenTopoMap (`CC-BY-SA 3.0`).
  - **Geocoding Proxy**: Server-side cached and rate-limited proxy (`GET /gis/search`) querying the local Kenya 47-County Gazetteer first and falling back to Nominatim (`countrycodes=ke`) with mandatory `User-Agent`, 72-hour response caching, and rate limiting (`30 req/min`) to prevent scraping or overloading public servers. For high-volume commercial production deployments, configure a commercial tile/geocoding endpoint (MapTiler, Stadia Maps, or self-hosted Nominatim/TileServer-GL) via `.env`.

### 7.2 Environment Configuration (`.env`)
No private API keys are ever hardcoded in frontend code. Configure tile and geocoding providers in `.env`:
```dotenv
GIS_MAP_DEFAULT_LAT=0.0236
GIS_MAP_DEFAULT_LNG=37.9062
GIS_MAP_DEFAULT_ZOOM=6
GIS_MAP_MIN_ZOOM=5
GIS_MAP_MAX_ZOOM=19
GIS_MAP_TILE_STREET_URL="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
GIS_MAP_TILE_DARK_URL="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
GIS_MAP_TILE_TOPO_URL="https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png"
GIS_MAP_TILE_ATTRIBUTION="&copy; OpenStreetMap contributors &copy; CARTO"
GIS_GEOCODER_URL="https://nominatim.openstreetmap.org/search"
GIS_GEOCODER_COUNTRY="ke"
GIS_GEOCODER_USER_AGENT="SmartohISPBillingGIS/2.0 (noc@smartoh.co.ke)"
GIS_GEOCODER_CACHE_HOURS=72
GIS_GEOCODER_RATE_LIMIT=30
```

### 7.3 Multitenant GIS Authorization & Audited Support Workflow
- **ISP Tenant Isolation**: Each ISP (`isp_admin`, `technician`, `billing_officer`) can only view, search, filter, or manage its own private network infrastructure (`FiberInfrastructure`, `Site`, `MikroTikDevice`, `OLTDevice`, `Client` drops, and `InstallationJob` work orders).
- **Role-Based Access Control (RBAC)**:
  - `isp_admin`: Create, edit, move, delete, and export GeoJSON (`GET /gis/export`).
  - `technician`: Create, edit, and move assets and complete field work orders.
  - `billing_officer`: Read-only access to the tenant's GIS map.
  - `client`: Forbidden (`403`) from internal ISP infrastructure maps.
- **Super Admin Audited Support Workflow**: Super Admin defaults to a **Platform Overview** of Kenya and can only inspect a specific tenant's private GIS assets after submitting an explicit support ticket/reason via `POST /gis/support-access` (logged to `audit_logs` as `gis.support_access.granted` and revocable via `POST /gis/support-access/revoke`).
