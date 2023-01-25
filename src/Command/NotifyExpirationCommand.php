<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\WarrantyRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:notify-expiration',
    description: 'Sends email for each item that is about to expire!',
)]
class NotifyExpirationCommand extends Command
{

    private WarrantyRepository $warrantyRepository;
    private UserRepository $userRepository;
    private MailerInterface $mailer;

    public function __construct(WarrantyRepository $warrantyRepository, UserRepository $userRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->warrantyRepository = $warrantyRepository;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findUsersWithAllowedNotifications();

        foreach ($users as $user)
        {
            $io->info($user->getEmail() . ' notifications: ');
            $warranties = $this->warrantyRepository->findToBeNotifiedOfOneUser($user->getId());
            foreach ($warranties as $warranty)
            {
                $io->writeln($warranty->getId(). ' - '. $warranty->getItem()->getName() . ' ' . $warranty->getExpiration()->format("d.m.Y"));
            }
        }

//        $email = (new TemplatedEmail())
//            ->to(new Address('kakaovnikk@gmail.com'))
//            ->subject('Thanks for signing up!')
//
//            // path of the Twig template to render
//            ->htmlTemplate('emails/expiration.html.twig')
//
//            // pass variables (name => value) to the template
//            ->context([
//                'item' => $this->itemRepository->find(1),
//            ])
//        ;
//
//        try {
//            $mailer->send($email);
//        } catch (TransportExceptionInterface $e)
//        {
//            dd($e);
//        }
        return Command::SUCCESS;
    }
}
