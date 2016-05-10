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

	function journal_manager_required($request) {
		$user = $request->getUser();
		$journal = $request->getJournal();

		// If we have no user, redirect to index
		if ($user == NULL) {
			$request->redirect(null, 'index');
		}

		// If we have a user, grab their roles from the DAO
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roles =& $roleDao->getRolesByUserId($user->getId(), $journal->getId());


		// Loop through the roles to check if the user is a Journal Manager
		$check = false;
		foreach ($roles as $role) {
			if ($role->getRoleId() == ROLE_ID_JOURNAL_MANAGER) {
				$check = true;
			}
		}

		// If user is a journal manager, return the user, if not, redirect to the user page.
		if ($check) {
			return $user;
		} else {
			$request->redirect(null, 'user');
		}

	}

	function build_csv_row($doi, $author) {
		if ($author->_data['middleName']) {
			$name = $author->_data['firstName'] . " " . $author->_data['middleName'] . " " . $author->_data['lastName'];
		} else {
			$name = $author->_data['firstName'] . " " . $author->_data['lastName'];
		}
		$orcid = $this->dao->get_orcid($author->_data['id']);

		if ($orcid) {
			$parts = explode("http://orcid.org/", $orcid);
			$orcid = $parts[1];
		}

		$record = array(
			$doi, $name, $author->_data['email'], $orcid
		);

		return $record;
	}

	function serve_csv($records, $filename = "export.csv", $delimiter=",") {
		// Generate a in memory CSV, add each line to it and serve.

	    $f = fopen('php://memory', 'w'); 
	   
	    foreach ($records as $line) { 
	        fputcsv($f, $line, $delimiter); 
	    }

	    fseek($f, 0);
	    header('Content-Type: application/csv');
	    header('Content-Disposition: attachment; filename="'.$filename.'";');
	    fpassthru($f);
	}

	function get_csv_data($pub_articles) {
		// Get each article for an issue, get their authors and generate a
		// row for each, and push it into the records array. Returns a 
		// CSV from serve_csv.

		$emails = $this->dao->get_excluded_emails();
		$errors = array();
		$records = array();

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		foreach ($pub_articles as $pub_article) {
			$article =& $articleDao->getArticle($pub_article['article_id']);
			$doi = $this->dao->get_doi($article->getId());
			if ($doi) {
				 $authors = $authorDao->getAuthorsBySubmissionId($article->getId());
				 foreach ($authors as $author) {
				 	$row = $this->build_csv_row($doi, $author);
				 	array_push($records, $row);
				 }
				 
			}
		}

		return $this->serve_csv($records, $filename = "export.csv", $delimiter=",");
	
	}

	//
	// views
	//
	
	/* handles requests to:
		/kudos/
		/kudos/index/
	*/
	function index($args, &$request) {

		$user = $this->journal_manager_required($request);
		$journal =& $request->getJournal();

		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issues =& $issueDao->getPublishedIssues($journal->getId());
	
		$context = array(
			"page_title" => "KUDOS Export",
			"issues" => $issues,
		);
		$this->display('index.tpl', $context);
	}

	function issue($args, &$request) {
		$user = $this->journal_manager_required($request);
		$journal =& $request->getJournal();

		$issue_id = $_GET['issue_id'];
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issue = $issueDao->getIssueById($issue_id, $journal->getId());

		$pub_articles = $this->dao->get_articles_for_issue($issue->getId());

		return $this->get_csv_data($pub_articles);
	}

	function email($args, &$request) {
		$user = $this->journal_manager_required($request);
		$journal =& $request->getJournal();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$address = $_POST["address"];

			if ($address) {
				$this->dao->save_address($address);
			}
		} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['email']) {
			$this->dao->delete_address($_GET['email']);
		}

		$emails = $this->dao->get_excluded_emails();

		$context = array(
			"page_title" => "Exclude Email from KUDOS Export",
			"emails" => $emails,
		);
		$this->display('email.tpl', $context);
	}

	
}

?>