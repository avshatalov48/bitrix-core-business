<?php

namespace Bitrix\Vote\Uf;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Vote\AttachTable;

Loc::loadMessages(__FILE__);

final class VoteUserType
{
	const TYPE_NEW_ATTACH      = 'NewAttach';
	const TYPE_SAVED_ATTACH = 'SavedAttach';
	const NEW_VOTE_PREFIX = 'n';

	/**
	 * @return array
	 */
	public static function getUserTypeDescription()
	{
		AddEventHandler("main", "OnBeforeUserTypeUpdate", array(__CLASS__, "checkSettings"));
		AddEventHandler("main", "OnBeforeUserTypeAdd", array(__CLASS__, "checkSettings"));
		if (IsModuleInstalled("blog"))
		{
			AddEventHandler("blog", "OnBeforePostUserFieldUpdate", array(__CLASS__, "onBeforePostUserFieldUpdate"));
		}

		return array(
			"USER_TYPE_ID" => "vote",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => Loc::getMessage("V_USER_TYPE_DESCRIPTION"),
			"BASE_TYPE" => "int",
		);
	}

	/**
	 * @param $userField
	 * @return string
	 */
	public static function getDBColumnType($userField)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		if($connection instanceof \Bitrix\Main\DB\MysqlCommonConnection)
		{
			return 'int(11)';
		}
		if($connection instanceof \Bitrix\Main\DB\OracleConnection)
		{
			return 'number(18)';
		}
		if($connection instanceof \Bitrix\Main\DB\MssqlConnection)
		{
			return 'int';
		}

		throw new \Bitrix\Main\NotSupportedException("The '{$connection->getType()}' is not supported in current context");
	}

	/**
	 * @param string $entityId BLOG_POST.
	 * @param integer $ID Blog ID.
	 * @param array $fields Array (
			[ID] => 29,
			[URL] => "",
			[BLOG] => "",
			...).
	 * @return void
	 */
	public static function onBeforePostUserFieldUpdate($entityId, $ID, $fields)
	{
		global $USER_FIELD_MANAGER;
		$userFields = $USER_FIELD_MANAGER->GetUserFields($entityId, $ID, LANGUAGE_ID);
		if (is_array($userFields) && !empty($userFields))
		{
			$userFields = array_intersect_key($userFields, $fields);
			$path = str_replace("#post_id#", $ID, $fields["PATH"]);
			$userField = reset($userFields);
			do {
				if (is_array($userField["USER_TYPE"]) &&
					$userField["USER_TYPE"]["USER_TYPE_ID"] == "vote" &&
					$userField["USER_TYPE"]["CLASS_NAME"] == __CLASS__ &&
					isset($GLOBALS[__CLASS__.$userField["ENTITY_VALUE_ID"]]))
				{
					$GLOBALS[__CLASS__.$userField["ENTITY_VALUE_ID"]]["VOTE"]["URL"] = $path;
				}
			} while ($userField = next($userFields));
		}
	}

	/**
	 * Prepares data("SETTINGS") for serialization in functions CUserTypeEntity::Add & CUserTypeEntity::Update
	 * @param array $userField Array (
			[ID] => 29
			[ENTITY_ID] => BLOG_POST
			[FIELD_NAME] => UF_BLOG_POST_VOTE
			[USER_TYPE_ID] => vote
			[XML_ID] => UF_BLOG_POST_VOTE
			[SORT] => 100
			[MULTIPLE] => N
			[MANDATORY] => N
			[SHOW_FILTER] => N
			[SHOW_IN_LIST] => Y
			[EDIT_IN_LIST] => Y
			[IS_SEARCHABLE] => N
			[SETTINGS] => Array
				(
					[CHANNEL_ID] => 1
					[UNIQUE] => 40
					[UNIQUE_IP_DELAY] => Array
						(
							[DELAY] =>
							[DELAY_TYPE] => S
						)

					[NOTIFY] => I
				)

			[EDIT_FORM_LABEL] => UF_BLOG_POST_VOTE
			[LIST_COLUMN_LABEL] =>
			[LIST_FILTER_LABEL] =>
			[ERROR_MESSAGE] =>
			[HELP_MESSAGE] =>
			[USER_TYPE] => Array
				(
					[USER_TYPE_ID] => vote
					[CLASS_NAME] => Bitrix\Vote\Uf\VoteUserType
					[DESCRIPTION] => "Vote"
					[BASE_TYPE] => int
				)
			[VALUE] => 27
			[ENTITY_VALUE_ID] => 247)).
	 * @return array
	 */
	public static function prepareSettings($userField)
	{
		$userField["SETTINGS"] = (is_array($userField["SETTINGS"]) ? $userField["SETTINGS"] : @unserialize($userField["SETTINGS"]));
		$userField["SETTINGS"] = (is_array($userField["SETTINGS"]) ? $userField["SETTINGS"] : array());
		$tmp = array("CHANNEL_ID" => intval($userField["SETTINGS"]["CHANNEL_ID"]));

		if ($userField["SETTINGS"]["CHANNEL_ID"] == "add")
		{
			$tmp["CHANNEL_TITLE"] = trim($userField["SETTINGS"]["CHANNEL_TITLE"]);
			$tmp["CHANNEL_SYMBOLIC_NAME"] = trim($userField["SETTINGS"]["CHANNEL_SYMBOLIC_NAME"]);
			$tmp["CHANNEL_USE_CAPTCHA"] = ($userField["SETTINGS"]["CHANNEL_USE_CAPTCHA"] == "Y" ? "Y" : "N");
		}

		$uniqType = $userField["SETTINGS"]["UNIQUE"];
		if (is_array($userField["SETTINGS"]["UNIQUE"]))
		{
			$uniqType = 0;
			foreach ($userField["SETTINGS"]["UNIQUE"] as $z)
				$uniqType |= $z;
		}

		$tmp["UNIQUE"] = $uniqType;
		$tmp["UNIQUE_IP_DELAY"] = is_array($userField["SETTINGS"]["UNIQUE_IP_DELAY"]) ?
			$userField["SETTINGS"]["UNIQUE_IP_DELAY"] : array();
		$tmp["NOTIFY"] = (in_array($userField["SETTINGS"]["NOTIFY"], array("I", "Y", "N")) ?
			$userField["SETTINGS"]["NOTIFY"] : "N");

		return $tmp;
	}

	/**
	 * Checks CHANNEL or creates add vote group.
	 * @param array &$params Settings array.
	 * @return boolean
	 */
	public static function checkSettings(&$params)
	{
		$settings = (is_array($params["SETTINGS"]) ? $params["SETTINGS"] : @unserialize($params["SETTINGS"]));
		$settings = is_array($settings) ? $settings : array($settings);
		if (array_key_exists("CHANNEL_ID", $settings))
		{
			$settings["CHANNEL_ID"] = intval($settings["CHANNEL_ID"]);
			if ($settings["CHANNEL_ID"] <= 0 && \Bitrix\Main\Loader::includeModule("vote"))
			{
				$isFiltered = "";
				$dbRes = \CVoteChannel::GetList($by = "ID", $order = "ASC",
					array("SYMBOLIC_NAME" => $settings["CHANNEL_SYMBOLIC_NAME"], "SYMBOLIC_NAME_EXACT_MATCH" => "Y"), $isFiltered);
				if (!($dbRes && ($channel = $dbRes->fetch()) && !!$channel))
				{
					$res = array(
						"TITLE" => $settings["CHANNEL_TITLE"],
						"SYMBOLIC_NAME" => $settings["CHANNEL_SYMBOLIC_NAME"],
						"ACTIVE" => "Y",
						"HIDDEN" => "Y",
						"C_SORT" => 100,
						"VOTE_SINGLE" => "N",
						"USE_CAPTCHA" => $settings["CHANNEL_USE_CAPTCHA"],
						"SITE" => array(),
						"GROUP_ID" => array()
					);
					$by = "sort"; $order = "asc";
					$dbRes = \CSite::GetList($by, $order);
					while ($site = $dbRes->getNext())
						$res["SITE"][] = $site["ID"];
					$dbRes = \CGroup::GetList($by = "sort", $order = "asc", Array("ADMIN" => "N"));
					while ($group = $dbRes->getNext())
						$res["GROUP_ID"][$group["ID"]] = ($group["ID"] == 2 ? 1 : 4);
					$res["GROUP_ID"] = (is_array($settings["GROUP_ID"]) ? array_intersect_key($settings["GROUP_ID"], $res["GROUP_ID"]) : $res["GROUP_ID"]);
					$channelId = \CVoteChannel::Add($res);
				}
				else
				{
					$channelId = $channel["ID"];
				}

				$settings["CHANNEL_ID"] = $channelId;
				unset($settings["CHANNEL_TITLE"]);
				unset($settings["CHANNEL_SYMBOLIC_NAME"]);
				unset($settings["CHANNEL_USE_CAPTCHA"]);
				if (!$settings["CHANNEL_ID"])
					return false;
			}
			$uniqType = $settings["UNIQUE"];
			if (is_array($settings["UNIQUE"]))
			{
				foreach ( $settings["UNIQUE"] as $res)
					$uniqType |= $res;
			}

			$settings["UNIQUE"] = $uniqType;
			$settings["UNIQUE_IP_DELAY"] = is_array($settings["UNIQUE_IP_DELAY"]) ?
				$settings["UNIQUE_IP_DELAY"] : array("DELAY" => "10", "DELAY_TYPE" => "D");
			$params["SETTINGS"] = serialize($settings);
			$params["MULTIPLE"] = "N";
			$params["MANDATORY"] = "N";
			$params["SHOW_FILTER"] = "N";
			$params["IS_SEARCHABLE"] = "N";
		}
		return true;
	}
	/**
	 * Shows data form in admin part when you edit or add usertype.
	 * @param bool $userField UserField array.
	 * @param string $htmlControl HtmlControl.
	 * @param bool $varsFromForm Get params from $_REQUEST.
	 * @return string
	 */
	public static function getSettingsHTML($userField = false, $htmlControl, $varsFromForm)
	{
		if (!\Bitrix\Main\Loader::includeModule("vote"))
			return '';
		global ${$htmlControl["NAME"]}, $aVotePermissions;
		$entity = ${$htmlControl["NAME"]};
		$value = "";
		if($varsFromForm)
			$value = $entity["CHANNEL_ID"];
		elseif(is_array($userField))
		{
			$value = $userField["SETTINGS"]["CHANNEL_ID"];
			$entity["NOTIFY"] = $userField["SETTINGS"]["NOTIFY"];
		}
		$value = (!empty($value) ? intval($value) : "add");
		$dbRes = \CVoteChannel::GetList($by = "", $order = "", array("ACTIVE" => "Y"), $isFiltered);
		$voteChannels = array("reference" => array(Loc::getMessage("V_NEW_CHANNEL")), "reference_id" => array("add"));
		if ($dbRes && $res = $dbRes->fetch())
		{
			do
			{
				$voteChannels["reference"][] = $res["TITLE"];
				$voteChannels["reference_id"][] = $res["ID"];
			} while ($res = $dbRes->fetch());
		}

		ob_start();
		?>
		<tr>
			<td><?=Loc::getMessage("V_CHANNEL_ID_COLON")?></td>
			<td><?=str_replace(
					"<select",
					"<select onchange='if(this.value!=\"add\"){BX.hide(BX(\"channel_create\"));BX.show(this.nextSibling);}".
					"else{BX(\"channel_create\").style.display=\"\";BX.hide(this.nextSibling);}' ",
					SelectBoxFromArray(
						$htmlControl["NAME"]."[CHANNEL_ID]",
						$voteChannels,
						$value)
				)?><a style="margin-left: 1em;" href="" rel="/bitrix/admin/vote_channel_edit.php?ID=#id#" <?
				?>onmousedown="this.href=this.rel.replace('#id#',this.previousSibling.value);"><?=Loc::getMessage("V_CHANNEL_ID_EDIT")?></a></td>
		</tr>
		<tbody id="channel_create" style="<?if ($value != "add"): ?>display:none;<? endif; ?>">
		<tr class="adm-detail-required-field">
			<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("V_CHANNEL_ID_TITLE")?></td>
			<td class="adm-detail-content-cell-r" width="60%"><input type="text" name="<?=$htmlControl["NAME"]?>[CHANNEL_TITLE]" <?
				?>value="<?=htmlspecialcharsbx($entity["CHANNEL_TITLE"]);?>" /></td>
		</tr>
		<tr class="adm-detail-required-field">
			<td class="adm-detail-content-cell-l"><?=Loc::getMessage("V_CHANNEL_ID_SYMBOLIC_NAME")?></td>
			<td class="adm-detail-content-cell-r"><input type="text" name="<?=$htmlControl["NAME"]?>[CHANNEL_SYMBOLIC_NAME]" <?
				?>value="<?=htmlspecialcharsbx($entity["CHANNEL_SYMBOLIC_NAME"]);?>" /></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l">&nbsp;</td>
			<td class="adm-detail-content-cell-r"><input type="checkbox" name="<?=$htmlControl["NAME"]?>[CHANNEL_USE_CAPTCHA]" <?
				?>id="CHANNEL_USE_CAPTCHA" <?if ($entity["CHANNEL_USE_CAPTCHA"] == "Y"): ?> checked <? endif;
				?>value="Y" /> <label for="CHANNEL_USE_CAPTCHA"><?=Loc::getMessage("V_CHANNEL_ID_USE_CAPTCHA")?></label></td>
		</tr><?
		$dbRes = \CGroup::GetList($by = "sort", $order = "asc", Array("ADMIN" => "N"));
		while ($group = $dbRes->getNext())
		{
			if($varsFromForm)
				$value = $entity["GROUP_ID"][$group["ID"]];
			else
				$value = ($group["ID"] == 2 ? 1 : ($group["ID"] == 1 ? 4 : 2));
			?>
			<tr>
			<td class="adm-detail-content-cell-l"><?=$group["NAME"].":"?></td>
			<td class="adm-detail-content-cell-r"><?=SelectBoxFromArray("GROUP_ID[".$group["ID"]."]", $aVotePermissions, $value);?></td>
			</tr><?
		}

		?>
		</tbody>
		<?
		if($varsFromForm)
		{
			$entity['UNIQUE'] = is_array($entity['UNIQUE']) ?
				$entity['UNIQUE'] : array();
			$uniqType = 0;
			foreach ($entity['UNIQUE'] as $res)
				$uniqType |= $res;
		}
		else
		{
			$uniqType = \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH|\Bitrix\Vote\Vote\EventLimits::BY_USER_ID;
			if (is_array($userField) && array_key_exists("SETTINGS", $userField) && array_key_exists("UNIQUE", $userField["SETTINGS"]))
			{
				$uniqType = 0;
				if (is_array($userField["SETTINGS"]["UNIQUE"]))
				{
					foreach ( $userField["SETTINGS"]["UNIQUE"] as $res)
						$uniqType |= $res;
				}
				else
				{
					$uniqType = intval($userField["SETTINGS"]["UNIQUE"]);
				}
			}
		}
		if ($uniqType&\Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH)
		{
			$uniqType |= \Bitrix\Vote\Vote\EventLimits::BY_USER_ID;
		}
		?>
		<script language="javascript">
			function __utch(show)
			{
				if (BX("UNIQUE_TYPE_IP").checked)
					BX.show(BX("DELAY_TYPE"), "");
				else
					BX.hide(BX("DELAY_TYPE"));

				var
					show = BX("UNIQUE_TYPE_USER_ID").checked,
					res = BX("UNIQUE_TYPE_USER_ID_NEW");
				res.disabled = !show;
				if (!!show)
					BX.show(res.parentNode.parentNode, "");
				else
					BX.hide(res.parentNode.parentNode);
			}
		</script>
		<tr>
			<td class="adm-detail-content-cell-l adm-detail-valign-top" width="40%"><?=Loc::getMessage("VOTE_NOTIFY")?></td>
			<td class="adm-detail-content-cell-r" width="60%"><?
				$entity["NOTIFY"] = (
				$entity["NOTIFY"] != "I" && $entity["NOTIFY"] != "Y" ?
					"N" : $entity["NOTIFY"]);
				if (IsModuleInstalled("im")): ?>
					<?=InputType("radio", $htmlControl["NAME"]."[NOTIFY]", "I", $entity["NOTIFY"], false, Loc::getMessage("VOTE_NOTIFY_IM"))?><br /><?
				else:
					$entity["NOTIFY"] = ($entity["NOTIFY"] == "I" ?
						"N" : $entity["NOTIFY"]);
				endif; ?>
				<?=InputType("radio", $htmlControl["NAME"]."[NOTIFY]", "Y", $entity["NOTIFY"], false, Loc::getMessage("VOTE_NOTIFY_EMAIL"))?><br />
				<?=InputType("radio", $htmlControl["NAME"]."[NOTIFY]", "N", $entity["NOTIFY"], false, Loc::getMessage("VOTE_NOTIFY_N"))?><?
				?></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l adm-detail-valign-top"><?=Loc::getMessage("V_UNIQUE")?></td>
			<td class="adm-detail-content-cell-r">
				<? if (IsModuleInstalled('statistic')): ?>
					<input type="checkbox" name="<?=$htmlControl["NAME"]?>[UNIQUE][]" id="UNIQUE_TYPE_SESSION" value="1" <?=($uniqType & \Bitrix\Vote\Vote\EventLimits::BY_SESSION)?" checked":""?> />
					<label for="UNIQUE_TYPE_SESSION"><?=Loc::getMessage("V_UNIQUE_SESSION")?></label><br />
				<? endif; ?>
				<input type="checkbox" name="<?=$htmlControl["NAME"]?>[UNIQUE][]" id="UNIQUE_TYPE_COOKIE" value="2" <?=($uniqType & \Bitrix\Vote\Vote\EventLimits::BY_COOKIE)?" checked":""?> />
				<label for="UNIQUE_TYPE_COOKIE"><?=Loc::getMessage("V_UNIQUE_COOKIE_ONLY")?></label><br />
				<input type="checkbox" name="<?=$htmlControl["NAME"]?>[UNIQUE][]" id="UNIQUE_TYPE_IP" onclick="__utch()" value="<?=\Bitrix\Vote\Vote\EventLimits::BY_IP?>" <?
				?><?=($uniqType & \Bitrix\Vote\Vote\EventLimits::BY_IP) ? " checked":""?> />
				<label for="UNIQUE_TYPE_IP"><?=Loc::getMessage("V_UNIQUE_IP_ONLY")?></label><br />
				<input type="checkbox" name="<?=$htmlControl["NAME"]?>[UNIQUE][]" id="UNIQUE_TYPE_USER_ID" onclick="__utch();" value="<?=(\Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH|\Bitrix\Vote\Vote\EventLimits::BY_USER_ID)?>" <?
				?><?=($uniqType & (\Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH|\Bitrix\Vote\Vote\EventLimits::BY_USER_ID))?" checked":""?> />
				<label for="UNIQUE_TYPE_USER_ID"><?=Loc::getMessage("V_UNIQUE_USER_ID_ONLY")?></label><br />
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l" width="40%">&nbsp;</td>
			<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="<?=$htmlControl["NAME"]?>[UNIQUE][]" id="UNIQUE_TYPE_USER_ID_NEW" value="16" <?
				?><?=($uniqType & 16)?" checked ":""?><?
				?><?=($uniqType & 8)?"": " disabled"?> /> <label for="UNIQUE_TYPE_USER_ID_NEW"><?=Loc::getMessage("V_UNIQUE_USER_ID_NEW")?></label>
			</td>
		</tr>
		<?
		if($varsFromForm)
			$value = $entity["UNIQUE_IP_DELAY"];
		else
			$value = (is_array($userField) ?
				$userField["SETTINGS"]["UNIQUE_IP_DELAY"] :
				array("DELAY" => "10", "DELAY_TYPE" => "D"));
		?>
		<tr id="DELAY_TYPE">
			<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("V_UNIQUE_IP_DELAY")?></td>
			<td class="adm-detail-content-cell-r" width="60%">
				<input type="text" name="<?=$htmlControl["NAME"]?>[UNIQUE_IP_DELAY][DELAY]" value="<?=htmlspecialcharsbx($value["DELAY"]);?>" />
				<?=SelectBoxFromArray(
					$htmlControl["NAME"]."[UNIQUE_IP_DELAY][DELAY_TYPE]",
					array(
						"reference_id" => array("S", "M", "H", "D"),
						"reference" => array(
							Loc::getMessage("V_SECONDS"), Loc::getMessage("V_MINUTES"),
							Loc::getMessage("V_HOURS"), Loc::getMessage("V_DAYS"))
					),
					$value["DELAY_TYPE"]);?>
				<script type="text/javascript">
					BX.ready(function(){
						if (!!document.forms.post_form.MULTIPLE)
							BX.hide(document.forms.post_form.MULTIPLE.parentNode.parentNode);
						__utch();
					});
				</script>

			</td>
		</tr>
		<?
		return ob_get_clean();
	}

	/**
	 * @param array $userField UserField array.
	 * @param array $value Value.
	 * @return string
	 */
	public static function getEditFormHTML($userField, $value)
	{
		ob_start();
		$params = array(
			"arUserField" => $userField
		);
		$result = $value;
		\Bitrix\Vote\UF\Manager::getInstance($userField)->showEdit($params, $value);
		return ob_get_clean();
	}

	/**
	 * @param array $userField UserField array.
	 * @param mixed $value Value for Vote.
	 * @return string
	 */
	public static function getPublicViewHTML($userField, $value)
	{
		ob_start();
		$params = array(
			"arUserField" => $userField
		);
		$result = array(
			$value
		);
		\Bitrix\Vote\UF\Manager::getInstance($userField)->showView($params, $result);
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public static function getFilterHTML()
	{
		return '';
	}

	/**
	 * @param array $userField UserField array.
	 * @param array $htmlControl HTML Control.
	 * @return string
	 */
	public static function getAdminListViewHTML($userField, $htmlControl)
	{
		global $APPLICATION;
		$return = '&nbsp;';
		$returnUrl = $APPLICATION->GetCurPageParam("", array("admin_history", "mode", "table_id"));

		if($htmlControl["VALUE"] > 0)
		{
			$dbRes = \CVote::GetByIDEx($htmlControl["VALUE"]);
			if ($dbRes && ($vote = $dbRes->GetNext()))
			{
				if ($vote["LAMP"] == "yellow")
					$vote["LAMP"] = ($vote["ID"] == \CVote::GetActiveVoteId($vote["CHANNEL_ID"]) ? "green" : "red");
				$return = "<div class=\"lamp-red\" title=\"".($vote["ACTIVE"] != 'Y' ? Loc::getMessage("VOTE_NOT_ACTIVE") : Loc::getMessage("VOTE_ACTIVE_RED_LAMP"))."\"  style=\"display:inline-block;\"></div>";
				if ($vote["LAMP"]=="green")
					$return = "<div class=\"lamp-green\" title=\"".Loc::getMessage("VOTE_LAMP_ACTIVE")."\" style=\"display:inline-block;\"></div>";
				$return .= " [<a href='vote_edit.php?lang=".LANGUAGE_ID."&ID=".$vote["ID"]."&return_url=".urlencode($returnUrl)."' title='".Loc::getMessage("VOTE_EDIT_TITLE")."'>".$vote["ID"]."</a>] ";
				$return .= $vote["TITLE"].(!empty($vote["DESCRIPTION"]) ? " <i>(".$vote["DESCRIPTION"].")</i>" : "");
				if ($vote["COUNTER"] > 0)
					$return .= Loc::getMessage("VOTE_VOTES")." <a href=\"vote_user_votes_table.php?lang={LANGUAGE_ID}&VOTE_ID={$vote["ID"]}\">".$vote["COUNTER"]."</a>";
			}

		}
		return $return;
	}

	/**
	 * @param array $userField UserField array.
	 * @param array $htmlControl HTML Control.
	 * @return string
	 */
	public static function getAdminListEditHTML($userField, $htmlControl)
	{
		return '<input type="text" '.
		'name="'.$htmlControl["NAME"].'" '.
		'size="'.$userField["SETTINGS"]["SIZE"].'" '.
		'value="'.$htmlControl["VALUE"].'" '.
		'>';
	}

	/**
	 * @param array $userField UserField array.
	 * @param array $htmlControl HTML Control.
	 * @return string
	 */
	public static function getAdminListEditHTMLMulty($userField, $htmlControl)
	{
		return "&nbsp;";
	}

	/**
	 * Checks fields of vote date before saving.
	 * @param array $userField UserFiled array.
	 * @param mixed $value Always singular value.
	 * @param bool $userId False means current user id.
	 * @return array
	 */
	public static function checkFields($userField, $value, $userId = false)
	{
		$res = "";
		if ($userField && is_array($userField["USER_TYPE"]) && $userField["USER_TYPE"]["CLASS_NAME"] == __CLASS__)
		{
			try
			{
				global ${$userField["FIELD_NAME"] . "_" . $value . "_DATA"};
				$data = ${$userField["FIELD_NAME"] . "_" . $value . "_DATA"} ?: false;
				if (!is_array($data) || empty($data))
					return array();

				$userFieldManager = Manager::getInstance($userField);

				list($type, $realValue) = self::detectType($value);

				$attach = ($type == self::TYPE_SAVED_ATTACH ? $userFieldManager->loadFromAttachId($realValue) :
					($data["ID"] > 0 ? $userFieldManager->loadFromVoteId($data["ID"]) : $userFieldManager->loadEmptyObject()));

				if (isset($attach["ID"]) && $attach["VOTE_ID"] != $data["ID"])
					throw new \Bitrix\Main\ArgumentException(Loc::getMessage("VOTE_IS_NOT_EXPECTED"));
				if (!$userFieldManager->belongsToEntity($attach, $userField['ENTITY_ID'], $userField['ENTITY_VALUE_ID']))
					throw new \Bitrix\Main\ObjectNotFoundException(Loc::getMessage("VOTE_IS_NOT_FOUND"));

				$data["OPTIONS"] = (is_array($data["OPTIONS"]) ? array_sum($data["OPTIONS"]) : 0);
				$data["UNIQUE_TYPE"] = ($userField["SETTINGS"]["UNIQUE"] & \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH ? $userField["SETTINGS"]["UNIQUE"] | \Bitrix\Vote\Vote\EventLimits::BY_USER_ID : $userField["SETTINGS"]["UNIQUE"]);
				$interval = intval($userField["SETTINGS"]["UNIQUE_IP_DELAY"]["DELAY"]);
				$interval = in_array($userField["SETTINGS"]["UNIQUE_IP_DELAY"]["DELAY_TYPE"], array("S", "M", "H")) ? "PT".$interval.$userField["SETTINGS"]["UNIQUE_IP_DELAY"]["DELAY_TYPE"] : "P".$interval."D";
				$data["KEEP_IP_SEC"] = (new \DateTime("@0"))->add(new \DateInterval($interval))->getTimestamp();
				$data["NOTIFY"] = $userField["SETTINGS"]["NOTIFY"];

				$attach->checkData($data);
			}
			catch (\Exception $e)
			{
				return array(array("id" => "voteUserType" , "text" => $e->getMessage()));
			}
		}
		return array();
	}

	/**
	 * @param array $userField UserFiled array.
	 * @return string
	 */
	public static function onSearchIndex($userField)
	{
		return "";
	}

	/**
	 * Called in all cases Mutiple=Y and Multiple=N
	 * @param array $userField UserFiled array.
	 * @param string $value Number of attach or n0, n1...
	 * @param int $userId User ID.
	 * @return int|string
	 */
	public static function onBeforeSave($userField, $value, $userId = false)
	{
/*		if (empty($value))
		{
			$alreadyExistsValues = $userField['VALUE'];
			if (!is_array($alreadyExistsValues))
			{
				$alreadyExistsValues = array($userField['VALUE']);
			}
			Attach::detachByFilter(array('ID' => $alreadyExistsValues));
		}*/
		try
		{
			global $USER;
			$userId = ($userId ?: (is_object($USER) ? $USER->getId() : $userId));
			global ${$userField["FIELD_NAME"] . "_" . $value . "_DATA"};
			$data = ${$userField["FIELD_NAME"] . "_" . $value . "_DATA"} ?: false;

			$userFieldManager = Manager::getInstance($userField);
			list($type, $realValue) = self::detectType($value);
			if ($type == self::TYPE_SAVED_ATTACH && (!is_array($data) || empty($data)))
			{
				return $value;
			}

			if (!is_array($data) || empty($data))
				return "";

			/*@var \Bitrix\Vote\Attach $attach*/
			$attach = ($type == self::TYPE_SAVED_ATTACH ? $userFieldManager->loadFromAttachId($realValue) :
				($data["ID"] > 0 ? $userFieldManager->loadFromVoteId($data["ID"]) : $userFieldManager->loadEmptyObject()));

			if (!isset($attach["ID"]) &&
				$attach->getStorage()->getId() != $userField["SETTINGS"]["CHANNEL_ID"] &&
				!$attach->getStorage()->canEditVote($userId))
				throw new \Bitrix\Main\AccessDeniedException(Loc::getMessage("VOTE_EDIT_ACCESS_IS_DENIED"));
			if (!$attach->canRead($userId))
				throw new \Bitrix\Main\AccessDeniedException(Loc::getMessage("VOTE_READ_ACCESS_IS_DENIED"));
			if (!empty($data) && !$attach->canEdit($userId))
				throw new \Bitrix\Main\AccessDeniedException(Loc::getMessage("VOTE_EDIT_ACCESS_IS_DENIED"));

			$data["OPTIONS"] = (is_array($data["OPTIONS"]) ? array_sum($data["OPTIONS"]) : 0);
			$data["UNIQUE_TYPE"] = intval($userField["SETTINGS"]["UNIQUE"] & \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH ? $userField["SETTINGS"]["UNIQUE"] | \Bitrix\Vote\Vote\EventLimits::BY_USER_ID : $userField["SETTINGS"]["UNIQUE"]);
			$interval = intval($userField["SETTINGS"]["UNIQUE_IP_DELAY"]["DELAY"]);
			$interval = in_array($userField["SETTINGS"]["UNIQUE_IP_DELAY"]["DELAY_TYPE"], array("S", "M", "H")) ? "PT".$interval.$userField["SETTINGS"]["UNIQUE_IP_DELAY"]["DELAY_TYPE"] : "P".$interval."D";
			$data["KEEP_IP_SEC"] = (new \DateTime("@0"))->add(new \DateInterval($interval))->getTimestamp();
			$data["NOTIFY"] = $userField["SETTINGS"]["NOTIFY"];

			$attach->save($data, $userId);

			return $attach->getAttachId();
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param array $userField
	 * @param int $newEntityId
	 * @param $attachedId
	 * @param object $implementer
	 * @param bool $userId
	 * @return array|int|string
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function onBeforeCopy(array $userField, int $newEntityId, $attachedId, $implementer, $userId = false)
	{
		if (empty($userField) || empty($attachedId))
		{
			return "";
		}

		global $USER;
		$userId = ($userId ?: (is_object($USER) ? $USER->getId() : $userId));

		$userFieldManager = Manager::getInstance($userField);

		$attachedObject = $userFieldManager->loadFromAttachId($attachedId);

		$voteId = 0;
		if (is_callable([$implementer, "copyVote"]))
		{
			$voteId = $implementer->copyVote($attachedObject->getVoteId());
		}

		$attachedId = "";
		if ($voteId > 0)
		{
			$attachedId = AttachTable::add([
				"MODULE_ID" => $attachedObject->getModuleId(),
				"OBJECT_ID" => $voteId,
				"ENTITY_ID" => $newEntityId,
				"ENTITY_TYPE" => $attachedObject->getEntityType(),
				"CREATED_BY" => $userId,
				"CREATE_TIME" => new DateTime()
			])->getId();
		}

		return $attachedId;
	}

	/**
	 * @param array $userField UserFiled array.
	 * @param string $value Number of attach or n0, n1...
	 * @return void
	 */
	public static function onDelete($userField, $value)
	{
		if (empty($value))
			return;
		$userFieldManager = Manager::getInstance($userField);

		list($type, $realValue) = self::detectType($value);
		$attach = ($type == self::TYPE_SAVED_ATTACH ? $userFieldManager->loadFromAttachId($realValue) :
			($realValue > 0 ? $userFieldManager->loadFromVoteId($realValue) : $userFieldManager->loadEmptyObject()));

		global $USER;
		if ($userFieldManager->belongsToEntity($attach, $userField['ENTITY_ID'], $userField['ENTITY_VALUE_ID']) && !$attach->canEdit($USER->getId()))
			$attach->delete();
	}

	/**
	 * Detect: this is already exists attachedObject or new object.
	 * @param mixed $value Integer if it ia an attach or n1, n2 in case unsaved attach.
	 * @return array
	 */
	public static function detectType($value)
	{
		$prefix = "";
		if (strpos($value, self::NEW_VOTE_PREFIX) === 0)
		{
			$prefix = self::NEW_VOTE_PREFIX;
			$value = intval(substr($value, 1));
		}
		else
			$value = intval($value);

		$return = ($prefix == self::NEW_VOTE_PREFIX ? array(self::TYPE_NEW_ATTACH, $value) : array(self::TYPE_SAVED_ATTACH, $value));

		return $return;
	}
}