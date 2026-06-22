# Use Case Diagrams

هذه هي النسخة المبسطة والمعتمدة لمخططات حالات الاستخدام في المشروع.

القاعدة المستخدمة هنا: نعرض ما يستطيع المستخدم فعله داخل النظام، وليس تفاصيل الجداول أو المتحكمات أو أسماء المسارات.

## الملفات

| الملف | المحتوى |
|------|---------|
| `usecase-overview.md` | النظرة العامة لكل الأدوار |
| `usecase-visitor.md` | الزائر |
| `usecase-buyer.md` | المشتري / المستثمر |
| `usecase-owner.md` | مالك العقار |
| `usecase-agent.md` | الوسيط العقاري |
| `usecase-company.md` | الشركة العقارية |
| `usecase-admin.md` | مدير النظام |

## مصدر التحليل

- واجهة Vue: `project-RealEstate/src/router/index.js`
- مسارات API العامة والمحمية: `project-RealEstate_database/routes/api`
- أدوار النظام: `project-RealEstate_database/config/realestate.php`
- المتحكمات: `project-RealEstate_database/app/Http/Controllers/Api`
