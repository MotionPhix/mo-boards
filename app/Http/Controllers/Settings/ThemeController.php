<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    /**
     * Update the user's theme preference.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
        ]);

        // Update the user's theme preference in the database
        $request->user()->update([
            'theme_preference' => $validated['theme'],
        ]);

        // Set the theme cookie for immediate feedback
        $cookie = cookie('theme', $validated['theme'], 60 * 24 * 365); // 1 year

        return response()->json(['success' => true])->withCookie($cookie);
    }
}
