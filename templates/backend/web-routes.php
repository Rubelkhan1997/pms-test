// FILE: routes/web.php (Add these lines)

use App\Modules\[MODULE]\Controllers\Web\[CONTROLLER]Controller;
use Illuminate\Support\Facades\Route;

// [MODULE] - [FEATURE] Web Routes
Route::[middleware]('[feature]s', [CONTROLLER]Controller::class)->group(function () {
    Route::get('/', 'index')->name('[feature]s.index');
    Route::get('/create', 'create')->name('[feature]s.create');
    Route::get('/{model}/edit', 'edit')->name('[feature]s.edit');
    Route::get('/{model}', 'show')->name('[feature]s.show');
});
