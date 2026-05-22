<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $approvedQuery = Property::query()->where('status', 'approved');

        $featured = Property::query()
            ->with(['media', 'landOwner:id,name'])
            ->where('status', 'approved')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Property $property) => [
                'id' => $property->id,
                'title' => $property->title,
                'city' => $property->city,
                'price_per_month' => $property->price_per_month,
                'image_url' => $property->primaryImageUrl(),
                'owner_name' => $property->landOwner?->name,
            ]);

        return Inertia::render('Welcome', [
            'featured' => $featured,
            'stats' => [
                'listings' => (clone $approvedQuery)->count(),
                'cities' => (clone $approvedQuery)->distinct()->count('city'),
            ],
        ]);
    }
}
