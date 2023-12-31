<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;

class Skus extends Command
{
    protected $signature = 'Lett:Skus';
    protected $description = 'Command description';

    public function handle()
    {
        $currentPage = 1;
        $perPage = 100;

        $foreignKeyOne = AuthLett::getForeignkey('App\Models\Brick');
        $foreignKeyTwo = AuthLett::getForeignkey('App\Models\Brand');
        $totalPages = AuthLett::getTotalPages('brands', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {

            $data = AuthLett::getData('skus', $perPage, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            /*
                    $jsonData = json_encode($decodedData);

                    if ($jsonData !== false) {
                        // Output the JSON data
                        dd($jsonData);
                    } else {
                        // Handle JSON encoding errors
                        echo "Error encoding data to JSON";
                    }
                */

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {
                Sku::updateOrCreate(
                    [
                        'ean' => $segmentData['ean'] ?? 000,
                        'external_id' => $segmentData['id'],
                    ],
                    [
                        'ean' => $segmentData['ean'] ?? 000,
                        'external_id' => $segmentData['id'],
                        'retailer_sku_match' => json_encode($segmentData['retailer_sku_match']),
                        'content' => json_encode($segmentData['content']),
                        'brick_id' => $foreignKeyOne[$segmentData['brick_id']]->id,
                        'brand_id' => $foreignKeyTwo[$segmentData['brand_id']]->id
                    ]
                );
            }

            DB::commit();
            $currentPage++;
            $bar->advance();
        } while ($currentPage <= $pages);

        $bar->finish();
    }
}
