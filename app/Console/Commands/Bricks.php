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
        /* Busca a chave estrangeira necessária*/
        $classes = Classe::get()->reduce(function ($acc, $classe) {
            $acc[$classe->external_id] = $classe;
            return $acc;
        });

        $currentPage = 1;

        /*realiza a consulta para pegar o num de páginas*/
        $data = AuthLett::getData('bricks', 100, $currentPage);
        $decodedData = json_decode($data, true);
        $pages = $decodedData['paging']['number_of_pages'];

        /* Adiciona barra de progreso */
        $bar = $this->output->createProgressBar($pages);

        do {

            $data = AuthLett::getData('bricks', 100, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            /*Inicia a transação de dados*/
            DB::beginTransaction();


            foreach ($decodedData['data'] as $segmentData) {

                /* Adiciona os dados na tabela */
                Brick::updateOrCreate(
                    /* Condição de comparação. Se tiver esses atributos ele atualiza, caso não, cria. */
                    [
                        'external_id' => $segmentData['id'],
                        'class_id' => $classes[$segmentData['class_id']]->id
                    ],
                    /* Altera os atributos */
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
