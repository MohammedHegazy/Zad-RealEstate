<?php
//الموثوقية والامان 
//جدول لتحقق من المستخدمين والوكلاء والشركات والعقارات 
//عن طريق التوثق بمستندات المستخدمين والوكلاء والشركات والعقارات
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();//المستخدم الذي قام بالطلب
            $table->string('document_type', 50);//نوع المستند
            //national_id → الرقم القومي
            //passport → الجواز السفر
            //business_license → رخصة العمل
            //other → غير ذلك
            $table->string('document_path');//مسار المستند
            $table->string('status', 20)->default('pending');
            //حالة الطلب pending → معلق
            //pending → معلق
            //approved → موافق عليه
            //rejected → مرفوض
            $table->text('admin_notes')->nullable();//ملاحظات المشرف

            $table->timestamp('reviewed_at')->nullable();//تاريخ المراجعة
           
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();//المشرف الذي قام بالمراجعة

            $table->timestamps();//تاريخ الإنشاء والتحديث
            $table->index(['user_id', 'status']);//ايجاد المستخدمين الذين يملكون طلبات تحقق بحسب الحالة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
    }
};
