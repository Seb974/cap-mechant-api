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
            ->addArgument('entity', InputArgument::OPTIONAL, 'Select only one entity to import : user, supplier or product (void for all)');
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 240);
        $io = new SymfonyStyle($input, $output);
        $entity = $input->getArgument('entity');

        if (!$entity) {
            $status = $this->userIntegrator->editUsers();
            $status = $this->supplierIntegrator->editSuppliers();
            $status = $this->productIntegrator->editProducts();
        } 
        else {
            $status = strtoupper($entity) == 'USER' ? $this->userIntegrator->editUsers() :
                     (strtoupper($entity) == 'SUPPLIER' ? $this->supplierIntegrator->editSuppliers() :
                     $this->productIntegrator->editProducts());
        }

        if ($status == 0) {
            $io->success("Les données ont bien été importées.");
            return Command::SUCCESS;
        } else {
            $io->error("Une erreur est survenue. Veuillez réessayer ultérieurement.");
            return Command::FAILURE;
        }
    }
}
