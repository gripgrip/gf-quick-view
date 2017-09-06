<?php
/*
Plugin Name: Gravity Forms quick view
Plugin URI: https://mircian.com/
Description: Quickly preview Gravity Forms entries.
Author: Mircian
Version: 1.0.0
Author URI: https://mircian.com
Text Domain: gfqv
License: GPL2

Gf-quick-view is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Gf-quick-view is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Gf-quick-view. If not, see http://www.gnu.org/licenses/gpl.html.
*/

class GF_Quick_View {

	/**
	 * @var string
	 */
	public $plugin_file;
	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * GF_Quick_View constructor.
	 */
	public function __construct() {

		$this->plugin_file = __FILE__;
		$this->plugin_url  = plugin_dir_url( $this->plugin_file );

		add_action( 'gform_entries_first_column_actions', array( $this, 'add_quick_view_button' ), 15, 5 );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'wp_ajax_gfqv_get_entry_data', array( $this, 'get_entry_data' ) );

		add_action( 'gform_post_entry_list', array( $this, 'table_markup' ) );
	}

	/**
	 * Add the button to the entries table.
	 *
	 * @param int $form_id The ID of the form that the entry is associated with
	 * @param int $field_id The ID of the field
	 * @param string $value The value of the field
	 * @param array $lead The Entry object
	 * @param string $query_string The query string used on the current page
	 */
	public function add_quick_view_button( $form_id, $field_id, $value, $lead, $query_string ) {

		$button_html = '<span class="quick-look"> | ';
		$button_html .= '<a data-entry="' . absint( $lead['id'] ) . '" href="#" title="' . esc_html__( 'Quick preview of this entry', 'gfqv' ) . '">
						' . esc_html__( 'Quick look', 'gfqv' ) . '</a>';
		$button_html .= '</span>';

		echo wp_kses( $button_html, array(
			'span' => array(
				'class' => array(),
			),
			'a'    => array(
				'href'       => array(),
				'title'      => array(),
				'class'      => array(),
				'data'       => array(),
				'rel'        => array(),
				'data-entry' => array(),
			),
		) );

	}

	/**
	 * Load the plugin scripts only in the proper page.
	 *
	 * @param string $hook The current page hook.
	 */
	public function load_scripts( $hook ) {

		// This might change
		if ( 'forms1_page_gf_entries' === $hook ) {

			wp_enqueue_script( 'gfqv_main_js', $this->plugin_url . 'assets/js/gfqv.js', array( 'jquery' ), '1.0.0', true );

			wp_enqueue_style( 'gfqv_main_css', $this->plugin_url . 'assets/css/gfqv.css', array(), '1.0.0' );

		}

	}

	/**
	 * Retrieve the entry data.
	 */
	public function get_entry_data() {

		// Bail out fast if the GF class doesn't exist.
		if ( ! class_exists( 'RGFormsModel' ) ) {
			return false;
		}

		$entry_id = empty( $_POST['entry_id'] ) ? 0 : absint( $_POST['entry_id'] );

		if ( $entry_id > 0 ) {

			// Retrieve the entry data.
			$entry_data = GFAPI::get_entry( $entry_id );

			wp_send_json( $entry_data );

		}

		wp_die();

	}

	/**
	 * @param int $form_id The id of the form for which the entries are loaded.
	 */
	public function table_markup( $form_id ) {

		$form = GFAPI::get_form( $form_id );

		?>
		<div class="gfqv-entry-container" id="gfqv-container">
			<div class="gfqv-entry-content">
				<a href="#" id="gfqv-close" class="gfqv-close"><span class="dashicons dashicons-no"></span></a>
				<ul>
					<?php
					foreach ( $form['fields'] as $field ) {
						/**
						 * @var GF_Field $field
						 */
						?>
						<li class="gfqv-entry-data">
							<span class="label"><?php echo esc_html( $field->get_field_label( true, '' ) ); ?></span>
							<span class="entry-data"></span>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php

	}
}

new GF_Quick_View();
