<?php
namespace CarlosIO\WhatsApp\Command;

use CarlosIO\WhatsApp\Protocol;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('carlosio:whatsapp:send')
            ->setDescription('Send message to phone number')
            ->addArgument('receiver', InputArgument::REQUIRED, 'Receiver telephone number')
            ->addArgument('message',  InputArgument::REQUIRED, 'Message content')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sender = $input->getArgument('sender');
        $imei = $input->getArgument('imei');
        $nickname = $input->getArgument('nickname');
        $receiver = $input->getArgument('receiver');
        $message = $input->getArgument('message');

        $wa = new Protocol($sender, $imei, $nickname);
        $output->write('Connecting...');
        $wa->Connect();
        $output->writeln(' [<info>OK</info>]');
        $output->write('Logging...');
        $wa->Login();
        $output->writeln(' [<info>OK</info>]');
        $output->write('Sending...');
        $wa->Message(time() . '-1', $receiver, $message);
        $output->writeln(' [<info>OK</info>]');
    }
}