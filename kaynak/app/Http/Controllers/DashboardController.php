<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $tabDefinitions = collect(config('tahsilat.tab_tanimlari', []));
        $permissions = $user->tabPermissions($tabDefinitions->pluck('key')->all());

        $visibleTabs = $tabDefinitions
            ->filter(fn (array $tab) => (bool) ($permissions[$tab['key']] ?? false))
            ->values();

        return view('dashboard', [
            'visibleTabs' => $visibleTabs,
            'defaultTab' => $visibleTabs->first()['key'] ?? null,
        ]);
    }
}
