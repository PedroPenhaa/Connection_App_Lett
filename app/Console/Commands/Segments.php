<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Segment;

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

        do {
            // Obter dados do serviço AuthLett
            $data = AuthLett::getData('segments', 4, $currentPage);
            // Decodificar os dados JSON, ajuste conforme necessário
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            // Verificar se há dados
            if (!empty($decodedData)) {
                // Iterar sobre os dados e salvá-los na tabela 'segments'
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

            $currentPage++;
        } while ($currentPage <= $pages);
    }
}
