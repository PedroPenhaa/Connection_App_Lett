<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Classe;
use Illuminate\Support\Facades\DB;

class Classes extends Command
{
    protected $signature = 'Lett:Classes';
    protected $description = 'Command description';

    public function handle()
    {
        $currentPage = 1;
        $perPage = 100;

        $foreignKey = AuthLett::getForeignkey('App\Models\Family');
        $totalPages = AuthLett::getTotalPages('classes', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {
            $data = AuthLett::getData('classes', $perPage, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Classe::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'family_id' => $foreignKey[$segmentData['family_id']]->id
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
