<?php

namespace Moop\Bundle\FatSecretBundle\Command;


use Moop\Bundle\FatSecretBundle\Tests\FatSecretUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FSFoodEntryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('moop:fatsecret:entry:add')
            ->addArgument('UID', InputArgument::REQUIRED)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user_id  = $input->getArgument('UID');
        $api      = $this->getContainer()->get('moop.fat_secret.api');
        $response = $api->getAuthTokenInfo($user_id);
        
        if (!array_key_exists('auth_token', $response)) {
            $response = $api->createProfile($user_id);
        }
        
        $user     = new FatSecretUser($response['auth_token'], $response['auth_secret']);
        $response = $api
            ->setUserOAuthTokens($user)
            ->addFoodEntry(
                1358,
                1251,
                "Beef Steak",
                'other',
                1.0
            )
        ;
        
        $output->writeln(print_r($response, 1));
    }
}