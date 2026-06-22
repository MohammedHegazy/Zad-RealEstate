# مخطط النشاط — نظام المحادثة / الرسائل (Chat / Messaging)

> **النطاق:** رسائل مباشرة بين مستخدمين، محادثات ثنائية، إشعارات  
> **التقنية:** REST API — **لا WebSockets** ولا LLM  
> **الملفات الرئيسية:** `MessageController`, `StoreMessageRequest`, Model `messages`, `Notifications`

---

## 1. مخطط النشاط الشامل

```mermaid
flowchart TD
    Start([عملية رسائل]) --> Entry{نوع العملية؟}

    %% ── إرسال ──
    Entry -->|إرسال رسالة| S1[POST /messages]
    S1 --> S2[auth:sanctum]
    S2 --> S3[401]
    S3 -->|لا| S401[StoreMessageRequest]
    S3 -->|نعم| S4[422]
    S4 --> S5[MessageController::store]
    S5 -->|لا| S422[201 — Message sent]
    S5 -->|نعم| S6[GET /messages]
    S6 --> S7[auth:sanctum]
    S7 --> S8[MessageController::index]
    S8 --> S201[WHERE sender OR receiver = user]

    %% ── صندوق الوارد ──
    Entry -->|قائمة الرسائل| I1[200 — paginated list]
    I1 --> I2["GET /messages/conversation/{user}"]
    I2 --> I3[auth:sanctum]
    I3 --> I4[MessageController::conversation]
    I4 --> I5[SELECT رسائل بين الزوج — oldest]
    I5 --> I200[UPDATE is_read=true للواردة غير المقروءة]

    %% ── محادثة ──
    Entry -->|فتح محادثة| C1[200 — thread]
    C1 --> C2["GET /messages/{id}"]
    C2 --> C3[404]
    C3 --> C4[UPDATE is_read=true]
    C4 --> C5[بدون تحديث]
    C5 --> C200[200]

    %% ── عرض رسالة ──
    Entry -->|عرض رسالة واحدة| V1["PATCH /messages/{id}/read"]
    V1 --> V2[403]
    V2 -->|لا| V404[UPDATE is_read=true]
    V2 -->|نعم| V3[200]
    V3 -->|نعم| V4["DELETE /messages/{id}"]
    V3 -->|لا| V5[404]
    V4 --> V200[message->delete]
    V5 --> V200[204]

    %% ── تحديد كمقروء ──
    Entry -->|PATCH read| R1{نوع العملية؟}
    R1 --> R2{مصادق؟}
    R2 -->|لا| R403{receiver_id موجود + text ≤ 5000؟}
    R2 -->|نعم| R3{userCanAccess — مرسل أو مستقبل؟}
    R3 --> R200{المستقبل يفتح unread؟}

    %% ── حذف ──
    Entry -->|حذف| D1{المستقبل فقط؟}
    D1 --> D2{userCanAccess؟}
    D2 -->|لا| D404[s48]
    D2 -->|نعم| D3[s49]
    D3 --> D204[s50]

    style S401 fill:#fee
    style S422 fill:#fee
    style V404 fill:#fee
    style R403 fill:#fee
    style D404 fill:#fee
```

---

## 2. مخطط نشاط — لوحة المدير (قراءة/حذف فقط)

```mermaid
flowchart TD
    AStart(["Admin — /api/v1/admin/messages"]) --> A1["auth:sanctum + admin"]
    A1 --> A2{"نوع الطلب؟"}
    A2 -->|"GET /"| A3["index — كل الرسائل"]
    A2 -->|"GET /conversation"| A4["conversation — بين مستخدمين"]
    A2 -->|"GET /{id}"| A5[show]
    A2 -->|"DELETE /{id}"| A6[destroy]
    A3 --> A200[200]
    A4 --> A200
    A5 --> A200
    A6 --> A204[204]
```

> **ملاحظة:** المدير **لا يرسل** رسائل عبر API الإدارة — إرسال فقط عبر `MessageController::store` للمستخدمين.

---

## 3. ما لا يفعله النظام

| الميزة | الحالة |
|--------|--------|
| WebSockets / Pusher | ❌ غير موجود |
| Real-time push | ❌ polling REST فقط |
| إشعار بريد/SMS | ❌ إشعار داخلي `Notifications` فقط |
| محادثة جماعية | ❌ ثنائية فقط |
| ذكاء اصطناعي / chatbot | ❌ غير موجود |
| واجهة Vue للرسائل | ❌ API جاهز — غير موصول بالواجهة |

---

## 4. الملفات والمسارات

| العملية | API | المتحكم |
|---------|-----|---------|
| قائمة | `GET /api/v1/messages` | `MessageController::index` |
| محادثة | `GET /api/v1/messages/conversation/{user}` | `conversation` |
| إرسال | `POST /api/v1/messages` | `store` |
| عرض | `GET /api/v1/messages/{message}` | `show` |
| مقروء | `PATCH /api/v1/messages/{message}/read` | `markAsRead` |
| حذف | `DELETE /api/v1/messages/{message}` | `destroy` |

**التعريف:** `routes/api/v1/authenticated/messages.php`

---

## 5. جدول `messages`

| العمود | الغرض |
|--------|-------|
| `sender_id` | المرسل |
| `receiver_id` | المستقبل |
| `text` | نص الرسالة (max 5000) |
| `is_read` | حالة القراءة |
