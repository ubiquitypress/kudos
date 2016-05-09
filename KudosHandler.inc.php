<?php

/**
 *
 * Plugin for exporting data for ingestion by Kudos.
 * Written by Andy Byers, Ubiquity Press
 * Funded by INASP
 *
 */



import('classes.handler.Handler');
require_once('KudosDAO.inc.php');

function redirect($url) {
	header("Location: ". $url); // http://www.example.com/"); /* Redirect browser */
	/* Make sure that code below does not get executed when we redirect. */
	exit;
}

function raise404($msg='404 Not Found') {
	header("HTTP/1.0 404 Not Found");
	fatalError($msg);
	return;
}

function clean_string($v) {
	// strips non-alpha-numeric characters from $v	
	return preg_replace('/[^\-a-zA-Z0-9]+/', '',$v);
}

function login_required($user) {
	if ($user === NULL) {
		redirect($journal->getUrl() . '/login/signIn?source=' . $_SERVER['REQUEST_URI']);
	}
}

class KudosHandler extends Handler {

	public $dao = null;

	function KudosHandler() {
		parent::Handler();
		$this->dao = new KudosDAO();
	}
	
	/* sets up the template to be rendered */
	function display($fname, $page_context=array()) {
		// setup template
		AppLocale::requireComponents(LOCALE_COMPONENT_OJS_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
		parent::setupTemplate();
		
		// setup template manager
		$templateMgr =& TemplateManager::getManager();
		
		// default page values
		$context = array(
			"page_title" => "KUDOS"
		);
		foreach($page_context as $key => $val) {
			$context[$key] = $val;
		}

		$plugin =& PluginRegistry::getPlugin('generic', KUDOS_PLUGIN_NAME);
		$tp = $plugin->getTemplatePath();
		$context["template_path"] = $tp;
		$context["article_select_template"] = $tp . "article_select_snippet.tpl";
		$context["article_pagination_template"] = $tp . "article_pagination_snippet.tpl";
		$context["disableBreadCrumbs"] = true;
		$templateMgr->assign($context); // http://www.smarty.net/docsv2/en/api.assign.tpl

		// render the page
		$templateMgr->display($tp . $fname);
	}

	//
	// views
	//
	
	/* handles requests to:
		/kudos/
		/kudos/index/
	*/
	function index($args, &$request) {
	
		$context = array(
			"page_title" => "KUDOS Export",
		);
		$this->display('index.tpl', $context);
	}

	
}

?>