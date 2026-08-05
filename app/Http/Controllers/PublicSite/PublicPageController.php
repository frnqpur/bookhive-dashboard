<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function aboutDemo(): Response
    {
        return Inertia::render('Public/AboutDemo', [
            'developer' => $this->developer(),
        ]);
    }

    public function contactDeveloper(): Response
    {
        return Inertia::render('Public/ContactDeveloper', [
            'developer' => $this->developer(),
        ]);
    }

    private function developer(): array
    {
        $settings = AppSetting::whereIn('key', [
            'contact.github',
            'contact.linkedin',
            'contact.portfolio',
            'contact.email',
        ])->pluck('value', 'key');

        return [
            'name' => env('BOOKHIVE_DEVELOPER_NAME', 'Frengki Josua Purba'),
            'github' => $settings['contact.github'] ?? 'https://github.com/frnqpur',
            'linkedin' => $settings['contact.linkedin'] ?? 'https://www.linkedin.com/in/frengkijosuapurba',
            'portfolio' => $settings['contact.portfolio'] ?? 'https://frengkipurba.com',
            'email' => $settings['contact.email'] ?? 'contact@frengkipurba.com',
            'note' => 'These contact details are restored to the default portfolio values whenever the public demo reset runs.',
        ];
    }
}
