<?php

add_action('admin_menu', 'bam_add_admin_menu');
function bam_add_admin_menu() {

	add_management_page(
			__( 'Backup and Migrate', 'bam' ),
			__( 'Backup and Migrate', 'bam' ),
			'manage_options',
			'bam-admin-page',
			'bam_admin_page'
		);

}

function bam_admin_page() {

	echo '<div class="wrap">';
		echo '<h1>' . __( 'Backup and Migrate', 'bam' ) . '</h1>';
	echo '</div class="wrap">';

}
