<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class Brands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Brands';

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

        $suppliers = Supplier::get()->reduce(function ($acc, $supplier) {
            $acc[$supplier->external_id] = $supplier;
            return $acc;
        });

        $currentPage = 1;

        $data = AuthLett::getData('brands', 100, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        $bar = $this->output->createProgressBar($pages);

        do {
            $data = AuthLett::getData('brands', 100, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Brand::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'supplier_id' => $suppliers[$segmentData['supplier_id']]->id
                    ],
                    [
                        'name' => $segmentData['name'],
                    ]
                );
            }
            DB::commit();

            $currentPage++;

            $bar->advance();
        } while ($currentPage <= $pages);
    }
}
