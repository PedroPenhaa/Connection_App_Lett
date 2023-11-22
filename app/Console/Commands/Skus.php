<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Sku;
use App\Models\Brick;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class Skus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Skus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $bricks = Brick::get()->reduce(function ($acc, $brick) {
            $acc[$brick->external_id] = $brick;
            return $acc;
        });

        $brands = Brand::get()->reduce(function ($acc, $brand) {
            $acc[$brand->external_id] = $brand;
            return $acc;
        });

        $currentPage = 1;

        $data = AuthLett::getData('skus', 100, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        $bar = $this->output->createProgressBar($pages);


        do {

            $data = AuthLett::getData('skus', 100, $currentPage);
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

                /*

                    [
                        'external_id' => $segmentData['id'],
                        'brick_id' => $bricks[$segmentData['brick_id']]->id,
                        'brand_id' => $brands[$segmentData['brand_id']]->id
                    ],

                */


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
                        'brick_id' => $bricks[$segmentData['brick_id']]->id,
                        'brand_id' => $brands[$segmentData['brand_id']]->id
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
