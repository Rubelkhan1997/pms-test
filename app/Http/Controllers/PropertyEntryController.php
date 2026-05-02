<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyEntryController extends Controller
{
    public function create()
    {
        return Inertia::render('Partner/Onboarding/Property/Create', [
            'headerTitle' => 'Property Setup',  // ← Appears in AppLayout header
        ]);
    }
    public function policiesCreate()
    {
        return Inertia::render('Partner/Onboarding/policies/Create', [
            'headerTitle' => 'Policies',
        ]);
    }
    public function TaxCreate()
    {
        return Inertia::render('Partner/Onboarding/Tax/TaxList', [
            'headerTitle' => 'Tax',
        ]);
    }
    public function PaymentMethodCreate()
    {
        return Inertia::render('Partner/Onboarding/Payment/PaymentMethod', [
            'headerTitle' => 'Payment',
        ]);
    }
    public function marketCreate()
    {
        return Inertia::render('Partner/Onboarding/Market/Create', [
            'headerTitle' => 'Market',
        ]);
    }

    public function ratePlanCreate()
    {

        return Inertia::render('Partner/Onboarding/Property/Create');
    }
}
