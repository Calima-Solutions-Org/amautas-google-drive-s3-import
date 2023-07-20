<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class GetFilesFromGoogleDriveAndImportInS3
{
    use AsAction;

    public string $commandSignature = 'import:amautas-files';

    public function handle()
    {
        GetGoogleDriveFiles::run();
        ImportFilesInAmazonS3::run();
    }
}
