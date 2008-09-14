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


/**
 * Utility class for the 'checklists' extension.
 *
 * @author	Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_checklists
 */
class tx_checklists_tools {

	/**
	 * This method is designed to get all the records from a given table, properly overlaid with versions and translations
	 * Its parameters are the same as t3lib_db::exec_SELECTquery()
	 * A small difference is that it will take only a single table
	 * The big difference is that it returns an array of properly overlaid records and not a result pointer
	 *
	 * @param	string		$selectFields: List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
	 * @param	string		$fromTable: Table from which to select. This is what comes right after "FROM ...". Required value.
	 * @param	string		$whereClause: Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		$groupBy: Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		$orderBy: Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		$limit: Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	array		Fully overlaid recordset
	 */
	public function getAllRecordsForTable($selectFields, $fromTable, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '') {
			// SQL WHERE clause is the base clause passed to the function, plus language condition, plus enable fields condition
		$where = $whereClause;
		if (!empty($where)) $where .= ' AND ';
		$where .= self::getLanguageCondition($fromTable);
		if (!empty($where)) $where .= ' AND ';
		$where .= self::getEnableFieldsCondition($fromTable);

			// Make sure the list of selected fields includes "uid", "pid" and language fields so that language overlays can be gotten properly
			// If these do not exist in the queried table, the recordset is returned as is, without overlay
		try {
			$selectFields = $this->selectOverlayFields();
			$doOverlays = true;
		}
		catch (Exception $e) {
			$doOverlays = false;
		}

			// Execute the query itself
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $fromTable, $where, $groupBy, $orderBy, $limit);
			// Assemble a raw recordset
		$records = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$records[] = $row;
		}

			// If we have both a uid and a pid field, we can proceed with overlaying the records
		if ($hasUidField && $hasPidField) {
			$records = $this->overlayRecordSet($fromTable, $records, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
		}
		return $records;
	}

	/**
	 * This method gets the SQL condition to apply for fetching the proper language
	 * depending on the localization settings in the TCA
	 *
	 * @param	string		$table: name of the table to assemble the condition for
	 * @return	string		SQL to add to the WHERE clause (without "AND")
	 */
	protected function getLanguageCondition($table) {
		$languageCondition = '';

			// First check if there's actually a TCA for the given table
		if (isset($GLOBALS['TCA'][$table]['ctrl'])) {
			$tableCtrlTCA = $GLOBALS['TCA'][$table]['ctrl'];

				// Assemble language condition only if a language field is defined
			if (!empty($tableCtrlTCA['languageField'])) {
				if (isset($GLOBALS['TSFE']->sys_language_contentOL) && isset($tableCtrlTCA['transOrigPointerField'])) {
					$languageCondition = $tableCtrlTCA['languageField'].' IN (0,-1)'; // Default language and "all" language

					// If current language is not default, select elements that exist only for current language
					// That means elements that exist for current language but have no parent element
					if ($GLOBALS['TSFE']->sys_language_content > 0) {
						$languageCondition .= ' OR ('.$tableCtrlTCA['languageField']." = '".$GLOBALS['TSFE']->sys_language_content."' AND ".$tableCtrlTCA['transOrigPointerField']." = '0')";
					}
				}
				else {
					$languageCondition = $tableCtrlTCA['languageField']." = '".$GLOBALS['TSFE']->sys_language_content."'";
				}
			}
		}
		return $languageCondition;
	}

	/**
	 * This method returns the condition on enable fields for the given table
	 * Basically it calls on the method provided by tslib_content, but without the " AND " in front
	 *
	 * @param	string		$table: name of the table to build the condition for
	 * @return	string		SQL to add to the WHERE clause (without "AND")
	 */
	protected function getEnableFieldsCondition($table) {
		$enableCondition = '';
			// First check if table has a TCA ctrl section, otherwise t3lib_page::enableFields() will die() (stupid thing!)
		if (isset($GLOBALS['TCA'][$table]['ctrl'])) {
			$enableCondition = $this->cObj->enableFields($table);
				// If an enable clause was returned, strip the first ' AND '
			if (!empty($enableCondition)) {
				$enableCondition = substr($enableCondition, strlen(' AND '));
			}
		}
		return $enableCondition;
	}

	/**
	 * This method makes sure that all the fields necessary for proper overlaying are included
	 * in the list of selected fields and exist in the table being queried
	 * If not, it throws an exception
	 *
	 * @param	string		$table: Table from which to select. This is what comes right after "FROM ...". Required value.
	 * @param	string		$selectFields: List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
	 * @return	string		Possibly modified list of fields to select
	 */
	protected function selectOverlayFields($table, $selectFields) {
		$select = $selectFields;
		$languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

			// In order to be properly overlaid, a table has to have a uid, a pid and languageField
		$hasUidField = strpos($selectFields, 'uid');
		$hasPidField = strpos($selectFields, 'pid');
		$hasLanguageField = strpos($selectFields, $languageField);
		if ($hasUidField === false || $hasPidField === false || $hasLanguageField === false) {
			$availableFields = $GLOBALS['TYPO3_DB']->admin_get_fields($fromTable);
			if (isset($availableFields['uid'])) {
				if ($selectFields != '*') $select .= ', uid';
				$hasUidField = true;
			}
			if (isset($availableFields['pid'])) {
				if ($selectFields != '*') $select .= ', pid';
				$hasPidField = true;
			}
			if (isset($availableFields[$languageField])) {
				if ($selectFields != '*') $select .= ', '.$languageField;
				$hasLanguageField = true;
			}
		}
			// If one of the fields is still missing after that, throw an exception
		if ($hasUidField === false || $hasPidField === false || $hasLanguageField === false) {
			throw new Exception('Not all overlay fields available.');
		}
			// Else return the modified list of fields to select
		else {
			return $select;
		}
	}

	/**
	 * Creates language-overlay for records in general (where translation is found in records from the same table)
	 *
	 * @param	string		$table: Table name
	 * @param	array		$recordset: Full recordset to overlay. Must containt uid, pid and $table]['ctrl']['languageField']
	 * @param	integer		$sys_language_contentPointer to the sys_language uid for content on the site.
	 * @param	string		$OLmodeOverlay mode. If "hideNonTranslated" then records without translation will not be returned un-translated but unset (and return value is false)
	 * @return	array		Returns the full overlaid recordset. If $OLmode is "hideNonTranslated" then some records may be missing if no translation was found.
	 */
	public function overlayRecordSet($table, $recordset, $sys_language_content, $OLmode = '') {

			// Test with the first row if uid and pid fields are present
		if (!empty($recordset[0]['uid']) && !empty($recordset[0]['pid'])) {

				// Test if the table has a TCA definition
			if (isset($GLOBALS['TCA'][$table])) {
				$tableCtrl = $TCA[$table]['ctrl'];

					// Test if the TCA definition includes translation information
				if ($tableCtrl['languageField'] && $tableCtrl['transOrigPointerField']) {

						// Test with the first row if languageField is present
					if (!empty($recordset[0][$tableCtrl['languageField']])) {
						if ($tableCtrl['transOrigPointerTable']) {
							// TODO: Handle overlays stored in separate table (see Olly's patch)
							// In the meantime, return recordset unchanged
							return $recordset;
						}
						else {
								// Filter out records that are not in the default or [ALL] language, should there be any
							$filteredRecordset = array();
							foreach ($recordset as $row) {
								if ($row[$tableCtrl['languageField']] <= 0) {
									$filteredRecordset[] = $row;
								}
							}
								// Will try to overlay a record only if the sys_language_content value is larger than zero,
								// that is, it is not default or [ALL] language
							if ($sys_language_content > 0) {
									// Assemble a list of uid's for getting the overlays,
									// but only from the filtered recordset
								$uidList = array();
								foreach ($filteredRecordset as $row) {
									$uidList[] = $row['uid'];
								}

									// Select overlays for all records
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'*',
									$table,
										$tableCtrl['languageField'].' = '.intval($sys_language_content).
										' AND '.$tableCtrl['transOrigPointerField'].' = '.intval($row['uid']).
										' AND '.self::getEnableFieldsCondition($table)
								);
									// Arrange overlay records according to transOrigPointerField, so that it's easy to relate them to the originals
									// This structure is actually a 2-dimensional array, with the pid as the second key
									// Because of versioning, there may be several overlays for a given original and matching the pid too
									// ensures that we are refering to the correct overlay
								$overlays = array();
								while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
									if (!isset($overlays[$row[$tableCtrl['transOrigPointerField']]])) $overlays[$row[$tableCtrl['transOrigPointerField']]] = array();
									$overlays[$row[$tableCtrl['transOrigPointerField']]][$row['pid']] = $row;
								}

									// Now loop on the filtered recordset and try to overlay each record
								$overlaidRecordset = array();
								foreach ($filteredRecordset as $row) {
										// An overlay exists, apply it
									if (isset($overlays[$row['uid']][$row['pid']])) {
										$overlaidRecordset[] = self::overlaySingleRecord($table, $row, $overlays[$row['uid']][$row['pid']]);
									}
										// No overlay exists
									else {
											// Take original record, only if non-translated are not hidden, or if language is [All]
										if ($OLmode != 'hideNonTranslated' || $row[$tableCtrl['languageField']] == -1) {
											$overlaidRecordset[] = $row;
										}
									}
								}
									// Return the overlaid recordset
								return $overlaidRecordset;
							}
							else {
									// When default language is displayed, we never want to return a record carrying another language!
									// Return the filtered recordset
								return $filteredRecordset;
							}
						}
					}
						// Provided recordset does not contain languageField field, return recordset unchanged
					else {
						return $recordset;
					}
				}
					// No appropriate language fields defined in TCA, return recordset unchanged
				else {
					return $recordset;
				}
			}
				// No TCA for table, return recordset unchanged
			else {
				return $recordset;
			}
		}
			// Recordset did not contain uid or pid field, return recordset unchanged
		else {
			return $recordset;
		}
	}

	/**
	 * This method takes a record and its overlay and performs the overlay according to active translation rules
	 *
	 * @param	string	$table: name of the table for which the operation is taking place
	 * @param	array	$record: record to overlay
	 * @param	array	$overlay: overlay of the record
	 * @return	array	Overlaid record
	 */
	protected function overlaySingleRecord($table, $record, $overlay) {
		$overlaidRecord = $row;
		$overlaidRecord['_LOCALIZED_UID'] = $overlay['uid'];
		foreach($record as $key => $value) {
			if ($key != 'uid' && $key != 'pid' && isset($overlay[$key])) {
				if ($GLOBALS['TSFE']->TCAcachedExtras[$table]['l10n_mode'][$key] != 'exclude'
						&& ($GLOBALS['TSFE']->TCAcachedExtras[$table]['l10n_mode'][$key] != 'mergeIfNotBlank' || strcmp(trim($overlay[$key]), ''))) {
					$overlaidRecord[$key] = $overlay[$key];
				}
			}
		}
		return $overlaidRecord;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/lib/class.tx_checklists_tools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/lib/class.tx_checklists_tools.php']);
}

?>