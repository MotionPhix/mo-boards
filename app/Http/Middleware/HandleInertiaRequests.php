<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that is loaded on the first page visit.
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determine the current asset version.
   */
  public function version(Request $request): string|null
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    return [
      ...parent::share($request),
      'auth' => [
        'user' => $request->user() ? [
          'id' => $request->user()->id,
          'name' => $request->user()->name,
          'email' => $request->user()->email,
          'phone' => $request->user()->phone,
          'current_company_id' => $request->user()->current_company_id,
          'last_active_at' => $request->user()->last_active_at,
          'companies' => $request->user()->companies()->with('pivot')->get()->map(function ($company) {
            return [
              'id' => $company->id,
              'name' => $company->name,
              'slug' => $company->slug,
              'industry' => $company->industry,
              'size' => $company->size,
              'subscription_plan' => $company->subscription_plan,
              'is_active' => $company->is_active,
              'pivot' => [
                'is_owner' => $company->pivot->is_owner,
                'joined_at' => $company->pivot->joined_at,
              ],
            ];
          }),
          'current_company' => $request->user()->currentCompany ? [
            'id' => $request->user()->currentCompany->id,
            'name' => $request->user()->currentCompany->name,
            'slug' => $request->user()->currentCompany->slug,
            'industry' => $request->user()->currentCompany->industry,
            'size' => $request->user()->currentCompany->size,
            'subscription_plan' => $request->user()->currentCompany->subscription_plan,
            'subscription_expires_at' => $request->user()->currentCompany->subscription_expires_at,
            'is_active' => $request->user()->currentCompany->is_active,
          ] : null,
        ] : null,
      ],
      'flash' => [
        'success' => $request->session()->get('success'),
        'error' => $request->session()->get('error'),
        'warning' => $request->session()->get('warning'),
        'info' => $request->session()->get('info'),
      ],
      'ziggy' => function () use ($request) {
        return array_merge((new \Tightenco\Ziggy\Ziggy)->toArray(), [
          'location' => $request->url(),
        ]);
      },
    ];
  }
}
