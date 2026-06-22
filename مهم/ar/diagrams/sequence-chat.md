# مخطط التسلسل — نظام المحادثة / الرسائل (Chat / Messaging)

> **النطاق:** إرسال رسالة، فتح محادثة، إشعار المستقبل  
> **الملفات:** `MessageController`, `StoreMessageRequest`, `messages`, `Notifications`  
> **ملاحظة:** REST فقط — لا WebSockets

---

## 1. تسلسل — إرسال رسالة + إشعار

```mermaid
sequenceDiagram
    autonumber
    actor Sender as المرسل
    actor Receiver as المستقبل
    participant API as POST /api/v1/messages
    participant Sanctum as auth:sanctum
    participant Req as StoreMessageRequest
    participant Ctrl as MessageController
    participant Msg as messages
    participant Notif as Notifications
    participant DB as قاعدة البيانات

    Sender->>API: POST { receiver_id, text }
    API->>Sanctum: التحقق من Bearer token
    alt غير مصادق
        Sanctum-->>Sender: 401
    end

    Sanctum->>Req: validate()
    Note over Req: receiver_id exists, text max 5000, different sender

    alt فشل التحقق
        Req-->>Sender: 422
    end

    Req->>Ctrl: store(request)
    Ctrl->>Msg: create(sender_id, receiver_id, text, is_read=false)
    Msg->>DB: INSERT messages

    Ctrl->>Notif: create(user_id=receiver, content=معاينة عربية)
    Notif->>DB: INSERT notifications
    Note over Notif: متزامن — ليس Queue

    Ctrl-->>Sender: 201 Message sent + sender/receiver loaded
```

---

## 2. تسلسل — فتح محادثة (وتحديد كمقروء)

```mermaid
sequenceDiagram
    autonumber
    actor Receiver as المستقبل
    participant API as GET /messages/conversation/{user}
    participant Sanctum as auth:sanctum
    participant Ctrl as MessageController
    participant DB as قاعدة البيانات

    Receiver->>API: GET conversation/{sender_id}
    API->>Sanctum: auth:sanctum
    Sanctum->>Ctrl: conversation(request, user)

    Ctrl->>DB: SELECT messages WHERE (A→B OR B→A) ORDER BY oldest
    DB-->>Ctrl: paginated thread

    Ctrl->>DB: UPDATE is_read=true WHERE sender=other AND receiver=me AND unread
    Note over Ctrl: تحديد كل الواردة كمقروءة دفعة واحدة

    Ctrl-->>Receiver: 200 conversation thread + pagination meta
```

---

## 3. تسلسل — صندوق الوارد / المرسل

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant API as GET /api/v1/messages
    participant Ctrl as MessageController
    participant DB as قاعدة البيانات

    User->>API: GET /messages?role=inbox&is_read=false
    API->>Ctrl: index(request)

    Ctrl->>DB: WHERE sender_id=user OR receiver_id=user
    Note over Ctrl: optional: role=sent/inbox, is_read filter

    DB-->>Ctrl: paginated messages + sender/receiver
    Ctrl-->>User: 200 Messages retrieved
```

---

## 4. تسلسل — عرض رسالة واحدة

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant API as GET /messages/{id}
    participant Ctrl as MessageController
    participant DB as messages

    User->>API: GET /messages/{message}
    API->>Ctrl: show(request, message)

    Ctrl->>Ctrl: userCanAccess(sender OR receiver)?
    alt لا صلاحية
        Ctrl-->>User: 404 Message not found
    end

    alt المستقبل يفتح unread
        Ctrl->>DB: UPDATE is_read=true
    end

    Ctrl-->>User: 200 Message retrieved
```

---

## 5. تسلسل — تحديد كمقروء يدوياً

```mermaid
sequenceDiagram
    autonumber
    actor Receiver as المستقبل
    participant API as PATCH /messages/{id}/read
    participant Ctrl as MessageController

    Receiver->>API: PATCH read
    API->>Ctrl: markAsRead(request, message)

    alt ليس المستقبل
        Ctrl-->>Receiver: 403 Only receiver can mark read
    end

    Ctrl->>Ctrl: message.update(is_read=true)
    Ctrl-->>Receiver: 200 Message marked as read
```

---

## 6. تسلسل — حذف رسالة

```mermaid
sequenceDiagram
    autonumber
    actor User as المرسل أو المستقبل
    participant API as DELETE /messages/{id}
    participant Ctrl as MessageController
    participant DB as messages

    User->>API: DELETE /messages/{message}
    API->>Ctrl: destroy(request, message)

    Ctrl->>Ctrl: userCanAccess?
    alt لا صلاحية
        Ctrl-->>User: 404
    end

    Ctrl->>DB: message.delete()
    Ctrl-->>User: 204 Deleted
```

---

## 7. تسلسل — المدير (مراقبة)

```mermaid
sequenceDiagram
    autonumber
    actor Admin as المدير
    participant API as GET /api/v1/admin/messages
    participant Ctrl as Admin\MessageController
    participant DB as messages

    Admin->>API: GET /admin/messages
    Note over API: auth:sanctum + admin middleware

    API->>Ctrl: index()
    Ctrl->>DB: SELECT all messages (paginated)
    DB-->>Ctrl: rows
    Ctrl-->>Admin: 200

    Note over Admin,DB: Admin: index, conversation, show, destroy — بدون store
```

---

## 8. ما لا يحدث في هذا النظام

| الميزة | الحالة |
|--------|--------|
| WebSocket push | ❌ |
| Laravel Notification channels (mail/SMS) | ❌ |
| Chatbot / AI | ❌ |
| Vue UI للرسائل | ❌ API جاهز فقط |

---

## 9. الملفات والمسارات

| التسلسل | API | المتحكم |
|---------|-----|---------|
| إرسال | `POST /api/v1/messages` | `MessageController::store` |
| محادثة | `GET /api/v1/messages/conversation/{user}` | `conversation` |
| قائمة | `GET /api/v1/messages` | `index` |
| عرض | `GET /api/v1/messages/{message}` | `show` |
| مقروء | `PATCH /api/v1/messages/{message}/read` | `markAsRead` |
| حذف | `DELETE /api/v1/messages/{message}` | `destroy` |

**التعريف:** `routes/api/v1/authenticated/messages.php`
