<?php
use PHPFrame\Facades\Route;

Route::shell('default/test', [App\Controllers\Shell\DefaultShell::class, 'testAction']);

Route::shell('database/describe', [App\Controllers\Shell\DatabaseShell::class, 'describeAction']);
Route::shell('database/tables', [App\Controllers\Shell\DatabaseShell::class, 'tablesAction']);
Route::shell('database/build-structure', [App\Controllers\Shell\DatabaseShell::class, 'structureAction']);
Route::shell('database/build-model-fields', [App\Controllers\Shell\DatabaseShell::class, 'fieldsAction']);

