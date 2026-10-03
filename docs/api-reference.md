---
layout: page
title: API Reference
---

## Namespace: `EFrane\ConsoleAdditions\`

### Root Classes

| Class | Description |
|-------|-------------|
| `EFrane\ConsoleAdditions\Command\Batch` | Main batch execution class |

---

## Namespace: `EFrane\ConsoleAdditions\Command`

### Batch

The main class for creating and executing command batches.

**Class:** `EFrane\ConsoleAdditions\Command\Batch`

#### Static Methods

| Method | Description |
|--------|-------------|
| `static create(Application $app, OutputInterface $output, bool $silenceErrors = false)` | Create a new batch instance |

#### Instance Methods

| Method | Type | Description |
|--------|------|-------------|
| `add(string\|array $command)` | `self` | Add one or more commands by name |
| `addCommand(Command $command, array $arguments = [])` | `self` | Add a Command instance |
| `addAction(Action $action)` | `self` | Add an Action instance |
| `addShell(string $command, ?Closure $callback = null)` | `self` | Add a shell command (requires symfony/process) |
| `addShellCb(string $command, Closure $callback)` | `self` | Add shell command with configuration callback |
| `addMessage(string $message, string $type = 'info')` | `self` | Add a message action |
| `run()` | `void` | Execute the batch |
| `silenceErrors(bool $silence = true)` | `self` | Enable/disable error silencing |
| `stopOnFailure(bool $stop = true)` | `self` | Enable/disable stop on failure |
| `setOutput(OutputInterface $output)` | `self` | Set the output |
| `setApplication(Application $app)` | `self` | Set the application |
| `getReturnCodes()` | `array` | Get all return codes |
| `allCommandsSucceeded()` | `bool` | Check if all commands returned 0 |
| `__toString()` | `string` | Get string representation of batch |

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$silenceErrors` | `bool` | Whether to silence errors |
| `$stopOnFailure` | `bool` | Whether to stop on first failure |

---

## Namespace: `EFrane\ConsoleAdditions\Output`

### Output Classes

#### FileOutputInterface

Interface for all file-based outputs.

```php
interface FileOutputInterface extends OutputInterface {
    public function getPath(): string;
}
```

#### NativeFileOutput

Output that writes to a file using PHP's native file functions.

**Class:** `EFrane\ConsoleAdditions\Output\NativeFileOutput`

**Constants:**
- `MODE_OVERWRITE = 0` - Overwrite existing file
- `MODE_APPEND = 1` - Append to existing file

**Constructor:**
```php
NativeFileOutput::__construct(string $path, int $mode = self::MODE_OVERWRITE)
```

**Methods:**
```php
getPath(): string    // Get the file path
getMode(): int       // Get the write mode
```

#### FlysystemFileOutput

Output that writes to a Flysystem adapter.

**Class:** `EFrane\ConsoleAdditions\Output\FlysystemFileOutput`

**Constructor:**
```php
FlysystemFileOutput::__construct(FilesystemInterface $filesystem, string $path)
```

#### MultiplexedOutput

Output that writes to multiple outputs simultaneously.

**Class:** `EFrane\ConsoleAdditions\Output\MultiplexedOutput`

**Constructor:**
```php
MultiplexedOutput::__construct(array $outputs)
```

**Methods:**
```php
addOutput(OutputInterface $output): self
getOutputs(): array
```

---

## Namespace: `EFrane\ConsoleAdditions\Batch`

### Action Interface

Base interface for all batch actions.

```php
interface Action {
    public function getName(): string;
    public function setOutput(OutputInterface $output): void;
    public function execute(SymfonyStyle $io): int;
}
```

### Action Types

| Class | Description |
|-------|-------------|
| `StringCommandAction` | Executes a command by name |
| `InstanceCommandAction` | Executes a Command instance |
| `ShellAction` | Executes a shell command |
| `ProcessAction` | Executes a Symfony Process |
| `MessageAction` | Outputs a message |

#### StringCommandAction

**Constructor:**
```php
StringCommandAction::__construct(string $commandName)
```

#### InstanceCommandAction

**Constructor:**
```php
InstanceCommandAction::__construct(Command $command, array $arguments = [])
```

#### ShellAction

**Constructor:**
```php
ShellAction::__construct(string $command, ?Closure $callback = null, ?SymfonyStyle $io = null)
```

#### ProcessAction

**Constructor:**
```php
ProcessAction::__construct(Process $process)
```

#### MessageAction

**Constructor:**
```php
MessageAction::__construct(string $message, string $type = 'info')
```

Message types: `info`, `comment`, `question`, `success`, `warning`, `error`

---

## Namespace: `EFrane\ConsoleAdditions\Exception`

### Exception Classes

| Class | Extends | Thrown When |
|-------|---------|-------------|
| `BatchException` | `\RuntimeException` | Batch command fails and errors not silenced |
| `MultiplexedOutputException` | `\RuntimeException` | Error writing to multiplexed output |
| `FileOutputException` | `\RuntimeException` | Error in file output operations |

#### BatchException Methods

```php
getCommand(): ?string    // Get the failed command name
getReturnCode(): ?int     // Get the exit code
getPrevious(): ?Throwable // Get previous exception
```

---

## Namespace: `EFrane\ConsoleAdditions\Batch`

### ReturnCodeStack

Manages the collection of return codes from executed commands.

**Class:** `EFrane\ConsoleAdditions\Batch\ReturnCodeStack`

**Methods:**
```php
push(int $returnCode): void
pop(): int
peek(): int
all(): array
isEmpty(): bool
count(): int
```
