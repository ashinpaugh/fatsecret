<?php

namespace Moop\Bundle\FatSecretBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FSExerciseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('moop:fatsecret:exercise:edit')
            ->addArgument('UID', InputArgument::REQUIRED)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user_id = $input->getArgument('UID');
        $api     = $this->getContainer()->get('moop.fat_secret.api');
        
        $response = $api->createProfile($user_id);
        $output->writeln(print_r($response, 1));
    }
}