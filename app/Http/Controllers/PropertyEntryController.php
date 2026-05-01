<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyEntryController extends Controller
{
    public function create(){
        return Inertia::render('Partner/Onboarding/Property/Create', [
            'headerTitle' => 'Property Setup',  // ← Appears in AppLayout header
        ]);
    }

    public function ratePlanCreate(){
        
        return Inertia::render('Partner/Onboarding/Property/Create');
    }
    
}
