<?php

add_action('admin_menu', 'bam_add_admin_menu');
function bam_add_admin_menu() {

	add_management_page(
			__( 'Backup and Migrate', 'backupmigrate' ),
			__( 'Backup and Migrate', 'backupmigrate' ),
			'manage_options',
			'bam-admin-page',
			'bam_admin_page'
		);

}

function bam_admin_page() {

	echo '<div class="wrap">';
		echo '<h1>' . __( 'Backup and Migrate', 'backupmigrate' ) . '</h1>';

		echo '<form method="post">';
			echo echo wp_nonce_field( 'bam_simple_backup', 'bam_simple_backup' );
			submit_button( __( 'Backup Database', 'backupmigrate' ) );
		echo '</form>';

	echo '</div class="wrap">';

}
