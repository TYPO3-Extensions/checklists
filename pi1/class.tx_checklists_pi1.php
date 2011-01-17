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
*
* $Id$
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
//t3lib_div::debug($this->piVars);

			// If a checklist form has been submitted (i.e. there is an "items" array), handle the results
		if (isset($this->piVars['items']) || isset($this->piVars['uncheck'])) {
			$this->saveChecks();
		}

			// If no id is defined, display the list of all checklists
		$id = intval($this->piVars['showUid']);
		if (empty($id)) {
			$content = $this->listView();

			// Display the chosen checklist
		} else {
			$content = $this->singleView($id);
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
		$where = '';
		$referencePage = $GLOBALS['TSFE']->id;
		if (!empty($this->cObj->data['pages'])) {
			$referencePage = $this->cObj->data['pages'];
		}
		$where = 'pid = ' . intval($this->cObj->data['pages']);
		$rows = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_instances', $where, '', 'title');
			// Display the list of checklist instances
		$instanceList = '';
		foreach ($rows as $aRow) {
			$GLOBALS['TSFE']->register['current_list_status'] = $aRow['status'];
			$icon = $this->cObj->cObjGetSingle($this->conf['listView.']['statusDisplay'], $this->conf['listView.']['statusDisplay.']);
			$link = $this->pi_linkTP($aRow['title'], array($this->prefixId.'[showUid]' => $aRow['uid']), 1);
			$instanceList .= $this->cObj->stdWrap($icon.$link, $this->conf['listView.']['item.']);
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

			// Get the record for the corresponding checklist instance
		$instance = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_instances', 'uid = ' . $id);
		if (count($instance) == 0) {
			// No record found or no translation, etc.
		}
		else {
			$instanceInfo = $instance[0];
			if (empty($instanceInfo['results'])) {
				$results = array();
			}
			else {
				$results = t3lib_div::xml2array($instanceInfo['results']);
			}
			$content .= '<h2>'.$instanceInfo['title'].'</h2>';
			if (!empty($instanceInfo['notes'])) $content .= '<p>'.nl2br($instanceInfo['notes']).'</p>';
				// Get the information about the checklist the instance is derived from
			$list = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_lists', "uid = '" . $instanceInfo['checklists_id'] . "'");
//t3lib_div::debug($list, 'List');
			if (count($list) == 0) {
				// No record found or no translation, etc.
			}
			else {
				$listInfo = $list[0];
					// Get all the groups of the given checklist
					// First check if the list was overlaid
					// TODO: check if it's really needed for translations here
				$realListId = $listInfo['uid'];
				if (isset($listInfo['_ORIG_uid'])) {
					$realListId = $listInfo['_ORIG_uid'];
				}
				$where = 'parentid = ' . $realListId . " AND parenttable = 'tx_checklists_lists'";
				$groups = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_itemgroups', $where, '', 'sorting');
//t3lib_div::debug($groups, 'Groups');
					// Get the uid's of all groups, in order to get their related items
				$groupUids = array();
				foreach ($groups as $row) {
					$realGroupId = $row['uid'];
					if (isset($row['_ORIG_uid'])) {
						$realGroupId = $row['_ORIG_uid'];
					}
					$groupUids[] = $realGroupId;
				}
					// Assemble condition on parent records and table
				$parentCondition = "parentid IN (" . implode(', ', $groupUids) . ") AND parenttable = 'tx_checklists_itemgroups'";
					// Condition must be extended in case of workspace preview, since new items have their
					// parentid and parenttable fields set to NULL
				if ($GLOBALS['TSFE']->sys_page->versioningPreview) {
					$parentCondition = "(parentid IN (" . implode(', ', $groupUids) . ") OR parentid IS NULL) AND (parenttable = 'tx_checklists_itemgroups' OR parenttable IS NULL)";
				}
				$items  = tx_overlays::getAllRecordsForTable('*', 'tx_checklists_items', $parentCondition, '', 'sorting');
//t3lib_div::debug($items, 'Items');
				if (count($items) == 0) {
					// No record found or no translation, etc.
				}
				else {
						// Group items by parent id, so that they can be related to their group easily
						// We also build a list of items uid's (this is used further down)
					$sortedItems = array();
					$itemUids = array();
					foreach ($items as $row) {
						$parentId = $row['parentid'];
						$itemUids[] = $row['uid'];
						if (!isset($sortedItems[$parentId])) $sortedItems[$parentId] = array();
						$sortedItems[$parentId][] = $row;
					}
						// Display groups with nested items
					$listContent = '';
					foreach ($groups as $aGroup) {
						$groupMarkers = array();
						$groupMarkers['###TITLE###'] = $this->cObj->stdWrap($aGroup['title'], $this->conf['singleView.']['group.']['title.']);
						$groupMarkers['###DESCRIPTION###'] = $this->cObj->stdWrap($aGroup['description'], $this->conf['singleView.']['group.']['description.']);
						$groupContent = $this->cObj->substituteMarkerArray($this->conf['singleView.']['group.']['layout'], $groupMarkers);
							// Get the proper uid to find the group's items
						$realGroupId = $aGroup['uid'];
						if (isset($aGroup['_ORIG_uid'])) {
							$realGroupId = $aGroup['_ORIG_uid'];
						} elseif (isset($aGroup['_LOCALIZED_UID'])) {
							$realGroupId = $aGroup['_LOCALIZED_UID'];
						}
							// Display the group's items
						if (isset($sortedItems[$realGroupId])) {
							foreach ($sortedItems[$realGroupId] as $anItem) {
								$itemMarkers = array();
									// Initialize item status
								$isDone = 0;
								$user = '';
								if (isset($results[$anItem['uid']])) {
									$isDone = $results[$anItem['uid']]['status'];
									$user = $results[$anItem['uid']]['user'];
								}
									// Load some registers used during rendering
								$GLOBALS['TSFE']->register['item_uid'] = $anItem['uid'];
								$GLOBALS['TSFE']->register['current_item_status'] = $isDone;
									// Render content for each marker
								$itemMarkers['###CHECKBOX###'] = $this->cObj->stdWrap($isDone, $this->conf['singleView.']['item.']['checkbox.']);
								if ($isDone) {
									$uncheckLink = $this->pi_linkTP($this->pi_getLL('uncheck'), array($this->prefixId.'[showUid]' => $id, $this->prefixId.'[uncheck]' => $anItem['uid']), 1);
									$itemMarkers['###UNCHECK###'] = $this->cObj->stdWrap($uncheckLink, $this->conf['singleView.']['item.']['uncheck.']);
								}
								else {
									$itemMarkers['###UNCHECK###'] = '';
								}
								$itemMarkers['###TITLE###'] = $this->cObj->stdWrap($anItem['title'], $this->conf['singleView.']['item.']['title.']);
								$itemMarkers['###DESCRIPTION###'] = $this->cObj->stdWrap($anItem['description'], $this->conf['singleView.']['item.']['description.']);
								$itemMarkers['###USER###'] = $this->cObj->stdWrap($user, $this->conf['singleView.']['item.']['user.']);
									// Assemble the content for the group
								$groupContent .= $this->cObj->substituteMarkerArray($this->conf['singleView.']['item.']['layout'], $itemMarkers);
							}
						}
						$listContent .= $groupContent;
					}
					$content .= $this->cObj->stdWrap($listContent, $this->conf['singleView.']['listWrap.']);
						// After having built a whole list, check that the checklist instance has a properly built results field
						// This is done only if no data was submitted, because a different process happens in that case
					if (empty($this->piVars['submit'])) {
						$this->checkResultsField($instanceInfo, $itemUids);
					}
				}
				$content .= $this->cObj->stdWrap($this->pi_getLL('submit'), $this->conf['singleView.']['submit.']);
				$content = $this->cObj->stdWrap($content, $this->conf['singleView.']['allWrap.']);
			}
		}
			// Add back to instance list link
		$content .= $this->cObj->stdWrap($this->pi_linkTP($this->pi_getLL('back_to_list')), $this->conf['singleView.']['backlink.']);
		return $content;
	}

	/**
	 * This method is used to check that the results field of the checklist instance
	 * has a full list of checklist items
	 *
	 * @param	array	$instance: DB record of the checklist instance
	 * @param	array	$items: list of all item uid's found in the list
	 * @return	void
	 */
	protected function checkResultsField($instance, $items) {
			// If there are no results yet, initialise array
		if (empty($instance['results'])) {
			$currentResults = array();

			// Otherwise extract array of results from stored XML
		} else {
			$currentResults = t3lib_div::xml2array($instance['results']);
				// Get the list of currently stored items
			$storedUids = array_keys($currentResults);
				// Check which items are not in the list anymore
			$removedUids = array_diff($storedUids, $items);
				// If some items are not in the list anymore, remove them from the results list
			if (count($removedUids) > 0) {
				foreach ($removedUids as $uid) {
					unset($currentResults[$uid]);
				}
			}
		}
			// Loop on all checklist items and initialise those that don't exist
		foreach ($items as $uid) {
			if (!isset($currentResults[$uid])) {
				$currentResults[$uid] = array('status' => 0, 'user' => '');
			}
		}
			// Store the updated results list into the instance
		$updates = array('results' => t3lib_div::array2xml($currentResults));
		$this->saveToOriginalAndTranslations($instance['uid'], $instance['pid'], $updates);
	}

	/**
	 * This method stores all the checkboxes that were checked
	 * or the single checkbox that was unchecked
	 *
	 * @return	void
	 */
	protected function saveChecks() {
			// First, get the checklist instance record for updating it
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_checklists_instances', "uid = '".$this->piVars['showUid']."'");
		$instanceInfo = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			// Get the results that were already stored for that list
			// First check if the results list hasn't been initialised beforehand
			// (this shouldn't happen normally)
		if (empty($instanceInfo['results'])) {
			$currentResults = array();
		
			// If yes, get the results as an array
		} else {
			$currentResults = t3lib_div::xml2array($instanceInfo['results']);
		}
			// If a user is logged it, take its username
		$user = '';
		if (isset($GLOBALS['TSFE']->fe_user->user[$GLOBALS['TSFE']->fe_user->username_column])) {
			$user = $GLOBALS['TSFE']->fe_user->user[$GLOBALS['TSFE']->fe_user->username_column];
		}
			// Loop on all submitted checkboxes and set them to done
		if (isset($this->piVars['items'])) {
			foreach ($this->piVars['items'] as $uid => $value) {
				$currentResults[$uid] = array('status' => 1, 'user' => $user);
			}
				// Check if all items have been completed
			$numItems = count($currentResults);
			$numItemsDone = 0;
			foreach ($currentResults as $itemInfo) {
				if ($itemInfo['status'] == 1) $numItemsDone++;
			}
			$status = 0;
			if ($numItemsDone == $numItems) {
				$status = 1;
			}

			// Uncheck the designated item
		} elseif (isset($this->piVars['uncheck'])) {
			$currentResults[$this->piVars['uncheck']] = array('status' => 0, 'user' => '');
				// Set the general status to 0
				// (if an item has been unchecked, the whole list cannot possibly be complete)
			$status = 0;
		}
			// Save result to checklist instance and translation
		$updates = array('results' => t3lib_div::array2xml($currentResults), 'status' => $status);
		$this->saveToOriginalAndTranslations($instanceInfo['uid'], $instanceInfo['pid'], $updates);
	}

	/**
	 * This method is used to save the updated checklist results to the original checklist instance
	 * and to its translations if any
	 *
	 * @param	integer		$uid: primary key of the checklist instance to update (original language)
	 * @param	integer		$pid: page id where the instance is located
	 * @param	array		$updates: fields to update
	 * @return	void
	 */
	protected function saveToOriginalAndTranslations($uid, $pid, $updates) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_checklists_instances', "uid = '" . $uid . "'", $updates);
			// Check if the instance has translations, as they must have the same results list
		$where = $GLOBALS['TCA']['tx_checklists_instances']['ctrl']['transOrigPointerField'] . " = '" . $uid . "' AND pid = '" . $pid . "'";
		$where .= $this->cObj->enableFields('tx_checklists_instances', 1);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_checklists_instances', $where);
			// Loop on all translations and update them with the same results list
		if ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_checklists_instances', "uid = '" . $row['uid'] . "'", $updates);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/pi1/class.tx_checklists_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/pi1/class.tx_checklists_pi1.php']);
}

?>