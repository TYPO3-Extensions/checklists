<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_checklists_lists'] = array (
	'ctrl' => $TCA['tx_checklists_lists']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,description,groups'
	),
	'feInterface' => $TCA['tx_checklists_lists']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_checklists_lists',
				'foreign_table_where' => 'AND tx_checklists_lists.pid=###CURRENT_PID### AND tx_checklists_lists.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_lists.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_lists.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'groups' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_lists.groups',		
			'config' => array (
				'type' => 'inline',
				'foreign_table' => 'tx_checklists_itemgroups',
				'foreign_table_field' => 'parenttable',
				'foreign_field' => 'parentid',
				'minitems'   => '0',
				'maxitems'   => '10',
				'appearance' => array(
								'collapseAll'           => '1',
								'expandSingle'          => '1',
								'useSortable'           => '1',
								'newRecordLinkAddTitle' => '1',
								'newRecordLinkPosition' => 'top',
								'useCombination'        => '0',
							),
				'behaviour' => array(
								'localizationMode' => 'select',
								'localizeChildrenAtParentLocalization' => 1
				)
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, description;;;;3-3-3, groups')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_checklists_itemgroups'] = array (
	'ctrl' => $TCA['tx_checklists_itemgroups']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,description,items,parentid,parenttable'
	),
	'feInterface' => $TCA['tx_checklists_itemgroups']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_checklists_itemgroups',
				'foreign_table_where' => 'AND tx_checklists_itemgroups.pid=###CURRENT_PID### AND tx_checklists_itemgroups.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_itemgroups.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_itemgroups.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'items' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_itemgroups.items',		
			'config' => array (
				'type' => 'inline',
				'foreign_table' => 'tx_checklists_items',
				'foreign_table_field' => 'parenttable',
				'foreign_field' => 'parentid',
				'minitems'   => '0',
				'maxitems'   => '10',
				'appearance' => array(
								'collapseAll'           => '1',
								'expandSingle'          => '1',
								'useSortable'           => '1',
								'newRecordLinkAddTitle' => '1',
								'newRecordLinkPosition' => 'top',
								'useCombination'        => '0',
							),
				'behaviour' => array(
								'localizationMode' => 'select',
								'localizeChildrenAtParentLocalization' => 1
				)
			)
		),
		'parentid' => array (		
			'config' => array (
				'type' => 'passthrough',
			)
		),
		'parenttable' => array (		
			'config' => array (
				'type' => 'passthrough',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, description;;;;3-3-3, items, parentid, parenttable')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_checklists_items'] = array (
	'ctrl' => $TCA['tx_checklists_items']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,description,parentid,parenttable'
	),
	'feInterface' => $TCA['tx_checklists_items']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_checklists_items',
				'foreign_table_where' => 'AND tx_checklists_items.pid=###CURRENT_PID### AND tx_checklists_items.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_items.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_items.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'parentid' => array (		
			'config' => array (
				'type' => 'passthrough',
			)
		),
		'parenttable' => array (		
			'config' => array (
				'type' => 'passthrough',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, description;;;;3-3-3, parentid, parenttable')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_checklists_instances'] = array (
	'ctrl' => $TCA['tx_checklists_instances']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,title,notes,checklists_id'
	),
	'feInterface' => $TCA['tx_checklists_instances']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_checklists_instances',
				'foreign_table_where' => 'AND tx_checklists_instances.pid=###CURRENT_PID### AND tx_checklists_instances.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_instances.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			)
		),
		'notes' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_instances.notes',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'results' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'checklists_id' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:checklists/locallang_db.xml:tx_checklists_instances.checklists_id',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_checklists_lists',	
				'foreign_table_where' => 'ORDER BY tx_checklists_lists.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, notes;;;;3-3-3, checklists_id')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);
?>