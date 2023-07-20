<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportFilesInAmazonS3
{
    use AsAction;

    public string $commandSignature = 'import:drive-to-s3';

    public function handle()
    {
        $files = Storage::files('public');

        foreach ($files as $file) {
            $filename = basename($file);
            Storage::disk('s3')->put("amautas/{$filename}",Storage::get($file));
        }
    }
}
