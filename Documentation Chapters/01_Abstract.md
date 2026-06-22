# Abstract

## الملخص

يهدف مشروع **زاد للعقارات** إلى بناء منصة ويب عقارية متكاملة تساعد المستخدمين على اكتشاف العقارات، تقييم الفرص الاستثمارية، التواصل مع الوسطاء والشركات، ومتابعة العقارات المناسبة من خلال المفضلة والتوصيات. يعالج النظام مشكلة تشتت المعلومات العقارية وصعوبة المقارنة بين الخيارات، وذلك من خلال واجهة عربية موجهة للمستخدم، وخادم خلفي يوفر واجهات API منظمة، وقاعدة بيانات تحفظ المدن والمناطق والعقارات والوسطاء والشركات والتفاعلات المرتبطة بها.

يتكون النظام من واجهة أمامية مبنية باستخدام Vue وVite وPinia، وخادم خلفي مبني باستخدام Laravel وSanctum، إضافة إلى خدمة منفصلة لتقدير السعر باستخدام Flask وscikit-learn. يوفر النظام تصفح العقارات العامة دون تسجيل دخول، ثم يضيف للمستخدم المسجل وظائف مثل إدارة الملف الشخصي، حفظ العقارات والوسطاء في المفضلة، إرسال الرسائل، ضبط تفضيلات الاستثمار، الحصول على توصيات، إنشاء تحليلات استثمارية، وإدارة محفظة عقارية. كما يدعم ملاك العقارات والشركات والوسطاء من خلال مسارات لإدارة العقارات، بيانات الشركة، الوسطاء التابعين، والروابط الاجتماعية.

يعتمد النظام على لوحة إدارة منفصلة تسمح للمدير بإدارة المستخدمين، المدن، المناطق، العقارات، الشركات، الوسطاء، الإشعارات، الرسائل، ومراجعة الثقة والتقييمات وطلبات التوثيق. ويحتوي المشروع على آليات لاحتساب العائد الاستثماري ROI وفترة الاسترداد، محرك توصيات يعتمد على تفضيلات المستخدم وسلوكه، بحث جغرافي على الخريطة، نظام تقييمات ومراجعات بحالة موافقة، ونظام درجة ثقة للوسطاء والشركات. وبذلك يقدم المشروع منصة عملية تجمع بين العرض العقاري، التحليل الاستثماري، الثقة، والإدارة في نظام واحد.

## English Abstract

**Ziad Real Estate** is a web platform for browsing, managing, and evaluating real estate opportunities. The system combines a public property catalog, authenticated user features, company and agent management, investment analysis, recommendations, messaging, notifications, review moderation, and an admin dashboard. It is implemented as a decoupled application with a Vue/Vite frontend, a Laravel Sanctum API backend, and a Flask machine-learning service for property price prediction.

The platform enables visitors to browse cities, places, agents, companies, and active properties, while registered users can manage profiles, favorites, preferences, recommendations, messages, investment analyses, and portfolios. Administrators can manage core records and moderate reviews, verification requests, property statuses, company statuses, and trust scores. The system therefore provides a practical digital real estate workflow that supports discovery, comparison, investment decision-making, communication, and operational control.
