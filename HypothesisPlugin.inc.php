<?php

/**
 * @file HypothesisPlugin.inc.php
 *
 * Copyright (c) 2013-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class HypothesisPlugin
 * @brief Hypothesis annotation/discussion integration
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class HypothesisPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			HookRegistry::register('ArticleHandler::download',array(&$this, 'callback'));
			HookRegistry::register('TemplateManager::display', array(&$this, 'callbackTemplateDisplay'));
			return true;
		}
		return false;
	}

	/**
	 * Hook callback function for TemplateManager::display
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callback($hookName, $args) {
		$galley =& $args[1];
		if (!$galley || $galley->getFileType() != 'text/html') return false;

		ob_start(function($buffer) {
			return str_replace('<head>', '<head><script async defer src="//hypothes.is/embed.js"></script>', $buffer);
		});

		return false;
	}

	/**
	 * Hook callback function for TemplateManager::display
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callbackTemplateDisplay($hookName, $args) {
		if ($hookName != 'TemplateManager::display') return false;
		$templateMgr = $args[0];
		$template = $args[1];
		$pluginPdfJsViewer = 'plugins-generic-pdfJsViewer';
		$pluginEpubJsViewer = 'plugins-generic-epubJsViewer';
		$submissiontpl = 'submissionGalley.tpl';
		$issuetpl = 'issueGalley.tpl';

		//pdf
		// template path contains the plugin path, and ends with the tpl file
		if ( (strpos($template, $pluginPdfJsViewer) !== false) && (  (strpos($template, ':'.$submissiontpl,  -1 - strlen($submissiontpl)) !== false)  ||  (strpos($template, ':'.$issuetpl,  -1 - strlen($issuetpl)) !== false))) {
			$templateMgr->registerFilter("output", array($this, 'changePdfjsPath'));
		}

		//epub
		// template path contains the plugin path, and ends with the tpl file
		if ( (strpos($template, $pluginEpubJsViewer) !== false) && (  (strpos($template, ':'.$submissiontpl,  -1 - strlen($submissiontpl)) !== false)  ||  (strpos($template, ':'.$issuetpl,  -1 - strlen($issuetpl)) !== false))) {
			$templateMgr->registerFilter("output", array($this, 'changeEpubjsPath'));
		}

		return false;
	}

	/**
	 * Output filter to create a new element in a registration form
	 * @param $output string
	 * @param $templateMgr TemplateManager
	 * @return $string
	 */
	function changePdfjsPath($output, $templateMgr) {
		$newOutput = str_replace('pdfJsViewer/pdf.js/web/viewer.html?file=', 'hypothesis/pdf.js/viewer/web/viewer.html?file=', $output);
		return $newOutput;
	}

	function changeEpubjsPath($output, $templateMgr) {
		$newOutput = str_replace('epubJsViewer/viewer/index.html?url=', 'hypothesis/epub.js/index.html?url=', $output);
		return $newOutput;
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.hypothesis.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.hypothesis.description');
	}
}

