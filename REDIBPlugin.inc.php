<?php

/**
 * @file plugins/generic/REDIB/REDIBPlugin.inc.php
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class REDIBPlugin
 * @ingroup plugins_generic_REDIB
 *
 * @brief REDIB plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');


class REDIBPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		//$this->addLocaleData();
		if ($success && $this->getEnabled()) {
			 // Change Dc11Desctiption -- include REDIB references
			HookRegistry::register('Dc11SchemaArticleAdapter::extractMetadataFromDataObject', array($this, 'changeDc11Description'));
		}
		return $success;
	}

	function getDisplayName() {
		return 'REDIB Plugin';
	}

	function getDescription() {
		return 'Add REDIB bibliographic references to DC11 metadata';
	}

	/**
	 * Change Dc11 Description to consider the OpenAIRE elements
	 */
	function changeDc11Description($hookName, $params) {
		$adapter =& $params[0];
		$article = $params[1];
		$journal = $params[2];
		$issue = $params[3];
		$dc11Description =& $params[4];

		if ($article->getCitations()!='') {
			$references = preg_replace("/((\r\n)|\r\n)/", "-JUMP-", $article->getCitations());
			$reference = preg_split("/(-JUMP-)+\s*(-JUMP-)+/", $references);
			foreach($reference as $value) {
				$value = trim($value);
				$value = str_replace("-JUMP-", " ", $value);
				$value = preg_replace("/^(\(\s*[0-9]*\s*\)|\[\s*[0-9]*\s*\]|[0-9]*\s*\.|[0-9]*\s*\))\s*/", "", $value);
				if (!preg_match("/^(http.*|<a\s+href.*)/", $value)) {
					$relation = '/*ref*/' . $value;
					$dc11Description->addStatement('dc:relation', $relation);
				}
			}
		}	

		return false;
	}
}
?>
