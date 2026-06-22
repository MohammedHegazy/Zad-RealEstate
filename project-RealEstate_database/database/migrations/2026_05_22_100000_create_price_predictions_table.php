<?php







use App\Models\Estate;

use App\Models\User;

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    public function up(): void

    {

        Schema::create('price_predictions', function (Blueprint $table) {

            $table->id();


            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();//المستخدم الذي قام بالتنبؤ
            
            $table->foreignIdFor(Estate::class)->nullable()->constrained()->nullOnDelete(); //العقار الذي تم التنبؤ عليه
           
            $table->string('place_label')->nullable();//المكان الذي تم التنبؤ عليه مثل المزة الميدان 
           
            $table->json('input_features'); //الميزات المرسلة لـ Flask
           
            $table->decimal('predicted_price', 15, 2);//السعر المتوقع
           
            $table->decimal('listed_price', 15, 2)->nullable();//السعر المعروض
           
            $table->decimal('price_difference', 15, 2)->nullable();//الفرق بين السعر المتوقع والسعر المعروض
           
            $table->decimal('price_difference_percent', 8, 2)->nullable();//نسبة الفرق بين السعر المتوقع والسعر المعروض
           
            $table->string('valuation_insight')->nullable();//التوصيات التي ستظهر في التنبؤ الاستنتاج 

            $table->timestamps();
           
            $table->index(['estate_id', 'created_at']);//العقار الذي تم التنبؤ عليه
           
            $table->index('created_at');//التاريخ الذي تم التنبؤ فيه

        });

    }



    public function down(): void

    {

        Schema::dropIfExists('price_predictions');

    }

};

