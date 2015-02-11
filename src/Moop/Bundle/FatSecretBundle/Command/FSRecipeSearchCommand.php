<?php

namespace Moop\Bundle\FatSecretBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FSRecipeSearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('moop:fatsecret:recipe:search')
            ->addArgument('search', InputArgument::REQUIRED)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $search = $input->getArgument('search');
        $api    = $this->getContainer()->get('moop.fat_secret.api');
        
        $response = $api->searchRecipes($search);
        $output->writeln(print_r($response, 1));
    }
}