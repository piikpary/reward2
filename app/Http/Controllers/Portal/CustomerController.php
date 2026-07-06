<?php

namespace App\Http\Controllers\Portal;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessWalletTransactionJob;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = User::query()
            ->where('user_type', UserType::CUSTOMER)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('portal.customers.index', compact('customers', 'search'));
    }

    public function wallet(User $user, WalletService $walletService)
    {
        $walletService->ensureUserWallets($user);

        $user->load('userWallets.wallet');

        return view('portal.customers.wallet', compact('user'));
    }

    public function addSpin(Request $request, User $user, WalletService $walletService)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $walletService->ensureUserWallets($user);

        DB::transaction(function () use ($user, $validated) {
            $spinWallet = Wallet::where('type', 'spin')->firstOrFail();

            $userSpinWallet = UserWallet::where('user_id', $user->id)
                ->where('wallet_id', $spinWallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $userSpinWallet->balance = (float) $userSpinWallet->balance + (int) $validated['qty'];
            $userSpinWallet->save();

            $walletTransaction = WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_id' => $spinWallet->id,
                'transaction_type' => 'admin_add_spin',
                'wallet_type' => 'spin',
                'amount' => (int) $validated['qty'],
                'from_user_id' => auth()->id(),
                'to_user_id' => $user->id,
                'description' => $validated['description'] ?? 'Spin added from portal',
            ]);

            ProcessWalletTransactionJob::dispatch($walletTransaction->id)->afterCommit();
        });

        return redirect()
            ->route('portal.customers.wallet', $user)
            ->with('success', 'Spin added successfully.');
    }

    public function addDiscount(Request $request, User $user, WalletService $walletService)
{
    $validated = $request->validate([
        'discount_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
        'description' => ['nullable', 'string', 'max:255'],
    ]);

    $walletService->ensureUserWallets($user);

    DB::transaction(function () use ($user, $validated) {
        $discountWallet = Wallet::where('type', 'discount')->firstOrFail();

        $userDiscountWallet = UserWallet::where('user_id', $user->id)
            ->where('wallet_id', $discountWallet->id)
            ->lockForUpdate()
            ->firstOrFail();

        $userDiscountWallet->balance = (float) $userDiscountWallet->balance + (float) $validated['discount_percentage'];
        $userDiscountWallet->save();

        $walletTransaction = WalletTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $discountWallet->id,
            'transaction_type' => 'admin_add_discount',
            'wallet_type' => 'discount',
            'amount' => (float) $validated['discount_percentage'],
            'from_user_id' => auth()->id(),
            'to_user_id' => $user->id,
            'description' => $validated['description'] ?? 'Discount added from portal',
        ]);
        ProcessWalletTransactionJob::dispatch($walletTransaction->id)->afterCommit();
    });

    return redirect()
        ->route('portal.customers.wallet', $user)
        ->with('success', 'Discount added successfully.');
}
}