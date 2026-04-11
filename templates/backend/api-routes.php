// FILE: routes/api.php (Add these lines)

use App\Modules\[MODULE]\Controllers\Api\V1\[CONTROLLER]Controller;

// [MODULE] - [FEATURE] API Routes
Route::prefix('[api-prefix]/[feature]s')->group(function () {
    Route::get('/', [[CONTROLLER]Controller::class, 'index']);
    Route::get('/{id}', [[CONTROLLER]Controller::class, 'show']);
    Route::post('/', [[CONTROLLER]Controller::class, 'store']);
    Route::put('/{id}', [[CONTROLLER]Controller::class, 'update']);
    Route::delete('/{id}', [[CONTROLLER]Controller::class, 'destroy']);
});
