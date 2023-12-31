<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Brick;
use Illuminate\Support\Facades\DB;

class Bricks extends Command
{
    protected $signature = 'Lett:Bricks';
    protected $description = 'Command description';

    public function handle()
    {
        $currentPage = 1;
        $perPage = 100;

        $foreignKey = AuthLett::getForeignkey('App\Models\Classe');
        $totalPages = AuthLett::getTotalPages('bricks', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {

            $data = AuthLett::getData('bricks', $perPage, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Brick::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'class_id' => $foreignKey[$segmentData['class_id']]->id
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
