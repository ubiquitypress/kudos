<?php

/**
 *
 * Plugin for exporting data for ingestion by Kudos.
 * Written by Andy Byers, Ubiquity Press
 * Funded by INASP
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');
require_once('KudosDAO.inc.php');

class KudosPlugin extends GenericPlugin {
	function register($category, $path) {
		if(!parent::register($category, $path)) {
			return false;
		}
		if($this->getEnabled()) {
			HookRegistry::register("LoadHandler", array(&$this, "handleRequest"));
			$tm =& TemplateManager::getManager();
			$tm->assign("kudosEnabled", true);
			define('KUDOS_PLUGIN_NAME', $this->getName());
		}
		return true;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page == 'kudos') {
			$this->import('KudosHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'KudosHandler');
			return true;
		}
		return false;
	}

	function getDisplayName() {
		return "Kudos Export";
	}
	
	function getDescription() {
		return "Allows CSV files to be exported to the Kudos format.";
	}
	
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}


}
