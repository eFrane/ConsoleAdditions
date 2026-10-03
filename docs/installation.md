---
layout: page
title: Installation
---

## Requirements

- PHP 8.0 or higher
- Composer

## Install via Composer

```bash
composer require efrane/console-additions
```

That's it! The package will be installed with its dependencies.

## Optional Dependencies

This package suggests some optional dependencies for additional features:

| Package | Purpose | Composer Requirement |
|---------|---------|---------------------|
| `symfony/process` | Shell command support in Batches | `^6.0 \|\| ^7.0` |
| `league/flysystem` | Flysystem file output adapter | `^2.0 \|\| ^3.0` |

Install them as needed:

```bash
# For shell command support
composer require symfony/process

# For cloud storage file outputs
composer require league/flysystem
```
