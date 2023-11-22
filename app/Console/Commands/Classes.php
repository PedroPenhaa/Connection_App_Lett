<?php

namespace App\Console\Commands;

use App\Services\AuthLett;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Familie;
use App\Models\Classe;
use App\Models\Segment;


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

        // Processar os dados da página atual
        $familys = Familie::get()->reduce(function ($acc, $family) {
            $acc[$family->external_id] = $family;
            return $acc;
        });

        // Inicializa as variáveis de paginação
        $currentPage = 1;

        do {
            // Obter dados do serviço AuthLett para a página atual
            $data = AuthLett::getData('classes', 5, $currentPage);
            $decodedData = json_decode($data, true);

            $pages = $decodedData['paging']['number_of_pages'];

            foreach ($decodedData['data'] as $familyData) {
                echo "Family Externo - ", "{$familyData['family_id']}", " Family Interno - ",
                "{$familys[$familyData['family_id']]->id}", "Total Páginas -  $currentPage/$pages ", "\n";

                Familie::updateOrCreate(
                    [
                        'external_id' => $familyData['id'],
                        'family_id' => $familys[$familyData['family_id']]->id
                    ],
                    [
                        'name' => $familyData['name'],
                    ]
                );
            }
            // Incrementa a página atual para obter os dados da próxima página
            $currentPage++;
        } while ($currentPage <= $pages);
    }
}
