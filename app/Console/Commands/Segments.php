<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Segment;
use Illuminate\Support\Facades\DB;

class Segments extends Command
{
    protected $signature = 'Lett:Segments';
    protected $description = 'Command description';

    public function handle()
    {
        $currentPage = 1;
        $perPage = 100;

        $totalPages = AuthLett::getTotalPages('brands', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {
            $data = AuthLett::getData('segments', $perPage, $currentPage);
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
                /*$this->info('Dados importados com sucesso.');*/
            } else {
                /*$this->info('Nenhum dado para importar.');*/
            }

            DB::commit();
            $currentPage++;
            $bar->advance();
        } while ($currentPage <= $pages);
    }
}
