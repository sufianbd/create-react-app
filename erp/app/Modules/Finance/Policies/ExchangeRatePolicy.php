<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Models\ExchangeRate;

class ExchangeRatePolicy
{
    public function viewAny(User $user): bool  { return $user->can('finance.view'); }
    public function view(User $user, ExchangeRate $exchangeRate): bool { return $user->can('finance.view'); }
    public function create(User $user): bool   { return $user->can('finance.create'); }
    public function update(User $user, ExchangeRate $exchangeRate): bool { return $user->can('finance.create'); }
    public function delete(User $user, ExchangeRate $exchangeRate): bool { return $user->can('finance.delete'); }
}
