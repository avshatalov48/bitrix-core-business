<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function __FindEventInItems($arItems, $event_id, $what = "TRANSPORT")
{
	foreach($arItems as $key => $arItem)
		if (
			$arItem["EVENT_ID"] == $event_id
			&& $arItem[$what] != "I"
		)
			return $key;		

	return false;
}

function __GetInheritedValue($arEvents, $entity_type, $entity_id, $is_cb, $is_my, $event_id, $what)
{
	$key = $entity_type;

	if ($is_cb)
		$key .= "_CB";
	else
	{
		if ($is_my)
		{
			$key .= "_My";
			if ($entity_id === 0)
				$key .= "Common";
		}
		elseif ($entity_id === 0)
			$key .= "_Common";
	}

	$entity_id_key = $entity_id;
	
	$items_key = "Items";	

	if (
		array_key_exists($key, $arEvents)
		&& array_key_exists($entity_id_key, $arEvents[$key])
		&& array_key_exists($items_key, $arEvents[$key][$entity_id_key])
	)
	{
		$itemKey = __FindEventInItems($arEvents[$key][$entity_id_key][$items_key], $event_id, $what);
		if ($itemKey !== false)
			return array(
				$what 					=> $arEvents[$key][$entity_id_key][$items_key][$itemKey][$what],
				$what."_IS_INHERITED" 	=> false,
				$what."_INHERITED_FROM"	=> false,
			);
	}

	$entityTypeKey = $entity_type;

	$entityTypeKeyMy = $entityTypeKey."_My";
	$entityTypeKeyCommon = $entityTypeKey."_Common";
	$entityTypeKeyMyCommon = $entityTypeKeyMy."Common";

	if ($entity_id > 0)
	{
		if ($is_my)
		{
			if ($event_id != "all")
			{
				// defined event for friend
				if (
					array_key_exists($entityTypeKeyMy, $arEvents)
					&& array_key_exists($entity_id_key, $arEvents[$entityTypeKeyMy])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyMy][$entity_id_key])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyMy][$entity_id_key][$items_key], "all", $what);
					if ($itemKey !== false)					
						return array(
							$what 					=> $arEvents[$entityTypeKeyMy][$entity_id_key][$items_key][$what],
							$what."_IS_INHERITED" 	=> true,
							$what."_INHERITED_FROM"	=> $entity_id.'_all'
						);
				}	
				
				if (
					array_key_exists($entityTypeKeyMyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyMyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyMyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyMyCommon][0][$items_key], $event_id, $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyMyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'allmy_event'
						);						
				}
				
				if (
					array_key_exists($entityTypeKeyMyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyMyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyMyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyMyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyMyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'allmy_all'
						);
				}

				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], $event_id, $what);
					if ($itemKey !== false)
						return array(
							$what					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_event'
						);
				}

				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}

				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}
			else
			{
				// all events for friend
				if (
					array_key_exists($entityTypeKeyMyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyMyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyMyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyMyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyMyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED" 	=> true,
							$what."_INHERITED_FROM"	=> 'allmy_all'
						);
				}

				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}

				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}
		}
		else
		{
			if ($event_id != "all")
			{
				// defined event for not friend
				if (
					array_key_exists($entityTypeKey, $arEvents)
					&& array_key_exists($entity_id_key, $arEvents[$entityTypeKey])
					&& array_key_exists($items_key, $arEvents[$entityTypeKey][$entity_id_key])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKey][$entity_id_key][$items_key], "all", $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKey][$entity_id_key][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> $entity_id.'_all'
						);
				}

				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], $event_id, $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_event'
						);
				}

				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)				
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}
				
				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}
			else
			{
				// all events for not friend
				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)					
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}
				
				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}				
		}
	}
	else
	{
		// entity_id = 0
		if ($is_my)
		{
			if ($event_id != "all")
			{
				// defined event for all friends
				if (
					array_key_exists($entityTypeKeyMyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyMyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyMyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyMyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)					
						return array(
							$what 					=> $arEvents[$entityTypeKeyMyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'allmy_all'
						);
				}

				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], $event_id, $what);
					if ($itemKey !== false)	
						return array(
							$what					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_event'
						);
				}
				
				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)					
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}

				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}
			else
			{
				// all events for all friends
				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)				
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}

				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}
		}
		else
		{
			if ($event_id != "all")
			{
				// defined event for all users
				if (
					array_key_exists($entityTypeKeyCommon, $arEvents)
					&& array_key_exists(0, $arEvents[$entityTypeKeyCommon])
					&& array_key_exists($items_key, $arEvents[$entityTypeKeyCommon][0])
				)
				{
					$itemKey = __FindEventInItems($arEvents[$entityTypeKeyCommon][0][$items_key], "all", $what);
					if ($itemKey !== false)
						return array(
							$what 					=> $arEvents[$entityTypeKeyCommon][0][$items_key][$itemKey][$what],
							$what."_IS_INHERITED"	=> true,
							$what."_INHERITED_FROM"	=> 'all_all'
						);
				}

				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}
			else
			{
				// all events for not friend
				return array(
					$what					=> ($what == "TRANSPORT" ? "N" : ($what == "VISIBLE" ? "Y" : "")),
					$what."_IS_INHERITED" 	=> true,
					$what."_INHERITED_FROM"	=> 'root_all'
				);
			}				
		}
	}
}

?>