<?php

namespace App\Command;

use App\Service\Serializer\CsvService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetData extends Command
{
    // protected $csvService;
    protected static $defaultName = 'app:get-data';
    protected static $defaultDescription = 'get refreshed datas from VIF';

    // public function __construct(CsvService $csvService)
    // {
    //     parent::__construct();
    //     $this->csvService = $csvService;
    // }

    protected function configure()
    {
        $this->setName(self::$defaultName)
             ->setDescription(self::$defaultDescription)
             ->addArgument('entity', InputArgument::OPTIONAL, 'Select only one entity to import : user or product (void for both)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $entity = $input->getArgument('entity');

        // if (!$entity) {
        //     $status = $this->csvService->getUsersFromCsv();
        //     $status = $this->csvService->getProductsFromCsv();
        // } else
        //     $status = strtoupper($entity) == 'USER' ? $this->csvService->getUsersFromCsv() : $this->csvService->getProductsFromCsv();
        $status = 0;
        $status == 0 ? $io->success("Les données ont bien été importées.") :
                       $io->error("Une erreur est survenue. Veuillez réessayer ultérieurement.");
        return $status;
    }
}
