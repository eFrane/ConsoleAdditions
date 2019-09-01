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
     * @param $parameters
     * @param $expectedOutput
     *
     * @dataProvider provideExecuteParameters
     */
    public function testExecutesWithDifferentParameters($parameters, $expectedOutput)
    {
        $sut = new MessageAction($parameters['message'], $parameters['newLine']);
        $sut->execute($this->output);

        $this->assertEquals($expectedOutput, $this->getOutput());
    }

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

    public function testExecuteHonorsVerbosity()
    {
        $sut = new MessageAction('Message', false);

        $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $sut->execute($this->output);

        $this->assertEquals('', $this->getOutput());
    }
}
