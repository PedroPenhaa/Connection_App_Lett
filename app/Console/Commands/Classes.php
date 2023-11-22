<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Classe;
use App\Models\Family;

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
        // Método Principal
        // Recupera os registros da tabela Family, reduzindo em um array associativo usando o 'externel_id' como chave;
        $familys = Family::get()->reduce(function ($acc, $family) {
            $acc[$family->external_id] = $family;
            return $acc;
        });

        $currentPage = 1;

        do {
            // Obter dados do serviço AuthLett para a página atual
            $data = AuthLett::getData('families', 5, $currentPage);
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
