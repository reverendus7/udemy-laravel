<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function homePage() {
        $ourName = 'Brad';
        $animals = [
            'Benji',
            'Holly',
            'Patty',
        ];

        return view(
            'homepage', 
            [
                'name' => $ourName,
                'animals' => $animals
            ]
        );
    }

    public function aboutPage() {
        return view('single-post');
    }
}
