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
        Schema::create('favorite_estates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Estate::class)->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'estate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_estates');
    }
};
