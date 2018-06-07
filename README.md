# Calibre Checker

Check the following things:

- Each book has an `epub` file
- The `epub` file can be read

## Usage

```
Usage:
  app:check <library>

Arguments:
  library               Library path.

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The Environment name. [default: "dev"]
      --no-debug        Switches off debug mode.
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

Exemple:

```
php bin/console app:check "/home/dmartin/Documents/Calibre/Perry Rhodan"
```
