<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Import CSV format: item_slug,server,price_1,price_10,price_100,date
     * Exemple: fer,draconiros,250,2375,22500,2025-05-11
     */
    public function csv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file    = $request->file('file');
        $handle  = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle); // Ignorer la ligne d'en-tête

        $imported = 0;
        $errors   = [];
        $line     = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (count($row) < 4) {
                $errors[] = "Ligne {$line}: format invalide (colonnes insuffisantes)";
                continue;
            }

            [$slug, $server, $price1] = $row;
            $price10  = $row[3] ?? (int)($price1 * 9.5);
            $price100 = $row[4] ?? (int)($price1 * 90);
            $date     = $row[5] ?? now()->toDateString();

            if (!in_array($server, ['draconiros', 'ombre', 'hellmina', 'orukam'])) {
                $errors[] = "Ligne {$line}: serveur '{$server}' invalide";
                continue;
            }

            $item = Item::where('slug', trim($slug))->first();
            if (!$item) {
                $errors[] = "Ligne {$line}: item '{$slug}' introuvable";
                continue;
            }

            Price::create([
                'item_id'     => $item->id,
                'server'      => $server,
                'price_1'     => (int)$price1,
                'price_10'    => (int)$price10,
                'price_100'   => (int)$price100,
                'recorded_at' => Carbon::parse($date),
            ]);

            $imported++;
        }

        fclose($handle);

        return response()->json([
            'imported' => $imported,
            'errors'   => $errors,
            'message'  => "✅ {$imported} prix importés" . ($errors ? ' avec ' . count($errors) . ' erreurs' : ''),
        ]);
    }

    /**
     * Télécharger un CSV exemple
     */
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dofusvalue-import-template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['item_slug', 'server', 'price_1', 'price_10', 'price_100', 'date']);
            fputcsv($handle, ['fer', 'draconiros', '250', '2375', '22500', date('Y-m-d')]);
            fputcsv($handle, ['bronze', 'orukam', '380', '3610', '34200', date('Y-m-d')]);
            fputcsv($handle, ['kobalte', 'hellmina', '950', '9025', '85500', date('Y-m-d')]);
            fclose($handle);
        }, 200, $headers);
    }
}
