<?php

namespace App\Http\Controllers;

class ContentController extends Controller
{
    public function getTerms() {
        return view('contents.terms');
    }

}
