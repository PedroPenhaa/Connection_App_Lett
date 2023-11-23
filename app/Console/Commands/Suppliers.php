<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class Suppliers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Suppliers';

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
        $perPage = 100;

        $totalPages = AuthLett::getTotalPages('brands', $perPage);

        $bar = $this->output->createProgressBar($totalPages);

        do {
            $data = AuthLett::getData('suppliers', $perPage, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            DB::beginTransaction();

            if (!empty($decodedData)) {
                foreach ($decodedData['data'] as $segmentData) {
                    Supplier::updateOrCreate(
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
