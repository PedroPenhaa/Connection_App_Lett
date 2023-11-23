<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class Brands extends Command
{
    protected $signature = 'Lett:Brands';
    protected $description = 'Command description';

    public function handle()
    {
        $currentPage = 1;
        $perPage = 100;

        $foreignKey = AuthLett::getForeignkey('App\Models\Supplier');
        $totalPages = AuthLett::getTotalPages('brands', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {
            $data = AuthLett::getData('brands', $perPage, $currentPage);
            $decodedData = json_decode($data, true);

            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Brand::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'supplier_id' => $foreignKey[$segmentData['supplier_id']]->id
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
