<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Family;
use App\Models\Segment;
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
        $segments = Segment::get()->reduce(function ($acc, $segment) {
            $acc[$segment->external_id] = $segment;
            return $acc;
        });

        $currentPage = 1;

        $data = AuthLett::getData('families', 10, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        $bar = $this->output->createProgressBar($pages);

        do {

            $data = AuthLett::getData('families', 10, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                /*
                echo "Segmento Externo - ", "{$segmentData['segment_id']}",
                " Segmento Interno - ", "{$segments[$segmentData['segment_id']]->id}",
                "Total Páginas -  $currentPage/$pages ", "\n";*/


                Family::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'segment_id' => $segments[$segmentData['segment_id']]->id
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
