<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\MessageAction;
use Symfony\Component\Console\Output\OutputInterface;

class MessageActionTest extends BatchTestCase
{
    /**
     * @param array<string, mixed> $parameters
     * @param string $expectedOutput
     *
     * @dataProvider provideExecuteParameters
     */
    public function testExecutesWithDifferentParameters(array $parameters, string $expectedOutput): void
    {
        $sut = new MessageAction($parameters['message'], $parameters['newLine']);
        $sut->execute($this->output);

        $this->assertEquals($expectedOutput, $this->getOutput());
    }

    /**
     * @return array<int, array{0: array<string, mixed>, 1: string}>
     */
    public function provideExecuteParameters(): array
    {
        return [
            [
                [
                    'message' => 'Hello',
                    'newLine' => false,
                ],
                'Hello',
            ],
            [
                [
                    'message' => 'Hello',
                    'newLine' => true,
                ],
                "Hello\n",
            ],
        ];
    }

    public function testExecuteHonorsVerbosity(): void
    {
        $sut = new MessageAction('Message', false);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $sut->execute($this->output);

        $this->assertEquals('', $this->getOutput());
    }
}
