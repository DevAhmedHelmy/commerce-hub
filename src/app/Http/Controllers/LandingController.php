<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Landing\LandingPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Public landing page + system-controlled PWA entry (prompt 46). `/` always renders the landing
 * (never auto-redirects). The primary CTA target is application-controlled (never an admin URL):
 * guest → login; authenticated but not onboarded → onboarding; onboarded → app home.
 */
class LandingController extends Controller
{
    public function __construct(private readonly LandingPageService $landing)
    {
    }

    public function index(): View
    {
        return view('landing.index', [
            'settings' => $this->landing->settings(),
            'sections' => $this->landing->sections(),
            'categories' => $this->landing->categories(),
            'featured' => $this->landing->featuredProducts(),
        ]);
    }

    /** System-controlled CTA entry into the customer app/auth/onboarding flow. */
    public function start(): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($customer === null) {
            return redirect()->route('login');
        }

        if (! $customer->hasCompletedOnboarding()) {
            return redirect()->route('onboarding.profile');
        }

        return redirect()->route('home');
    }
}
