<?php
/**
 * Require files that deal with various plugin integrations.
 *
 * @package Gema
 */

/**
 * Load Pixelgrade Care compatibility file.
 */
require trailingslashit( get_template_directory() ) . 'inc/integrations/pixelgrade-care.php';

/**
 * Load theme's configuration file (via Customify plugin)
 */
require trailingslashit( get_template_directory() ) . 'inc/integrations/customify.php';

/**
 * Admin Dashboard logic.
 */
require trailingslashit( get_template_directory() ) . 'inc/admin/admin.php'; // phpcs:ignore
