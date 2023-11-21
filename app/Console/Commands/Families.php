<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Families extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lett:Families';

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
        $baseUrl = "https://api-content.lett.digital";
        $service = "access_tokens";
        $username = "sidnei.simeao@vilanova.com.br";
        $password = "Sm#8gP4aq.z4jJ";
        $serviceFamilies= "families";
        $limit = 1;

        //Autenticação
        $responseToken = Http::post("{$baseUrl}/{$service}", [
                'username' => $username,
                'password' => $password
        ]);
        
        // Obter o token do corpo da resposta// Adicionar o token ao cabeçalho
        $token = $responseToken->json()['access_token'];

           //---------------------------  Families  --------------------------- 

           $responseFamilies = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token, 
           ])->get("{$baseUrl}/{$serviceFamilies}?limit=$limit");
               
           $bodyFamilies = $responseFamilies->body();
           echo("Familias \n\n");
           dump($bodyFamilies);
    }
}
