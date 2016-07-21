<?php

namespace Moop\Bundle\FatSecretBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FSFoodSearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('moop:fatsecret:food:search')
            ->addArgument('search', InputArgument::REQUIRED | InputArgument::IS_ARRAY)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $search = implode(" ", $input->getArgument('search'));
        $api    = $this->getContainer()->get('moop.fat_secret.api');
        
        $response = $api->searchFood($search);
        $output->writeln(print_r($response, 1));
    }
}