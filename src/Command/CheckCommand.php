<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class CheckCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:check');
        $this->addArgument('library', InputArgument::REQUIRED, 'Library path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $errors = [];

        $io->title('Calibre Checker');

        $foldersFinder = new Finder();
        $foldersFinder->directories()->in($input->getArgument('library'))->name('/\([0-9]+\)$/');

        $io->progressStart(iterator_count($foldersFinder));

        /** @var SplFileInfo $folder */
        foreach ($foldersFinder as $folder) {

            $io->progressAdvance();

            $epubFinder = new Finder();
            $epubFinder->files()->in($folder->getRealPath())->name('*.epub');

            if (0 === iterator_count($epubFinder)) {
                $errors[] = [
                    $folder->getRealPath(),
                    'No ePub file in this folder',
                ];

                continue;
            }

            // @todo Handle multiple epub files

            $zip = new \ZipArchive();
            $status = $zip->open(array_values(iterator_to_array($epubFinder))[0]->getRealPath());

            if (true !== $status) {
                switch ($status) {
                    case \ZipArchive::ER_NOZIP:
                        $errors[] = [
                            $folder->getRealPath(),
                            'ePub is not a zip',
                        ];
                        break;
                    case \ZipArchive::ER_INCONS :
                        $errors[] = [
                            $folder->getRealPath(),
                            'ePub consistency check failed',
                        ];
                        break;
                    case \ZipArchive::ER_CRC :
                        $errors[] = [
                            $folder->getRealPath(),
                            'ePub checksum failed',
                        ];
                        break;
                    default:
                        $errors[] = [
                            $folder->getRealPath(),
                            sprintf('ePub: %s', $status),
                        ];
                }
            }
        }

        if (count($errors) > 0) {

            $io->progressFinish();

            $io->section('Problems');

            $io->table(
                ['Folder', 'Error'],
                $errors
            );

            $io->error(sprintf('There are %d errors with your library.', count($errors)));

        }
    }
}