<?php

/**
 *
 * Plugin for exporting data for ingestion by Kudos.
 * Written by Andy Byers, Ubiquity Press
 * Funded by INASP
 *
 */


class KudosDAO extends DAO {

	function get_excluded_emails() {
		$sql = <<< EOF
			SELECT * FROM kudos_emails;
EOF;
		return $this->retrieve($sql);
	}

	function save_address($address) {
		$sql = <<< EOF
			INSERT INTO kudos_emails
			(email_address)
			VALUES
			(?)
EOF;
		$commit = $this->update($sql, array($address));

		return $commit;
	}

	function delete_address($email) {
		$sql = <<< EOF
			DELETE FROM kudos_emails
			WHERE id = ?
EOF;
		$commit = $this->update($sql, array($email));

		return $commit;
	}

}

