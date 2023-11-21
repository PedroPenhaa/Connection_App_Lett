<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Supplier;

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
        // Obter dados do serviço AuthLett
        $data = AuthLett::getData('suppliers', 4);
        // Decodificar os dados JSON, ajuste conforme necessário
        $decodedData = json_decode($data, true);

        // Verificar se há dados
        if (!empty($decodedData)) {
            // Iterar sobre os dados e salvá-los na tabela 'segments'

            foreach ($decodedData['data'] as $segmentData) {
                Supplier::updateOrCreate(
                    ['external_id' => $segmentData['id']],
                    ['name' => $segmentData['name']]
                );
            }

            $this->info('Dados importados com sucesso.');
        } else {
            $this->info('Nenhum dado para importar.');
        }
    }
}
