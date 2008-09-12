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
	 * @param	string		$select_fields: List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
	 * @param	string		$from_table: Table from which to select. This is what comes right after "FROM ...". Required value.
	 * @param	string		$where_clause: Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		$groupBy: Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		$orderBy: Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		$limit: Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	array		Fully overlaid recordset
	 */
	public function getAllRecordsForTable($select_fields, $from_table, $where_clause = '', $groupBy = '', $orderBy = '', $limit = '') {
		$where = $where_clause;
		if (!empty($where)) $where .= ' AND ';
		$where .= self::getLanguageCondition($from_table);
		if (!empty($where)) $where .= ' AND ';
		$where .= self::getEnableFieldsCondition($from_table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where, $groupBy, $orderBy, $limit);
		$records = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				// Overlay each instance, if appropriate
			$overlaidRow = $GLOBALS['TSFE']->sys_page->getRecordOverlay($from_table, $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
			if (isset($overlaidRow)) $records[] = $overlaidRow;
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
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/lib/class.tx_checklists_tools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/checklists/lib/class.tx_checklists_tools.php']);
}

?>