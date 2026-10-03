---
layout: page
title: Error Handling
---

Console Additions provides exception classes for handling errors in Batches and Outputs. Understanding these exceptions helps you build robust applications.

## Exception Hierarchy

All Console Additions exceptions extend from `\RuntimeException`:

```
RuntimeException
├── BatchException
│   └── (thrown by Batch when commands fail)
└── MultiplexedOutputException
    └── (thrown by MultiplexedOutput)
```

Additionally, file-related exceptions:

```
FileOutputException
└── (thrown by file output implementations)
```

## BatchException

Thrown when a batch command fails and `silenceErrors(false)` is set (default behavior).

```php
use EFrane\ConsoleAdditions\Exception\BatchException;

try {
    $batch = Batch::create($application, $output)
        ->add('might:fail')
        ->silenceErrors(false) // This is the default
        ->run();
} catch (BatchException $e) {
    // Handle batch failure
    $output->writeln(sprintf(
        '<error>Batch failed: %s</error>',
        $e->getMessage()
    ));
    
    // Get the failed command
    $failedCommand = $e->getCommand();
    $returnCode = $e->getReturnCode();
}
```

### Methods

| Method | Type | Description |
|--------|------|-------------|
| `getCommand()` | `string\|null` | Returns the command name that failed |
| `getReturnCode()` | `int\|null` | Returns the exit code of the failed command |
| `getPrevious()` | `\Throwable\|null` | Returns the previous exception (if nested) |

### Common Causes

- A command returns a non-zero exit code
- A command throws an exception
- A shell command fails (requires symfony/process)

## MultiplexedOutputException

Thrown when an error occurs while writing to one of the multiplexed outputs.

```php
use EFrane\ConsoleAdditions\Output\MultiplexedOutput;
use EFrane\ConsoleAdditions\Exception\MultiplexedOutputException;

try {
    $output = new MultiplexedOutput([
        $consoleOutput,
        new NativeFileOutput('/nonexistent/directory/file.log'),
    ]);
    
    $output->writeln('This will fail');
} catch (MultiplexedOutputException $e) {
    // Handle output failure
    $output->writeln(sprintf(
        '<error>Failed to write to one or more outputs: %s</error>',
        $e->getMessage()
    ));
}
```

### Common Causes

- Permission denied when writing to a file
- Directory doesn't exist
- Network issues with cloud storage (FlysystemFileOutput)
- One of the outputs throws an exception

## FileOutputException

Thrown by file output implementations when file operations fail.

```php
use EFrane\ConsoleAdditions\Output\NativeFileOutput;
use EFrane\ConsoleAdditions\Exception\FileOutputException;

try {
    $output = new NativeFileOutput('/protected/directory/file.log');
    $output->writeln('This will fail');
} catch (FileOutputException $e) {
    // Handle file output failure
    $output->writeln(sprintf(
        '<error>File output failed: %s</error>',
        $e->getMessage()
    ));
}
```

### Common Causes

- Permission denied
- Directory doesn't exist
- Disk is full
- File path is invalid

## Handling Errors in Batches

### Silencing Errors

To prevent BatchException from being thrown:

```php
$batch = Batch::create($application, $output)
    ->add('might:fail')
    ->silenceErrors(true) // Continue even if commands fail
    ->run();

// Check results after
if ($batch->allCommandsSucceeded()) {
    $output->writeln('All commands succeeded!');
} else {
    $returnCodes = $batch->getReturnCodes();
    foreach ($returnCodes as $i => $code) {
        if ($code !== 0) {
            $output->writeln(sprintf('Command %d failed with code %d', $i, $code));
        }
    }
}
```

### Stopping on Failure

Stop executing subsequent commands when one fails:

```php
$batch = Batch::create($application, $output)
    ->add('command:one')
    ->add('command:two') // Won't run if command:one fails
    ->add('command:three')
    ->stopOnFailure(true)
    ->run();
```

### Getting Return Codes

Always available after running a batch:

```php
$batch->run();

// Get all return codes as array
$returnCodes = $batch->getReturnCodes();

// Check if all succeeded
if ($batch->allCommandsSucceeded()) {
    // All commands returned 0
}

// Check specific command
if ($returnCodes[0] === 0) {
    // First command succeeded
}
```

## Best Practices

1. **Use `silenceErrors(true)` for non-critical batches** where you want to see all results
2. **Use `stopOnFailure(true)` for deployments** where you don't want to continue after a failure
3. **Always check return codes** for conditional logic
4. **Wrap batches in try-catch** for critical operations
5. **Log failures** with details from the exception

## Complete Error Handling Example

```php
use EFrane\ConsoleAdditions\Command\Batch;
use EFrane\ConsoleAdditions\Exception\BatchException;

class SafeDeployCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        $batch = Batch::create($this->getApplication(), $output)
            ->add('deploy:prepare')
            ->add('deploy:execute')
            ->stopOnFailure(true)
            ->silenceErrors(false);

        try {
            $batch->run();
            $output->writeln('<info>Deployment successful!</info>');
            return Command::SUCCESS;
        } catch (BatchException $e) {
            $output->writeln(sprintf(
                '<error>Deployment failed at: %s (exit code: %d)</error>',
                $e->getCommand() ?? 'unknown',
                $e->getReturnCode() ?? -1
            ));
            
            // Attempt cleanup
            $this->runCleanup($output);
            
            return Command::FAILURE;
        }
    }

    private function runCleanup(OutputInterface $output) {
        try {
            $cleanup = Batch::create($this->getApplication(), $output);
            $cleanup
                ->add('deploy:rollback')
                ->silenceErrors(true)
                ->run();
            
            $output->writeln('<comment>Cleanup completed</comment>');
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>Cleanup also failed: %s</error>',
                $e->getMessage()
            ));
        }
    }
}
```
