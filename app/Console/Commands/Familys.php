<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Family;
use Illuminate\Support\Facades\DB;

class Familys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Familys';

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
        $currentPage = 1;
        $perPage = 10;

        $foreignKey = AuthLett::getForeignkey('App\Models\Segment');
        $totalPages = AuthLett::getTotalPages('families', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {

            $data = AuthLett::getData('families', $perPage, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Family::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'segment_id' => $foreignKey[$segmentData['segment_id']]->id
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
