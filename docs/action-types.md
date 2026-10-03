---
layout: page
title: Action Types
---

The Batch system uses a flexible Action pattern that allows different types of commands and operations to be executed in sequence. Each action type implements a common interface and can be mixed within a single batch.

## Action Interface

All action types implement `EFrane\ConsoleAdditions\Batch\Action`:

```php
interface Action {
    public function getName(): string;
    public function setOutput(OutputInterface $output): void;
    public function execute(Style\SymfonyStyle $io): int;
}
```

## Available Action Types

### StringCommandAction

The simplest action type — represents a command by its name as a string.

```php
use EFrane\ConsoleAdditions\Batch\StringCommandAction;

$action = new StringCommandAction('cache:clear');
$batch->addAction($action);

// Or simply use the shorthand:
$batch->add('cache:clear'); // Creates StringCommandAction internally
```

**Use when:** You have a command name and want to execute it with its default behavior.

### InstanceCommandAction

Wraps an actual Symfony Command instance for direct execution.

```php
use EFrane\ConsoleAdditions\Batch\InstanceCommandAction;

$command = $application->find('my:command');
$action = new InstanceCommandAction($command, ['--option' => 'value']);
$batch->addAction($action);

// Or use the shorthand:
$batch->addCommand($command, ['--option' => 'value']);
```

**Use when:** You need to pass a Command instance directly, or configure the command before adding it to the batch.

### ShellAction

Executes a shell command. **Requires `symfony/process` package.**

```php
use EFrane\ConsoleAdditions\Batch\ShellAction;

$action = new ShellAction('ls -la');
$batch->addAction($action);

// Or use the shorthand:
$batch->addShell('ls -la');
```

**Constructor:**
```php
ShellAction::__construct(
    string $command,
    ?Closure $callback = null,
    ?Style\SymfonyStyle $io = null
)
```

**Use when:** You need to run system-level commands as part of your batch.

### ProcessAction

A lower-level action that wraps a Symfony Process. **Requires `symfony/process` package.**

This gives you full control over the process configuration.

```php
use EFrane\ConsoleAdditions\Batch\ProcessAction;
use Symfony\Component\Process\Process;

$process = new Process(['git', 'pull']);
$process->setTimeout(300);
$process->setWorkingDirectory('/path/to/repo');

$action = new ProcessAction($process);
$batch->addAction($action);
```

**Use when:** You need fine-grained control over process execution (timeout, working directory, environment variables, TTY, etc.).

### MessageAction

Outputs a message without executing any command. Useful for adding informative text between commands.

```php
use EFrane\ConsoleAdditions\Batch\MessageAction;

$action = new MessageAction('Running migrations...');
$batch->addAction($action);

// Or use the shorthand:
$batch->addMessage('Running migrations...');
```

**Constructor:**
```php
MessageAction::__construct(
    string $message,
    string $type = 'info' // 'info', 'comment', 'question', 'success', 'warning', 'error'
)
```

**Example with different message types:**
```php
$batch
    ->addMessage('Starting deployment', 'info')
    ->add('deploy:prepare')
    ->addMessage('Clearing cache...', 'comment')
    ->add('cache:clear')
    ->addMessage('Deployment complete!', 'success');
```

## Combining Action Types

All action types can be freely mixed within a single batch:

```php
use EFrane\ConsoleAdditions\Command\Batch;

$batch = Batch::create($application, $output);

$batch
    ->addMessage('Starting deployment sequence', 'info')
    ->add('deploy:prepare')
    ->addShell('composer install --no-dev')
    ->addMessage('Running database migrations', 'comment')
    ->add('database:migrations:migrate')
    ->addShell('npm run build')
    ->addMessage('Deployment finished!', 'success');
```

## Custom Actions

You can create your own action types by implementing the `Action` interface:

```php
use EFrane\ConsoleAdditions\Batch\Action;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CustomAction implements Action {
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setOutput(OutputInterface $output): void {
        // Store output for later use
    }

    public function execute(SymfonyStyle $io): int {
        $io->writeln('Executing custom action: ' . $this->name);
        // Do your custom logic here
        return 0; // Return code
    }
}

// Use it in a batch
$batch->addAction(new CustomAction('my-custom-action'));
```

## Action Lifecycle

1. **Creation**: Action is created and added to the batch
2. **Output Injection**: `setOutput()` is called with the batch's output
3. **Execution**: `execute()` is called when the batch runs
4. **Return Code**: The action returns an integer exit code (0 = success)

The batch collects all return codes and can be configured to stop on failure or continue regardless.
