<?php

namespace App\Models;

use App\Geography;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function fromGeography(string $country, string $region = ''): self
    {
        $location = static::firstOrCreate(
            ['name' => app(Geography::class)->locationName($country, $region)],
            ['country_code' => $country, 'region_code' => $region],
        );

        if ($location->country_code === null) {
            $location->update(['country_code' => $country, 'region_code' => $region]);
        }

        return $location;
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }
}
