<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Classe;
use App\Models\Family;
use Illuminate\Support\Facades\DB;

class Classes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Classes';

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
        $familys = Family::get()->reduce(function ($acc, $family) {
            $acc[$family->external_id] = $family;
            return $acc;
        });

        $currentPage = 82;

        $data = AuthLett::getData('classes', 100, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        $bar = $this->output->createProgressBar($pages);

        do {
            $data = AuthLett::getData('classes', 100, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Classe::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'family_id' => $familys[$segmentData['family_id']]->id
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
