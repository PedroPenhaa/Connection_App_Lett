<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Brick;
use App\Models\Classe;


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
        // Método Principal
        // Recupera os registros da tabela Family, reduzindo em um array associativo usando o 'externel_id' como chave;
        $classes = Classe::get()->reduce(function ($acc, $classe) {
            $acc[$classe->external_id] = $classe;
            return $acc;
        });

        $currentPage = 1;

        do {
            // Obter dados do serviço AuthLett para a página atual
            $data = AuthLett::getData('bricks', 100, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            // Iterando sobre os dados recebidos
            foreach ($decodedData['data'] as $segmentData) {

                //Atualiza ou Cria um registro na tabela.      
                Brick::updateOrCreate(
                    // Primeiro Array que será para validação.
                    [
                        'external_id' => $segmentData['id'],
                        'class_id' => $classes[$segmentData['class_id']]->id
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
