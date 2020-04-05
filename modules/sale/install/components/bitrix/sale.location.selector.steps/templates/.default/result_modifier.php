<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['SUPPRESS_ERRORS'] = 				$this->__component->tryParseBoolean($arParams['SUPPRESS_ERRORS']);
$arParams['DISABLE_KEYBOARD_INPUT'] = 		$this->__component->tryParseBoolean($arParams['DISABLE_KEYBOARD_INPUT']);
$arParams['JS_CONTROL_GLOBAL_ID'] = 		$this->__component->tryParseStringStrict($arParams['JS_CONTROL_GLOBAL_ID']);
$arParams['JS_CONTROL_DEFERRED_INIT'] = 	$this->__component->tryParseStringStrict($arParams['JS_CONTROL_DEFERRED_INIT']);
$arParams['JS_CALLBACK'] = 					$this->__component->tryParseStringStrict($arParams['JS_CALLBACK']);
$arParams['INITIALIZE_BY_GLOBAL_EVENT'] = 	$this->__component->tryParseStringStrict($arParams['INITIALIZE_BY_GLOBAL_EVENT']);

$arResult['PRECACHED_POOL_JSON'] = array('a' => array()); // force PhpToJSObject to map this to {}, not to []

if(is_array($arResult['PRECACHED_POOL']))
{
	foreach($arResult['PRECACHED_POOL'] as $levelId => $levelNodes)
	{
		if(is_array($levelNodes))
		{
			foreach($levelNodes as $nodeId => $node)
			{
				$fNode = array(
					'DISPLAY' => $node['NAME'],
					'VALUE' => intval($node['ID']),
					'CODE' => $node['CODE'],
					'IS_PARENT' => $node['CHILD_CNT'] > 0,
					'TYPE_ID' => intval($node['TYPE_ID'])
				);

				if($node['IS_UNCHOOSABLE'])
					$fNode['IS_UNCHOOSABLE'] = true;

				$arResult['PRECACHED_POOL_JSON'][intval($levelId)][] = $fNode;
			}
		}
	}
}

$arResult['RANDOM_TAG'] = rand(999, 99999);
$arResult['ADMIN_MODE'] = ADMIN_SECTION == 1;

// trunk
$arResult['ROOT_NODE'] = 0;
if(is_array($arResult['TREE_TRUNK']) && !empty($arResult['TREE_TRUNK']))
{
	$names = array();
	foreach($arResult['TREE_TRUNK'] as $item)
	{
		$names[] = $item['NAME'];
		$arResult['ROOT_NODE'] = $item['ID'];
	}

	$arResult['TRUNK_NAMES'] = $names;
}

// modes
$modes = array();
if(ADMIN_SECTION == 1 || $arParams['ADMIN_MODE'] == 'Y')
	$modes[] = 'admin';

foreach($modes as &$mode)
	$mode = 'bx-'.$mode.'-mode';

$arResult['MODE_CLASSES'] = implode(' ', $modes);