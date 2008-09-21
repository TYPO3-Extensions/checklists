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
require_once(t3lib_extMgm::extPath('overlays', 'class.tx_overlays.php'));


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
t3lib_div::debug($this->piVars);

			// If a checklist form has been submitted, handle the results
		if (isset($this->piVars['submit'])) {
			
		}

			// If no id is defined, display the list of all checklists
		if (empty($this->piVars['showUid'])) {
			$content = $this->listView();
		}
			// Display the chosen checklist
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
		$rows = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_instances', $where, '', 'title');
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
		$content = '';
			// Test output to verify whether new overlay mechanism is active or not
		if ($this->conf['useNewOverlays']) {
			$content .= '<p><em>New overlay mechanism is active.</em></p>';
		}
		else {
			$content .= '<p><em>New overlay mechanism is inactive.</em></p>';
		}

			// Get the record for the corresponding checklist instance
//		$instance = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_instances', "uid = '$id'");
		$instance = $this->getAllRecordsForTable('*', 'tx_checklists_instances', "uid = '$id'");
		if (count($instance) == 0) {
			// No record found or no translation, etc.
		}
		else {
			$instanceInfo = $instance[0];
			$content .= '<h2>'.$instanceInfo['title'].'</h2>';
			if (!empty($instanceInfo['notes'])) $content .= '<p>'.nl2br($instanceInfo['notes']).'</p>';
				// Get the information about the checklist the instance is derived from
//			$list = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_lists', "uid = '".$instanceInfo['checklists_id']."'");
			$list = $this->getAllRecordsForTable('*', 'tx_checklists_lists', "uid = '".$instanceInfo['checklists_id']."'");
			if (count($list) == 0) {
				// No record found or no translation, etc.
			}
			else {
				$listInfo = $list[0];
					// Get all the groups of the given checklist
//				$groups = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_itemgroups', "parentid = '".$listInfo['uid']."' AND parenttable = 'tx_checklists_lists'", '', 'sorting');
				$groups = $this->getAllRecordsForTable('*', 'tx_checklists_itemgroups', "parentid = '".$listInfo['uid']."' AND parenttable = 'tx_checklists_lists'", '', 'sorting');
					// Get the uid's of all groups, in order to get their related items
				$groupUids = array();
				foreach ($groups as $row) {
					$groupUids[] = $row['uid'];
				}
				$items  = $this->getAllRecordsForTable('*', 'tx_checklists_items', "parentid IN (".implode(', ', $groupUids).") AND parenttable = 'tx_checklists_itemgroups'", '', 'sorting');
				if (count($items) == 0) {
					// No record found or no translation, etc.
				}
				else {
						// Group items by parent id, so that they can be related to their group easily
					$sortedItems = array();
					foreach ($items as $row) {
						$parentId = $row['parentid'];
						if (!isset($sortedItems[$parentId])) $sortedItems[$parentId] = array();
						$sortedItems[$parentId][] = $row;
					}
						// Display groups with nested items
					$listContent = '';
					foreach ($groups as $aGroup) {
						$groupMarkers = array();
						$groupMarkers['###TITLE###'] = $this->cObj->stdWrap($aGroup['title'], $this->conf['listView.']['group.']['title.']);
						$groupMarkers['###DESCRIPTION###'] = $this->cObj->stdWrap($aGroup['description'], $this->conf['listView.']['group.']['description.']);
						$groupContent = $this->cObj->substituteMarkerArray($this->conf['listView.']['group.']['layout'], $groupMarkers);
							// Get the proper uid to find the group's items
						if (isset($aGroup['_LOCALIZED_UID'])) {
							$realGroupId = $aGroup['_LOCALIZED_UID'];
						}
						else {
							$realGroupId = $aGroup['uid'];
						}
							// Display the group's items
						if (isset($sortedItems[$realGroupId])) {
							$groupItemContents = '';
							foreach ($sortedItems[$realGroupId] as $anItem) {
								$itemMarkers['###CHECKBOX###'] = $this->cObj->stdWrap($anItem['uid'], $this->conf['listView.']['item.']['checkbox.']);
								$itemMarkers['###TITLE###'] = $this->cObj->stdWrap($anItem['title'], $this->conf['listView.']['item.']['title.']);
								$itemMarkers['###DESCRIPTION###'] = $this->cObj->stdWrap($anItem['description'], $this->conf['listView.']['item.']['description.']);
								$itemMarkers['###USER###'] = $this->cObj->stdWrap('', $this->conf['listView.']['item.']['user.']);
								$groupContent .= $this->cObj->substituteMarkerArray($this->conf['listView.']['item.']['layout'], $itemMarkers);
							}
						}
						$listContent .= $groupContent;
					}
					$content .= $this->cObj->stdWrap($listContent, $this->conf['listView.']['listWrap.']);
				}
				$content .= $this->cObj->stdWrap($this->pi_getLL('submit'), $this->conf['listView.']['submit.']);
				$content = $this->cObj->stdWrap($content, $this->conf['listView.']['allWrap.']);
			}
		}
		return $content;
	}

	/**
	 * This method stores all the checkboxes that were checked
	 *
	 * @return	void
	 */
	protected function saveChecks() {
		// First, get the checklist instance record for updating it
		// Loop on all submitted checkboxes and set them to done
		// Check if all items have been completed
		// Save result to checklist instance
		// Save to translations too, so that status is in sync
	}

	/**
	 * This is a temporary wrapper method for testing the new and old overlay methods
	 */
	private function getAllRecordsForTable($selectFields, $fromTable, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '') {
		if ($this->conf['useNewOverlays']) {
			$recordset = tx_overlays::getAllRecordsForTable($selectFields, $fromTable, $whereClause, $groupBy, $orderBy, $limit);
		}
		else {
			$recordset = array();
			if (isset($GLOBALS['TCA'][$fromTable])) {
				$enableCondition = $GLOBALS['TSFE']->sys_page->enableFields($fromTable);
				if (empty($whereClause)) $whereClause = '1 = 1';
				$whereClause .= $enableCondition;
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $fromTable, $whereClause, $groupBy, $orderBy, $limit);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$overlay = $GLOBALS['TSFE']->sys_page->getRecordOverlay($fromTable, $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
				if ($overlay !== false) {
					$recordset[] = $overlay;
				}
			}
		}
		return $recordset;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/pi1/class.tx_checklists_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/pi1/class.tx_checklists_pi1.php']);
}

?>