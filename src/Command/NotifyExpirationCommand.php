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
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:notify-expiration',
    description: 'Sends email for each item that is about to expire!',
)]
class NotifyExpirationCommand extends Command
{

    private WarrantyRepository $warrantyRepository;
    private UserRepository $userRepository;
    private MailerInterface $mailer;
    private LocaleSwitcher $localeSwitcher;
    private TranslatorInterface $translator;


    public function __construct(WarrantyRepository $warrantyRepository, UserRepository $userRepository, MailerInterface $mailer, LocaleSwitcher $localeSwitcher, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->warrantyRepository = $warrantyRepository;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->localeSwitcher = $localeSwitcher;
        $this->translator = $translator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findUsersWithAllowedNotifications();

        foreach ($users as $user)
        {
            $io->info($user->getEmail() . ' notifications: ');
            $warranties = $this->warrantyRepository->findToBeNotifiedByEmailOneUser($user->getId());
            $length = sizeof($warranties);

            if($length === 0)
                continue;

            foreach ($warranties as $warranty)
            {
                $io->writeln('------------------');
                $io->writeln($warranty->getId());
                $io->writeln($warranty->getItem()->getName());
                $io->writeln($warranty->getItem()->getCategory()->getName());
                $io->writeln($warranty->getExpiration()->format('d.m.Y'));
                $io->writeln($warranty->getNotifyDaysBefore());
                //$warranty->setNotifiedByEmail(true);
                //$this->warrantyRepository->save($warranty, true);
            }

           /* $this->localeSwitcher->runWithLocale($user->getPreferredLocale(), function() use ($user, $length, $warranties, $io) {

                $email = (new TemplatedEmail())->to(new Address($user->getEmail()));

                if($length === 1) {
                    $email->subject($warranties[0]->getItem()->getName() . ' ' . $this->translator->trans('is about to expire!'));
                }
                else
                    $email->subject($length . ' '. $this->translator->trans('items are about to expire!'));

                $email->htmlTemplate('emails/expiration.html.twig')
                    ->context([
                        'warranties' => $warranties
                    ]);

                try {
                    $this->mailer->send($email);
                } catch (TransportExceptionInterface $e)
                {
                    $io->error($e->getMessage());
                }
            });*/

        }
        return Command::SUCCESS;
    }
}
