<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Can;
use Mockery\CountValidator\Exact;
use PhpParser\Node\Expr\Cast;

class AuthLett
{
    private static array $credentials  = [];
    private static string $service = "access_tokens";


    private static function getToken(): string
    {

        self::$credentials = [
            'baseUrl' => config('lett.credentials.url'),
            'userName' => config('lett.credentials.username'),
            'password' => config('lett.credentials.password'),
        ];


        try {

            if (!Cache::get('token_lett')) {

                try {

                    $responseToken = Http::post(self::$credentials['baseUrl'] . '/' . self::$service, [
                        'username' => self::$credentials['userName'],
                        'password' => self::$credentials['password']
                    ]);

                    if (!$responseToken->successful()) {
                        throw new Exception('Não foi possível retornanr um token');
                    }

                    $token = $responseToken->json()['access_token'];
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }

                Cache::set('token_lett', $token, 300);
            }

            return Cache::get('token_lett');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public static function getData(string $service, int $limit = 10, int $page)
    {
        //dump($service, $limit, $page);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' .  self::getToken(),
        ])->timeout(60 * 20)->get(self::$credentials['baseUrl'] . "/{$service}?limit={$limit}&page={$page}");

        if (!$response->successful() && $response->json()) {

            $res = $response->json();

            throw new Exception($res['message']);
        }

        if (!$response->successful()) {
            throw new Exception("Erro inesperado");
        }

        $body = $response->body();

        return $body;
    }


    public static function getForeignkey(string $modelRef)
    {
        $date = $modelRef::get()->reduce(function ($acc, $item) {
            $acc[$item->external_id] = $item;
            return $acc;
        });
        return $date;
    }

    public static function getTotalPages(string $endpoint, $perPage)
    {

        $data = self::getData($endpoint, 1, 1);

        $data = json_decode($data);

        return (int) ceil($data->paging->total / $perPage);
    }
}
