<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CAjax::Init();
CUtil::InitJSCore(array("ajax", "tooltip"));

if (!function_exists("__GetVisibleJS")) 
{
	function __GetVisibleJS($arRes, $bTopLevel = false)
	{
		if ($arRes["VISIBLE_IS_INHERITED"])
		{
			if ($bTopLevel)
				$strArCheckboxVal = "arCheckboxVal = ['I', 'N'];\n";
			elseif ($arRes["VISIBLE"] == "Y")
				$strArCheckboxVal = "arCheckboxVal = ['I', 'N', 'Y'];\n";
			else
				$strArCheckboxVal = "arCheckboxVal = ['I', 'Y', 'N'];\n";
			
			if ($bTopLevel)
				$strCheckboxClassName = "checkboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-".$arRes["VISIBLE"]."';\n";
			else
				$strCheckboxClassName = "checkboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-i-".$arRes["VISIBLE"]."';\n";
				
			$strHiddenValue = "hiddenValue = 'I'\n";
		}
		else
		{
			$strArCheckboxVal = "arCheckboxVal = ['Y', 'N'];\n";
			$strCheckboxClassName = "checkboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-".$arRes["VISIBLE"]."';\n";
			$strHiddenValue = "hiddenValue = '".$arRes["VISIBLE"]."'\n";
		}

		return array(
				"strArCheckboxVal"		=> $strArCheckboxVal,
				"strCheckboxClassName"	=> $strCheckboxClassName,
				"strHiddenValue"		=> $strHiddenValue
			);
	}
}

?><script language="JavaScript">
<!--
	BX.message({
		sonetSLGetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.subscribe_list/ajax.php')?>',
		sonetSLSessid: '<?=bitrix_sessid_get()?>',
		sonetSLLangId: '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
		sonetSLSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
		sonetSLInherited: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_INHERITED'))?>',
		sonetSLVisibleY: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_VISIBLE_Y'))?>',
		sonetSLVisibleN: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_VISIBLE_N'))?>',
		sonetSLTransportN: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_TRANSPORT_N'))?>',		
		sonetSLTransportM: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_TRANSPORT_M'))?>',
		sonetSLTransportX: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_TRANSPORT_X'))?>',
		sonetSLTransportD: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_TRANSPORT_D'))?>',
		sonetSLTransportE: '<?=CUtil::JSEscape(GetMessage('SONET_C30_T_TRANSPORT_E'))?>',
		sonetSLNoSubscriptions: '<?=CUtil::JSEscape(GetMessage('SONET_C30_NO_SUBSCRIPTIONS'))?>',
		sonetSLShowInList: '<?=CUtil::JSEscape(GetMessage('SONET_C30_SHOW_IN_LIST'))?>',
		sonetSLDeleteSubscription: '<?=GetMessage('SONET_C30_DELETE_SUBSCRIPTION')?>',
		sonetSLBUseVisible: '<?(!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true ? "Y" : "N")?>'
	});	
	var SLVisibleCheckbox = null;
	var arCheckboxVal = null;
	var checkboxClassName = null;
	var hiddenValue = null;
	
	var SLTree = new BX.CSLTree;
	var nodeTmp = null;
	var arVisibleCheckbox = [];
//-->
</script><?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	?>
	<div class="sonet-cntnr-subscribe-list">
	<form name="bx_sl_form" method="POST" action="<?=POST_FORM_ACTION_URI?>">
	<? 
	$bFirstBlock = true;
	foreach ($arResult["arSocNetAllowedSubscribeEntityTypes"] as $entity_type):

		if (
			in_array($entity_type, array(SONET_SUBSCRIBE_ENTITY_USER, SONET_SUBSCRIBE_ENTITY_GROUP))
			|| $bFirstBlock
		)
		{
			?>
			<table width="100%" class="subscribe-list-table">
			<tr>
				<td class="subscribe-list-header" colspan="2">
				<table width="100%">
				<tr>
					<td class="subscribe-list-header-left"><img src="/bitrix/images/1.gif" width="2" height="29"></td>
					<td class="subscribe-list-header-center"><b><?
					if (in_array($entity_type, array(SONET_SUBSCRIBE_ENTITY_USER, SONET_SUBSCRIBE_ENTITY_GROUP)))
						echo GetMessage("SONET_C30_SUBHEADER_".$entity_type);
					else
					{
						echo GetMessage("SONET_C30_SUBHEADER_OTHER");
						$bFirstBlock = false;
					}
					?></b></td>
					<td class="subscribe-list-header-right"><img src="/bitrix/images/1.gif" width="2" height="29"></td>
				</tr>
				</table>
				</td>
			</tr>
			</table>
			<?
		}
		?>
		<table width="100%" class="subscribe-list-table">
		<tr>
			<td valign="top" width="30%" class="subscribe-list-entity-td">
				<script language="JavaScript">
				<!--
				SLTree.Tree['<?=$entity_type?>_N'] = {
						'root': {}
					};

				SLTree.Tree['<?=$entity_type?>_N']['root'] = {
							'all': BX.create('DIV', {
								props: {
									'type': 'all',
									'node_type': 'root_all',
									'id': 'tree_<?=$entity_type?>_N_root_all'
								}
							})
					};
					
				var q = {props: { 'arEvents': {} }};
				q.props['arEvents']['all'] = {
							'Transport': 'N',
							'Visible': 'Y'
							};
							
				BX.adjust(SLTree.Tree['<?=$entity_type?>_N']['root']['all'], q);

				SLTree.Tree['<?=$entity_type?>_N']['all'] = {};

				SLTree.Tree['<?=$entity_type?>_N']['all']['all'] = SLTree.Tree['<?=$entity_type?>_N']['root']['all'].appendChild(BX.create('DIV', {
								props: {
									'type': 'all',
									'node_type': 'all_all',
									'arEvents':	{},
									'id': 'tree_<?=$entity_type?>_N_all_all'
								}
							}));

				SLTree.Tree['<?=$entity_type?>_N']['all']['event'] = SLTree.Tree['<?=$entity_type?>_N']['all']['all'].appendChild(BX.create('DIV', {
								props: {
									'type': 	'event',
									'node_type': 'all_event',
									'arEvents':	{},
									'id': 		'tree_<?=$entity_type?>_N_all_event'
								}
							}));
				-->
				</script>
				<?
				if (
					array_key_exists($entity_type, $arResult["EventsNew"])
					&& is_array($arResult["EventsNew"][$entity_type])
					&& count($arResult["EventsNew"][$entity_type]) > 0
				)
					$bHasEntities = true;
				else
					$bHasEntities = false;
				
				if ($bHasEntities)
				{
					?><a href="javascript:void(0)" onclick="sonet_sl_list_show('bx_sl_list_<?=$entity_type?>'); return false;"><div id="plus_bx_sl_list_<?=$entity_type?>" class="subscribe-list-selector subscribe-list-selector-plus"></div><?
				}
				?>
				<b><?=$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_LIST"]?></b>
				<?
				if ($bHasEntities)
				{
					?></a><?
				}
				?>
			</td>
			<td valign="top" width="70%" class="subscribe-list-entity-td">
				<?
				if (
					array_key_exists($entity_type, $arResult["ENTITY_TYPES"])
					&& count($arResult["ENTITY_TYPES"][$entity_type]) == 1
				)
				{
					$event_tmp = $arResult["ENTITY_TYPES"][$entity_type][0];
					$tree_node_tmp = "event";
				}
				else
				{
					$event_tmp = "all";
					$tree_node_tmp = "all";
				}
				
				if ($event_tmp == "all")
				{
					?><table width="100%"><?
				}
				else
				{
					?><table width="100%" class="subscribe-list-feature"><?
				}
				?>
					<tbody>
					<?
					if ($event_tmp != "all")
					{
						?>
						<tr class="subscribe-list-features">
							<td class="subscribe-list-corners" colspan="2">
								<div class="subscribe-list-features-lt"><div class="subscribe-list-features-rt"></div></div>
							</td>
						</tr>
						<?
					}
					
					if ($event_tmp == "all")
					{
						?>
						<tr>
							<td width="30%" valign="top">
						<?
					}
					else
					{
						?>
						<tr class="subscribe-list-features">
							<td width="30%" valign="top" class="subscribe-list-feature-name">
						<?					
					}

							if ($event_tmp == "all")
							{
								?>
							<a href="javascript:void(0)" onclick="sonet_sl_get('bx_sl_<?=$entity_type?>_all', '<?=$entity_type?>', 'all'); return false;"><div id="plus_bx_sl_<?=$entity_type?>_all" class="subscribe-list-selector subscribe-list-selector-plus"></div>
							<?=GetMessage("SONET_C30_T_all")?></a>
								<?
							}
							?>
						</td>
						<td width="70%" valign="top" id="v_bx_sl_<?=$entity_type?>_all_<?=$event_tmp?>_td">
							<?
							$arRes = __GetInheritedValue($arResult["EventsNew"], $entity_type, 0, false, false, $event_tmp, "TRANSPORT");
							?>
							<script language="JavaScript">
							<!--
							var q = {props: { 'arEvents': {} }};
							q.props['arEvents']['<?=$event_tmp?>'] = {
										'Transport': '<?=($arRes["TRANSPORT_IS_INHERITED"] ? 'I' : $arRes["TRANSPORT"])?>'
										<?
										if ($arRes["TRANSPORT_IS_INHERITED"])
										{
											?>,
											'TransportInheritedFrom': '<?=$arRes["TRANSPORT_INHERITED_FROM"]?>'
											<?
										}
										?>
									};
							-->
							</script>							
							<select name="t_bx_sl_<?=$entity_type?>_all_<?=$event_tmp?>" id="t_N_bx_sl_<?=$entity_type?>_all_<?=$event_tmp?>">
							<?
							foreach($arResult["Transport"] as $key => $value):
							
								$optioni_id = false;

								if ($arRes["TRANSPORT"] == $key)
								{
									$selected = " selected";
									if ($arRes["TRANSPORT_IS_INHERITED"])
									{
										$key = "I";
										$optioni_id = "t_N_bx_sl_".$entity_type."_all_".$event_tmp."_optioni";
									}
								}
								else
									$selected = "";							
								?>
								<option value="<?=$key?>"<?=$selected?><?=($optioni_id ? ' id="'.$optioni_id.'"' : '')?>><?=$value?></option>
								<?
							endforeach;
							?>
							</select>
							<script language="JavaScript">
							<!--
							BX.bind(BX('t_N_bx_sl_<?=$entity_type?>_all_<?=$event_tmp?>'), "change", BX.delegate(SLTree.onChangeTransport, SLTree));
							-->
							</script>
							<script language="JavaScript">
							<!--
							<?
							if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
							{
								$arRes = __GetInheritedValue($arResult["EventsNew"], $entity_type, 0, false, false, $event_tmp, "VISIBLE");
								?>
								q.props['arEvents']['<?=$event_tmp?>']['Visible'] = '<?=($arRes["VISIBLE_IS_INHERITED"] ? 'I' : $arRes["VISIBLE"])?>';
								<?
								if ($arRes["VISIBLE_IS_INHERITED"])
								{
									?>
									q.props['arEvents']['<?=$event_tmp?>']['VisibleInheritedFrom'] = '<?=$arRes["VISIBLE_INHERITED_FROM"]?>';
									<?
								}
							}
							?>
							BX.adjust(SLTree.Tree['<?=$entity_type?>_N']['all']['<?=$tree_node_tmp?>'], q);
							-->
							</script><?

							if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
							{
								$arVisibleJS = __GetVisibleJS($arRes, true);
								?>
								<script language="JavaScript">
								<!--
								<?=$arVisibleJS["strArCheckboxVal"]?>
								<?=$arVisibleJS["strCheckboxClassName"]?>
								<?=$arVisibleJS["strHiddenValue"]?>

								SLVisibleCheckbox = new BX.CSLVisibleCheckbox(
									{
										'arCheckboxVal': arCheckboxVal,
										'bindElement': BX('v_bx_sl_<?=$entity_type?>_all_<?=$event_tmp?>_td'),
										'checkboxClassName': checkboxClassName,
										'node': 'bx_sl_<?=$entity_type?>_all',
										'cb': '',
										'feature': '<?=$event_tmp?>',
										'hiddenValue': hiddenValue,
										'visibleValue': '<?=$arRes["VISIBLE"]?>',
										'bTopLevel': true
									}
								);
								arVisibleCheckbox['bx_sl_<?=$entity_type?>_all_<?=$event_tmp?>'] = SLVisibleCheckbox;
								SLVisibleCheckbox.Show();
								-->
								</script><?
							}
						?></td>
					</tr>
					<?
					if ($event_tmp != "all")
					{
						?>
						<tr class="subscribe-list-features">
							<td class="subscribe-list-corners" colspan="2">
								<div class="subscribe-list-features-lb"><div class="subscribe-list-features-rb"></div></div>
							</td>
						</tr>
						<tr>
							<td class="subscribe-list-feature-sep" colspan="2"></td>
						</tr>
						<?
					}					
					?>
					</tbody>
				</table>
				<?
				if ($event_tmp == "all")
				{
					?>
					<div id="bx_sl_<?=$entity_type?>_all" style="display: none;">
					<div id="bx_sl_<?=$entity_type?>_all_content"></div>
					</div>
					<?
				}
				?>
			</td>
		</tr>
		</table>
		<?
		if (array_key_exists($entity_type, $arResult["EventsNew"])):
		?><div id="bx_sl_list_<?=$entity_type?>" style="display: none;"><?
		foreach ($arResult["EventsNew"][$entity_type] as $eventKey => $event):?>
			<table width="100%" class="subscribe-list-table">
			<tr id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_tr">
				<td valign="top" width="30%" class="subscribe-list-entity-td subscribe-list-entity-name">
					<script language="JavaScript">
					<!--
					SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>] = {};
					
					SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['all'] = SLTree.Tree['<?=$entity_type?>_N']['all']['event'].appendChild(BX.create('DIV', {
									props: {
										'type': 'all',		
										'node_type': 'entity_all',
										'id': 'tree_<?=$entity_type?>_N_<?=$event["ENTITY_ID"]?>_all'
									}
								}));
								
					SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['event'] = SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['all'].appendChild(BX.create('DIV', {
									props: {
										'type': 	'event',
										'node_type': 'entity_event',
										'arEvents':	{},
										'id': 		'tree_<?=$entity_type?>_N_<?=$event["ENTITY_ID"]?>_event'
									}
								}));
					-->
					</script>				
					<?
					$name = call_user_func(
						array(
							$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$event["ENTITY_TYPE"]]["CLASS_DESC_SHOW"],
							$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$event["ENTITY_TYPE"]]["METHOD_DESC_SHOW"]
						),
						$event["ENTITY_DESC"],
						$event["ENTITY_URL"],
						$arParams
					);
					echo $name;
					?>
				</td>
				<td valign="top" width="70%" class="subscribe-list-entity-td">
					<?
					if ($event_tmp == "all")
					{
						?><table width="100%"><?
					}
					else
					{
						?><table width="100%" class="subscribe-list-feature"><?
					}
					
					?>
					<tbody>
						<?
						if ($event_tmp != "all")
						{
							?>
							<tr class="subscribe-list-features">
								<td class="subscribe-list-corners" colspan="3">
									<div class="subscribe-list-features-lt"><div class="subscribe-list-features-rt"></div></div>
								</td>
							</tr>
							<?
						}
						
						if ($event_tmp == "all")
						{
							?>
							<tr>
								<td width="30%" valign="top">
							<?
						}
						else
						{
							?>
							<tr class="subscribe-list-features">
								<td width="30%" valign="top" class="subscribe-list-feature-name">
							<?					
						}
					
							if ($event_tmp == "all")
							{
								?>
								<a href="javascript:void(0)" onclick="sonet_sl_get('bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>', '<?=$event["ENTITY_TYPE"]?>', '<?=$event["ENTITY_ID"]?>'); return false;"><div id="plus_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>" class="subscribe-list-selector subscribe-list-selector-plus"></div>
								<?=GetMessage("SONET_C30_T_all")?></a>
								<?
							}
							?>
							</td>
							<td width="65%" valign="top" id="v_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_<?=$event_tmp?>_td">
								<?
								$arRes = __GetInheritedValue($arResult["EventsNew"], $event["ENTITY_TYPE"], $event["ENTITY_ID"], false, false, $event_tmp, "TRANSPORT");
								?>
								<script language="JavaScript">
								<!--
								var q = {props: { 'arEvents': {} }};
								q.props['arEvents']['<?=$event_tmp?>'] = {
											'Transport': '<?=($arRes["TRANSPORT_IS_INHERITED"] ? 'I' : $arRes["TRANSPORT"])?>'
											<?
											if ($arRes["TRANSPORT_IS_INHERITED"])
											{
												?>,
												'TransportInheritedFrom': '<?=$arRes["TRANSPORT_INHERITED_FROM"]?>'
												<?
											}
											?>
										};
								-->
								</script>
								<select name="t_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_<?=$event_tmp?>" id="t_N_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_<?=$event_tmp?>">
								<?
								if ($arRes["TRANSPORT_IS_INHERITED"])
								{
									?><option value="I" selected id="t_N_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all_optioni"><?=GetMessage("SONET_C30_T_INHERITED")." (".GetMessage("SONET_C30_T_TRANSPORT_".$arRes["TRANSPORT"]).")";?></option><?
								}							

								foreach($arResult["Transport"] as $key => $value):
									if ($arRes["TRANSPORT"] == $key && !$arRes["TRANSPORT_IS_INHERITED"])
										$selected = " selected";
									else
										$selected = "";
									?>
									<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
									<?
								endforeach;
								?>
								</select>
								<script language="JavaScript">
								<!--
								BX.bind(BX('t_N_bx_sl_<?=$entity_type?>_<?=$event["ENTITY_ID"]?>_<?=$event_tmp?>'), "change", BX.delegate(SLTree.onChangeTransport, SLTree));
								-->
								</script>
								<script language="JavaScript">
								<!--
								<?
								if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
								{
									$arRes = __GetInheritedValue($arResult["EventsNew"], $event["ENTITY_TYPE"], $event["ENTITY_ID"], false, false, $event_tmp, "VISIBLE");
									?>

									q.props['arEvents']['<?=$event_tmp?>']['Visible'] = '<?=($arRes["VISIBLE_IS_INHERITED"] ? 'I' : $arRes["VISIBLE"])?>';
									<?
									if ($arRes["VISIBLE_IS_INHERITED"])
									{
										?>
										q.props['arEvents']['<?=$event_tmp?>']['VisibleInheritedFrom'] = '<?=$arRes["VISIBLE_INHERITED_FROM"]?>';
										<?
									}
								}
								?>
								BX.adjust(SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['<?=$tree_node_tmp?>'], q);
								-->
								</script>
								<?
								if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
								{
									$arVisibleJS = __GetVisibleJS($arRes);
									?>
									<script language="JavaScript">
									<!--
									<?=$arVisibleJS["strArCheckboxVal"]?>
									<?=$arVisibleJS["strCheckboxClassName"]?>
									<?=$arVisibleJS["strHiddenValue"]?>

									SLVisibleCheckbox = new BX.CSLVisibleCheckbox(
										{
											'arCheckboxVal': arCheckboxVal,
											'bindElement': BX('v_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_<?=$event_tmp?>_td'),
											'checkboxClassName': checkboxClassName,
											'node': 'bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>',
											'cb': '',
											'feature': '<?=$event_tmp?>',
											'hiddenValue': hiddenValue,
											'visibleValue': '<?=$arRes["VISIBLE"]?>'
										}
									);
									arVisibleCheckbox['bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_<?=$event_tmp?>'] = SLVisibleCheckbox;
									SLVisibleCheckbox.Show();
									-->
									</script><?
								}
							?></td>
							<td width="5%">
								<a title="<?=GetMessage("SONET_C30_DELETE_SUBSCRIPTION")?>" class="subscribe-list-del" href="javascript:void(0)" onclick="sonet_sl_del('bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_tr', '<?=$event["ENTITY_TYPE"]?>', '<?=$event["ENTITY_ID"]?>', 'all'); return false;"></a>
							</td>
						</tr>
						<?
						if ($event_tmp != "all")
						{
							?>
							<tr class="subscribe-list-features">
								<td class="subscribe-list-corners" colspan="3">
									<div class="subscribe-list-features-lb"><div class="subscribe-list-features-rb"></div></div>
								</td>
							</tr>
							<tr>
								<td class="subscribe-list-feature-sep" colspan="3"></td>
							</tr>
							<?
						}
						?>
					</tbody>	
					</table>
					<?
					if ($event_tmp == "all")
					{
						?>
						<div id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>" style="display: none;">
							<div id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_content"></div>
						</div>
						<?
					}
					?>
				</td>
			</tr>
			</table>
		<?endforeach;
		?></div><?
		endif;
		
		/* my */
		?>
		<script language="JavaScript">
		<!--
		SLTree.Tree['<?=$entity_type?>_N']['allmy'] = {};
				
		SLTree.Tree['<?=$entity_type?>_N']['allmy']['all'] = SLTree.Tree['<?=$entity_type?>_N']['all']['event'].appendChild(BX.create('DIV', {
				props: {
					'type': 'all',
					'node_type': 'allmy_all',
					'id': 'tree_<?=$entity_type?>_N_allmy_all'
				}
			}));
			
		SLTree.Tree['<?=$entity_type?>_N']['allmy']['event'] = SLTree.Tree['<?=$entity_type?>_N']['allmy']['all'].appendChild(BX.create('DIV', {
				props: {
					'type': 	'event',
					'node_type': 'allmy_event',
					'arEvents':	{},
					'id': 		'tree_<?=$entity_type?>_N_allmy_event'
				}
			}));
		-->
		</script>	
		<?
		if (
			is_array($arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("HAS_MY", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]) 
			&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_MY"] == "Y"
		):
			?>
			<table width="100%" class="subscribe-list-table">
			<tr>
				<td valign="top" width="30%" class="subscribe-list-entity-td">
					<?
					if (
						array_key_exists($entity_type."_My", $arResult["EventsNew"])
						&& is_array($arResult["EventsNew"][$entity_type."_My"])
						&& count($arResult["EventsNew"][$entity_type."_My"]) > 0
					)
						$bHasEntities = true;
					else
						$bHasEntities = false;
					
					if ($bHasEntities)
					{
						?><a href="javascript:void(0)" onclick="sonet_sl_list_show('bx_sl_list_<?=$entity_type?>_my'); return false;"><div id="plus_bx_sl_list_<?=$entity_type?>_my" class="subscribe-list-selector subscribe-list-selector-plus"></div><?
					}
					?>
					<b><?=$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_LIST_MY"]?></b>
					<?
					if ($bHasEntities)
					{
						?></a><?
					}
					?>					
				</td>
				<td valign="top" width="70%" class="subscribe-list-entity-td">
					<table width="100%">
						<tr>
							<td width="30%" valign="top">
								<a href="javascript:void(0)" onclick="sonet_sl_get('bx_sl_<?=$entity_type?>_allmy', '<?=$entity_type?>', 'allmy'); return false;"><div id="plus_bx_sl_<?=$entity_type?>_allmy" class="subscribe-list-selector subscribe-list-selector-plus"></div>
								<?=GetMessage("SONET_C30_T_all")?></a>
							</td>
							<td width="70%" valign="top" id="v_bx_sl_<?=$entity_type?>_allmy_all_td">
								<?
								$arRes = __GetInheritedValue($arResult["EventsNew"], $entity_type, 0, false, true, "all", "TRANSPORT");
								?>
								<script language="JavaScript">
								<!--
								var q = {props: { 'arEvents': {} }};
								q.props['arEvents']['all'] = {
											'Transport': '<?=($arRes["TRANSPORT_IS_INHERITED"] ? 'I' : $arRes["TRANSPORT"])?>'
											<?
											if ($arRes["TRANSPORT_IS_INHERITED"])
											{
												?>,
												'TransportInheritedFrom': '<?=$arRes["TRANSPORT_INHERITED_FROM"]?>'
												<?
											}
											?>
										};
								-->
								</script>
								<select name="t_bx_sl_<?=$entity_type?>_allmy_all" id="t_N_bx_sl_<?=$entity_type?>_allmy_all">
								<?
								if ($arRes["TRANSPORT_IS_INHERITED"])
								{
									?><option value="I" selected id="t_N_bx_sl_<?=$entity_type?>_allmy_all_optioni"><?=GetMessage("SONET_C30_T_INHERITED")." (".GetMessage("SONET_C30_T_TRANSPORT_".$arRes["TRANSPORT"]).")";?></option><?
								}
								
								foreach($arResult["Transport"] as $key => $value):
									if ($arRes["TRANSPORT"] == $key && !$arRes["TRANSPORT_IS_INHERITED"])
										$selected = " selected";
									else
										$selected = "";								
									?>
									<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
									<?
								endforeach;
								?>
								</select>
								<script language="JavaScript">
								<!--
								BX.bind(BX('t_N_bx_sl_<?=$entity_type?>_allmy_all'), "change", BX.delegate(SLTree.onChangeTransport, SLTree));
								-->
								</script>
								<script language="JavaScript">
								<!--
								<?
								if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
								{
									$arRes = __GetInheritedValue($arResult["EventsNew"], $entity_type, 0, false, true, "all", "VISIBLE");
									?>
									q.props['arEvents']['all']['Visible'] = '<?=($arRes["VISIBLE_IS_INHERITED"] ? 'I' : $arRes["VISIBLE"])?>';
									<?
									if ($arRes["VISIBLE_IS_INHERITED"])
									{
										?>
										q.props['arEvents']['all']['VisibleInheritedFrom'] = '<?=$arRes["VISIBLE_INHERITED_FROM"]?>';
										<?
									}
								}
								?>
								BX.adjust(SLTree.Tree['<?=$entity_type?>_N']['allmy']['all'], q);
								-->
								</script>
								<?
								if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
								{
									$arVisibleJS = __GetVisibleJS($arRes);
									?>
									<script language="JavaScript">
									<!--
									<?=$arVisibleJS["strArCheckboxVal"]?>
									<?=$arVisibleJS["strCheckboxClassName"]?>
									<?=$arVisibleJS["strHiddenValue"]?>

									SLVisibleCheckbox = new BX.CSLVisibleCheckbox(
										{
											'arCheckboxVal': arCheckboxVal,
											'bindElement': BX('v_bx_sl_<?=$entity_type?>_allmy_all_td'),
											'checkboxClassName': checkboxClassName,
											'node': 'bx_sl_<?=$entity_type?>_allmy',
											'cb': '',
											'feature': 'all',
											'hiddenValue': hiddenValue,
											'visibleValue': '<?=$arRes["VISIBLE"]?>'
										}
									);
									arVisibleCheckbox['bx_sl_<?=$entity_type?>_allmy_all'] = SLVisibleCheckbox;
									SLVisibleCheckbox.Show();
									-->
									</script>
									<?
								}
							?></td>
						</tr>
					</table>
					<div id="bx_sl_<?=$entity_type?>_allmy" style="display: none;">
						<div id="bx_sl_<?=$entity_type?>_allmy_content"></div>
					</div>
				</td>
			</tr>
			</table width="100%">
			<?
			if (array_key_exists($entity_type."_My", $arResult["EventsNew"])):
			?><div id="bx_sl_list_<?=$entity_type?>_my" style="display: none;"><?
			foreach ($arResult["EventsNew"][$entity_type."_My"] as $eventKey => $event):?>
				<table width="100%" class="subscribe-list-table">
				<tr id="bx_sl_<?=$entity_type?>_<?=$event["ENTITY_ID"]?>_tr">
					<td valign="top" width="30%" class="subscribe-list-entity-td subscribe-list-entity-name">
						<script language="JavaScript">
						<!--
						SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>] = {};
						
						SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['all'] = SLTree.Tree['<?=$entity_type?>_N']['allmy']['event'].appendChild(BX.create('DIV', {
										props: {
											'type': 'all',		
											'node_type': 'entity_all',
											'id': 'tree_<?=$entity_type?>_N_<?=$event["ENTITY_ID"]?>_all'
										}
									}));
									
						SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['event'] = SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['all'].appendChild(BX.create('DIV', {
										props: {
											'type': 	'event',
											'node_type': 'entity_event',
											'arEvents':	{},
											'id': 		'tree_<?=$entity_type?>_N_<?=$event["ENTITY_ID"]?>_event'
										}
									}));
						-->
						</script>								
						<?
						$name = call_user_func(
							array(
								$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$event["ENTITY_TYPE"]]["CLASS_DESC_SHOW"],
								$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$event["ENTITY_TYPE"]]["METHOD_DESC_SHOW"]
							),
							$event["ENTITY_DESC"],
							$event["ENTITY_URL"],
							$arParams
						);
						echo $name;
						?>
					</td>
					<td valign="top" width="70%" class="subscribe-list-entity-td">
						<table width="100%">
							<tr>
								<td width="30%" valign="top">
									<a href="javascript:void(0)" onclick="sonet_sl_get('bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>', '<?=$event["ENTITY_TYPE"]?>', '<?=$event["ENTITY_ID"]?>'); return false;"><div id="plus_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>" class="subscribe-list-selector subscribe-list-selector-plus"></div>
									<?=GetMessage("SONET_C30_T_all")?></a>
								</td>
								<td width="65%" valign="top" id="v_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all_td">
									<?
									$arRes = __GetInheritedValue($arResult["EventsNew"], $entity_type, $event["ENTITY_ID"], false, true, "all", "TRANSPORT");
									?>
									<script language="JavaScript">
									<!--
									var q = {props: { 'arEvents': {} }};
									q.props['arEvents']['all'] = {
												'Transport': '<?=($arRes["TRANSPORT_IS_INHERITED"] ? 'I' : $arRes["TRANSPORT"])?>'
												<?
												if ($arRes["TRANSPORT_IS_INHERITED"])
												{
													?>,
													'TransportInheritedFrom': '<?=$arRes["TRANSPORT_INHERITED_FROM"]?>'
													<?
												}
												?>
											};
									-->
									</script>
									<select name="t_bx_sl_<?=$entity_type?>_<?=$event["ENTITY_ID"]?>_all" id="t_N_bx_sl_<?=$entity_type?>_<?=$event["ENTITY_ID"]?>_all">
									<?
									if ($arRes["TRANSPORT_IS_INHERITED"])
									{
										?><option value="I" selected id="t_N_bx_sl_<?=$entity_type?>_<?=$event["ENTITY_ID"]?>_all_optioni"><?=GetMessage("SONET_C30_T_INHERITED")." (".GetMessage("SONET_C30_T_TRANSPORT_".$arRes["TRANSPORT"]).")";?></option><?
									}

									foreach($arResult["Transport"] as $key => $value):
										if ($arRes["TRANSPORT"] == $key && !$arRes["TRANSPORT_IS_INHERITED"])
											$selected = " selected";
										else
											$selected = "";								
										?>
										<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
										<?
									endforeach;
									?>
									</select>
									<script language="JavaScript">
									<!--
									BX.bind(BX('t_N_bx_sl_<?=$entity_type?>_<?=$event["ENTITY_ID"]?>_all'), "change", BX.delegate(SLTree.onChangeTransport, SLTree));
									-->
									</script>
									<script language="JavaScript">
									<!--
									<?
									if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
									{
										$arRes = __GetInheritedValue($arResult["EventsNew"], $entity_type, $event["ENTITY_ID"], false, true, "all", "VISIBLE");
										?>
										q.props['arEvents']['all']['Visible'] = '<?=($arRes["VISIBLE_IS_INHERITED"] ? 'I' : $arRes["VISIBLE"])?>';
										<?
										if ($arRes["VISIBLE_IS_INHERITED"])
										{
											?>
											q.props['arEvents']['all']['VisibleInheritedFrom'] = '<?=$arRes["VISIBLE_INHERITED_FROM"]?>';
											<?
										}
									}
									?>
									BX.adjust(SLTree.Tree['<?=$entity_type?>_N'][<?=$event["ENTITY_ID"]?>]['all'], q);
									-->
									</script>
									<?
									if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
									{
										$arVisibleJS = __GetVisibleJS($arRes);
										?>
										<script language="JavaScript">
										<!--
										<?=$arVisibleJS["strArCheckboxVal"]?>
										<?=$arVisibleJS["strCheckboxClassName"]?>
										<?=$arVisibleJS["strHiddenValue"]?>
										
										SLVisibleCheckbox = new BX.CSLVisibleCheckbox(
											{
												'arCheckboxVal': arCheckboxVal,
												'bindElement': BX('v_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all_td'),
												'checkboxClassName': checkboxClassName,
												'node': 'bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>',
												'cb': '',
												'feature': 'all',
												'hiddenValue': hiddenValue,
												'visibleValue': '<?=$arRes["VISIBLE"]?>'
											}
										);
										arVisibleCheckbox['bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all'] = SLVisibleCheckbox;
										SLVisibleCheckbox.Show();
										-->
										</script>
										<?
									}
								?></td>
								<td width="5%">
									<a title="<?=GetMessage("SONET_C30_DELETE_SUBSCRIPTION")?>" class="subscribe-list-del" href="javascript:void(0)" onclick="sonet_sl_del('bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_tr', '<?=$event["ENTITY_TYPE"]?>', '<?=$event["ENTITY_ID"]?>', 'all'); return false;"></a>
								</td>
							</tr>
						</table>
						<div id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>" style="display: none;">
							<div id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_content"></div>
						</div>
					</td>
				</tr>
				</table>
			<?endforeach;
			?></div><?
			endif;
		endif;
		?>
		<?
		
		/* created by */
		
		if (
			is_array($arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("HAS_CB", $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]) 
			&& $arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_CB"] == "Y"
			&& array_key_exists($entity_type."_CB", $arResult["EventsNew"])
			&& is_array($arResult["EventsNew"][$entity_type."_CB"])
			&& count($arResult["EventsNew"][$entity_type."_CB"]) > 0
		):
			?>
			<table width="100%" class="subscribe-list-table">
			<tr>
				<td valign="top" colspan="2" class="subscribe-list-entity-td">
					<a href="javascript:void(0)" onclick="sonet_sl_list_show('bx_sl_list_<?=$entity_type?>_CB'); return false;"><div id="plus_bx_sl_list_<?=$entity_type?>_CB" class="subscribe-list-selector subscribe-list-selector-plus"></div><b><?=GetMessage("SONET_C30_CREATED_BY");?></b></a>
				</td>
			</tr>
			</table>
			<script language="JavaScript">
			<!--
			SLTree.Tree['<?=$entity_type?>_Y'] = {};
			-->
			</script>
			<div id="bx_sl_list_<?=$entity_type?>_CB" style="display: none;">			
			<?foreach ($arResult["EventsNew"][$entity_type."_CB"] as $eventKey => $event):?>
				<table width="100%" class="subscribe-list-table">
				<tr id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb_tr">
					<td valign="top" width="30%" class="subscribe-list-entity-td subscribe-list-entity-name">
						<script language="JavaScript">
						<!--
						SLTree.Tree['<?=$entity_type?>_Y'][<?=$event["ENTITY_ID"]?>] = {};
						
						SLTree.Tree['<?=$entity_type?>_Y'][<?=$event["ENTITY_ID"]?>]['all'] = SLTree.Tree['<?=$entity_type?>_N']['root']['all'].appendChild(BX.create('DIV', {
										props: {
											'type': 'all',
											'entity_type': 'entity_all',
											'id': 'tree_<?=$entity_type?>_Y_<?=$event["ENTITY_ID"]?>_all'
										}
									}));

						SLTree.Tree['<?=$entity_type?>_Y'][<?=$event["ENTITY_ID"]?>]['event'] = SLTree.Tree['<?=$entity_type?>_Y'][<?=$event["ENTITY_ID"]?>]['all'].appendChild(BX.create('DIV', {
										props: {
											'type': 	'event',
											'entity_type': 'entity_event',
											'arEvents':	{},
											'id': 		'tree_<?=$entity_type?>_Y_<?=$event["ENTITY_ID"]?>_event'
										}
									}));
						-->
						</script>
						<?
						$name = call_user_func(
							array(
								$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$event["ENTITY_TYPE"]]["CLASS_DESC_SHOW"],
								$arResult["arSocNetAllowedSubscribeEntityTypesDesc"][$event["ENTITY_TYPE"]]["METHOD_DESC_SHOW"]
							),
							$event["ENTITY_DESC"],
							$event["ENTITY_URL"],
							$arParams
						);
						echo $name;
						?>
					</td>
					<td valign="top" width="70%" class="subscribe-list-entity-td">
						<table width="100%">
							<tr>
								<td width="30%" valign="top">
									<a href="javascript:void(0)" onclick="sonet_sl_get('bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb', '<?=$event["ENTITY_TYPE"]?>', '<?=$event["ENTITY_ID"]?>', 'Y'); return false;"><div id="plus_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb" class="subscribe-list-selector subscribe-list-selector-plus"></div>
									<?=GetMessage("SONET_C30_T_all")?></a>
								</td>
								<td width="65%" valign="top" id="v_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb_td">
									<?
									$arRes = __GetInheritedValue($arResult["EventsNew"], $event["ENTITY_TYPE"], $event["ENTITY_ID"], true, false, "all", "TRANSPORT");
									?>
									<script language="JavaScript">
									<!--
									var q = {props: { 'arEvents': {} }};
									q.props['arEvents']['all'] = {
												'Transport': '<?=($arRes["TRANSPORT_IS_INHERITED"] ? 'I' : $arRes["TRANSPORT"])?>'
												<?
												if ($arRes["TRANSPORT_IS_INHERITED"])
												{
													?>,
													'TransportInheritedFrom': '<?=$arRes["TRANSPORT_INHERITED_FROM"]?>'
													<?
												}
												?>
											};
									-->
									</script>
									<select name="t_cb_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all" id="t_Y_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all">
									<?
									if ($arRes["TRANSPORT_IS_INHERITED"])
									{
										?><option value="I" selected id="t_Y_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all_optioni"><?=GetMessage("SONET_C30_T_INHERITED")." (".GetMessage("SONET_C30_T_TRANSPORT_".$arRes["TRANSPORT"]).")";?></option><?
									}							

									foreach($arResult["Transport"] as $key => $value):
										if ($arRes["TRANSPORT"] == $key && !$arRes["TRANSPORT_IS_INHERITED"])
											$selected = " selected";
										else
											$selected = "";
										?><option value="<?=$key?>"<?=$selected?>><?=$value?></option><?
									endforeach;
									?>
									</select>
									<script language="JavaScript">
									<!--
									BX.bind(BX('t_Y_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all'), "change", BX.delegate(SLTree.onChangeTransport, SLTree));
									-->
									</script>									
									<script language="JavaScript">
									<!--
									<?
									if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
									{
										$arRes = __GetInheritedValue($arResult["EventsNew"], $event["ENTITY_TYPE"], $event["ENTITY_ID"], true, true, "all", "VISIBLE");
										?>
										q.props['arEvents']['all']['Visible'] = '<?=($arRes["VISIBLE_IS_INHERITED"] ? 'I' : $arRes["VISIBLE"])?>';
										<?
										if ($arRes["VISIBLE_IS_INHERITED"])
										{
											?>
											q.props['arEvents']['all']['VisibleInheritedFrom'] = '<?=$arRes["VISIBLE_INHERITED_FROM"]?>';
											<?
										}
									}
									?>
									BX.adjust(SLTree.Tree['<?=$entity_type?>_Y'][<?=$event["ENTITY_ID"]?>]['all'], q);
									-->
									</script>
									<?
									if (!defined("DisableSonetLogVisibleSubscr") || DisableSonetLogVisibleSubscr !== true)
									{
										$arVisibleJS = __GetVisibleJS($arRes);
										?>
										<script language="JavaScript">
										<!--
										<?=$arVisibleJS["strArCheckboxVal"]?>
										<?=$arVisibleJS["strCheckboxClassName"]?>
										<?=$arVisibleJS["strHiddenValue"]?>

										SLVisibleCheckbox = new BX.CSLVisibleCheckbox(
										{
												'arCheckboxVal': arCheckboxVal,
												'bindElement': BX('v_bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb_td'),
												'checkboxClassName': checkboxClassName,
												'node': 'bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb',
												'cb': 'cb_',
												'feature': 'all',
												'hiddenValue': hiddenValue,
												'visibleValue': '<?=$arRes["VISIBLE"]?>'
											}
										);
										arVisibleCheckbox['bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_all_cb'] = SLVisibleCheckbox;
										SLVisibleCheckbox.Show();
										-->
										</script>
										<?
									}
								?></td>
								<td width="5%">
									<a title="<?=GetMessage("SONET_C30_DELETE_SUBSCRIPTION")?>" class="subscribe-list-del" href="javascript:void(0)" onclick="sonet_sl_del('bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb_tr', '<?=$event["ENTITY_TYPE"]?>', '<?=$event["ENTITY_ID"]?>', 'all', 'Y'); return false;"></a>
								</td>
							</tr>
						</table>
						<div id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb" style="display: none;">
							<div id="bx_sl_<?=$event["ENTITY_TYPE"]?>_<?=$event["ENTITY_ID"]?>_cb_content"></div>
						</div>
					</td>
				</tr>
				</table>
			<?endforeach;?>
			</div>			
			<?
		endif;
	endforeach;
	?>
	<div class="subscribe-list-submit"><input type="submit" name="save" value="<?= GetMessage("SONET_C30_T_SUBMIT") ?>"></div>
	<?=bitrix_sessid_post()?>
	</form>
	</div>
	<?
}
?>