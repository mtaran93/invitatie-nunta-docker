<?php

namespace App\Http\Controllers;

use App\Enums\Menu;
use App\Http\Requests\WrittenGuestRequest;
use App\Models\WrittenGuest;
use Illuminate\Contracts\View\View;

class WrittenGuestController extends Controller
{
    public function index(): View
    {
        return view('invitation');
    }
    public function store(WrittenGuestRequest $request)
    {
        $validated = $request->validated();

        if (! $validated['answer']) {
            WrittenGuest::create([
                'name' => $validated['name'],
                'persons' => 0,
                'children' => false,
                'answer' => $validated['answer'],
                'menu_1' => Menu::label($validated['menu1']),
                'menu_2' => Menu::label($validated['menu2']),
            ]);

            return response()->json([
                'message' => 'fail',
            ]);
        }

        WrittenGuest::create([
            'name' => $validated['name'],
            'persons' => $validated['attendance'] === 'alone' ? 1 : 2,
            'children' => $validated['kids'] === 'noKids' ? false : true,
            'answer' => $validated['answer'],
            'menu_1' => Menu::label($validated['menu1']),
            'menu_2' => Menu::label($validated['menu2']),
        ]);

        return response()->json([
            'message' => 'success',
        ]);
    }
}
