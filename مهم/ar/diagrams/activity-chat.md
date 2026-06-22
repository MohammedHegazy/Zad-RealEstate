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
    S2 --> S3{مصادق؟}
    S3 -->|لا| S401[401]
    S3 -->|نعم| S4[StoreMessageRequest]
    S4 --> S5{receiver_id موجود + text ≤ 5000؟}
    S5 -->|لا| S422[422]
    S5 -->|نعم| S6[MessageController::store]
    S6 --> S7["messages::create(sender, receiver, text, is_read=false)"]
    S7 --> S8["Notifications::create — معاينة عربية للمستقبل"]
    S8 --> S201[201 — Message sent]

    %% ── صندوق الوارد ──
    Entry -->|قائمة الرسائل| I1[GET /messages]
    I1 --> I2[auth:sanctum]
    I2 --> I3[MessageController::index]
    I3 --> I4{فلاتر: is_read, role=sent/inbox}
    I4 --> I5[WHERE sender OR receiver = user]
    I5 --> I200[200 — paginated list]

    %% ── محادثة ──
    Entry -->|فتح محادثة| C1[GET /messages/conversation/{user}]
    C1 --> C2[auth:sanctum]
    C2 --> C3[MessageController::conversation]
    C3 --> C4[SELECT رسائل بين الزوج — oldest]
    C4 --> C5[UPDATE is_read=true للواردة غير المقروءة]
    C5 --> C200[200 — thread]

    %% ── عرض رسالة ──
    Entry -->|عرض رسالة واحدة| V1[GET /messages/{id}]
    V1 --> V2{userCanAccess — مرسل أو مستقبل؟}
    V2 -->|لا| V404[404]
    V2 -->|نعم| V3{المستقبل يفتح unread؟}
    V3 -->|نعم| V4[UPDATE is_read=true]
    V3 -->|لا| V5[بدون تحديث]
    V4 --> V200[200]
    V5 --> V200

    %% ── تحديد كمقروء ──
    Entry -->|PATCH read| R1[PATCH /messages/{id}/read]
    R1 --> R2{المستقبل فقط؟}
    R2 -->|لا| R403[403]
    R2 -->|نعم| R3[UPDATE is_read=true]
    R3 --> R200[200]

    %% ── حذف ──
    Entry -->|حذف| D1[DELETE /messages/{id}]
    D1 --> D2{userCanAccess؟}
    D2 -->|لا| D404[404]
    D2 -->|نعم| D3[message->delete]
    D3 --> D204[204]

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
    AStart([Admin — /api/v1/admin/messages]) --> A1[auth:sanctum + admin]
    A1 --> A2{نوع الطلب؟}
    A2 -->|GET /| A3[index — كل الرسائل]
    A2 -->|GET /conversation| A4[conversation — بين مستخدمين]
    A2 -->|GET /{id}| A5[show]
    A2 -->|DELETE /{id}| A6[destroy]
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
