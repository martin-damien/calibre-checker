<?php

namespace App\Command;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class CheckCommand extends ContainerAwareCommand
{
    protected $bookRepository;

    public function __construct($name = null, BookRepository $bookRepository)
    {
        parent::__construct($name);

        $this->bookRepository = $bookRepository;
    }

    protected function configure()
    {
        $this->setName('app:check');
        $this->addArgument('library', InputArgument::REQUIRED, 'Library path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $databasePath = sprintf('%s/metadata.db', $input->getArgument('library'));
        $this->getContainer()->get('doctrine.dbal.dynamic_connection')->forceSwitch($databasePath);

        /** @var array $books */
        $books = $this->bookRepository->findAll();

        $errors = [];

        $io->title('Calibre Checker');

        $io->progressStart(count($books));

        /** @var Book $book */
        foreach ($books as $book) {

            $io->progressAdvance();

            $folder = sprintf('%s/%s', $input->getArgument('library'), $book->getPath());

            $epubFinder = new Finder();
            $epubFinder->files()->in($folder)->name('*.epub');

            if (0 === iterator_count($epubFinder)) {
                $errors[] = [
                    $folder,
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
                            $folder,
                            'ePub is not a zip',
                        ];
                        break;
                    case \ZipArchive::ER_INCONS :
                        $errors[] = [
                            $folder,
                            'ePub consistency check failed',
                        ];
                        break;
                    case \ZipArchive::ER_CRC :
                        $errors[] = [
                            $folder,
                            'ePub checksum failed',
                        ];
                        break;
                    default:
                        $errors[] = [
                            $folder,
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