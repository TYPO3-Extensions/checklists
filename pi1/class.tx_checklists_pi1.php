<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Francois Suter (Cobweb) <typo3@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('checklists', 'lib/class.tx_checklists_tools.php'));


/**
 * Plugin 'Checklists' for the 'checklists' extension.
 *
 * @author	Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_checklists
 */
class tx_checklists_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_checklists_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_checklists_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'checklists';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method dispatches the generation of the content to the relevant method given the view parameter
	 * The default view is the list view
	 *
	 * @param	string		$content: The plugin's content
	 * @param	array		$conf: The plugin's TS configuration
	 *
	 * @return	string		The content to be displayed on the website
	 */
	public function main($content, $conf) {
		$this->conf = $conf;
//		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if (empty($this->piVars['showUid'])) {
			$content = $this->listView();
		}
		else {
			$content = $this->singleView($this->piVars['showUid']);
		}
	
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * This method displays a list of all checklist instances
	 *
	 * @return	string	HTML content to display
	 */
	protected function listView() {
			// Get the list of all checklist instances, for a given page or all
		if (empty($this->cObj->data['pages'])) {
			$where = '';
		}
		else {
			$where = "pid = '".$this->cObj->data['pages']."'";
		}
		$rows = tx_checklists_tools::getAllRecordsForTable('*', 'tx_checklists_instances', $where, '', 'title');
			// Display the list of checklist instances
		$instanceList = '';
		foreach ($rows as $aRow) {
			$icon = $this->cObj->cObjGetSingle($this->conf['statusDisplay'], $this->conf['statusDisplay.']);
			$link = $this->pi_linkTP($aRow['title'], array($this->prefixId.'[showUid]' => $aRow['uid']), 1);
			$instanceList .= $this->cObj->stdWrap($icon.$link, $this->conf['listView.']['itemWrap.']);
		}
		$content = $this->cObj->stdWrap($instanceList, $this->conf['listView.']['allWrap.']);
		return $content;
	}

	/**
	 * This method displays a single checklist instance
	 *
	 * @param	integer		$id: primary key of the checklist instance to display
	 *
	 * @return	string		HTML content to display
	 */
	protected function singleView($id) {
			// Get the record for the corresponding checklist instance
		$instance = tx_checklists_tools::getAllRecordsForTable('*', 'tx_checklists_instances', "uid = '$id'");
t3lib_div::debug($instance);
		if (count($instance) == 0) {
			// No record found or no translation, etc.
		}
		else {
			$row = $instance[0];
				// Get the information about the checklist the instance is derived from
			$list = tx_checklists_tools::getAllRecordsForTable('*', 'tx_checklists_lists', "uid = '".$row['checklists_id']."'");
t3lib_div::debug($list);
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/pi1/class.tx_checklists_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/pi1/class.tx_checklists_pi1.php']);
}

?>