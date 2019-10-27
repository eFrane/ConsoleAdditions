---
sidebar: 'auto'
---

# Getting started

## Command Batches

This package provides a way to batch up a series of commands (and other things).
The main purpose of this is separating configuration of a command chain from
running it. It is also possible to print a more or less shell-script like version
of the configured command batch.

```php
use \EFrane\ConsoleAdditions\Command\Batch;

class MyBatchingCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        // ...
        
        // configure a batch
        $batch = Batch::create($this->getApplication(), $output)
            ->add('my:command --with-option')
            ->add('my:other:command for this input');
        
        // print it's contents
        $output->writeln((string)$batch);
        
        // and run it
        $batch->run();
    }
}
```

## Output Extensions

There are a few output extensions provided which mainly focus on making
the output of commands persistable. 

```php
use \EFrane\ConsoleAdditions\Output\MultiplexedOutput;
use \EFrane\ConsoleAdditions\Output\NativeFileOutput;

class MyLoggingCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        // send output to multiple destinations:
        $output = new MultiplexedOutput([
            // 1: the default output of the command
            $output,
            // 2: the file command.log in the current working directory 
            new NativeFileOutput('command.log')
        ]);
    }
}
```
