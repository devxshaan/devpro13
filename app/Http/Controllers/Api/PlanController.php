<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Plan $plan) => $this->formatPlan($plan));

        return response()->json($plans);
    }

    public function show(Plan $plan)
    {
        return response()->json($this->formatPlan($plan));
    }

    private function formatPlan(Plan $plan): array
    {
        return [
            'key'           => $plan->plan_key,
            'name'          => $plan->name,
            'slug'          => $plan->slug,
            'description'   => $plan->description,
            'price'         => $plan->price,
            'currency'      => $plan->currency,
            'billing_cycle' => $plan->billing_cycle,
            'trial_days'    => $plan->trial_days,
            'features'      => $plan->features,
            'is_featured'   => $plan->is_featured,
        ];
    }
}