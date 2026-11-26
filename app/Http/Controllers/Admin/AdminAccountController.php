<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAccountController
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json([
                'orders' => [],
                'products' => [],
                'customers' => [],
            ]);
        }

        $orders = [];
        if (Schema::hasTable('orders')) {
            $orders = DB::table('orders')
                ->where(function ($query) use ($term): void {
                    if (Schema::hasColumn('orders', 'order_number')) {
                        $query->orWhere('order_number', 'like', "%{$term}%");
                    }
                    if (Schema::hasColumn('orders', 'customer_email')) {
                        $query->orWhere('customer_email', 'like', "%{$term}%");
                    }
                    if (Schema::hasColumn('orders', 'id')) {
                        $query->orWhere('id', $term);
                    }
                })
                ->limit(5)
                ->get()
                ->map(fn ($order): array => [
                    'title' => $order->order_number ?? ('Order #' . $order->id),
                    'subtitle' => $order->customer_email ?? ($order->status ?? 'Order'),
                    'url' => route('admin.module', ['section' => 'orders', 'slug' => 'orders']),
                ])
                ->values()
                ->all();
        }

        $products = Product::query()
            ->with(['brand', 'translations', 'variants'])
            ->where(function (Builder $query) use ($term): void {
                $query->where('slug', 'like', "%{$term}%")
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('translations', fn (Builder $t) => $t->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('variants', fn (Builder $v) => $v->where('sku', 'like', "%{$term}%"));
            })
            ->limit(6)
            ->get()
            ->map(function (Product $product): array {
                $name = $product->translated('en')?->name ?? $product->slug;
                $sku = $product->variants->first()?->sku;

                return [
                    'title' => $name,
                    'subtitle' => trim(($product->brand?->name ?? '') . ($sku ? " · {$sku}" : ''), ' ·'),
                    'badge' => ucfirst($product->status),
                    'url' => route('admin.products.edit', $product),
                ];
            })
            ->values()
            ->all();

        $customers = User::query()
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->orderByRaw("CASE WHEN role = 'customer' THEN 0 ELSE 1 END")
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'title' => $user->name,
                'subtitle' => $user->email,
                'badge' => str_replace('_', ' ', $user->role),
                'url' => route('admin.settings.users-roles'),
            ])
            ->values()
            ->all();

        return response()->json([
            'orders' => $orders,
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function profile(Request $request): View
    {
        return view('admin.account.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
        ]);

        return redirect()
            ->route('admin.account.profile')
            ->with('status', 'Profile details updated.');
    }

    public function password(): View
    {
        return view('admin.account.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:14', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.account.password')
            ->with('status', 'Password updated successfully.');
    }

    public function preferences(Request $request): View
    {
        return view('admin.account.preferences', [
            'preferences' => $request->session()->get('admin.preferences', [
                'sidebar_default' => 'expanded',
                'open_new_tab_storefront' => true,
            ]),
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sidebar_default' => ['required', Rule::in(['expanded', 'collapsed'])],
            'open_new_tab_storefront' => ['nullable', 'boolean'],
        ]);

        $request->session()->put('admin.preferences', [
            'sidebar_default' => $validated['sidebar_default'],
            'open_new_tab_storefront' => (bool) ($validated['open_new_tab_storefront'] ?? false),
        ]);

        return redirect()
            ->route('admin.account.preferences')
            ->with('status', 'Account preferences saved.');
    }
}
