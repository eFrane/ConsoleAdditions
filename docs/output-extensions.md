---
layout: page
title: Output Extensions
---

This library provides several output extensions that build on Symfony's Console OutputInterface.

## Overview

The output extensions primarily focus on making command output **persistable** — allowing you to send console output to files, cloud storage, or multiple destinations simultaneously.

## Available Extensions

### FileOutput (Interface)

Base interface that all file-based outputs implement. Defines the contract for file output behavior.

```php
use EFrane\ConsoleAdditions\Output\FileOutputInterface;

// Type hint against the interface
public function someMethod(FileOutputInterface $output) {
    // Works with any file output implementation
}
```

### NativeFileOutput

Uses PHP's native file streaming functions. Good for:
- Local file system destinations
- Remote destinations (depending on your PHP `allow_url_fopen` configuration)

```php
use EFrane\ConsoleAdditions\Output\NativeFileOutput;

// Write to a local file
$fileOutput = new NativeFileOutput('command.log');

// Append mode (default is overwrite)
$fileOutput = new NativeFileOutput('command.log', NativeFileOutput::MODE_APPEND);
```

**Constructor:**
```php
NativeFileOutput::__construct(
    string $path,
    int $mode = NativeFileOutput::MODE_OVERWRITE
)
```

### FlysystemFileOutput

Integrates with [league/flysystem](https://flysystem.thephpleague.com/) for cloud storage support.

```php
use EFrane\ConsoleAdditions\Output\FlysystemFileOutput;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;

$adapter = new LocalFilesystemAdapter('/path/to/storage');
$filesystem = new Filesystem($adapter);

$output = new FlysystemFileOutput($filesystem, 'command.log');
```

**Cloud Storage Example:**
```php
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

$adapter = new AwsS3V3Adapter(
    $client,
    $bucket,
    $prefix = ''
);
$filesystem = new Filesystem($adapter);

$output = new FlysystemFileOutput($filesystem, 's3-command.log');
```

### MultiplexedOutput

Combines multiple output interfaces into a single output that writes to all destinations.

This is the most common use case — send output to both the console **and** a file.

```php
use EFrane\ConsoleAdditions\Output\MultiplexedOutput;
use EFrane\ConsoleAdditions\Output\NativeFileOutput;

class MyCommand extends Command {
    public function execute(InputInterface $input, OutputInterface $output) {
        $output = new MultiplexedOutput([
            // Keep the original console output
            $output,
            // Add file output
            new NativeFileOutput('command.log'),
            // Add another file
            new NativeFileOutput('debug.log', NativeFileOutput::MODE_APPEND),
        ]);

        // All output now goes to all three destinations
        $output->writeln('This goes to console and both files');
    }
}
```

**Constructor:**
```php
MultiplexedOutput::__construct(array $outputs)
```

**Adding outputs dynamically:**
```php
$multiplexed = new MultiplexedOutput([$consoleOutput]);
$multiplexed->addOutput(new NativeFileOutput('added-file.log'));
```

## Use Cases

### Logging Command Output

```php
$output = new MultiplexedOutput([
    $consoleOutput,
    new NativeFileOutput('/var/log/commands.log', NativeFileOutput::MODE_APPEND)
]);
```

### Cloud Backup of CLI Output

```php
// Backup all command output to S3
$output = new MultiplexedOutput([
    $consoleOutput,
    new FlysystemFileOutput($s3Filesystem, 'backup/command-'.date('Y-m-d').'.log')
]);
```

### Debug vs Production Output

```php
$outputs = [$consoleOutput];

if ($debugMode) {
    $outputs[] = new NativeFileOutput('debug.log');
}

$output = new MultiplexedOutput($outputs);
```
