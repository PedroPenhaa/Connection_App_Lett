<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Family;
use App\Models\Segment;

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
        // Método Principal
        // Recupera os registros da tabela Segment, reduzindo em um array associativo usando o 'externel_id' como chave;
        $segments = Segment::get()->reduce(function ($acc, $segment) {
            $acc[$segment->external_id] = $segment;
            return $acc;
        });

        $currentPage = 1;

        do {
            // Obter dados do serviço AuthLett para a página atual
            $data = AuthLett::getData('families', 10, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            // Iterando sobre os dados recebidos
            foreach ($decodedData['data'] as $segmentData) {

                //Exibe informações sobre o seguimento
                echo "Segmento Externo - ", "{$segmentData['segment_id']}",
                " Segmento Interno - ", "{$segments[$segmentData['segment_id']]->id}",
                "Total Páginas -  $currentPage/$pages ", "\n";

                //Atualiza ou Cria um registro na tabela.      
                Family::updateOrCreate(
                    // Primeiro Array que será para validação.
                    [
                        'external_id' => $segmentData['id'],
                        'segment_id' => $segments[$segmentData['segment_id']]->id
                    ],
                    // Array que pode ser alterado os dados
                    [
                        'name' => $segmentData['name'],
                    ]
                );
            }
            // Incrementa a página atual para obter os dados da próxima página
            $currentPage++;
        } while ($currentPage <= $pages);
    }
}
