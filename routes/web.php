<?php

use App\Http\Controllers\PhotoUploadController;
use App\Http\Controllers\WrittenGuestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Invitat;
use App\Models\WeddingTable;

Route::get('/', [WrittenGuestController::class, 'index'])->name('invitation');
Route::post('/answer', [WrittenGuestController::class, 'store'])->name('answer')
    ->middleware('throttle:answer');;

Route::get('/poze/upload', [PhotoUploadController::class, 'show'])->name('poze.upload');
Route::post('/poze/upload', [PhotoUploadController::class, 'store'])
    ->name('poze.upload.store')
    ->middleware('throttle:poze-upload');

Route::prefix('mese/config')
    ->name('mese.config.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', function () {
            $guests = Invitat::orderBy('name')->get([
                'id', 'name', 'person_number', 'kid_number', 'confirmed', 'wedding_table_id',
            ]);

            $tables = WeddingTable::with(['guests' => function ($q) {
                $q->orderBy('name')->select('id', 'name', 'person_number', 'kid_number', 'wedding_table_id');
            }])->orderBy('number')->get(['id', 'number', 'finished']);

            return view('guests.index', compact('guests', 'tables'));
        });

        Route::post('/guests', function (Request $request) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'person_number' => ['required', 'integer', 'min:0'],
                'kid_number' => ['required', 'integer', 'min:0'],
                'confirmed' => ['required', 'boolean'],
                'accommodation' => ['required', 'boolean'],
            ]);

            $guest = Invitat::create($data);

            return response()->json([
                'id' => $guest->id,
                'name' => $guest->name,
                'person_number' => $guest->person_number,
                'kid_number' => $guest->kid_number,
                'confirmed' => $guest->confirmed,
                'accommodation' => $guest->accommodation,
                'wedding_table_id' => $guest->wedding_table_id,
            ]);
        });

        Route::get('/guests/search', function (Request $request) {
            $q = trim((string) $request->query('q', ''));

            $query = Invitat::query()
                ->select('id', 'name', 'person_number', 'kid_number', 'wedding_table_id')
                ->orderBy('name')
                ->limit(20);

            if ($q !== '') {
                $query->where('name', 'like', '%'.$q.'%');
            }

            if ($request->boolean('unassigned')) {
                $query->whereNull('wedding_table_id');
            }

            return response()->json($query->get());
        });

        Route::post('/tables', function () {
            $next = (int) (WeddingTable::max('number') ?? 0) + 1;
            $table = WeddingTable::create(['number' => $next, 'finished' => false]);

            return response()->json([
                'id' => $table->id,
                'number' => $table->number,
                'finished' => $table->finished,
                'guests' => [],
            ]);
        });

        Route::patch('/tables/{table}', function (Request $request, WeddingTable $table) {
            $data = $request->validate([
                'number' => ['sometimes', 'integer', 'min:1', 'unique:wedding_tables,number,'.$table->id],
                'finished' => ['sometimes', 'boolean'],
            ]);

            if (empty($data)) {
                abort(422, 'No editable fields provided.');
            }

            if (array_key_exists('number', $data) && $table->finished && (! array_key_exists('finished', $data) || $data['finished'])) {
                abort(409, 'Table is finished. Edit it first to change the number.');
            }

            $table->fill($data)->save();

            return response()->json([
                'id' => $table->id,
                'number' => $table->number,
                'finished' => $table->finished,
            ]);
        });

        Route::delete('/tables/{table}', function (WeddingTable $table) {
            if ($table->finished) {
                abort(409, 'Table is finished. Edit it first to delete.');
            }

            $table->delete();

            return response()->json(['ok' => true]);
        });

        Route::post('/tables/{table}/guests', function (Request $request, WeddingTable $table) {
            if ($table->finished) {
                abort(409, 'Table is finished. Edit it first to add guests.');
            }

            $data = $request->validate([
                'guest_id' => ['required', 'integer', 'exists:invitati,id'],
            ]);

            $guest = Invitat::findOrFail($data['guest_id']);
            $guest->wedding_table_id = $table->id;
            $guest->save();

            return response()->json([
                'id' => $guest->id,
                'name' => $guest->name,
                'person_number' => $guest->person_number,
                'kid_number' => $guest->kid_number,
                'wedding_table_id' => $guest->wedding_table_id,
            ]);
        });

        Route::patch('/guests/{guest}', function (Request $request, Invitat $guest) {
            $data = $request->validate([
                'confirmed' => ['sometimes', 'boolean'],
                'person_number' => ['sometimes', 'integer', 'min:0'],
                'kid_number' => ['sometimes', 'integer', 'min:0'],
            ]);

            if (empty($data)) {
                abort(422, 'No editable fields provided.');
            }

            $guest->fill($data)->save();

            return response()->json([
                'id' => $guest->id,
                'confirmed' => $guest->confirmed,
                'person_number' => $guest->person_number,
                'kid_number' => $guest->kid_number,
            ]);
        });

        Route::delete('/tables/{table}/guests/{guest}', function (WeddingTable $table, Invitat $guest) {
            if ($table->finished) {
                abort(409, 'Table is finished. Edit it first to remove guests.');
            }

            if ($guest->wedding_table_id === $table->id) {
                $guest->wedding_table_id = null;
                $guest->save();
            }

            return response()->json(['ok' => true]);
        });
    });
