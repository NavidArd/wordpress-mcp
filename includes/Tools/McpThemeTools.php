<?php
declare( strict_types=1 );

namespace Automattic\WordpressMcp\Tools;

use Automattic\WordpressMcp\Core\RegisterMcpTool;

/**
 * Class for managing MCP Theme Development Tools functionality.
 */
class McpThemeTools {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wordpress_mcp_init', array( $this, 'register_tools' ) );
	}

	/**
	 * Register the tools.
	 */
	public function register_tools(): void {
		new RegisterMcpTool(
			array(
				'name'                => 'wp_list_themes',
				'description'         => 'List all installed WordPress themes',
				'type'                => 'read',
				'callback'            => array( $this, 'list_themes' ),
				'permission_callback' => array( $this, 'can_manage_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => new \stdClass(),
					'required'   => array(),
				),
				'annotations'         => array(
					'title'         => 'List Themes',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_get_active_theme',
				'description'         => 'Get information about the currently active theme',
				'type'                => 'read',
				'callback'            => array( $this, 'get_active_theme' ),
				'permission_callback' => array( $this, 'can_manage_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => new \stdClass(),
					'required'   => array(),
				),
				'annotations'         => array(
					'title'         => 'Get Active Theme',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_switch_theme',
				'description'         => 'Switch to a different installed theme',
				'type'                => 'update',
				'callback'            => array( $this, 'switch_theme' ),
				'permission_callback' => array( $this, 'can_manage_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'theme' => array(
							'type'        => 'string',
							'description' => 'Theme directory name (slug)',
						),
					),
					'required'   => array( 'theme' ),
				),
				'annotations'         => array(
					'title'           => 'Switch Theme',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_edit_theme_file',
				'description'         => 'Edit a theme file (CSS, PHP, JS, etc.)',
				'type'                => 'update',
				'callback'            => array( $this, 'edit_theme_file' ),
				'permission_callback' => array( $this, 'can_edit_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'theme' => array(
							'type'        => 'string',
							'description' => 'Theme slug (leave empty for active theme)',
						),
						'file' => array(
							'type'        => 'string',
							'description' => 'File path relative to theme (e.g., style.css, functions.php)',
						),
						'content' => array(
							'type'        => 'string',
							'description' => 'New file content',
						),
					),
					'required'   => array( 'file', 'content' ),
				),
				'annotations'         => array(
					'title'           => 'Edit Theme File',
					'readOnlyHint'    => false,
					'destructiveHint' => true,
					'idempotentHint'  => true,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_create_theme',
				'description'         => 'Create a new WordPress theme based on specifications',
				'type'                => 'create',
				'callback'            => array( $this, 'create_theme' ),
				'permission_callback' => array( $this, 'can_manage_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'name' => array(
							'type'        => 'string',
							'description' => 'Theme name',
						),
						'description' => array(
							'type'        => 'string',
							'description' => 'Theme description',
						),
						'template' => array(
							'type'        => 'string',
							'description' => 'Base template: minimal, blog, business, portfolio',
							'default'     => 'minimal',
						),
						'colors' => array(
							'type'        => 'object',
							'description' => 'Color scheme',
							'properties'  => array(
								'primary'   => array( 'type' => 'string', 'default' => '#0073aa' ),
								'secondary' => array( 'type' => 'string', 'default' => '#23282d' ),
								'background' => array( 'type' => 'string', 'default' => '#ffffff' ),
								'text'      => array( 'type' => 'string', 'default' => '#333333' ),
							),
						),
						'activate' => array(
							'type'        => 'boolean',
							'description' => 'Activate theme after creation',
							'default'     => true,
						),
					),
					'required'   => array( 'name', 'description' ),
				),
				'annotations'         => array(
					'title'           => 'Create Theme',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_read_theme_file',
				'description'         => 'Read content of a theme file',
				'type'                => 'read',
				'callback'            => array( $this, 'read_theme_file' ),
				'permission_callback' => array( $this, 'can_edit_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'theme' => array(
							'type'        => 'string',
							'description' => 'Theme slug (leave empty for active theme)',
						),
						'file' => array(
							'type'        => 'string',
							'description' => 'File path relative to theme (e.g., style.css)',
						),
					),
					'required'   => array( 'file' ),
				),
				'annotations'         => array(
					'title'         => 'Read Theme File',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_list_theme_files',
				'description'         => 'List all files in a theme directory',
				'type'                => 'read',
				'callback'            => array( $this, 'list_theme_files' ),
				'permission_callback' => array( $this, 'can_edit_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'theme' => array(
							'type'        => 'string',
							'description' => 'Theme slug (leave empty for active theme)',
						),
						'directory' => array(
							'type'        => 'string',
							'description' => 'Subdirectory to list (optional)',
						),
					),
				),
				'annotations'         => array(
					'title'         => 'List Theme Files',
					'readOnlyHint'  => true,
					'openWorldHint' => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_create_child_theme',
				'description'         => 'Create a child theme based on a parent theme',
				'type'                => 'create',
				'callback'            => array( $this, 'create_child_theme' ),
				'permission_callback' => array( $this, 'can_manage_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'parent' => array(
							'type'        => 'string',
							'description' => 'Parent theme slug',
						),
						'name' => array(
							'type'        => 'string',
							'description' => 'Child theme name',
						),
						'description' => array(
							'type'        => 'string',
							'description' => 'Child theme description',
						),
						'activate' => array(
							'type'        => 'boolean',
							'description' => 'Activate after creation',
							'default'     => true,
						),
					),
					'required'   => array( 'parent', 'name' ),
				),
				'annotations'         => array(
					'title'           => 'Create Child Theme',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'wp_add_theme_template',
				'description'         => 'Add a template file to theme (page.php, single.php, archive.php, etc.)',
				'type'                => 'create',
				'callback'            => array( $this, 'add_theme_template' ),
				'permission_callback' => array( $this, 'can_edit_themes' ),
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'theme' => array(
							'type'        => 'string',
							'description' => 'Theme slug (leave empty for active theme)',
						),
						'template' => array(
							'type'        => 'string',
							'description' => 'Template type: page, single, archive, search, 404, category, tag, author, date, custom',
						),
						'name' => array(
							'type'        => 'string',
							'description' => 'Template name (for custom templates)',
						),
						'content' => array(
							'type'        => 'string',
							'description' => 'Custom template content (optional, uses default if empty)',
						),
					),
					'required'   => array( 'template' ),
				),
				'annotations'         => array(
					'title'           => 'Add Theme Template',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => false,
					'openWorldHint'   => false,
				),
			)
		);
	}

	/**
	 * List all themes.
	 */
	public function list_themes(): array {
		$themes = wp_get_themes();
		$theme_list = array();
		
		foreach ( $themes as $theme_slug => $theme ) {
			$theme_list[] = array(
				'name'        => $theme->get( 'Name' ),
				'slug'        => $theme_slug,
				'version'     => $theme->get( 'Version' ),
				'author'      => $theme->get( 'Author' ),
				'description' => $theme->get( 'Description' ),
				'active'      => ( get_stylesheet() === $theme_slug ),
			);
		}
		
		return array( 'themes' => $theme_list );
	}

	/**
	 * Get active theme info.
	 */
	public function get_active_theme(): array {
		$theme = wp_get_theme();
		
		return array(
			'name'        => $theme->get( 'Name' ),
			'slug'        => get_stylesheet(),
			'version'     => $theme->get( 'Version' ),
			'author'      => $theme->get( 'Author' ),
			'author_uri'  => $theme->get( 'AuthorURI' ),
			'description' => $theme->get( 'Description' ),
			'theme_uri'   => $theme->get( 'ThemeURI' ),
			'screenshot'  => $theme->get_screenshot(),
			'parent'      => $theme->parent() ? $theme->parent()->get( 'Name' ) : null,
		);
	}

	/**
	 * Switch theme.
	 */
	public function switch_theme( array $params ): array {
		$theme_slug = sanitize_text_field( $params['theme'] );
		$theme = wp_get_theme( $theme_slug );
		
		if ( ! $theme->exists() ) {
			throw new \Exception( 'Theme not found' );
		}
		
		switch_theme( $theme_slug );
		
		return array(
			'success' => true,
			'message' => sprintf( 'Switched to theme: %s', $theme->get( 'Name' ) ),
			'theme'   => $theme_slug,
		);
	}

	/**
	 * Create a new theme.
	 */
	public function create_theme( array $params ): array {
		$name = sanitize_text_field( $params['name'] );
		$slug = sanitize_title( $name );
		$description = sanitize_text_field( $params['description'] );
		$template = $params['template'] ?? 'minimal';
		$colors = $params['colors'] ?? array();
		
		// Create theme directory
		$theme_dir = get_theme_root() . '/' . $slug;
		if ( file_exists( $theme_dir ) ) {
			throw new \Exception( 'Theme directory already exists' );
		}
		
		wp_mkdir_p( $theme_dir );
		
		// Create style.css
		$style_css = $this->generate_style_css( $name, $description, $colors );
		file_put_contents( $theme_dir . '/style.css', $style_css );
		
		// Create functions.php
		$functions_php = $this->generate_functions_php( $slug, $template );
		file_put_contents( $theme_dir . '/functions.php', $functions_php );
		
		// Create index.php
		$index_php = $this->generate_index_php( $template );
		file_put_contents( $theme_dir . '/index.php', $index_php );
		
		// Create header.php
		$header_php = $this->generate_header_php( $name );
		file_put_contents( $theme_dir . '/header.php', $header_php );
		
		// Create footer.php
		$footer_php = $this->generate_footer_php();
		file_put_contents( $theme_dir . '/footer.php', $footer_php );
		
		// Activate if requested
		if ( ! empty( $params['activate'] ) ) {
			switch_theme( $slug );
		}
		
		return array(
			'success' => true,
			'message' => sprintf( 'Theme "%s" created successfully', $name ),
			'theme'   => array(
				'name' => $name,
				'slug' => $slug,
				'path' => $theme_dir,
				'activated' => ! empty( $params['activate'] ),
			),
		);
	}

	/**
	 * Generate style.css content.
	 */
	private function generate_style_css( string $name, string $description, array $colors ): string {
		$primary = $colors['primary'] ?? '#0073aa';
		$secondary = $colors['secondary'] ?? '#23282d';
		$bg = $colors['background'] ?? '#ffffff';
		$text = $colors['text'] ?? '#333333';
		
		return "/*
Theme Name: {$name}
Description: {$description}
Version: 1.0.0
License: GPL v2 or later
Text Domain: " . sanitize_title( $name ) . "
*/

/* Basic Reset */
* { margin: 0; padding: 0; box-sizing: border-box; }

/* Typography */
body {
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	line-height: 1.6;
	color: {$text};
	background: {$bg};
}

/* Layout */
.container {
	max-width: 1200px;
	margin: 0 auto;
	padding: 0 20px;
}

/* Header */
.site-header {
	background: {$primary};
	color: white;
	padding: 1rem 0;
}

.site-title {
	font-size: 2rem;
	margin: 0;
}

.site-title a {
	color: white;
	text-decoration: none;
}

/* Navigation */
.main-navigation ul {
	list-style: none;
	display: flex;
	gap: 20px;
}

.main-navigation a {
	color: white;
	text-decoration: none;
}

/* Content */
.site-content {
	padding: 2rem 0;
	min-height: 60vh;
}

.entry-title {
	color: {$primary};
	margin-bottom: 1rem;
}

/* Footer */
.site-footer {
	background: {$secondary};
	color: white;
	padding: 2rem 0;
	text-align: center;
}

/* WordPress Classes */
.alignleft { float: left; margin-right: 1rem; }
.alignright { float: right; margin-left: 1rem; }
.aligncenter { display: block; margin: 0 auto; }
.wp-caption { max-width: 100%; }
.wp-caption-text { font-size: 0.9em; color: #666; }";
	}

	/**
	 * Generate functions.php content.
	 */
	private function generate_functions_php( string $slug, string $template ): string {
		return "<?php
/**
 * Theme functions and definitions
 */

// Theme setup
function {$slug}_setup() {
	// Add theme support
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	
	// Register navigation menu
	register_nav_menus( array(
		'primary' => 'Primary Menu',
	) );
}
add_action( 'after_setup_theme', '{$slug}_setup' );

// Enqueue styles
function {$slug}_scripts() {
	wp_enqueue_style( '{$slug}-style', get_stylesheet_uri(), array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', '{$slug}_scripts' );

// Widget areas
function {$slug}_widgets_init() {
	register_sidebar( array(
		'name'          => 'Sidebar',
		'id'            => 'sidebar-1',
		'before_widget' => '<div class=\"widget %2\$s\">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class=\"widget-title\">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', '{$slug}_widgets_init' );";
	}

	/**
	 * Generate index.php content.
	 */
	private function generate_index_php( string $template ): string {
		return "<?php get_header(); ?>

<main class=\"site-content\">
	<div class=\"container\">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article id=\"post-<?php the_ID(); ?>\" <?php post_class(); ?>>
					<h2 class=\"entry-title\">
						<a href=\"<?php the_permalink(); ?>\"><?php the_title(); ?></a>
					</h2>
					<div class=\"entry-content\">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
			
			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<p>No posts found.</p>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>";
	}

	/**
	 * Generate header.php content.
	 */
	private function generate_header_php( string $name ): string {
		return "<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset=\"<?php bloginfo( 'charset' ); ?>\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class=\"site-header\">
	<div class=\"container\">
		<h1 class=\"site-title\">
			<a href=\"<?php echo esc_url( home_url( '/' ) ); ?>\"><?php bloginfo( 'name' ); ?></a>
		</h1>
		<p class=\"site-description\"><?php bloginfo( 'description' ); ?></p>
		
		<nav class=\"main-navigation\">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'fallback_cb' => false ) ); ?>
		</nav>
	</div>
</header>";
	}

	/**
	 * Generate footer.php content.
	 */
	private function generate_footer_php(): string {
		return "<footer class=\"site-footer\">
	<div class=\"container\">
		<p>&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>";
	}

	/**
	 * Permission callback.
	 */
	public function can_manage_themes(): bool {
		return current_user_can( 'switch_themes' );
	}

public function edit_theme_file( array $params ): array {
    $theme_slug = $params['theme'] ?? get_stylesheet();
    $file = sanitize_text_field( $params['file'] );
    $content = $params['content'];
    
    $theme = wp_get_theme( $theme_slug );
    if ( ! $theme->exists() ) {
        throw new \Exception( 'Theme not found' );
    }
    
    $file_path = get_theme_root() . '/' . $theme_slug . '/' . $file;
    
    // Security check
    $allowed = array( '.php', '.css', '.js', '.json', '.html', '.txt' );
    if ( ! $this->has_allowed_extension( $file_path, $allowed ) ) {
        throw new \Exception( 'File type not allowed' );
    }
    
    // Backup original
    if ( file_exists( $file_path ) ) {
        copy( $file_path, $file_path . '.backup-' . time() );
    }
    
    file_put_contents( $file_path, $content );
    
    return array(
        'success' => true,
        'message' => sprintf( 'File %s updated in theme %s', $file, $theme->get( 'Name' ) ),
        'file' => $file,
        'theme' => $theme_slug,
    );
}

private function has_allowed_extension( string $file, array $allowed ): bool {
    foreach ( $allowed as $ext ) {
        if ( substr( $file, -strlen( $ext ) ) === $ext ) {
            return true;
        }
    }
    return false;
}

public function can_edit_themes(): bool {
    return current_user_can( 'edit_themes' );
}

public function read_theme_file( array $params ): array {
    $theme_slug = $params['theme'] ?? get_stylesheet();
    $file = sanitize_text_field( $params['file'] );
    
    $theme = wp_get_theme( $theme_slug );
    if ( ! $theme->exists() ) {
        throw new \Exception( 'Theme not found' );
    }
    
    $file_path = get_theme_root() . '/' . $theme_slug . '/' . $file;
    
    if ( ! file_exists( $file_path ) ) {
        throw new \Exception( 'File not found' );
    }
    
    $content = file_get_contents( $file_path );
    
    return array(
        'content' => $content,
        'file' => $file,
        'theme' => $theme_slug,
        'size' => filesize( $file_path ),
        'modified' => date( 'Y-m-d H:i:s', filemtime( $file_path ) ),
    );
}

public function list_theme_files( array $params ): array {
    $theme_slug = $params['theme'] ?? get_stylesheet();
    $directory = $params['directory'] ?? '';
    
    $theme = wp_get_theme( $theme_slug );
    if ( ! $theme->exists() ) {
        throw new \Exception( 'Theme not found' );
    }
    
    $theme_dir = get_theme_root() . '/' . $theme_slug;
    $scan_dir = $directory ? $theme_dir . '/' . $directory : $theme_dir;
    
    if ( ! is_dir( $scan_dir ) ) {
        throw new \Exception( 'Directory not found' );
    }
    
    $files = array();
    $items = scandir( $scan_dir );
    
    foreach ( $items as $item ) {
        if ( $item === '.' || $item === '..' ) {
            continue;
        }
        
        $path = $scan_dir . '/' . $item;
        $relative_path = $directory ? $directory . '/' . $item : $item;
        
        $files[] = array(
            'name' => $item,
            'path' => $relative_path,
            'type' => is_dir( $path ) ? 'directory' : 'file',
            'size' => is_file( $path ) ? filesize( $path ) : null,
            'modified' => date( 'Y-m-d H:i:s', filemtime( $path ) ),
        );
    }
    
    return array(
        'theme' => $theme_slug,
        'directory' => $directory,
        'files' => $files,
    );
}

public function create_child_theme( array $params ): array {
    $parent_slug = sanitize_text_field( $params['parent'] );
    $name = sanitize_text_field( $params['name'] );
    $description = $params['description'] ?? "Child theme of {$parent_slug}";
    $child_slug = sanitize_title( $name );
    
    $parent_theme = wp_get_theme( $parent_slug );
    if ( ! $parent_theme->exists() ) {
        throw new \Exception( 'Parent theme not found' );
    }
    
    $child_dir = get_theme_root() . '/' . $child_slug;
    if ( file_exists( $child_dir ) ) {
        throw new \Exception( 'Child theme directory already exists' );
    }
    
    wp_mkdir_p( $child_dir );
    
    // Create style.css
    $style_css = "/*
Theme Name: {$name}
Description: {$description}
Author: " . wp_get_current_user()->display_name . "
Template: {$parent_slug}
Version: 1.0.0
*/

/* Import parent theme styles */
@import url('../{$parent_slug}/style.css');

/* Child theme customizations */
";
    
    file_put_contents( $child_dir . '/style.css', $style_css );
    
    // Create functions.php
    $functions_php = "<?php
/**
 * {$name} functions and definitions
 */

// Enqueue parent theme styles
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});";
    
    file_put_contents( $child_dir . '/functions.php', $functions_php );
    
    // Copy screenshot if exists
    $screenshot = $parent_theme->get_screenshot( 'relative' );
    if ( $screenshot ) {
        $parent_screenshot = get_theme_root() . '/' . $parent_slug . '/' . $screenshot;
        if ( file_exists( $parent_screenshot ) ) {
            copy( $parent_screenshot, $child_dir . '/' . basename( $screenshot ) );
        }
    }
    
    if ( ! empty( $params['activate'] ) ) {
        switch_theme( $child_slug );
    }
    
    return array(
        'success' => true,
        'message' => sprintf( 'Child theme "%s" created', $name ),
        'theme' => array(
            'name' => $name,
            'slug' => $child_slug,
            'parent' => $parent_slug,
            'activated' => ! empty( $params['activate'] ),
        ),
    );
}

public function add_theme_template( array $params ): array {
    $theme_slug = $params['theme'] ?? get_stylesheet();
    $template_type = sanitize_text_field( $params['template'] );
    $custom_name = $params['name'] ?? '';
    $custom_content = $params['content'] ?? '';
    
    $theme = wp_get_theme( $theme_slug );
    if ( ! $theme->exists() ) {
        throw new \Exception( 'Theme not found' );
    }
    
    $theme_dir = get_theme_root() . '/' . $theme_slug;
    
    // Determine filename
    $filename = match( $template_type ) {
        'page' => 'page.php',
        'single' => 'single.php',
        'archive' => 'archive.php',
        'search' => 'search.php',
        '404' => '404.php',
        'category' => 'category.php',
        'tag' => 'tag.php',
        'author' => 'author.php',
        'date' => 'date.php',
        'custom' => 'template-' . sanitize_title( $custom_name ) . '.php',
        default => throw new \Exception( 'Invalid template type' ),
    };
    
    $file_path = $theme_dir . '/' . $filename;
    
    if ( file_exists( $file_path ) ) {
        throw new \Exception( 'Template file already exists' );
    }
    
    // Generate content if not provided
    if ( empty( $custom_content ) ) {
        $custom_content = $this->generate_template_content( $template_type, $custom_name );
    }
    
    file_put_contents( $file_path, $custom_content );
    
    return array(
        'success' => true,
        'message' => sprintf( 'Template %s created', $filename ),
        'file' => $filename,
        'theme' => $theme_slug,
    );
}

private function generate_template_content( string $type, string $name = '' ): string {
    $header = "<?php\n/**\n * Template: " . ucfirst( $type ) . "\n";
    if ( $type === 'custom' && $name ) {
        $header .= " * Template Name: {$name}\n";
    }
    $header .= " */\n\nget_header(); ?>\n\n";
    
    $footer = "\n\n<?php get_footer(); ?>";
    
    $content = match( $type ) {
        'page' => '<main class="site-main">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>',
        'single' => '<main class="site-main">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-meta">
                Posted on <?php echo get_the_date(); ?> by <?php the_author(); ?>
            </div>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>',
        'archive' => '<main class="site-main">
    <h1 class="archive-title"><?php the_archive_title(); ?></h1>
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
        <?php the_posts_navigation(); ?>
    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>
</main>',
        '404' => '<main class="site-main">
    <h1>404 - Page Not Found</h1>
    <p>Sorry, the page you are looking for does not exist.</p>
    <a href="<?php echo home_url(); ?>">Return to Homepage</a>
</main>',
        'search' => '<main class="site-main">
    <h1>Search Results for: <?php echo get_search_query(); ?></h1>
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p>No results found.</p>
    <?php endif; ?>
</main>',
        default => '<main class="site-main">
    <!-- Add your template content here -->
</main>',
    };
    
    return $header . $content . $footer;
}
}