<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Brick;
use App\Models\Classe;
use Illuminate\Support\Facades\DB;

class Bricks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Bricks';

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
        $classes = Classe::get()->reduce(function ($acc, $classe) {
            $acc[$classe->external_id] = $classe;
            return $acc;
        });

        $currentPage = 1;

        $data = AuthLett::getData('bricks', 100, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        $bar = $this->output->createProgressBar($pages);

        do {

            $data = AuthLett::getData('bricks', 100, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            foreach ($decodedData['data'] as $segmentData) {

                Brick::updateOrCreate(
                    [
                        'external_id' => $segmentData['id'],
                        'class_id' => $classes[$segmentData['class_id']]->id
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
