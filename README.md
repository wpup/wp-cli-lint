# wp-cli-lint

[![Build Status](https://travis-ci.org/frozzare/wp-cli-lint.svg)](https://travis-ci.org/frozzare/wp-cli-lint)

Lint your WordPress code using WP CLI.

## Installation

Require this file in your global config file or add it to your project.

Example of `~/.wp-cli/config.yml`:
```yaml
require:
	- /path/to/wp-cli-lint/src/class-lint-command.php
```

For other methods, please refer to WP-CLI's [Community Packages](https://github.com/wp-cli/wp-cli/wiki/Community-Packages) wiki.

## Usage

Running the command without any options will lint your code in the current directory with `WordPress-Core` standard.

Example:

```
wp lint
wp lint path/to/code
```

### Config

You can add the path to the `phpcs` bin to use in WP CLI's config file and/or the standard that should be used.

Example of `~/.wp-cli/config.yml`:

```yaml
lint:
  phpcs: /path/to/phpcs
  standard: WordPress-Extra
```

### Options

#### `[<directory>]`
The directory to lint code in. **Default: 'Current directory'**

#### `[--standard=<standard>]`
The standard to use when running `phpcs`. **Default: 'WordPress-Core'**

### Examples
```
wp lint
wp lint path/to/code --standard=WordPress-Extra
```
