---
layout: page
title: Use Cases & Recipes
---

## Common Use Cases

### Deployment Scripts

Run a sequence of commands to deploy your application:

```php
Batch::create($application, $output)
    ->add('cache:clear')
    ->addShell('composer install --no-dev --classmap-authoritative')
    ->addShell('npm install')
    ->addShell('npm run build')
    ->add('database:migrations:migrate --no-interaction')
    ->add('assets:install')
    ->stopOnFailure()
    ->run();
```

### Database Migration Chaining

Run multiple migration-related commands in sequence:

```php
Batch::create($application, $output)
    ->add('doctrine:database:create --if-not-exists')
    ->add('doctrine:migrations:migrate --no-interaction')
    ->add('doctrine:fixtures:load --no-interaction')
    ->add('cache:clear')
    ->run();
```

### Log Aggregation

Send command output to multiple log files based on environment:

```php
$outputs = [
    $consoleOutput,
    new NativeFileOutput('/var/log/commands.log', NativeFileOutput::MODE_APPEND),
];

if ($debug) {
    $outputs[] = new NativeFileOutput('/var/log/debug.log', NativeFileOutput::MODE_APPEND);
}

$output = new MultiplexedOutput($outputs);

// Now run your batch with this output
Batch::create($application, $output)
    ->add('my:command')
    ->run();
```

### CI/CD Command Sequencing

In your CI pipeline, run a predictable sequence of commands:

```php
// In your CI command
Batch::create($application, $output)
    ->add('tests:phpunit')
    ->add('tests:phpstan')
    ->add('tests:psalm')
    ->add('code:style:fix')
    ->silenceErrors(false) // Fail on first error
    ->run();

return $batch->allCommandsSucceeded() ? Command::SUCCESS : Command::FAILURE;
```

### Multi-Destination Logging

Send output to console, local file, and cloud storage simultaneously:

```php
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

// Set up S3 filesystem
$adapter = new AwsS3V3Adapter($s3Client, $bucketName);
$s3Filesystem = new Filesystem($adapter);

$output = new MultiplexedOutput([
    $consoleOutput,
    new NativeFileOutput('local.log'),
    new FlysystemFileOutput($s3Filesystem, 's3-logs/command-'.date('Y-m-d-His').'.log'),
]);
```

## Recipes

### Dry Run Mode

Show what would be executed without actually running it:

```php
class DryRunCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        $batch = Batch::create($this->getApplication(), $output);

        // Build your batch
        $batch->add('cache:clear');
        $batch->add('migrations:migrate');

        if ($input->getOption('dry-run')) {
            $output->writeln('<comment>Dry run mode. The following would be executed:</comment>');
            $output->writeln('');
            $output->writeln((string) $batch);
            return Command::SUCCESS;
        }

        $batch->run();
        return Command::SUCCESS;
    }
}
```

### Retry with Backoff

Implement retry logic for flaky commands:

```php
use EFrane\ConsoleAdditions\Command\Batch;

class RetryBatchCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        $maxRetries = 3;
        $retryDelay = 5; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $batch = Batch::create($this->getApplication(), $output)
                    ->add('flaky:command')
                    ->silenceErrors(false);

                $batch->run();
                return Command::SUCCESS;
            } catch (\Exception $e) {
                if ($attempt < $maxRetries) {
                    $output->writeln(sprintf(
                        '<warning>Attempt %d/%d failed, retrying in %d seconds...</warning>',
                        $attempt, $maxRetries, $retryDelay
                    ));
                    sleep($retryDelay);
                    continue;
                }
                throw $e;
            }
        }
    }
}
```

### Conditional Command Execution

Run commands conditionally based on previous results:

```php
$batch = Batch::create($application, $output);

$batch->add('check:precondition');

// Store result for later
$batch->run();

$returnCodes = $batch->getReturnCodes();

if ($returnCodes[0] === 0) {
    // Precondition passed, run main commands
    $batch = Batch::create($application, $output)
        ->add('main:command')
        ->run();
}
```

### Parallel-like Execution with Groups

While true parallel execution requires async, you can group related commands:

```php
// Database operations
$batch->addMessage('=== Database Operations ===');
$batch->add('database:create');
$batch->add('database:migrate');
$batch->add('database:fixtures');

// Asset operations
$batch->addMessage('=== Asset Operations ===');
$batch->add('assets:install');
$batch->addShell('npm run build');

// Cache operations
$batch->addMessage('=== Cache Operations ===');
$batch->add('cache:clear');
$batch->add('cache:warmup');
```

### Interactive Batch with Confirmation

Confirm before executing a batch:

```php
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfirmedBatchCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $batch = Batch::create($this->getApplication(), $output)
            ->add('cache:clear')
            ->add('database:migrate');

        $io->text('The following commands will be executed:');
        $io->listing(explode(' && ', (string) $batch));

        if (!$io->confirm('Continue?')) {
            return Command::SUCCESS;
        }

        $batch->run();
        return Command::SUCCESS;
    }
}
```

### Progress Tracking

Track progress through a long-running batch:

```php
$commands = [
    'step1:prepare',
    'step2:process',
    'step3:finalize',
];

$batch = Batch::create($application, $output);
$total = count($commands);

foreach ($commands as $i => $command) {
    $batch->addMessage(sprintf('Step %d/%d: %s', $i + 1, $total, $command));
    $batch->add($command);
}

$batch->run();
```

### Cleanup on Failure

Register a cleanup action that runs when the batch fails:

```php
$batch = Batch::create($application, $output);

try {
    $batch
        ->add('deploy:start')
        ->add('deploy:execute')
        ->stopOnFailure()
        ->run();
} catch (\Exception $e) {
    $output->writeln('<error>Deployment failed, running cleanup...</error>');
    
    // Run cleanup batch
    $cleanup = Batch::create($application, $output);
    $cleanup
        ->add('deploy:rollback')
        ->addShell('git checkout main')
        ->run();
    
    throw $e;
}
```

## Tips & Best Practices

1. **Use `stopOnFailure()`** for critical deployments where you don't want to continue after a failure
2. **Use `silenceErrors()`** for non-critical batches where you want to see all results
3. **Add MessageAction** between commands to make output more readable
4. **Log the batch configuration** using `(string) $batch` for debugging
5. **Check return codes** with `allCommandsSucceeded()` or `getReturnCodes()` for conditional logic
