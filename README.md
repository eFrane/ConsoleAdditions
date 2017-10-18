# Console Additions

Tools to make working with Symfony Console even more awesome.

## Output

This Package offers additional console output interfaces:

### `FileOutput`

FileOutputs write all their data to a file stream and come in to concrete flavours:

- `NativeFileOutput` uses the native PHP file streaming functions, thus being a good
  option for local destinations and depending on your servers PHP streaming protocols
  configuration it might even suffice for remote destinations.
  
- `FlysystemFileOutput` on the other hand passes the stream data on to a 
  `league/flysystem`-Adapter, thus being able to send that data to any Flysystem-supported
  destination, i.e. S3, Dropbox, FTP, etc.

### `MultiplexedOutput`

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
