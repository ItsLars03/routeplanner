<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{
    /**
     * Handle the incoming webhook request.
     */
    public function handle(Request $request)
    {
        // 1. Haal de ruwe data op (body van het request)
        $content = $request->getContent();

        // 2. Maak de logregel met tijdstempel
        // now() is de Laravel helper voor de huidige tijd
        $logEntry = "[" . now()->format('Y-m-d H:i:s') . "] " . $content . PHP_EOL;

        // 3. Sla op in storage/app/private/webhook_log.txt
        // De 'append' methode voegt data toe zonder het bestand te overschrijven
        Storage::disk('local')->append('webhook_log.txt', $logEntry);

        // 4. Geef een nette 200 OK response terug
        return response('Data ontvangen en gelogd.', 200)
            ->header('Content-Type', 'text/plain');
    }
}