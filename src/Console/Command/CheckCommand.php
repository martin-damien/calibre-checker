<?php

namespace App\Console\Command;

use App\Entity\Book;
use App\Repository\BookRepository;
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

        if (!file_exists($databasePath)) {
            $io->error('Can\'t find a database in this folder.');

            die();
        }

        // Connect to database
        $this->getContainer()->get('doctrine.dbal.dynamic_connection')->forceSwitch($databasePath);

        $io->title('Calibre Checker');

        /** @var array $books */
        $books = $this->bookRepository->findAll();

        $io->progressStart(count($books));

        /** @var Book $book */
        foreach ($books as $book) {

            $io->progressAdvance();

            $folder = sprintf('%s/%s', $input->getArgument('library'), $book->getPath());

            $epubFinder = new Finder();
            $epubFinder->files()->in($folder)->name('*.epub');

            /** @var SplFileInfo $epub */
            foreach ($epubFinder as $epub) {
                $checkZipCommand = new CheckZipCommand($epub);

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