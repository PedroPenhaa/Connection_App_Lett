<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use App\Models\Sku;
use App\Models\Brick;
use App\Models\Brand;

class Skus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Skus';

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
        $bricks = Brick::get()->reduce(function ($acc, $brick) {
            $acc[$brick->external_id] = $brick;
            return $acc;
        });

        $brands = Brand::get()->reduce(function ($acc, $brand) {
            $acc[$brand->external_id] = $brand;
            return $acc;
        });



        $currentPage = 1;

        do {
            // Obter dados do serviço AuthLett para a página atual
            $data = AuthLett::getData('skus', 2, $currentPage);
            $decodedData = json_decode($data, true);
            $pages = $decodedData['paging']['number_of_pages'];

            // Iterando sobre os dados recebidos
            foreach ($decodedData['data'] as $segmentData) {

                //Atualiza ou Cria um registro na tabela.      
                Sku::updateOrCreate(
                    // Primeiro Array que será para validação.
                    [
                        'external_id' => $segmentData['id'],
                        'brick_id' => $bricks[$segmentData['brick_id']]->id,
                        'brand_id' => $brands[$segmentData['brand_id']]->id
                    ],
                    // Array que pode ser alterado os dados
                    [
                        'ean' => $segmentData['ean'],
                        'retailer_sku_match' => $segmentData['retailer_sku_match'],
                        'content' => $segmentData['content'],
                    ]
                );
            }
            // Incrementa a página atual para obter os dados da próxima página
            $currentPage++;
        } while ($currentPage <= $pages);
    }
}
