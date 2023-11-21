<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthLett
{
    private static array $credentials  = [];
    private static string $service = "access_tokens";

    private static function getToken(): string
    {
        try {

            self::$credentials = [
                'baseUrl' => config('lett.credentials.url'),
                'userName' => config('lett.credentials.username'),
                'password' => config('lett.credentials.password'),
            ];
            $token = Cache::remember('token', false, function () {
                // Obter o token do corpo da resposta// Adicionar o token ao cabeçalho
                $responseToken = Http::post(self::$credentials['baseUrl'] . '/' . self::$service, [
                    'username' => self::$credentials['userName'],
                    'password' => self::$credentials['password']
                ]);

                // Armazenar o token em cache com um tempo de vida (TTL)
                return $responseToken->json()['access_token'];
            });

            return $token;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public static function getData(string $service, int $limit = 10)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' .  self::getToken(),
        ])->get(self::$credentials['baseUrl'] . "/{$service}?limit={$limit}");

        $body = $response->body();

        return $body;
    }
}