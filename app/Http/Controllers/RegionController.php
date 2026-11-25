<?php

namespace App\Http\Controllers;

use App\Geography;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    public function __invoke(Geography $geography, string $country): JsonResponse
    {
        abort_unless(array_key_exists($country, $geography->countries()), 404);

        return response()->json(['regions' => $geography->regions($country)]);
    }
}
