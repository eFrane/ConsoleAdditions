<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests;


use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends Command
{
    public function configure()
    {
        $this->setName('testCommand');
        $this->addOption('throw-exception');
        $this->addArgument('name', InputArgument::OPTIONAL, '', 'Test');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Hello '.$input->getArgument('name'));

        if ($input->getOption('throw-exception')) {
            throw new RuntimeException('Testing exception cascading');
        }

        return 0;
    }
}
