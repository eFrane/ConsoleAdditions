---
layout: page
title: Batch Features
---

The Batch class is the workhorse of Console Additions, enabling you to chain multiple commands together and execute them as a single unit.

## Basic Usage

```php
use EFrane\ConsoleAdditions\Command\Batch;

Batch::create($application, $output)
    ->add('cache:clear')
    ->add('migrations:migrate')
    ->add('assets:install')
    ->run();
```

## Command Chaining

Commands are executed in the order they are added. Each command's return code is tracked.

### Adding Commands

```php
$batch = Batch::create($application, $output);

// Add by command name with arguments
$batch->add('my:command --option=value');

// Add multiple at once
$batch->add(['command:one', 'command:two --arg']);

// Chain fluently
$batch->add('first')->add('second')->add('third');
```

### Adding Command Instances

```php
use EFrane\ConsoleAdditions\Command\InstanceCommandAction;

$command = $application->find('my:command');
$batch->addCommand($command, ['--option' => 'value']);
```

## Shell Commands

**Requires `symfony/process` package**

Execute system shell commands alongside Symfony console commands.

### Basic Shell Command

```php
$batch->addShell('ls -la');
```

### Advanced Shell Commands with Callbacks

For complex shell commands requiring custom configuration (e.g., process piping):

```php
use Symfony\Component\Process\Process;

$batch->addShellCb('ls -la', function(Process $process) {
    $process->setTimeout(60);
    $process->setTty(true);
});
```

## Error Handling

### Silencing Errors

Run all commands in the batch without throwing exceptions on failure:

```php
$batch->silenceErrors();

// Or on creation
Batch::create($application, $output, true); // $silenceErrors = true
```

### Return Code Tracking

Check the return codes of all executed commands:

```php
$batch->run();

// Get all return codes
$returnCodes = $batch->getReturnCodes();

// Check if all succeeded (return code 0)
if ($batch->allCommandsSucceeded()) {
    $output->writeln('All commands completed successfully');
}
```

### Stop on Failure

Stop executing subsequent commands if any command fails:

```php
$batch->stopOnFailure();
```

## Output Control

### String Representation

Get a shell-script-like string representation of the batch:

```php
$batch = Batch::create($application, $output)
    ->add('command:one')
    ->add('command:two --arg');

echo (string) $batch;
// Output: command:one && command:two --arg
```

This is useful for:
- Logging the batch configuration
- Generating documentation
- Allowing users to see what will be executed

### Custom Output

You can provide custom output for the batch:

```php
$batch->setOutput($customOutput);
```

## Configuration Options

| Method | Description |
|--------|-------------|
| `silenceErrors(bool)` | Don't throw exceptions on command failure |
| `stopOnFailure(bool)` | Stop batch execution on first failure |
| `setOutput(OutputInterface)` | Set a custom output |
| `setApplication(Application)` | Set the Symfony Application |

## Real-World Example

```php
use EFrane\ConsoleAdditions\Command\Batch;

class DeployCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        $batch = Batch::create($this->getApplication(), $output);

        $batch
            ->add('cache:clear --no-warmup')
            ->addShell('composer install --no-dev')
            ->addShellCb('npm install', function(Process $p) {
                $p->setTimeout(300);
            })
            ->add('database:migrations:migrate --no-interaction')
            ->add('assets:install')
            ->stopOnFailure()
            ->silenceErrors(false);

        try {
            $batch->run();
            $output->writeln('<info>Deployment completed successfully!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Deployment failed: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
```
