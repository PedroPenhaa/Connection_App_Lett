<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Segment;
use Illuminate\Support\Facades\DB;

class Segments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Segments';

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

        $data = AuthLett::getData('segments', 100, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        $bar = $this->output->createProgressBar($pages);

        do {
            $data = AuthLett::getData('segments', 100, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            if (!empty($decodedData)) {
                foreach ($decodedData['data'] as $segmentData) {
                    Segment::updateOrCreate(
                        ['external_id' => $segmentData['id']],
                        ['name' => $segmentData['name']]
                    );
                }
                $this->info('Dados importados com sucesso.');
            } else {
                $this->info('Nenhum dado para importar.');
            }
            DB::commit();

            $currentPage++;

            $bar->advance();
        } while ($currentPage <= $pages);
    }
}
