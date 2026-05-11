<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoutePageController extends Controller
{
    public function show($date = null)
    {
        // Parse date if provided, otherwise use today
        if ($date) {
            // Support multiple date formats: DD-MM-YYYY, YYYY-MM-DD, etc.
            $date = $this->parseDate($date);
            
            if (!$date) {
                abort(404, 'Invalid date format');
            }
        }

        $branchSetting = \App\Models\BranchSetting::current();

        return view('route', ['initialDate' => $date, 'branchSetting' => $branchSetting]);
    }

    private function parseDate($dateString)
    {
        $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'];
        
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $dateString);
            if ($parsed && $parsed->format($format) === $dateString) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }
}
