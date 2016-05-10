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

	function get_articles_for_issue($issue_id) {
		$sql = <<< EOF
			SELECT * FROM published_articles
			WHERE issue_id = ?;
EOF;
		return $this->retrieve($sql, array($issue_id));
	}

	function get_doi($article_id) {
		$sql = <<< EOF
			SELECT setting_value FROM article_settings
			WHERE setting_name = "pub-id::doi" and article_id = ?;
EOF;
		$setting = $this->retrieve($sql, array($article_id));
		return $setting->fields['setting_value'];
	}

	function get_orcid($article_id) {
		$sql = <<< EOF
			SELECT setting_value FROM author_settings
			WHERE setting_name = "orcid" and author_id = ?;
EOF;
		$setting = $this->retrieve($sql, array($article_id));
		return $setting->fields['setting_value'];
	}

}

