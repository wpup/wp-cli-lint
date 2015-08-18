<?php

// Only run through WP CLI.
if ( ! defined( 'WP_CLI' ) ) {
	return;
}

/**
 * Lint command for WP CLI.
 */
class Lint_Command extends WP_CLI_Command {

	/**
	 * The default options for this command.
	 *
	 * @var array
	 */
	private $default_options = [
		'standard' => 'WordPress-Core'
	];

	/**
	 * Get config value from lint config.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	private function get_lint_config( $key ) {
		$config = WP_CLI::get_configurator()->to_array();

		if ( count( $config ) === 1 || ! isset( $config[1]['lint'] ) ) {
			return;
		}

		if ( ! isset( $config[1]['lint'][$key] ) ) {
			return;
		}

		return $config[1]['lint'][$key];
	}

	/**
	 * Get the PHPCS standard that should be used.
	 *
	 * @param string $root_path
	 * @param array $options
	 *
	 * @return string
	 */
	private function get_phpcs_standard( $root_path, array $options = [] ) {
		if ( $phpcs_standard = $this->get_lint_config( 'standard' ) ) {
			return $phpcs_standard;
		}

		if ( ! empty( $options['standard'] ) ) {
			return $options['standard'];
		}

		$paths = [
			getcwd(),
			rtrim( preg_replace( '/\/web\/wp$/', '', $root_path ), '/' ),
			$root_path,
			rtrim( $_SERVER['HOME'], '/' ) . '/.wp-cli',
		];

		$files = [
			'phpcs.xml',
			'phpcs.ruleset.xml',
			'ruleset.xml'
		];

		$phpcs_standard = $this->default_options['standard'];

		foreach ( $paths as $path ) {
			if ( empty( $path ) || ! file_exists( $path ) ) {
				continue;
			}

			foreach ( $files as $file ) {

				if ( file_exists( $path . '/' . $file ) ) {
					$phpcs_standard = $path . '/' . $file;
					break;
				}
			}
		}

		return $phpcs_standard;
	}

	/**
	 * Get PHPCS bin.
	 *
	 * @param string $root_path
	 *
	 * @return string
	 */
	private function get_phpcs_bin( $root_path ) {
		if ( $phpcs_bin = $this->get_lint_config( 'phpcs' ) ) {
			return $phpcs_bin;
		}

		$paths = [
			getcwd(),
			preg_replace( '/\/web\/wp$/', '', $root_path ),
			$root_path,
		];

		$file      = '/vendor/bin/phpcs';
		$phpcs_bin = 'phpcs';

		foreach ( $paths as $path ) {
			if ( empty( $path ) ) {
				continue;
			}

			if ( file_exists( $path . $file ) ) {
				$phpcs_bin = $path . $file;
				break;
			}
		}

		return $phpcs_bin;
	}

	/**
	 * Lint your WordPress code using WP CLI.
	 *
	 * ### Config
	 * You can add the path to the `phpcs` bin to use in WP CLI's config file and/or the standard that should be used.
	 *
	 * Example of `~/.wp-cli/config.yml`:
	 *
	 *     lint:
	 *       phpcs: /path/to/phpcs
	 *       standard: `WordPress-Extra`
	 *
	 * ### Options
	 *
	 * #### `[<directory>]`
	 * The directory to lint code in. **Default: '__DIR__'**
	 *
	 * #### `[--standard=<standard>]`
	 * The standard to use when running `phpcs`. **Default: 'WordPress-Core'**
	 *
	 * ### Examples
	 *
	 *     wp lint
	 *     wp lint path/to/code --standard=WordPress-Extra
	 *
	 * @param array $args
	 * @param array $options
	 *
	 * @when before_wp_load
	 */
	public function __invoke( array $args = [], array $options = [] ) {
		if ( empty( $args ) ) {
			$args[] = getcwd();
		}

		if ( ! file_exists( $args[0] ) ) {
			WP_CLI::error( sprintf( 'The file "%s" does not exist', $args[0] ) );
		}

		$root_path      = rtrim( ABSPATH, '/' );
		$phpcs_bin      = $this->get_phpcs_bin( $root_path );
		$phpcs_standard = $this->get_phpcs_standard( $root_path, $options );
		$command_args   = '-s --extensions=php --standard=' . $phpcs_standard;
		$command        = sprintf( '%s %s %s', $phpcs_bin, $command_args, $args[0] );

		if ( WP_CLI::get_config( 'debug' ) ) {
			echo sprintf( "Running command: %s \n", $command );
		}

		exec( $command, $output, $status );

		if ( count( $output ) === 1 && strpos( $output[0], 'ERROR' ) === false ) {
			$output = [];
		}

		foreach ( $output as $line ) {
			if ( strpos( $output[0], 'ERROR' ) === false ) {
				WP_CLI::log( $line );
			} else {
				WP_CLI::error( str_replace( 'ERROR: ', '', $line ) );
			}
		}

		if ( $status !== 0 ) {
			WP_CLI::error( 'Sorry, but your code does not follow the code style. Please fix before commit.' );
		} else {
			WP_CLI::success( 'Good job! Your code follows the code style.' );
		}
	}

}

\WP_CLI::add_command( 'lint', 'Lint_Command' );
