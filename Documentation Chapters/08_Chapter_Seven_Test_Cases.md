# الفصــل السابع

# حالات الاختبار

## 7.1. مقدمة

تعد الاختبارات جزءاً أساسياً من التحقق من جودة مشروع **زاد للعقارات**، لأن النظام يحتوي على عدة وحدات مترابطة: الإدارة، العقارات، الشركات، الوسطاء، المواقع الجغرافية، المفضلة، التوصيات، الاستثمار، المحافظ، الإشعارات، الروابط الاجتماعية، الثقة، والتوثيق. وجود هذه الوحدات يجعل الاختبار ضرورياً للتأكد من أن كل وظيفة تعمل بشكل صحيح، وأن الصلاحيات والعلاقات والقيود لا تسمح بسلوك غير مرغوب.

تعتمد الاختبارات في المشروع على PHPUnit داخل Laravel، وتوجد في مجلد `project-RealEstate_database/tests`. تنقسم الاختبارات إلى:

- **Feature Tests:** تختبر المسارات والسلوك الكامل للطلبات والاستجابات وقاعدة البيانات.
- **Unit Tests:** تختبر خدمات محددة أو منطق حسابي بمعزل عن المسار الكامل.

يمكن تشغيل الاختبارات من مجلد الخلفية بالأمر:

```bash
php artisan test
```

أو عبر سكربت Composer:

```bash
composer test
```

## 7.2. الاختبارات والتحقق

## 7.2.1. Admin Can Fetch Dashboard Statistics: Valid

### Main Success Scenario

يدخل مدير النظام إلى لوحة الإدارة، ثم تطلب الواجهة إحصاءات عامة من المسار الإداري. يتحقق النظام من أن المستخدم مصادق وأنه يملك صلاحية admin، ثم يعيد إحصاءات مثل عدد المستخدمين، العقارات، الشركات، الوسطاء، أو غيرها من مؤشرات لوحة التحكم.

### Extensions

- إذا كان المستخدم غير مسجل، يجب أن يعاد خطأ مصادقة.
- إذا كان المستخدم مسجلاً لكنه ليس مديراً، يجب أن يمنع النظام الوصول.
- إذا لم توجد بيانات كافية، يجب أن تعاد الإحصاءات بقيم صفرية أو مناسبة دون فشل.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminDashboardStatisticsTest.php`

الاختبارات المرتبطة:

- `test_admin_can_fetch_dashboard_statistics`
- `test_non_admin_cannot_fetch_dashboard_statistics`

## 7.2.2. Admin User Management: Valid

### Main Success Scenario

يدخل المدير إلى صفحة المستخدمين في لوحة الإدارة. يستطيع عرض المستخدمين مع الفلاتر، فتح تفاصيل مستخدم، تعديل حالة المستخدم أو حالة التحقق، وحذف مستخدم غير إداري عند الحاجة. يجب أن تعكس قاعدة البيانات التعديلات التي أجراها المدير.

### Extensions

- لا يجب أن يستطيع المدير حذف حسابه الخاص.
- يجب أن تمنع المسارات الإدارية المستخدم غير المدير.
- يجب أن تعمل الفلاتر دون تعطيل قائمة المستخدمين.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminUserManagementTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_users_with_filters`
- `test_admin_can_view_user_details`
- `test_admin_can_update_user_status_and_verification`
- `test_admin_cannot_delete_own_account`
- `test_admin_can_delete_non_admin_user`

## 7.2.3. Admin Estate Management: Valid

### Main Success Scenario

يدخل المدير إلى إدارة العقارات، ويستطيع عرض العقارات مع الفلاتر، فتح تفاصيل عقار، إنشاء عقار لمالك، رفع صور وفيديوهات، تحديث بيانات العقار، تغيير الحالة، أو حذف العقار. عند رفع صورة يجب أن تعود الاستجابة برابط يمكن استخدامه في الواجهة.

### Extensions

- إذا كانت بيانات العقار غير صحيحة، يجب أن يعيد النظام أخطاء تحقق.
- تغيير حالة العقار يجب أن يقبل الحالات المحددة فقط.
- حذف العقار يجب أن يحذف العلاقات التابعة حسب قواعد قاعدة البيانات.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminEstateManagementTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_estates_with_filters`
- `test_admin_can_view_estate_details`
- `test_admin_can_create_estate_for_owner`
- `test_admin_can_create_estate_with_images_and_videos`
- `test_admin_can_upload_estate_image_with_url_in_response`
- `test_admin_can_update_estate_fields_and_status`
- `test_admin_can_update_estate_status_via_patch`
- `test_admin_can_delete_estate`

## 7.2.4. Admin Company Management: Valid

### Main Success Scenario

يعرض المدير قائمة الشركات، يفتح تفاصيل شركة، ينشئ شركة لمستخدم، يحدث بياناتها، أو يغير حالتها عبر PATCH. عند نجاح التحديث يجب أن تظهر البيانات الجديدة في قاعدة البيانات والاستجابة.

### Extensions

- لا يجب حذف شركة لديها وسطاء تابعون إذا كان ذلك يكسر العلاقات.
- يستطيع المدير حذف شركة لا تملك وسطاء.
- يجب قبول حالات الشركة المسموحة فقط مثل approved أو rejected أو suspended.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminCompanyManagementTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_companies_with_filters`
- `test_admin_can_view_company_details`
- `test_admin_can_create_company_for_owner`
- `test_admin_can_update_company_fields`
- `test_admin_can_update_company_status_via_patch`
- `test_admin_cannot_delete_company_with_agents`
- `test_admin_can_delete_company_without_agents`

## 7.2.5. Admin Agent Management: Valid

### Main Success Scenario

يدخل المدير إلى إدارة الوسطاء، فيستطيع عرض الوسطاء مع الفلاتر، فتح تفاصيل وسيط، إنشاء وسيط وربطه بمستخدم وشركة، تحديث الشركة أو درجة الثقة، وحذف الوسيط. يجب أن يحافظ النظام على العلاقة بين الوسيط والمستخدم والشركة.

### Extensions

- يجب منع إنشاء وسيط ببيانات مستخدم أو شركة غير صحيحة.
- عند حذف الوسيط يجب أن تزال علاقاته التابعة حسب القيود.
- يجب ألا يتم تكرار ملف وسيط لنفس المستخدم.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminAgentManagementTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_agents_with_filters`
- `test_admin_can_view_agent_details`
- `test_admin_can_create_agent_for_user_and_company`
- `test_admin_can_update_agent_company_and_trust_score`
- `test_admin_can_delete_agent`

## 7.2.6. Admin Location Management: Valid

### Main Success Scenario

يدير المدير المدن والمناطق من لوحة الإدارة. يستطيع البحث في المدن، إنشاء مدينة، تحديثها، رفع صورة لها، عرض المناطق حسب المدينة، إنشاء منطقة، تحديثها، أو حذف منطقة لا ترتبط بعقارات أو شركات.

### Extensions

- لا يجب حذف مدينة تحتوي على مناطق.
- لا يجب حذف منطقة مرتبطة بعقارات أو شركات.
- يجب أن يعمل فلتر المدينة عند عرض المناطق.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminLocationManagementTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_cities_with_search`
- `test_admin_can_create_and_update_city`
- `test_admin_can_update_city_image_via_post`
- `test_admin_cannot_delete_city_with_places`
- `test_admin_can_list_places_with_city_filter`
- `test_admin_can_create_and_update_place`
- `test_admin_cannot_delete_place_with_estates_or_companies`
- `test_admin_can_delete_place_without_dependencies`

## 7.2.7. Company Status Flow: Valid

### Main Success Scenario

ينشئ المستخدم ملف شركة، فيبدأ الملف بحالة `pending`. لا تظهر الشركة في الدليل العام إلا إذا كانت معتمدة. يستطيع صاحب الشركة رؤية ملفه حتى لو كان قيد المراجعة، بينما لا يستطيع مستخدم آخر الوصول إلى ملف شركة غير معتمد.

### Extensions

- لا يستطيع المستخدم إنشاء ملف شركة ثانٍ لنفس الحساب.
- يجب رفض أيام العمل غير الصحيحة.
- يمكن تحويل صيغة أيام العمل القديمة إلى صيغة منظمة عند الإنشاء.
- يستطيع المدير تغيير حالة الشركة.

### Test Case

ملف الاختبار: `tests/Feature/Companies/CompanyStatusTest.php`

الاختبارات المرتبطة:

- `test_user_created_company_starts_as_pending`
- `test_public_directory_lists_only_approved_companies`
- `test_public_show_hides_non_approved_company`
- `test_owner_can_view_own_pending_company_profile`
- `test_user_without_company_gets_not_found_on_my_company`
- `test_user_cannot_create_second_company_profile`
- `test_admin_can_update_company_status`
- `test_legacy_work_days_string_is_normalized_on_create`
- `test_invalid_work_day_is_rejected`

## 7.2.8. Geo Search: Valid

### Main Success Scenario

يرسل المستخدم إحداثياته أو يفتح الخريطة، فيعيد النظام العقارات القريبة مرتبة حسب المسافة، أو يعيد العقارات داخل نصف قطر محدد. كما يعيد مسار الخريطة علامات العقارات وإعدادات مزود الخريطة مثل Leaflet وOpenStreetMap.

### Extensions

- إذا لم يرسل المستخدم الإحداثيات المطلوبة، يجب أن يعيد النظام خطأ تحقق.
- يجب أن يعمل فلتر الصندوق الجغرافي bounding box في الخريطة.
- يجب أن تكون المسافة محسوبة بشكل صحيح في خدمة البحث الجغرافي.

### Test Case

ملفات الاختبار:

- `tests/Feature/Geo/EstateGeoTest.php`
- `tests/Unit/Services/GeoSearchServiceTest.php`

الاختبارات المرتبطة:

- `test_nearby_returns_estates_ordered_by_distance`
- `test_in_radius_filters_by_distance_and_paginates`
- `test_map_returns_markers_and_provider_config`
- `test_map_filters_by_bounding_box`
- `test_nearby_requires_coordinates`
- `test_calculate_distance_km`
- `test_map_providers_include_leaflet_osm_and_google`

## 7.2.9. Recommendations After Saving Preferences: Valid

### Main Success Scenario

يحفظ المستخدم تفضيلاته مثل المدينة أو الميزانية أو نوع العقار، ثم يطلب قائمة التوصيات. يجمع النظام التفضيلات والعقارات المرشحة، يحسب درجة الملاءمة، ثم يعيد توصيات للمستخدم.

### Extensions

- لا يستطيع الزائر الوصول إلى التوصيات.
- يجب أن تعود التوصيات الأعلى بعدد محدود.
- يجب أن يعمل مسار العقارات المشابهة.
- يمكن أن تعتمد التوصيات على العقارات المفضلة أيضاً.

### Test Case

ملفات الاختبار:

- `tests/Feature/Recommendations/RecommendationTest.php`
- `tests/Feature/Recommendations/UserPreferenceTest.php`
- `tests/Unit/Services/RecommendationScoringServiceTest.php`

الاختبارات المرتبطة:

- `test_user_gets_recommendations_after_saving_preferences`
- `test_top_recommendations_returns_limited_results`
- `test_similar_estates_endpoint`
- `test_guest_cannot_access_recommendations`
- `test_recommendations_based_on_favorite_estates`
- `test_user_can_save_preferences_with_preferred_city_id`
- `test_guest_cannot_save_preferences`
- `test_scores_estate_within_budget_and_location`
- `test_score_is_capped_at_100`
- `test_similarity_score_favors_same_area_and_type`

## 7.2.10. Investment Analysis: Valid

### Main Success Scenario

يسجل المستخدم الدخول، ثم ينشئ تحليلاً استثمارياً لعقار. يتحقق النظام من المدخلات، يحسب المؤشرات مثل ROI وفترة الاسترداد، ويحفظ التحليل. يستطيع المستخدم بعد ذلك عرض تحليلاته وفتح التحليل الخاص به.

### Extensions

- لا يستطيع الزائر الوصول إلى التحليلات الاستثمارية.
- لا يستطيع المستخدم عرض تحليل مستخدم آخر.
- إذا كانت البيانات المالية غير كافية، يجب أن يتعامل النظام مع القيم الناقصة دون كسر الطلب.

### Test Case

ملف الاختبار: `tests/Feature/Investment/InvestmentAnalysisTest.php`

الاختبارات المرتبطة:

- `test_guest_cannot_access_investment_analyses`
- `test_user_can_create_and_list_analyses`
- `test_user_can_show_own_analysis`
- `test_user_cannot_view_other_users_analysis`

## 7.2.11. Investment Portfolio: Valid

### Main Success Scenario

ينشئ المستخدم محفظة استثمارية، ثم يضيف عقاراً إليها. يجب أن يحفظ النظام المحفظة والعقار المرتبط بها، ويعرض عقارات المحفظة، ويسمح بإزالة العقار. كما يجب أن يعرض تفاصيل المحفظة مع إجمالي الاستثمار.

### Extensions

- يجب منع إضافة عقار غير صالح أو غير منشور حسب قواعد الخدمة.
- يجب منع تكرار نفس العقار داخل نفس المحفظة.
- يجب تحديث ملخص المحفظة عند الإضافة أو الحذف.

### Test Case

ملفات الاختبار:

- `tests/Feature/Investment/InvestmentPortfolioTest.php`
- `tests/Unit/Services/Portfolio/PortfolioServiceTest.php`

الاختبارات المرتبطة:

- `test_user_can_create_and_list_portfolios`
- `test_user_can_add_and_list_portfolio_properties`
- `test_user_can_remove_property_from_portfolio`
- `test_portfolio_show_includes_total_invested`

## 7.2.12. Investor Dashboard: Valid

### Main Success Scenario

يدخل المستخدم إلى لوحة المستثمر، فيجمع النظام عقارات المحافظ ويحسب المؤشرات مثل إجمالي الاستثمارات ومتوسط العائد وأفضل وأسوأ عقار. تعاد النتائج في استجابة يمكن للواجهة عرضها في لوحة ملخص.

### Extensions

- لا يستطيع الزائر الوصول إلى لوحة المستثمر.
- إذا لم يملك المستخدم محافظ أو عقارات، يجب أن تعاد قيم مناسبة دون فشل.
- يجب أن يكون حساب المؤشرات متوافقاً مع خدمة الاستثمار.

### Test Case

ملفات الاختبار:

- `tests/Feature/Investment/InvestorDashboardTest.php`
- `tests/Unit/Services/Investment/InvestorDashboardServiceTest.php`
- `tests/Unit/Services/Investment/InvestmentCalculatorServiceTest.php`

الاختبارات المرتبطة:

- `test_guest_cannot_access_dashboard`
- `test_user_can_view_investor_dashboard`

## 7.2.13. Notifications Display and Mark as Read: Valid

### Main Success Scenario

يفتح المستخدم قائمة الإشعارات الخاصة به. يعيد النظام الإشعارات المرتبطة بالمستخدم فقط، مع إمكانية الفلترة حسب حالة القراءة. عند فتح إشعار غير مقروء أو تحديده كمقروء، يتم تحديث حالته. كما يمكن تحديد كل الإشعارات كمقروءة ومعرفة عدد غير المقروء.

### Extensions

- لا يستطيع المستخدم الوصول إلى إشعار مستخدم آخر.
- يستطيع المستخدم حذف إشعاره.
- يستطيع المدير إرسال إشعار إلى مستخدم.
- يجب حذف الإشعارات عند حذف المستخدم حسب العلاقة.

### Test Case

ملف الاختبار: `tests/Feature/Notifications/NotificationTest.php`

الاختبارات المرتبطة:

- `test_user_can_list_own_notifications`
- `test_user_can_filter_notifications_by_read_status`
- `test_user_cannot_access_another_users_notification`
- `test_show_marks_unread_notification_as_read`
- `test_user_can_mark_notification_as_read`
- `test_user_can_mark_all_notifications_as_read`
- `test_unread_count_endpoint`
- `test_user_can_delete_own_notification`
- `test_admin_can_send_notification_to_user`
- `test_notification_service_send_to_many`
- `test_notifications_are_deleted_when_user_is_deleted`

## 7.2.14. Social Links: Valid

### Main Success Scenario

يدير المستخدم أو الشركة أو المدير الروابط الاجتماعية المرتبطة بحساب أو شركة أو عقار أو وسيط. يتحقق النظام من المنصة والرابط، ثم يحفظ السجل في جدول متعدد الأشكال. يجب أن يمنع النظام تكرار نفس المنصة لنفس الكيان، وأن يسمح بنفس المنصة لكيانات مختلفة.

### Extensions

- لا يستطيع الزائر إدارة روابط مستخدم.
- يجب رفض رابط غير صالح.
- يجب رفض منصة غير مسموحة.
- لا يستطيع مستخدم إدارة روابط شركة لا يملكها.
- يجب أن تحذف الروابط عند حذف الكيان المرتبط بها.

### Test Case

ملفات الاختبار:

- `tests/Feature/SocialLinks/UserSocialLinkTest.php`
- `tests/Feature/SocialLinks/CompanySocialLinkTest.php`
- `tests/Feature/SocialLinks/EstateSocialLinkTest.php`
- `tests/Feature/SocialLinks/AdminSocialLinkTest.php`
- `tests/Feature/SocialLinks/SocialLinkDatabaseIntegrityTest.php`
- `tests/Unit/SocialLinkServiceTest.php`

الاختبارات المرتبطة تشمل إدارة روابط المستخدم، الشركة، العقار، المدير، منع التكرار، التحقق من الروابط، والعلاقات polymorphic.

## 7.2.15. Property Review: Valid

### Main Success Scenario

يرسل المستخدم تقييماً لعقار، فيحفظه النظام مع حالة مراجعة. يستطيع المستخدم لاحقاً إدارة تقييمه حسب القيود. ولا تظهر في القائمة العامة إلا التقييمات المعتمدة.

### Extensions

- لا يستطيع المستخدم تقييم نفس العقار مرتين.
- يجب أن يحسب ملخص التقييم المتوسط من التقييمات المعتمدة فقط.
- التقييم غير المعتمد يجب ألا يظهر للزائر.

### Test Case

ملف الاختبار: `tests/Feature/Trust/PropertyReviewTest.php`

الاختبارات المرتبطة:

- `test_user_can_submit_property_review`
- `test_user_cannot_review_same_property_twice`
- `test_public_list_shows_only_approved_reviews`
- `test_average_rating_summary`

## 7.2.16. Agent Review and Company Review: Valid

### Main Success Scenario

يرسل المستخدم تقييماً لوسيط أو شركة. يحفظ النظام التقييم، ويستخدم التقييمات المعتمدة في حساب المتوسط أو ملخص التقييم. ترتبط التقييمات بالكيان الصحيح وتخضع لقواعد منع التكرار.

### Extensions

- لا يستطيع المستخدم تكرار تقييم نفس الوسيط أو الشركة إذا كان القيد يمنع ذلك.
- يجب استخدام التقييمات المعتمدة فقط في المتوسط.
- عند حذف المستخدم أو الوسيط يجب أن تتبع العلاقات قواعد الحذف.

### Test Case

ملفات الاختبار:

- `tests/Feature/Trust/AgentReviewTest.php`
- `tests/Feature/Trust/CompanyReviewTest.php`
- `tests/Feature/Agents/AgentReviewDatabaseIntegrityTest.php`

الاختبارات المرتبطة:

- `test_user_can_submit_agent_review`
- `test_agent_rating_summary`
- `test_user_cannot_submit_duplicate_agent_review`
- `test_user_can_submit_company_review`
- `test_company_average_rating`
- `test_average_rating_uses_only_approved_reviews`

## 7.2.17. Trust Moderation: Valid

### Main Success Scenario

يدخل المدير إلى صفحة الثقة، يعرض التقييمات أو طلبات التوثيق المعلقة، ثم يعتمد أو يرفض أو يحذف حسب الحالة. عند اعتماد تقييم وسيط أو طلب توثيق يجب أن يعاد حساب درجة الثقة عند الحاجة.

### Extensions

- لا يستطيع غير المدير مراجعة التقييمات.
- عند رفض طلب توثيق يجب حفظ حقول المراجعة أو الملاحظات.
- عند حذف المدير، يجب أن تبقى المراجع السابقة مرتبطة بطريقة لا تكسر قاعدة البيانات.

### Test Case

ملفات الاختبار:

- `tests/Feature/Trust/TrustModerationTest.php`
- `tests/Feature/Admin/AdminTrustManagementTest.php`
- `tests/Unit/Services/Trust/TrustScoreServiceTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_pending_reviews`
- `test_admin_can_approve_agent_review_and_update_trust_score`
- `test_admin_can_approve_verification_request`
- `test_admin_can_reject_verification_request_with_audit_fields`
- `test_non_admin_cannot_moderate_reviews`
- `test_admin_can_recalculate_agent_trust_score`

## 7.2.18. Verification Request: Valid

### Main Success Scenario

يرسل المستخدم طلب توثيق يتضمن نوع المستند والبيانات المطلوبة. يحفظ النظام الطلب ضمن طلبات المستخدم، ثم يستطيع المستخدم عرض طلباته. بعد ذلك يستطيع المدير مراجعة الطلب وقبوله أو رفضه.

### Extensions

- إذا كانت بيانات الطلب ناقصة أو المستند غير صحيح، يجب رفض الطلب.
- لا يستطيع المستخدم عرض طلبات توثيق مستخدم آخر.
- يستطيع المدير تحميل مستند التوثيق عند المراجعة.

### Test Case

ملفات الاختبار:

- `tests/Feature/Trust/VerificationRequestTest.php`
- `tests/Feature/Admin/AdminTrustManagementTest.php`

الاختبارات المرتبطة:

- `test_user_can_submit_verification_request`
- `test_user_can_list_own_verification_requests`
- `test_admin_can_list_verifications_with_status_filter`
- `test_admin_can_download_verification_document`

## 7.2.19. Favorite Agent Integrity: Valid

### Main Success Scenario

يحفظ المستخدم وسيطاً في المفضلة. يجب أن يمنع النظام تكرار نفس الوسيط لنفس المستخدم، لكنه يسمح لمستخدمين مختلفين بحفظ نفس الوسيط. كما يجب أن تحذف السجلات عند حذف المستخدم أو الوسيط.

### Extensions

- استخدام `firstOrCreate` يجب ألا ينشئ تكراراً.
- يجب أن يحافظ القيد الفريد على سلامة قاعدة البيانات حتى عند محاولات التكرار.

### Test Case

ملف الاختبار: `tests/Feature/Favorites/FavoriteAgentDatabaseIntegrityTest.php`

الاختبارات المرتبطة:

- `test_unique_user_agent_pair_constraint`
- `test_same_agent_can_be_favorited_by_different_users`
- `test_first_or_create_does_not_duplicate_favorite`
- `test_deleting_user_cascades_favorite_agents`
- `test_deleting_agent_cascades_favorite_agents`

## 7.2.20. Database Integrity for Agents and Companies: Valid

### Main Success Scenario

يتحقق النظام من العلاقات الأساسية بين المستخدم والشركة والوسيط. لا يجب أن يمتلك المستخدم أكثر من ملف شركة واحد أو ملف وسيط واحد، ويجب أن يؤدي حذف المستخدم أو الشركة إلى حذف السجلات التابعة حسب قواعد قاعدة البيانات.

### Extensions

- محاولة إنشاء ملف شركة أو وسيط مكرر لنفس المستخدم يجب أن تفشل.
- حذف الشركة يجب أن يحذف الوسطاء المرتبطين بها.
- علاقات Eloquent يجب أن تعيد السجلات الصحيحة.

### Test Case

ملفات الاختبار:

- `tests/Feature/Agents/AgentDatabaseIntegrityTest.php`
- `tests/Feature/Companies/CompanyDatabaseIntegrityTest.php`

الاختبارات المرتبطة:

- `test_unique_user_id_constraint_prevents_duplicate_agent_profiles`
- `test_user_has_one_agent_relationship`
- `test_deleting_user_cascades_agent_profile`
- `test_deleting_company_cascades_agents`
- `test_unique_user_id_constraint_prevents_duplicate_company_profiles`
- `test_user_has_one_company_relationship`
- `test_deleting_user_cascades_company_profile`

## 7.2.21. Admin Social Links Management: Valid

### Main Success Scenario

يدخل المدير إلى إدارة الروابط الاجتماعية، فيستطيع عرض جميع الروابط، فلترتها حسب نوع الكيان، فتح تفاصيل رابط محدد، تحديث الرابط، أو حذفه. يجب أن يتعامل النظام مع الروابط كعلاقة متعددة الأشكال، أي أن الرابط قد يعود إلى مستخدم أو شركة أو وسيط أو عقار.

### Extensions

- لا يستطيع الزائر الوصول إلى مسارات الروابط الاجتماعية الإدارية.
- لا يجب السماح بتكرار نفس المنصة لنفس الكيان.
- عند تحديث الرابط يجب التحقق من صحة الرابط والمنصة.
- يجب أن يستطيع المدير إدارة روابط شركة من المسارات الخاصة بالشركات أيضاً.

### Test Case

ملف الاختبار: `tests/Feature/SocialLinks/AdminSocialLinkTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_social_links`
- `test_admin_can_filter_by_socialable_type`
- `test_admin_can_show_social_link`
- `test_admin_can_update_social_link`
- `test_admin_can_delete_social_link`
- `test_admin_duplicate_platform_returns_error`
- `test_admin_company_social_links_routes`
- `test_guest_cannot_access_admin_social_links`

## 7.2.22. User Social Links Sync: Valid

### Main Success Scenario

يدير المستخدم روابط التواصل الخاصة به من مسار `my/social-media`. يستطيع المستخدم عرض روابطه، مزامنة روابط قديمة مثل facebook وinstagram، أو إرسال مصفوفة روابط كاملة. عند المزامنة يستبدل النظام الروابط القديمة بالروابط الجديدة وفق المنصات المرسلة.

### Extensions

- لا يستطيع الزائر الوصول إلى روابط مستخدم.
- يجب رفض المنصة غير المسموحة.
- يجب رفض الرابط غير الصحيح.
- يجب رفض تكرار المنصة داخل نفس الطلب.
- يستطيع الجمهور عرض رابط اجتماعي عام عبر مسار العرض.

### Test Case

ملف الاختبار: `tests/Feature/SocialLinks/UserSocialLinkTest.php`

الاختبارات المرتبطة:

- `test_guest_cannot_access_my_social_media`
- `test_user_can_list_own_social_links_via_legacy_endpoint`
- `test_user_can_sync_links_via_legacy_fields`
- `test_user_can_sync_links_array`
- `test_sync_replaces_existing_links`
- `test_invalid_platform_is_rejected`
- `test_invalid_url_is_rejected_in_links_array`
- `test_duplicate_platforms_in_single_request_are_rejected`
- `test_public_can_show_social_link`
- `test_legacy_social_media_show_route_works`
- `test_user_social_links_relationship_returns_owned_rows`

## 7.2.23. Company Social Links Ownership: Valid

### Main Success Scenario

يدير صاحب الشركة روابط التواصل الخاصة بشركته. يستطيع عرض الروابط، إنشاء رابط جديد، تحديث رابط موجود، أو حذف رابط. يجب أن يتأكد النظام أن المستخدم الحالي هو صاحب الشركة قبل السماح له بإدارة الروابط.

### Extensions

- لا يستطيع مستخدم آخر إدارة روابط شركة لا يملكها.
- يجب رفض الرابط المكرر لنفس المنصة في نفس الشركة.
- يجب رفض الرابط غير الصحيح عند الإنشاء.
- لا يستطيع المستخدم تحديث رابط يعود إلى شركة أخرى.
- يجب أن تعيد علاقة الشركة روابطها فقط.

### Test Case

ملف الاختبار: `tests/Feature/SocialLinks/CompanySocialLinkTest.php`

الاختبارات المرتبطة:

- `test_owner_can_list_company_social_links`
- `test_owner_can_create_company_social_link`
- `test_owner_can_update_company_social_link`
- `test_owner_can_delete_company_social_link`
- `test_non_owner_cannot_manage_company_social_links`
- `test_duplicate_platform_per_company_is_rejected`
- `test_invalid_url_on_create_is_rejected`
- `test_cannot_update_link_from_another_companys_profile`
- `test_company_social_links_relationship`

## 7.2.24. Estate Social Links Ownership: Valid

### Main Success Scenario

يدير مالك العقار روابط التواصل المرتبطة بعقاره. يرسل المستخدم الروابط إلى endpoint خاص بالعقار، فيتحقق النظام من ملكية العقار، ثم يحفظ الروابط في جدول `social_links` باستخدام العلاقة متعددة الأشكال.

### Extensions

- لا يستطيع غير مالك العقار تحديث روابطه.
- لا يستطيع الزائر تحديث روابط عقار.
- يجب أن تستخدم روابط العقار نفس backend المتعدد الأشكال.
- يجب أن تعيد علاقة العقار الروابط المرتبطة به فقط.

### Test Case

ملف الاختبار: `tests/Feature/SocialLinks/EstateSocialLinkTest.php`

الاختبارات المرتبطة:

- `test_owner_can_update_estate_social_links_via_legacy_endpoint`
- `test_non_owner_cannot_update_estate_social_links`
- `test_guest_cannot_update_estate_social_links`
- `test_estate_social_links_use_polymorphic_backend`
- `test_estate_social_links_relationship`

## 7.2.25. Social Link Database Integrity: Valid

### Main Success Scenario

يتحقق النظام من سلامة العلاقة متعددة الأشكال للروابط الاجتماعية. يجب أن يستطيع الرابط معرفة الكيان الأب، وأن يمنع تكرار نفس المنصة لنفس الكيان، وأن يسمح بنفس المنصة لكيانات مختلفة. كما يجب أن تحذف الروابط عند حذف الكيان المرتبط بها.

### Extensions

- حذف المستخدم يجب أن يحذف روابطه.
- حذف الشركة يجب أن يحذف روابطها.
- حذف العقار يجب أن يحذف روابطه.
- حذف الوسيط يجب أن يحذف روابطه.
- يجب عزل ملكية الروابط بين الكيانات المختلفة.

### Test Case

ملف الاختبار: `tests/Feature/SocialLinks/SocialLinkDatabaseIntegrityTest.php`

الاختبارات المرتبطة:

- `test_morph_to_resolves_parent_user`
- `test_unique_platform_per_entity_constraint`
- `test_deleting_user_cascades_social_links`
- `test_deleting_company_cascades_social_links`
- `test_deleting_estate_cascades_social_links`
- `test_deleting_agent_cascades_social_links`
- `test_same_platform_allowed_on_different_entities`
- `test_polymorphic_ownership_is_isolated_per_entity`

## 7.2.26. Social Link Service: Valid

### Main Success Scenario

تختبر الخدمة الداخلية `SocialLinkService` منطق مزامنة الروابط القديمة، منع التكرار، وتنسيق مجموعة الروابط. هذه الاختبارات مهمة لأن أكثر من متحكم يعتمد على نفس الخدمة، وأي خطأ فيها قد يؤثر على المستخدم والشركة والعقار والوسيط.

### Extensions

- عند إرسال حقول قديمة مثل facebook أو instagram يجب تحويلها إلى سجلات platform منظمة.
- عند وجود منصة مكررة يجب أن ترمي الخدمة استثناء مناسباً.
- عند تنسيق مجموعة روابط يجب أن تعاد مرتبة بطريقة ثابتة.

### Test Case

ملف الاختبار: `tests/Unit/SocialLinkServiceTest.php`

الاختبارات المرتبطة:

- `test_sync_legacy_fields_creates_platform_rows`
- `test_assert_platform_available_throws_when_duplicate`
- `test_format_collection_returns_sorted_links`

## 7.2.27. Recommendation Scoring Service: Valid

### Main Success Scenario

تختبر هذه الحالة منطق حساب درجة العقار داخل محرك التوصيات. عند وجود عقار ضمن ميزانية المستخدم وموقعه المفضل، يجب أن يحصل على درجة مرتفعة. كما يجب ألا تتجاوز الدرجة 100 حتى لو حصل العقار على نقاط إضافية من السلوك أو التشابه.

### Extensions

- يجب أن يفضل score التشابه في المنطقة والنوع عند حساب العقارات المشابهة.
- إذا كانت الميزانية أو الموقع غير مطابقين، يجب أن تنخفض الدرجة.
- يجب أن تبقى النتيجة قابلة للتفسير من خلال عوامل التقييم.

### Test Case

ملف الاختبار: `tests/Unit/Services/RecommendationScoringServiceTest.php`

الاختبارات المرتبطة:

- `test_scores_estate_within_budget_and_location`
- `test_score_is_capped_at_100`
- `test_similarity_score_favors_same_area_and_type`

## 7.2.28. Investment Calculator Service: Valid

### Main Success Scenario

تختبر هذه الحالة نواة حساب الاستثمار. تدخل الخدمة قيماً مثل الإيجار الشهري، نسبة الإشغال، المصاريف، الصيانة، الضرائب، وسعر الشراء. يجب أن تحسب الدخل السنوي المتوقع، ROI، وفترة الاسترداد بشكل صحيح ومنسجم مع الحسابات المستخدمة في العقارات والتحليلات ولوحة المستثمر.

### Extensions

- إذا كان الدخل المتوقع صفراً أو السعر غير صالح، يجب ألا يتم حساب ROI بطريقة خاطئة.
- يجب أن تتعامل الخدمة مع القيم الناقصة أو الافتراضية.
- يجب أن تكون النتائج قابلة لإعادة الاستخدام في أكثر من مسار.

### Test Case

ملف الاختبار: `tests/Unit/Services/Investment/InvestmentCalculatorServiceTest.php`

الهدف من هذا الاختبار هو ضمان أن كل مسارات الاستثمار تعتمد على نفس الحساب المركزي، بدلاً من وجود حسابات مختلفة بين العقار والتحليل والمحفظة.

## 7.2.29. Portfolio Service: Valid

### Main Success Scenario

تختبر هذه الحالة منطق إدارة المحافظ داخل `PortfolioService`. عند إضافة عقار إلى محفظة، يجب التأكد من أن العقار منشور، وأنه غير موجود مسبقاً داخل نفس المحفظة، ثم يتم إنشاء عنصر محفظة بحالة أولية مناسبة.

### Extensions

- إذا كان العقار غير منشور، يجب رفض إضافته.
- إذا كان العقار موجوداً مسبقاً، يجب منع التكرار.
- عند تحديث حالة عنصر المحفظة، يجب احترام الانتقالات المسموحة.
- يجب حفظ تواريخ الاستثمار أو البيع عند تغير الحالة.

### Test Case

ملف الاختبار: `tests/Unit/Services/Portfolio/PortfolioServiceTest.php`

يركز الاختبار على منطق الخدمة وليس فقط على استجابة HTTP، لأن هذه الخدمة قد تستخدم من أكثر من متحكم.

## 7.2.30. Trust Score Service: Valid

### Main Success Scenario

تختبر هذه الحالة حساب درجة الثقة للوسيط أو الشركة. تعتمد الدرجة على عوامل مثل التوثيق، عدد العقارات المعتمدة، متوسط التقييم، عدد المراجعات، والنشاط داخل المنصة. يجب أن تعود الدرجة ضمن المجال الصحيح وألا تتجاوز الحدود المتوقعة.

### Extensions

- إذا لم توجد مراجعات أو توثيق، يجب أن تكون الدرجة منخفضة أو صفرية حسب القواعد.
- إذا زادت المراجعات المعتمدة ومتوسط التقييم، يجب أن تتحسن الدرجة.
- يجب أن تستخدم الخدمة الأوزان الموجودة في إعدادات النظام.

### Test Case

ملف الاختبار: `tests/Unit/Services/Trust/TrustScoreServiceTest.php`

الغرض من الاختبار هو التأكد من أن درجة الثقة ليست قيمة عشوائية، بل ناتجة عن حساب واضح وقابل للتعديل من الإعدادات.

## 7.2.31. Admin Trust Management: Valid

### Main Success Scenario

يدخل المدير إلى إدارة الثقة، فيعرض التقييمات حسب الحالة، يرفض تقييماً مع ملاحظات إدارية، يحذف تقييم وسيط ويعيد حساب الثقة، يعرض طلبات التوثيق حسب الحالة، ويستطيع تحميل مستند التوثيق عند المراجعة.

### Extensions

- يجب أن تعمل فلاتر الحالة في المراجعات والتوثيق.
- عند حذف تقييم مؤثر، يجب إعادة حساب درجة الثقة.
- يجب أن يحمي النظام تحميل المستندات بحيث لا تكون متاحة لغير المدير.
- يستطيع المدير تشغيل إعادة حساب درجة ثقة الوسيط عند الحاجة.

### Test Case

ملف الاختبار: `tests/Feature/Admin/AdminTrustManagementTest.php`

الاختبارات المرتبطة:

- `test_admin_can_list_reviews_by_status`
- `test_admin_can_reject_review_with_admin_notes`
- `test_admin_can_delete_agent_review_and_recalculate_trust`
- `test_admin_can_list_verifications_with_status_filter`
- `test_admin_can_download_verification_document`
- `test_admin_can_recalculate_agent_trust_score`

## 7.2.32. Agent Review Database Integrity: Valid

### Main Success Scenario

يتحقق النظام من أن المستخدم لا يستطيع إنشاء أكثر من تقييم لنفس الوسيط، وأن متوسط التقييم يعتمد على التقييمات المعتمدة فقط. كما يتحقق من أن حذف المستخدم أو الوسيط يؤدي إلى حذف التقييمات المرتبطة.

### Extensions

- التقييمات المعلقة أو المرفوضة لا يجب أن تدخل في المتوسط العام.
- قيد المستخدم والوسيط يجب أن يمنع التكرار على مستوى قاعدة البيانات.
- العلاقات يجب أن تبقى صحيحة بعد الحذف.

### Test Case

ملف الاختبار: `tests/Feature/Agents/AgentReviewDatabaseIntegrityTest.php`

الاختبارات المرتبطة:

- `test_unique_user_agent_pair_constraint`
- `test_average_rating_uses_only_approved_reviews`
- `test_deleting_user_cascades_agent_reviews`
- `test_deleting_agent_cascades_agent_reviews`

## 7.2.33. Notification Service Send To Many: Valid

### Main Success Scenario

ينشئ النظام إشعارات لمجموعة من المستخدمين دفعة واحدة من خلال خدمة الإشعارات. يجب أن تحفظ الإشعارات لكل مستخدم مستهدف، وأن ترتبط بالمستخدم الصحيح، وأن تظهر لاحقاً في قائمة إشعاراته.

### Extensions

- إذا كانت قائمة المستخدمين فارغة، يجب ألا يحدث خطأ غير متوقع.
- يجب أن تكون الإشعارات الجديدة غير مقروءة افتراضياً.
- عند حذف المستخدم، يجب حذف إشعاراته حسب العلاقة.

### Test Case

ملف الاختبار: `tests/Feature/Notifications/NotificationTest.php`

الاختبار المرتبط:

- `test_notification_service_send_to_many`

## 7.2.34. Public Review Visibility: Invalid Until Approved

### Main Success Scenario

يرسل المستخدم تقييماً لعقار أو وسيط أو شركة. يحفظ النظام التقييم، لكنه لا يظهر في القائمة العامة إلا بعد اعتماده من المدير. عند اعتماد التقييم يدخل في ملخص التقييم وحساب المتوسط أو الثقة.

### Extensions

- التقييم المعلق لا يظهر للزائر.
- التقييم المرفوض لا يظهر للزائر.
- اعتماد التقييم يجب أن يحدث أثراً في الملخص العام.
- حذف التقييم يجب أن يزيل أثره من الحسابات اللاحقة.

### Test Case

ترتبط هذه الحالة بعدة ملفات:

- `tests/Feature/Trust/PropertyReviewTest.php`
- `tests/Feature/Trust/AgentReviewTest.php`
- `tests/Feature/Trust/CompanyReviewTest.php`
- `tests/Feature/Trust/TrustModerationTest.php`

الهدف منها هو التأكد من أن نظام الثقة يخضع للمراجعة الإدارية ولا ينشر كل ما يكتبه المستخدم مباشرة.

## 7.2.35. Guest Access Restrictions: Invalid

### Main Success Scenario

يحاول الزائر الوصول إلى مسارات تحتاج إلى تسجيل دخول، مثل التوصيات، التفضيلات، التحليلات الاستثمارية، لوحة المستثمر، إدارة الروابط، أو مسارات الإدارة. يجب أن يرفض النظام الطلب لأن المستخدم غير مصادق.

### Extensions

- إذا كان المستخدم مصادقاً لكنه ليس مديراً، يجب أن يمنع من مسارات الإدارة.
- إذا كان المستخدم مصادقاً لكنه لا يملك المورد، يجب أن يمنع من تعديله أو عرضه.
- يجب أن تعاد رموز HTTP مناسبة مثل 401 أو 403 أو 404 حسب الحالة.

### Test Case

تظهر هذه الحالة في عدة اختبارات، منها:

- `test_guest_cannot_access_recommendations`
- `test_guest_cannot_save_preferences`
- `test_guest_cannot_access_investment_analyses`
- `test_guest_cannot_access_dashboard`
- `test_guest_cannot_access_my_social_media`
- `test_guest_cannot_update_estate_social_links`
- `test_guest_cannot_access_admin_social_links`

## 7.2.36. Dependency Delete Restrictions: Invalid

### Main Success Scenario

يحاول المدير حذف مدينة أو منطقة أو شركة لها علاقات تابعة. يجب أن يمنع النظام الحذف إذا كان سيؤدي إلى كسر علاقات مهمة، مثل حذف مدينة تحتوي على مناطق، أو منطقة مرتبطة بعقارات أو شركات، أو شركة لديها وسطاء.

### Extensions

- يجب السماح بالحذف عندما لا توجد علاقات تابعة.
- يجب أن تكون رسالة الفشل واضحة بما يكفي لمعرفة سبب المنع.
- منع الحذف هنا يحمي اتساق البيانات وليس مجرد قرار واجهة.

### Test Case

تظهر هذه الحالة في:

- `test_admin_cannot_delete_city_with_places`
- `test_admin_cannot_delete_place_with_estates_or_companies`
- `test_admin_can_delete_place_without_dependencies`
- `test_admin_cannot_delete_company_with_agents`
- `test_admin_can_delete_company_without_agents`

## 7.2.37. Duplicate Data Restrictions: Invalid

### Main Success Scenario

يحاول المستخدم أو النظام إنشاء بيانات مكررة لا يجب السماح بها، مثل ملف شركة ثانٍ لنفس المستخدم، ملف وسيط ثانٍ لنفس المستخدم، تقييم مكرر، مفضلة مكررة، أو رابط اجتماعي مكرر لنفس المنصة والكيان. يجب أن ترفض قاعدة البيانات أو منطق الخدمة هذا التكرار.

### Extensions

- يسمح النظام بنفس الوسيط أن يكون مفضلاً لدى مستخدمين مختلفين.
- يسمح النظام بنفس المنصة الاجتماعية لكيانات مختلفة.
- يمنع النظام التكرار فقط داخل نفس نطاق الملكية أو نفس الكيان.

### Test Case

تظهر هذه الحالة في:

- `test_user_cannot_create_second_company_profile`
- `test_unique_user_id_constraint_prevents_duplicate_agent_profiles`
- `test_unique_user_id_constraint_prevents_duplicate_company_profiles`
- `test_unique_user_agent_pair_constraint`
- `test_user_cannot_review_same_property_twice`
- `test_user_cannot_submit_duplicate_agent_review`
- `test_unique_platform_per_entity_constraint`
- `test_duplicate_platforms_in_single_request_are_rejected`

## 7.3. خلاصة الفصل

توضح حالات الاختبار أن المشروع لا يعتمد على اختبار الواجهة فقط، بل يحتوي على اختبارات خلفية تغطي المسارات والقيود والخدمات الأساسية. أهم المجالات التي تم التحقق منها هي الإدارة، العقارات، الشركات، الوسطاء، المواقع الجغرافية، الاستثمار، المحافظ، التوصيات، الإشعارات، الروابط الاجتماعية، المراجعات، التوثيق، ودرجة الثقة.

كما تظهر الاختبارات أهمية حماية الصلاحيات ومنع التكرار والحفاظ على العلاقات. فالنظام لا يختبر فقط أن الطلب الناجح يعمل، بل يختبر أيضاً الحالات البديلة مثل منع غير المدير من الوصول، منع المستخدم من عرض بيانات غيره، منع تكرار المفضلة أو التقييمات، وإظهار التقييمات المعتمدة فقط. وبذلك تمثل الاختبارات طبقة تحقق مهمة لضمان استقرار منصة زاد للعقارات عند التطوير أو التوسعة المستقبلية.
