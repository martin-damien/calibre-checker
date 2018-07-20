<?php

namespace App\Handler;

use App\Command\CheckZipCommand;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class CheckZipHandler
{
    public function handle(CheckZipCommand $command): Collection
    {
        $errors = new ArrayCollection();

        $zip = new \ZipArchive();
        $status = $zip->open($command->file->getRealPath());

        if (true !== $status) {
            switch ($status) {
                case \ZipArchive::ER_NOZIP:
                    $errors->add('Not a zip');
                    break;
                case \ZipArchive::ER_INCONS :
                    $errors->add('Consistency check failed');
                    break;
                case \ZipArchive::ER_CRC :
                    $errors->add('Checksum failed');
                    break;
                default:
                    $errors->add(sprintf('Zip error: %s', $status));
            }
        }

        return $errors;
    }
}