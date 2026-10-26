<?php

namespace App\Models;

use Database\Factories\InsurancePolicyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['policy_number', 'holder_name', 'insurer', 'starts_on', 'ends_on', 'premium'])]
class InsurancePolicy extends Model
{
    /** @use HasFactory<InsurancePolicyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'premium' => 'decimal:2',
        ];
    }
}
