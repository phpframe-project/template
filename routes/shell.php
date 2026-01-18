<?php
use PHPFrame\Facades\Route;

Route::shell('default/test', [App\Controllers\Shell\DefaultShell::class, 'testAction']);

Route::shell('database/migrate', [App\Controllers\Shell\DatabaseShell::class, 'migrateAction']);
Route::shell('database/rollback', [App\Controllers\Shell\DatabaseShell::class, 'rollbackAction']);
Route::shell('database/reset', [App\Controllers\Shell\DatabaseShell::class, 'resetAction']);
Route::shell('database/refresh', [App\Controllers\Shell\DatabaseShell::class, 'refreshAction']);
Route::shell('database/status', [App\Controllers\Shell\DatabaseShell::class, 'statusAction']);
Route::shell('database/create', [App\Controllers\Shell\DatabaseShell::class, 'createAction']);
Route::shell('database/describe', [App\Controllers\Shell\DatabaseShell::class, 'describeAction']);
Route::shell('database/build-structure', [App\Controllers\Shell\DatabaseShell::class, 'structureAction']);
Route::shell('database/build-model-fields', [App\Controllers\Shell\DatabaseShell::class, 'fieldsAction']);

