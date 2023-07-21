<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class GetGoogleDriveFiles
{
    use AsAction;

    public string $commandSignature = 'import:google-drive-files';

    public Client $client;
    public string $refreshToken;
    public string $folderId;
    public string $destinationPath;
    public string $accessToken;
    public string $clientId;
    public string $clientSecret;
    public string $redirectUri;


    public function __construct()
    {
        $this->client = new Client();
        $this->refreshToken = config('googleDriveAuth.refresh_token');
        $this->folderId = config('googleDriveAuth.folder_id');
        $this->destinationPath = storage_path('app/public');
        $this->clientId = config('googleDriveAuth.client_id');
        $this->clientSecret = config('googleDriveAuth.client_secret');
        $this->redirectUri = config('googleDriveAuth.redirect_uri');
    }

    public function handle()
    {
        $this->accessToken = $this->getAccessToken();
        $data = $this->getFilesId();
        $this->importFiles($data);
        return 'Arhivos obtenidos correctamente';
    }

    private function getFilesId(): array
    {
        $response = $this->client->get(
            "https://www.googleapis.com/drive/v3/files",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
                'query' => [
                    'q' => "'{$this->folderId}' in parents",
                    'fields' => 'files(id, name)',
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    private function importFiles(array $data): void
    {
        foreach ($data['files'] as $file) {
            $fileResponse = $this->client->get(
                "https://www.googleapis.com/drive/v3/files/{$file['id']}?alt=media",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                    ],
                ]
            );
            $fileContent = $fileResponse->getBody();
            file_put_contents("{$this->destinationPath}/{$file['name']}", $fileContent);
        }
    }

    private function getAccessToken(): string
    {
        $response = $this->client->post('https://accounts.google.com/o/oauth2/token', [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
                'redirect_url' => $this->redirectUri
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }
}
