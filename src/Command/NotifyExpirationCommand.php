<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:notify-expiration',
    description: 'Notifies each user if his item is about to expire!',
)]
class NotifyExpirationCommand extends Command
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();
        foreach ($users as $user)
        {
            // Check if notifications are allowed

            // query for all user's items

            // call notify

            // if yes, send email (async if possible)
        }
        return Command::SUCCESS;
    }
}
