<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// Declare all tables for extension

// Checklists definitions

t3lib_extMgm::allowTableOnStandardPages('tx_checklists_lists');

$TCA['tx_checklists_lists'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_lists',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
		'enablecolumns' => array(		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_checklists_lists.gif',
	),
);

// Groups of checlist items

t3lib_extMgm::allowTableOnStandardPages('tx_checklists_itemgroups');

$TCA['tx_checklists_itemgroups'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_itemgroups',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array(		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_checklists_itemgroups.gif',
	),
);

// Checklist items

t3lib_extMgm::allowTableOnStandardPages('tx_checklists_items');

$TCA['tx_checklists_items'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_items',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array(		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_checklists_items.gif',
	),
);

// Checklist instances

t3lib_extMgm::allowTableOnStandardPages('tx_checklists_instances');

t3lib_extMgm::addToInsertRecords('tx_checklists_instances');

$TCA['tx_checklists_instances'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_instances',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
		'enablecolumns' => array(		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_checklists_instances.gif',
	),
);

// Modify tt_content TCA for plugin

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key';

// Add plugin to list of plugins

t3lib_extMgm::addPlugin(array('LLL:EXT:checklists/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');

// Declare static templates
t3lib_extMgm::addStaticFile( $_EXTKEY, 'static/', 'Checklists' );

// Declare class for content element wizard icon

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_checklists_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_checklists_pi1_wizicon.php';
}
?>