<?php
namespace OWC\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class Options {

	/*
	|-----------------------------------------------------------
	| PROPERTIES
	|-----------------------------------------------------------
	*/

	private $prefix  = 'owc';
	private $label   = 'Options';
	private $options = array();

	/*
	|-----------------------------------------------------------
	| CONSTRUCTOR
	|-----------------------------------------------------------
	*/

	public function __construct( $prefix = 'owc', $label = 'Options', $options = array() ) {
		$this->prefix  = $prefix;
		$this->label   = $label;
		$this->options = $options;

		// admin actions
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		$this->settings = get_option( $this->prefix );
	}

	/*
	|-----------------------------------------------------------
	| ACTIONS
	|-----------------------------------------------------------
	*/

	// admin_init
	public function admin_init() {
		register_setting( $this->prefix . '_options', $this->prefix );

		foreach ( $this->options as $section_key => $section ) {
			add_settings_section(
				$section_key,
				$section['title'],
				array( $this, 'section' ),
				$this->prefix
			);
			if ( is_array( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field_key => $field ) {
					$options = array();
					if ( isset( $field['options'] ) ) {
						if ( isset( $field['option_key'] ) && isset( $field['option_value'] ) ) {
							foreach ( $field['options'] as $obj ) {
								$obj = (array)$obj;
								$key = $obj[ $field['option_key'] ];
								$value = $obj[ $field['option_value'] ];
								$options[ $key ] = $value;
							}
						} else {
							$options = $field['options'];
						}
					}

					add_settings_field(
						$field_key,
						$field['title'],
						array( $this, 'input_' . $field['type'] ),
						$this->prefix,
						$section_key,
						array(
							'name'  => $this->prefix . '[' . $field_key . ']',
							'value' => isset( $this->settings[$field_key] )
								? $this->settings[$field_key]
								: $field['value'],
							'options' => $options,
							'label'	=> isset( $field['title'] ) ? $field['title'] : ''
						)
					);
				}
			}
		}
	}

	// admin_menu
	public function admin_menu() {
		add_options_page( $this->label, $this->label, 'manage_options', $this->prefix . '_options', array( $this, 'admin_page' ) );
	}

	/*
	|-----------------------------------------------------------
	| TEMPLATES
	|-----------------------------------------------------------
	*/

	public function admin_page() {
	?>
		<div class="wrap">
			<h2><?php echo $this->label; ?></h2>
			<form action="options.php" method="POST">
				<?php settings_fields( $this->prefix . '_options' ); ?>
				<?php do_settings_sections( $this->prefix ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}

	public function section( $args ) {
		if ( isset( $this->options[$args['id']]['description'] ) ) {
			echo '<p>' . $this->options[$args['id']]['description'] . '</p>';
		}
	}

	/*
	|-----------------------------------------------------------
	| INPUTS
	|-----------------------------------------------------------
	*/

	public function input_text( $args ) {
		$name  = esc_attr( $args['name'] );
		$value = esc_attr( $args['value'] );
		echo "<input type='text' name='$name' value='$value' />";
	}

	public function input_textarea( $args ) {
		$name  = esc_attr( $args['name'] );
		$value = esc_attr( $args['value'] );
		echo "<textarea name='$name'>$value</textarea>";
	}

	public function input_page( $args ) {
		$name  = esc_attr( $args['name'] );
		$value = esc_attr( $args['value'] );
		$posts = get_posts( 'post_type=page&posts_per_page=-1' );

		echo '<select name="' . $name . '">';
		echo '<option value="0">' . __( 'Select a page', 'owc' ) . '</option>';
		foreach ($posts as $post) {
			echo '<option value="' . $post->ID . '"' . selected( $value, $post->ID ) . '>' . $post->post_title . '</option>';
		}
		echo '</select>';
	}

	public function input_select( $args ) {
		$name  = esc_attr( $args['name'] );
		$value = esc_attr( $args['value'] );

		echo '<select name="' . $name . '">';
		echo '<option value="0">' . sprintf( __( 'Select a %s', 'owc' ), esc_attr( $args['label'] ) ) . '</option>';
		foreach ($args['options'] as $key => $name ) {
			echo '<option value="' . esc_attr( $key ) . '"' . selected( $value, $key ) . '>' . esc_attr( $name ) . '</option>';
		}
		echo '</select>';
	}

	/*
	|-----------------------------------------------------------
	| METHODS
	|-----------------------------------------------------------
	*/

	// meta methods
	public function get_meta($post_id, $key ) {
		return get_post_meta( $post_id, $this->prefix . '_' . $key );
	}
	public function update_meta( $post_id, $key, $value ) {
		return update_post_meta( $post_id, $this->prefix . '_' . $key, $value );
	}
	public function delete_meta( $post_id, $key ) {
		return delete_post_meta( $post_id, $this->prefix . '_' . $key );
	}

}
