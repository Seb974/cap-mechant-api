<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\User\DataIntegrator as UserDataIntegrator;
use App\Service\Product\DataIntegrator as ProductDataIntegrator;
use App\Service\Supplier\DataIntegrator as SupplierDataIntegrator;

class GetDataCommand extends Command
{
    protected $userIntegrator;
    protected $productIntegrator;
    protected $supplierIntegrator;
    protected static $defaultName = 'app:get-data';
    protected static $defaultDescription = 'Get refreshed datas from VIF';


    public function __construct(ProductDataIntegrator $productIntegrator, UserDataIntegrator $userIntegrator, SupplierDataIntegrator $supplierIntegrator)
    {
        parent::__construct();
        $this->userIntegrator = $userIntegrator;
        $this->productIntegrator = $productIntegrator;
        $this->supplierIntegrator = $supplierIntegrator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('entity', InputArgument::OPTIONAL, 'Select only one entity to import : user or product (void for both)');
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entity = $input->getArgument('entity');

        // if (!$entity) {
            // $status = $this->productIntegrator->editProducts();
            // $status = $this->userIntegrator->editUsers();
            $status = $this->supplierIntegrator->editSuppliers();
        // } 
        // else {
        //     $status = strtoupper($entity) == 'USER' ? $this->csvService->getUsersFromCsv() : $this->csvService->getProductsFromCsv();
        // }

        $status == 0 ? $io->success("Les données ont bien été importées.") :
                       $io->error("Une erreur est survenue. Veuillez réessayer ultérieurement.");
        return $status;

        return Command::SUCCESS;
    }
}
