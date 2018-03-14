# Console Additions

Tools to make working with Symfony Console even more awesome.

## Installation

This package is available on [Packagist](https://packagist.org/efrane/symfony-console-additions):

```bash
composer require efrane/symfony-console-additions
```

## The Additions

### `Batch`

This class offers batching commands of a Symfony Console Application. This can be
useful when writing things like deployment or update scripts as console commands
which call many other commands in a set order e.g. cache updating, database
migrations, etc.

Usage in a `Command::execute`:

```php
\EFrane\ConsoleAdditions\Command\Batch::create($this->getApplication(), $output)
    ->add('my:command --with-option')
    ->add('my:other:command for-this-input')
    ->run();
```

### Output

This Package offers additional console output interfaces:

#### `FileOutput`

FileOutputs write all their data to a file stream and come in to concrete flavours:

- `NativeFileOutput` uses the native PHP file streaming functions, thus being a good
  option for local destinations and depending on your servers PHP streaming protocols
  configuration it might even suffice for remote destinations.
  
- `FlysystemFileOutput` on the other hand passes the stream data on to a 
  `league/flysystem`-Adapter, thus being able to send that data to any Flysystem-supported
  destination, i.e. S3, Dropbox, FTP, etc.

#### `MultiplexedOutput`

MultiplexedOutput can be used to combine multiple output interfaces to act as one.
This is the logical companion of file outputs since usually one would probably
want to send the output to the user's console and some other destination.
A simple setup inside might look like this:

```php
    class Command extends \Symfony\Component\Console\Command {
        public function execute(InputInterface $input, OutputInterface $output) {
            // send output to multiple destinations
            $output = new \EFrane\ConsoleAdditions\Output\MultiplexedOutput([
                $output,
                new \EFrane\ConsoleAdditions\Output\NativeFileOutput('command.log')
            ]);
            
            // normal console command
            
        }
    }
```
