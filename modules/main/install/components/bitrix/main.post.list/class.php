<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

final class MainPostList extends CBitrixComponent
{
	const STATUS_SCOPE_MOBILE = 'mobile';
	const STATUS_SCOPE_WEB = 'web';

	private $scope;
	private $sign;
	static $users = array();

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->scope = self::STATUS_SCOPE_WEB;
		if (is_callable(array('\Bitrix\MobileApp\Mobile', 'getApiVersion')) && \Bitrix\MobileApp\Mobile::getApiVersion() >= 1 &&
			defined("BX_MOBILE") && BX_MOBILE === true)
			$this->scope = self::STATUS_SCOPE_MOBILE;

		$templateName = $this->getTemplateName();

		if ((empty($templateName) || $templateName == ".default" || $templateName == "bitrix24"))
		{
			if ($this->isWeb())
				$this->setTemplateName(".default");
			else
				$this->setTemplateName("mobile_app");
		}

		$this->sign = (new \Bitrix\Main\Security\Sign\Signer());
	}

	protected function isWeb()
	{
		return ($this->scope == self::STATUS_SCOPE_WEB);
	}

	protected function isAjax()
	{
		return (
			$this->request->getQuery("AJAX_POST") == "Y" && $this->request->getQuery("ENTITY_XML_ID") == $this->arParams["ENTITY_XML_ID"] ||
			$this->request->getPost("AJAX_POST") == "Y" && $this->request->getPost("ENTITY_XML_ID") == $this->arParams["ENTITY_XML_ID"]
		);
	}

	protected function getMode()
	{
		$viewMode = "plain";
		if ($this->isAjax())
		{
			$viewMode = strtoupper($this->request->getPost("MODE") ?: $this->request->getQuery("MODE"));
		}
		return $viewMode;
	}

	protected function joinToPull()
	{
		$text = "";
		if ($this->getUser()->isAuthorized()
			&& CModule::IncludeModule("pull")
			&& \CPullOptions::GetNginxStatus()
		)
		{
			if ($this->isWeb())
			{
				if ($this->arParams["RIGHTS"]["MODERATE"] == "Y" || $this->arParams["RIGHTS"]["MODERATE"] == "ALL")
				{
					\CPullWatch::Add($this->getUser()->getId(), 'UNICOMMENTSEXTENDED'.$this->arParams["ENTITY_XML_ID"]);
					$text = <<<HTML
						<script>
							BX.ready(function(){BX.PULL.extendWatch("UNICOMMENTSEXTENDED{$this->arParams["ENTITY_XML_ID"]}");});
						</script>
HTML;
				}
				else
				{
					\CPullWatch::Add($this->getUser()->GetId(), 'UNICOMMENTS'.$this->arParams["ENTITY_XML_ID"]);
					$text = <<<HTML
						<script>
							BX.ready(function(){BX.PULL.extendWatch("UNICOMMENTS{$this->arParams["ENTITY_XML_ID"]}");});
						</script>
HTML;
				}
			}
			else
			{
				if ($this->arParams["RIGHTS"]["MODERATE"] == "Y" || $this->arParams["RIGHTS"]["MODERATE"] == "ALL")
				{
					\CPullWatch::Add($this->getUser()->GetId(), 'UNICOMMENTSMOBILEEXTENDED'.$this->arParams["ENTITY_XML_ID"]);
					$text .= <<<HTML
						<script>
							BXMobileApp.onCustomEvent('onPullExtendWatch', {'id': "UNICOMMENTSMOBILEEXTENDED{$this->arParams["ENTITY_XML_ID"]}"}, true);
						</script>
HTML;
				}
				else
				{
					\CPullWatch::Add($this->getUser()->GetId(), 'UNICOMMENTSMOBILE'.$this->arParams["ENTITY_XML_ID"]);
					$text = <<<HTML
						<script>
							BXMobileApp.onCustomEvent('onPullExtendWatch', {'id': "UNICOMMENTSMOBILE{$this->arParams["ENTITY_XML_ID"]}"}, true);
						</script>
HTML;
				}
				\CPullWatch::DeferredSql();
			}
		}
		return preg_replace("/\\s+/", "", $text);
	}

	protected function sendIntoPull(array &$arParams, array &$arResult)
	{
		if (((
				check_bitrix_sessid()
				|| (
					isset($arParams["PUSH&PULL"])
					&& isset($arParams["PUSH&PULL"]["AUTHOR_ID"])
					&& intval($arParams["PUSH&PULL"]["AUTHOR_ID"]) > 0
				)
			) &&
			//$this->getUser()->IsAuthorized() &&
			($this->request->getPost("ENTITY_XML_ID") == $arParams["ENTITY_XML_ID"] ||
				$this->request->getQuery("ENTITY_XML_ID") == $arParams["ENTITY_XML_ID"]) || $arParams["MODE"] == "PULL_MESSAGE") &&
			is_array($arParams["PUSH&PULL"]) && $arParams["PUSH&PULL"]["ID"] > 0 &&
			CModule::IncludeModule("pull") && \CPullOptions::GetNginxStatus())
		{
			if ($arParams["PUSH&PULL"]["ACTION"] != "DELETE")
			{
				if (($res = $arParams["RECORDS"][$arParams["PUSH&PULL"]["ID"]]) && $res)
				{
					$comment = array_merge($res, $res["WEB"]);
					unset($comment["WEB"]);
					unset($comment["MOBILE"]);
					$comment["ACTION"] = $arParams["PUSH&PULL"]["ACTION"];
					$comment["USER_ID"] = (isset($arParams["PUSH&PULL"]) && isset($arParams["PUSH&PULL"]["AUTHOR_ID"]) && intval($arParams["PUSH&PULL"]["AUTHOR_ID"]) > 0 ? intval($arParams["PUSH&PULL"]["AUTHOR_ID"]) : $this->getUser()->getId());
					if ($this->request->getPost("EXEMPLAR_ID") !== null)
						$comment["EXEMPLAR_ID"] = $this->request->getPost("EXEMPLAR_ID");
					if ($this->request->getPost("COMMENT_EXEMPLAR_ID") !== null)
						$comment["COMMENT_EXEMPLAR_ID"] = $this->request->getPost("COMMENT_EXEMPLAR_ID");

					\CPullWatch::AddToStack('UNICOMMENTSEXTENDED'.$arParams["ENTITY_XML_ID"],
						array(
							'module_id' => 'unicomments',
							'command' => 'comment',
							'params' => $comment
						)
					);
					if ($comment["APPROVED"] == "Y")
					{
						\CPullWatch::AddToStack('UNICOMMENTS'.$arParams["ENTITY_XML_ID"],
							array(
								'module_id' => 'unicomments',
								'command' => 'comment',
								'params' => $comment
							)
						);
					}
					else if ($comment["ACTION"] == "MODERATE")
					{
						\CPullWatch::AddToStack('UNICOMMENTS'.$arParams["ENTITY_XML_ID"],
							array(
								'module_id' => 'unicomments',
								'command' => 'comment',
								'params' => array(
									"ID" => $comment["ID"],
									"ENTITY_XML_ID" => $comment["ENTITY_XML_ID"],
									"APPROVED" => "N",
									"ACTION" => "HIDE",
									"USER_ID" => $comment["USER_ID"]
								)
							)
						);
					}
					if (IsModuleInstalled("mobile"))
					{
						$comment = array_merge($comment, $res["MOBILE"]);
						\CPullWatch::AddToStack('UNICOMMENTSMOBILEEXTENDED'.$arParams["ENTITY_XML_ID"],
							Array(
								'module_id' => 'unicomments',
								'command' => 'comment_mobile',
								'params' => $comment
							)
						);
						if ($comment["APPROVED"] == "Y")
						{
							\CPullWatch::AddToStack('UNICOMMENTSMOBILE'.$arParams["ENTITY_XML_ID"],
								Array(
									'module_id' => 'unicomments',
									'command' => 'comment_mobile',
									'params' => $comment
								)
							);
						}
						else if ($comment["ACTION"] == "MODERATE")
						{
							\CPullWatch::AddToStack('UNICOMMENTSMOBILE'.$arParams["ENTITY_XML_ID"],
								Array(
									'module_id' => 'unicomments',
									'command' => 'comment_mobile',
									'params' => array(
										"ID" => $comment["ID"],
										"ENTITY_XML_ID" => $comment["ENTITY_XML_ID"],
										"APPROVED" => "N",
										"ACTION" => "HIDE",
										"USER_ID" => $comment["USER_ID"]
									)
								)
							);
						}
					}
				}
			}
			else
			{
				\CPullWatch::AddToStack('UNICOMMENTS'.$arParams["ENTITY_XML_ID"],
					array(
						'module_id' => 'unicomments',
						'command' => 'comment',
						'params' => array(
							"ID" => $arParams["PUSH&PULL"]["ID"],
							"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
							"ACTION" => "DELETE",
							"USER_ID" => $this->getUser()->getId()
						)
					)
				);
				\CPullWatch::AddToStack('UNICOMMENTSEXTENDED'.$arParams["ENTITY_XML_ID"],
					array(
						'module_id' => 'unicomments',
						'command' => 'comment',
						'params' => array(
							"ID" => $arParams["PUSH&PULL"]["ID"],
							"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
							"ACTION" => "DELETE",
							"USER_ID" => $this->getUser()->getId()
						)
					)
				);

				if (IsModuleInstalled("mobile"))
				{
					\CPullWatch::AddToStack('UNICOMMENTSMOBILEEXTENDED'.$arParams["ENTITY_XML_ID"],
						Array(
							'module_id' => 'unicomments',
							'command' => 'comment_mobile',
							'params' => array(
								"ID" => $arParams["PUSH&PULL"]["ID"],
								"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
								"ACTION" => "DELETE",
								"USER_ID" => $this->getUser()->getId()
							)
						)
					);
					\CPullWatch::AddToStack('UNICOMMENTSMOBILE'.$arParams["ENTITY_XML_ID"],
						Array(
							'module_id' => 'unicomments',
							'command' => 'comment_mobile',
							'params' => array(
								"ID" => $arParams["PUSH&PULL"]["ID"],
								"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
								"ACTION" => "DELETE",
								"USER_ID" => $this->getUser()->getId()
							)
						)
					);
				}
			}
		}
	}

	protected function buildUser($id)
	{
		static $extranetUserIdList = false;

		if (
			$extranetUserIdList === false
			&& Loader::includeModule('socialnetwork')
		)
		{
			$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();
		}

		$res = $id;
		if (is_integer($res))
		{
			if (!array_key_exists($res, static::$users))
			{
				$res = \CUser::GetById($res)->Fetch();
				$res = array(
					"ID" => $res["ID"],
					"NAME" => $res["NAME"],
					"LAST_NAME" => $res["LAST_NAME"],
					"SECOND_NAME" => $res["SECOND_NAME"],
					"AVATAR" => $res["AVATAR"],
					"EXTERNAL_AUTH_ID" => $res["EXTERNAL_AUTH_ID"]
				);
				static::$users[$id] = $res;
			}
			$res = static::$users[$id];
		}

		$res["NAME"] = htmlspecialcharsbx($res["NAME"]);
		$res["LAST_NAME"] = htmlspecialcharsbx($res["LAST_NAME"]);
		$res["SECOND_NAME"] = htmlspecialcharsbx($res["SECOND_NAME"]);
		$res["IS_EXTRANET"] = is_array($extranetUserIdList) && in_array($res["ID"], $extranetUserIdList) ? "Y" : "N";
		if (!isset($res["TYPE"]))
		{
			if (!empty($res["UF_USER_CRM_ENTITY"]))
			{
				$res["TYPE"] = "EMAILCRM";
			}
			elseif (
				isset($res["EXTERNAL_AUTH_ID"])
				&& $res["EXTERNAL_AUTH_ID"] == 'email'
			)
			{
				$res["TYPE"] = "EMAIL";
			}
			elseif ($res["IS_EXTRANET"] == 'Y')
			{
				$res["TYPE"] = "EXTRANET";
			}
			else
			{
				$res["TYPE"] = false;
			}
		}
		return $res;
	}

	protected function buildComment(&$res)
	{
		$arParams = $this->arParams;
		$templateId = implode('_', array($arParams["TEMPLATE_ID"], 'ID', $res['ID'], ''));

		$result = array(
			"ID" => $res["ID"], // integer
			"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"], // string
			"FULL_ID" => array($arParams["ENTITY_XML_ID"], $res["ID"]),
			"NEW" => $res["NEW"], //"Y" | "N"
			"AUX" => (isset($res["AUX"]) ? $res["AUX"] : ''),
			"AUX_LIVE_PARAMS" => (isset($res["AUX_LIVE_PARAMS"]) ? $res["AUX_LIVE_PARAMS"] : array()),
			"APPROVED" => $res["APPROVED"], //"Y" | "N"
			"POST_TIMESTAMP" => ($res["POST_TIMESTAMP"] - CTimeZone::GetOffset()),
			"~POST_MESSAGE_TEXT" => $res["~POST_MESSAGE_TEXT"],
			"AUTHOR" => $this->buildUser($res["AUTHOR_ID"] ?: $res["AUTHOR"]),
			"RATING" => array_key_exists("RATING", $res) ? $res["RATING"] : false,
			"WEB" => array(), // html
			"MOBILE" => array() // html
		);

		foreach (array("WEB", "MOBILE") as $key)
		{
			$val = ($res[$key] ?: $res);
			$result[$key] = array(
				"POST_TIME" => (isset($val["POST_TIME"]) ? $val["POST_TIME"] : CComponentUtil::GetDateTimeFormatted($res["POST_TIMESTAMP"], $arParams["DATE_TIME_FORMAT"], CTimeZone::GetOffset())),
				"POST_DATE" => (isset($val["POST_DATE"]) ? $val["POST_DATE"] : CComponentUtil::GetDateTimeFormatted($res["POST_TIMESTAMP"], $arParams["DATE_TIME_FORMAT"], CTimeZone::GetOffset())),
				"CLASSNAME" => $val["CLASSNAME"],
				"POST_MESSAGE_TEXT" => $val["POST_MESSAGE_TEXT"],
				"BEFORE_HEADER" => $val["BEFORE_HEADER"].$this->getApplication()->GetViewContent($templateId.'BEFORE_HEADER'),
				"BEFORE_ACTIONS" => $val["BEFORE_ACTIONS"].$this->getApplication()->GetViewContent($templateId.'BEFORE_ACTIONS'),
				"AFTER_ACTIONS" => $val["AFTER_ACTIONS"].$this->getApplication()->GetViewContent($templateId.'AFTER_ACTIONS'),
				"AFTER_HEADER" => $val["AFTER_HEADER"].$this->getApplication()->GetViewContent($templateId.'AFTER_HEADER'),
				"BEFORE" => $val["BEFORE"].$this->getApplication()->GetViewContent($templateId.'BEFORE'),
				"AFTER" => $val["AFTER"].$this->getApplication()->GetViewContent($templateId.'AFTER'),
				"BEFORE_RECORD" => $val["BEFORE_RECORD"].$this->getApplication()->GetViewContent($templateId.'BEFORE_RECORD'),
				"AFTER_RECORD" => $val["AFTER_RECORD"].$this->getApplication()->GetViewContent($templateId.'AFTER_RECORD')
			);
		}

		if ($result["RATING"] === false && array_key_exists("RATING_RESULTS", $this->arParams))
		{
			ob_start();
			$result["RATING"] = $result["WEB"]["RATING"] = $this->getApplication()->includeComponent(
				"bitrix:rating.vote",
				"like",
				Array(
					"ENTITY_TYPE_ID" => $this->arParams["RATING_TYPE_ID"],
					"ENTITY_ID" => $result["ID"],
					"OWNER_ID" => $result["AUTHOR"]["ID"],
					"USER_VOTE" => $this->arParams["RATING_RESULTS"][$result["ID"]]["USER_VOTE"],
					"USER_HAS_VOTED" => $this->arParams["RATING_RESULTS"][$result["ID"]]["USER_HAS_VOTED"],
					"TOTAL_VOTES" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_VOTES"],
					"TOTAL_POSITIVE_VOTES" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_POSITIVE_VOTES"],
					"TOTAL_NEGATIVE_VOTES" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_NEGATIVE_VOTES"],
					"TOTAL_VALUE" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_VALUE"],
					"PATH_TO_USER_PROFILE" => $this->arParams["AUTHOR_URL"]
				),
				$this,
				array("HIDE_ICONS" => "Y")
			);
			$result["WEB"]["BEFORE_ACTIONS"] .= ob_get_clean();

			ob_start();
			$result["MOBILE"]["RATING"] = $this->getApplication()->includeComponent(
				"bitrix:rating.vote",
				"mobile_comment_like",
				Array(
					"ENTITY_TYPE_ID" => $this->arParams["RATING_TYPE_ID"],
					"ENTITY_ID" => $result["ID"],
					"OWNER_ID" => $result["AUTHOR"]["ID"],
					"USER_VOTE" => $this->arParams["RATING_RESULTS"][$result["ID"]]["USER_VOTE"],
					"USER_HAS_VOTED" => $this->arParams["RATING_RESULTS"][$result["ID"]]["USER_HAS_VOTED"],
					"TOTAL_VOTES" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_VOTES"],
					"TOTAL_POSITIVE_VOTES" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_POSITIVE_VOTES"],
					"TOTAL_NEGATIVE_VOTES" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_NEGATIVE_VOTES"],
					"TOTAL_VALUE" => $this->arParams["RATING_RESULTS"][$result["ID"]]["TOTAL_VALUE"],
					"PATH_TO_USER_PROFILE" => $this->arParams["AUTHOR_URL"]
				),
				$this,
				array("HIDE_ICONS" => "Y")
			);

			$result["MOBILE"]["AFTER"] .= ob_get_clean();
		}

		if (is_array($res["FILES"]))
		{
			$images = array();
			$files = array();
			foreach ($res["FILES"] as $file)
			{
				if (is_array($file) &&
					($file = array_change_key_case($file, CASE_UPPER)) &&
					array_key_exists("SRC", $file))
				{
					if (CFile::IsImage($file["ORIGINAL_NAME"], $file["CONTENT_TYPE"]))
						$images[] = $file;
					else
						$files[] = $file;
				}
			}
			if (!empty($images))
			{
				ob_start();
				?><div class="feed-com-files">
					<div class="feed-com-files-title"><?=GetMessage("MPL_PHOTO")?></div>
					<div class="feed-com-files-cont"><?
				foreach ($images as $file)
				{
					$thumbnail = ($file["THUMBNAIL"] ?: $file["SRC"]);
					?><span class="feed-com-files-photo">
						<img src="<?=$thumbnail?>" data-bx-src="<?=$file["SRC"]?>" <?
							?>border="0" data-bx-viewer="image" <?
							?>data-bx-width="<?=$file["WIDTH"]?>" <?
							?>data-bx-height="<?=$file["HEIGHT"]?>" <?
							?>data-bx-title="<?=($file["FILE_NAME"])?>" <?
							?>data-bx-size="<?=$file["FILE_SIZE"]?>"/></span><?
				}
					?></div>
				</div><?
				$result["WEB"]["AFTER"] = preg_replace("/[\n\t]/", "", ob_get_clean()).$result["WEB"]["AFTER"];

				ob_start();
				?><div class="post-item-attached-img-wrap"><?
					$ids = array();
					foreach($images as $file)
					{
						$id = "mpl-".$arParams["ENTITY_XML_ID"]."-".strtolower(randString(5));
						$ids[] = $id;
						$thumbnail = ($file["THUMBNAIL"] ?: $file["SRC"]);
						?><div class="post-item-attached-img-block" onclick="<?
							?>app.loadPageBlank({ url: '<?=$file["SRC"]?>' }); <?
							?>event.stopPropagation();"><img class="post-item-attached-img" <?
							?>id="<?=$id?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2N88f7jfwAJWAPJBTw90AAAAABJRU5ErkJggg==" <?
							?>data-src="<?=$thumbnail?>" border="0"></div><?
					}
				?><script>BitrixMobile.LazyLoad.registerImages(<?=CUtil::PhpToJSObject($ids)?>, oMSL.checkVisibility);</script><?
				?></div><?
				$result["MOBILE"]["AFTER"] = preg_replace("/[\n\t]/", "", ob_get_clean()).$result["MOBILE"]["AFTER"];
			}
			if (!empty($files))
			{
				ob_start();
				?><div class="feed-com-files feed-com-basic-files-entity">
					<div class="feed-com-files-title"><?=GetMessage("MPL_FILES")?></div>
					<div class="feed-com-files-cont"><?
				foreach ($files as $file)
				{
					$url = $file["URL"] ?: $file["SRC"];
					$size = CFile::FormatSize($file["FILE_SIZE"]);
					$ext = GetFileExtension($file["FILE_NAME"]);
					?><div class="feed-com-file-wrap">
						<span class="feed-con-file-icon feed-file-icon-<?=$ext?>"></span>
						<span class="feed-com-file-name-wrap">
							<a href="<?=$url?>" <?
								?>class="feed-com-file-name" <?
								?>data-bx-viewer="unknown" <?
								?>data-bx-src="<?=$url?>" <?
								?>data-bx-title="<?=($file["FILE_NAME"])?>" <?
								?>data-bx-size="<?=$size?>" <?
								?>data-bx-owner="" <?
								?>data-bx-dateModify="" <?
								?>title="<?=($file["FILE_NAME"])?>" <?
								?>target="_blank" ><?=($file["FILE_NAME"])?></a>
							<span class="feed-com-file-size"><?=$size?></span>
						</span>
					</div><?
				}
					?></div>
				</div><?
				$result["WEB"]["AFTER"] = preg_replace("/[\n\t]/", "", ob_get_clean()).$result["WEB"]["AFTER"];

				ob_start();
				?><ul class="post-item-attached-file-wrap"><?
					foreach($files as $file)
					{
						?><li><?=$file["FILE_NAME"]?></li><?
					}
				?></ul><?
				$res["MOBILE"]["AFTER"] .= ob_get_clean();
			}
		}
		if (is_array($res["UF"]))
		{
			ob_start();
			foreach ($res["UF"] as $arPostField)
			{
				if(!empty($arPostField["VALUE"]))
				{
					$this->getApplication()->IncludeComponent(
						"bitrix:system.field.view",
						$arPostField["USER_TYPE"]["USER_TYPE_ID"],
						array(
							"arUserField" => $arPostField,
							"TEMPLATE" => $this->getTemplateName(),
							"LAZYLOAD" => (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] == "Y" ? "Y" : "N"),
							"DISABLE_LOCAL_EDIT" => (isset($arParams["bPublicPage"]) && $arParams["bPublicPage"])
						) + $arParams,
						null,
						array("HIDE_ICONS"=>"Y")
					);
				}
			}
			$result["WEB"]["AFTER"] = ob_get_clean().$result["WEB"]["AFTER"];

			ob_start();

			foreach ($res["UF"] as $arPostField)
			{
				if(!empty($arPostField["VALUE"]))
				{
					$this->getApplication()->IncludeComponent(
						"bitrix:system.field.view",
						$arPostField["USER_TYPE"]["USER_TYPE_ID"],
						array(
							"arUserField" => $arPostField,
							"TEMPLATE" => $this->getTemplateName(),
							"LAZYLOAD" => (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] == "Y" ? "Y" : "N"),
							"MOBILE" => "Y"
						) + $arParams,
						null,
						array("HIDE_ICONS"=>"Y")
					);
				}
			}
			$html = ob_get_clean();
			if (!empty($html))
			{
				$result["MOBILE"]["AFTER"] = '<div class="post-item-attached-file-wrap" id="record-'.$arParams["ENTITY_XML_ID"].'-'.$res["ID"].'-uf">'.$html.'</div>'.$result["MOBILE"]["AFTER"];
			}
			$result["CLASSNAME"] .= " feed-com-block-uf";
		}
		$result = array_merge($result, ($this->isWeb() ? $result["WEB"] : $result["MOBILE"]));

		return $result;
	}

	public function parseTemplate(array $res, array $arParams, $template)
	{
		global $USER;
		static $extranetSiteId = null;

		$todayString = ConvertTimeStamp();

		if ($extranetSiteId === null)
		{
			$extranetSiteId = (CModule::IncludeModule('extranet') ? CExtranet::GetExtranetSiteID() : false);
		}

		$authorUrl = (
			$res["AUTHOR"]["ID"]
				? str_replace(
					array("#ID#", "#id#", "#USER_ID#", "#user_id#"),
					array($res["ID"], $res["ID"], $res["AUTHOR"]["ID"], $res["AUTHOR"]["ID"]),
					$arParams["AUTHOR_URL"]
				)
				: "javascript:void();"
		);

		$authorStyle = '';
		$authorTooltipParams = array();

		if (!empty($res["AUTHOR"]["TYPE"]))
		{
			if ($res["AUTHOR"]["TYPE"] == 'EMAILCRM')
			{
				$authorStyle = ' feed-com-name-emailcrm';
			}
			if ($res["AUTHOR"]["TYPE"] == 'EMAIL')
			{
				$authorStyle = ' feed-com-name-email';
			}
			else if ($res["AUTHOR"]["TYPE"] == 'EXTRANET')
			{
				$authorStyle = ' feed-com-name-extranet';
			}
		}
		else if ($res["AUTHOR"]["IS_EXTRANET"] == "Y")
		{
			$authorStyle = ' feed-com-name-extranet';
		}

		if (
			!empty($arParams["AUTHOR_URL_PARAMS"]) && is_array($arParams["AUTHOR_URL_PARAMS"])
			&& (
				(isset($arParams["bPublicPage"]) && $arParams["bPublicPage"])
				|| SITE_ID == $extranetSiteId
				|| (!empty($res["AUTHOR"]["TYPE"]) && in_array($res["AUTHOR"]["TYPE"], array('EMAIL', 'EMAILCRM', 'EXTRANET')))
			)
		)
		{
			$authorTooltipParams = $arParams["AUTHOR_URL_PARAMS"];
			if (
				!isset($arParams["bPublicPage"])
				|| !$arParams["bPublicPage"])
			{
				$strParams = '';
				$i = 0;
				foreach ($arParams["AUTHOR_URL_PARAMS"] as $key => $value)
				{
					$strParams .= ($i > 0 ? '&' : '').urlencode($key).'='.urlencode($value);
					$i++;
				}
				$authorUrl .= (strpos($authorUrl, '?') === false ? '?' : '&').$strParams;
			}
		}

		$replacement = array(
			"#ID#" =>
				$res["ID"],
			"#FULL_ID#" =>
				$arParams["ENTITY_XML_ID"]."-".$res["ID"],
			"#CONTENT_ID#" =>
				(!empty($arParams["RATING_TYPE_ID"]) ? $arParams["RATING_TYPE_ID"]."-".$res["ID"] : (!empty($arParams["CONTENT_TYPE_ID"]) ? $arParams["CONTENT_TYPE_ID"]."-".$res["ID"] : "")),
			"#ENTITY_XML_ID#" =>
				$arParams["ENTITY_XML_ID"],
			"#NEW#" =>
				($res["NEW"] == "Y" ? "new" : "old"),
			"#APPROVED#" =>
				($res["APPROVED"] != "Y" ? "hidden" : "approved"),
			"#DATE#" => (ConvertTimeStamp(($res["POST_TIMESTAMP"] + CTimeZone::GetOffset()), "SHORT") == $todayString ? $res["POST_TIME"] : $res["POST_DATE"]),
			"#TEXT#" => str_replace(array("\001", "#"), array("", "\001"), $res["POST_MESSAGE_TEXT"]),
			"#CLASSNAME#" =>
				(isset($res["CLASSNAME"]) ? " ".$res["CLASSNAME"] : ""),
			"#VOTE_ID#" =>
				(is_array($res["RATING"]) ? $res["RATING"]["VOTE_ID"] : ""),
			"#VIEW_URL#" =>
				str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["VIEW_URL"]),
			"#VIEW_SHOW#" =>
				($arParams["VIEW_URL"] == "" ? "N" : "Y"),
			"#EDIT_URL#" =>
				str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["EDIT_URL"]),
			"#EDIT_SHOW#" => (
				empty($res["AUX"])
				&& (
					$arParams["RIGHTS"]["EDIT"] == "Y" || $arParams["RIGHTS"]["EDIT"] == "ALL" ||
					$arParams["RIGHTS"]["EDIT"] == "OWN" && $USER->GetID() == intval($res["AUTHOR"]["ID"])
				)
					? "Y"
					: "N"
			),
			"#MODERATE_URL#" =>
				str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["MODERATE_URL"]),
			"#MODERATE_SHOW#" =>
				($arParams["RIGHTS"]["MODERATE"] == "Y" || $arParams["RIGHTS"]["MODERATE"] == "ALL" ||
				$arParams["RIGHTS"]["MODERATE"] == "OWN" && $USER->GetID() == intval($res["AUTHOR"]["ID"]) ? "Y" : "N"),
			"#DELETE_URL#" =>
				str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["DELETE_URL"]),
			"#DELETE_SHOW#" =>
				($arParams["RIGHTS"]["DELETE"] == "Y" || $arParams["RIGHTS"]["DELETE"] == "ALL" ||
				$arParams["RIGHTS"]["DELETE"] == "OWN" && $USER->GetID() == intval($res["AUTHOR"]["ID"]) ? "Y" : "N"),
			"#CREATETASK_SHOW#" => (
				empty($res["AUX"])
				&& $arParams["RIGHTS"]["CREATETASK"] == "Y"
					? "Y"
					: "N"
			),
			"#BEFORE_HEADER#" => $res["BEFORE_HEADER"],
			"#BEFORE_ACTIONS#" => $res["BEFORE_ACTIONS"],
			"#AFTER_ACTIONS#" => $res["AFTER_ACTIONS"],
			"#AFTER_HEADER#" => $res["AFTER_HEADER"],
			"#BEFORE#" => $res["BEFORE"],
			"#AFTER#" => $res["AFTER"],
			"#BEFORE_RECORD#" => $res["BEFORE_RECORD"],
			"#AFTER_RECORD#" => $res["AFTER_RECORD"],
			"#AUTHOR_ID#" =>
				$res["AUTHOR"]["ID"],
			"#AUTHOR_AVATAR_IS#" =>
				(empty($res["AUTHOR"]["AVATAR"]) ? "N" : "Y"),
			"#AUTHOR_AVATAR#" => (
				!empty($res["AUTHOR"]["AVATAR"])
					? $res["AUTHOR"]["AVATAR"]
					: (
						!empty($arParams["AVATAR_DEFAULT"])
							? $arParams["AVATAR_DEFAULT"]
							: ""
					)
			),
			"#AUTHOR_URL#" => $authorUrl,
			"#AUTHOR_NAME#" =>
				CUser::FormatName(
				$arParams["NAME_TEMPLATE"],
				array(
					"NAME" => $res["AUTHOR"]["NAME"],
					"LAST_NAME" => $res["AUTHOR"]["LAST_NAME"],
					"SECOND_NAME" => $res["AUTHOR"]["SECOND_NAME"],
					"LOGIN" => $res["AUTHOR"]["LOGIN"],
					"NAME_LIST_FORMATTED" => ""
				),
				($arParams["SHOW_LOGIN"] != "N"),
				false),
			"#AUTHOR_PERSONAL_GENDER#" => !empty($res["AUTHOR"]["PERSONAL_GENDER"]) ?
				$res["AUTHOR"]["PERSONAL_GENDER"] : "",
			"#AUTHOR_TOOLTIP_PARAMS#" => CUtil::PhpToJSObject($authorTooltipParams),
			"#SHOW_POST_FORM#" =>
				$arParams["SHOW_POST_FORM"],
			"#AUTHOR_EXTRANET_STYLE#" => $authorStyle,
			"background:url('') no-repeat center;" =>
				""
		);
		return str_replace(array_merge(array_keys($replacement), array("\001")), array_merge(array_values($replacement), array("#")), $template);
	}

	protected function prepareParams(array &$arParams, array &$arResult)
	{
		// Action params
		/*@param string $arParams["mfi"] contains hash of something to add new uploaded file into session array */
		$arParams["mfi"] = trim($arParams["mfi"]);
		// List params
		/*@param string $arParams["ENTITY_XML_ID"] main param that means ID */
		$arParams["ENTITY_XML_ID"] = trim($arParams["ENTITY_XML_ID"]);
		/*@param array $arParams["RECORDS"] contains data to view */
		$arParams["RECORDS"] = (is_array($arParams["RECORDS"]) ? $arParams["RECORDS"] : array());
		$arParams["NAV_STRING"] = (!!$arParams["NAV_STRING"] && is_string($arParams["NAV_STRING"]) ? $arParams["NAV_STRING"] : "");
		//$arParams["NAV_RESULT"] = (!!$arParams["NAV_STRING"] && is_object($arParams["NAV_RESULT"]) ? $arParams["NAV_RESULT"] : false);
		$arParams["PREORDER"] = ($arParams["PREORDER"] == "Y" ? "Y" : "N");
		$arParams["RIGHTS"] = (is_array($arParams["RIGHTS"]) ? $arParams["RIGHTS"] : array());
		foreach (array("MODERATE", "EDIT", "DELETE", "CREATETASK") as $act)
			$arParams["RIGHTS"][$act] = in_array(strtoupper($arParams["RIGHTS"][$act]), array("Y", "ALL", "OWN", "OWNLAST")) ? $arParams["RIGHTS"][$act] : "N";
		$arParams["LAST_RECORD"] = array();
		// Answer params
		/*@param int $arParams["RESULT"] contains id of new record for cutting out and sending back*/
		$arParams["RESULT"] = intval($arParams["RESULT"] ?: $this->request->getPost("MID"));
		/*@param array $arParams["PUSH&PULL"] contains record id to pushing other clients */
		$arParams["PUSH&PULL"] = (isset($arParams["~PUSH&PULL"]) ? $arParams["~PUSH&PULL"] : $arParams["PUSH&PULL"]);
		$arParams["MODE"] = (is_array($arParams["PUSH&PULL"]) && $arParams["PUSH&PULL"]["ID"] > 0 && $arParams["MODE"] == "PULL_MESSAGE" ? "PULL_MESSAGE" : "PLAIN");

		/*@param string $arParams["NOTIFY_TAG"] params for bottom notifier */
		$arParams["NOTIFY_TAG"] = trim($arParams["NOTIFY_TAG"]);
		$arParams["NOTIFY_TEXT"] = trim($arParams["NOTIFY_TEXT"]);
		$arParams["ERROR_MESSAGE"] = trim($arParams["ERROR_MESSAGE"]);
		$arParams["OK_MESSAGE"] = trim($arParams["OK_MESSAGE"]);
		// Template params
		$arParams["VISIBLE_RECORDS_COUNT"] = (!!$arParams["NAV_RESULT"] ? intval($arParams["VISIBLE_RECORDS_COUNT"]) : 0);
		$arParams["TEMPLATE_ID"] = (!!$arParams["TEMPLATE_ID"] ? $arParams["TEMPLATE_ID"] : 'COMMENT_'.$arParams["ENTITY_XML_ID"].'_');
		$arParams["AVATAR_SIZE"] = ($arParams["AVATAR_SIZE"] > 0 ? $arParams["AVATAR_SIZE"] : 100);
		//$arParams["IMAGE_SIZE"] = ($arParams["IMAGE_SIZE"] > 0 ? $arParams["IMAGE_SIZE"] : 30);
		$arParams['SHOW_MINIMIZED'] = ($arParams['SHOW_MINIMIZED'] == "Y" ? "Y" : "N");

		$arParams["NAME_TEMPLATE"] = (!!$_REQUEST["NAME_TEMPLATE"] ? $_REQUEST["NAME_TEMPLATE"] : (!!$arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : \CSite::GetNameFormat()));
		$arParams["SHOW_LOGIN"] = ($_REQUEST["SHOW_LOGIN"] == "Y" ? "Y" : ($arParams["SHOW_LOGIN"] == "Y" ? "Y" : "N"));
		$arParams["DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
		$arParams["SHOW_POST_FORM"] = ($arParams["SHOW_POST_FORM"] == "Y" ? "Y" : "N");
		$arParams["BIND_VIEWER"] = ($arParams["BIND_VIEWER"] == "N" ? "N" : "Y");
		$arParams["SIGN"] = $this->sign->sign($arParams["ENTITY_XML_ID"], "main.post.list");

		$arParams["VIEW_URL"] = trim($arParams["VIEW_URL"]);
		$arParams["EDIT_URL"] = trim($arParams["EDIT_URL"]);
		$arParams["MODERATE_URL"] = trim($arParams["MODERATE_URL"]);
		$arParams["DELETE_URL"] = trim($arParams["DELETE_URL"]);
		$arParams["AUTHOR_URL"] = trim($arParams["PATH_TO_USER"] ?: $arParams["AUTHOR_URL"]);

		if ($arParams["VISIBLE_RECORDS_COUNT"] > 0)
		{
			if ($arParams["NAV_RESULT"]->bShowAll)
				$arParams["VISIBLE_RECORDS_COUNT"] = 0;
			else if (array_key_exists($arParams['RESULT'], $arParams["RECORDS"]))
				$arParams["VISIBLE_RECORDS_COUNT"] = count($arParams["RECORDS"]);
			else if (0 < $arParams["NAV_RESULT"]->NavRecordCount && $arParams["NAV_RESULT"]->NavRecordCount <= $arParams["VISIBLE_RECORDS_COUNT"])
				$arParams["VISIBLE_RECORDS_COUNT"] = $arParams["NAV_RESULT"]->NavRecordCount;
			else if (isset($_REQUEST["PAGEN_".$arParams["NAV_RESULT"]->NavNum]) ||
				isset($_REQUEST["FILTER"]) && $arParams["ENTITY_XML_ID"] == $_REQUEST["ENTITY_XML_ID"])
				$arParams["VISIBLE_RECORDS_COUNT"] = 0;
			if (!!$arParams["NAV_STRING"])
			{
				$path = "PAGEN_".$arParams["NAV_RESULT"]->NavNum."=";
				if ($arParams["VISIBLE_RECORDS_COUNT"] > 0)
					$path .= $arParams["NAV_RESULT"]->NavPageNomer;
				else if ($arParams["NAV_RESULT"]->bDescPageNumbering)
					$path .= ($arParams["NAV_RESULT"]->NavPageNomer - 1);
				else
					$path .= ($arParams["NAV_RESULT"]->NavPageNomer + 1);
				$arParams["NAV_STRING"] .= (strpos($arParams["NAV_STRING"], "?") === false ? "?" : "&").$path;
			}
		}
		if (!empty($arParams["RECORDS"]))
		{
			if ($arParams["VISIBLE_RECORDS_COUNT"] > 0)
			{
				$list = array();
				$res = 0;
				for ($ii = 0; $ii < $arParams["VISIBLE_RECORDS_COUNT"]; $ii++)
				{
					$res = array_shift($arParams["RECORDS"]);
					$list[$res["ID"]] = $res;
				}

				$arParams["RECORDS"] = $list;
			}

			$arParams["LAST_RECORD"] = end($arParams["RECORDS"]);
			reset($arParams["RECORDS"]);

			if ($arParams["PREORDER"] === "N")
				$arParams["RECORDS"] = array_reverse($arParams["RECORDS"], true);

			if (!empty($arParams["RATING_TYPE_ID"]))
				$arParams["RATING_RESULTS"] = CRatings::GetRatingVoteResult($arParams["RATING_TYPE_ID"], array_keys($arParams["RECORDS"]));

			$arParams["~RECORDS"] = $arParams["RECORDS"];
			foreach ($arParams["~RECORDS"] as $key => $res)
				$arParams["RECORDS"][$key] = $this->buildComment($res);
		}

		$arResult["AUTHOR"] = array(
			"ID" => $this->getUser()->getId(),
			"NAME" => CUser::FormatName(
				$arParams["NAME_TEMPLATE"],
				array(
					"NAME" => $this->getUser()->getFirstName(),
					"LAST_NAME" => $this->getUser()->getLastName(),
					"SECOND_NAME" => $this->getUser()->getSecondName(),
					"LOGIN" => $this->getUser()->getLogin(),
					"NAME_LIST_FORMATTED" => "",
				),
				($arParams["SHOW_LOGIN"] != "N"),
				false),
			"AVATAR" => \CFile::ResizeImageGet(
					$_SESSION["SESS_AUTH"]["PERSONAL_PHOTO"],
					array(
						"width" => $arParams["AVATAR_SIZE"],
						"height" => $arParams["AVATAR_SIZE"]
					),
					BX_RESIZE_IMAGE_EXACT
				)
		);
	}

	public function executeComponent()
	{
		try
		{
			$this->prepareParams($this->arParams, $this->arResult);
			ob_start();

			$this->includeComponentTemplate();

			$output = ob_get_clean();
			$json = false;

			foreach (GetModuleEvents('main.post.list', 'OnCommentsDisplayTemplate', true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array(&$output, &$this->arParams, &$this->arResult));
			}
			$this->sendIntoPull($this->arParams, $this->arResult);

			if ($this->arParams["MODE"] == "PULL_MESSAGE")
			{
				$json = $this->parseHTML($output, "RECORD");
			}
			else if ($this->getMode() == "RECORD" || $this->getMode() == "LIST")
			{
				$json = $this->parseHTML($output, $this->getMode());
				$this->sendJsonResponse($json);
			}

			$output .= $this->joinToPull();
			return array("HTML" => $output, "JSON" => $json);
		}
		catch (\Exception $e)
		{
			$this->sendJsonResponse(array(
				"status" => "error",
				"message" => $e->getMessage()
			));
		}
	}

	protected function sendJsonResponse($response)
	{
		$this->getApplication()->restartBuffer();
		while (ob_end_clean());
		header('Content-Type:application/json; charset=UTF-8');
		?><?=Json::encode($response)?><?
		/** @noinspection PhpUndefinedClassInspection */
		\CMain::finalActions();
		die;
	}

	private function parseHTML($response, $mode = "RECORD")
	{
		include_once(__DIR__."/html_parser.php");
		$JSResult = array();
		$FHParser = new MPLSimpleHTMLParser($response);
		$SHParser = new MPLSimpleHTMLParser($this->getApplication()->GetHeadStrings());
		$arParams = &$this->arParams;

		if ($mode == "LIST")
		{
			$messageList = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->').
				$FHParser->getInnerHTML('<!--RCRDLIST_'.$arParams["ENTITY_XML_ID"].'-->', '<!--RCRDLIST_END_'.$arParams["ENTITY_XML_ID"].'-->');

			$messageNavigation = $FHParser->getTagHTML('a[class=feed-com-all]');

			$JSResult += array(
				'status' => "success",
				'messageList' => $messageList,
				'navigation' => $messageNavigation
			);
		}
		else if ($mode == "RECORD")
		{
			$record = $arParams["RESULT"];
			if ($record <= 0)
			{
				$filter = $this->request->getQuery("FILTER");
				$record = (is_array($filter) ? intval($filter["ID"]) : 0);
			}
			$message = $FHParser->getInnerHTML('<!--RCRD_'.$arParams["ENTITY_XML_ID"]."-".$record.'-->', '<!--RCRD_END_'.$arParams["ENTITY_XML_ID"]."-".$record.'-->');
			$res = false;
			if (array_key_exists($record, $arParams["RECORDS"]) && array_key_exists($record, $arParams["~RECORDS"]))
			{
				$res = $arParams["RECORDS"][$record];
				$res = array_merge($arParams["~RECORDS"][$record], $res, ($this->isWeb() ? $res["WEB"] : $res["MOBILE"]));
				unset($res["WEB"]);
				unset($res["MOBILE"]);


				if (!!$res["FILES"] && (
						$this->arParams["RIGHTS"]["EDIT"] == "ALL" ||
						$this->arParams["RIGHTS"]["EDIT"] == "Y" ||
						$this->arParams["RIGHTS"]["EDIT"] == "OWN" && $res["AUTHOR"]["ID"] == $this->getUser()->getId()
					))
				{
					$_SESSION["MFI_UPLOADED_FILES_".$arParams["mfi"]] = array();
					foreach($res["FILES"] as $key => $arFile)
					{
						$_SESSION["MFI_UPLOADED_FILES_".$arParams["mfi"]][] = $key;
						if (CFile::IsImage($arFile["FILE_NAME"], $arFile["CONTENT_TYPE"]))
						{
							$aImgNew = CFile::ResizeImageGet(
								$key,
								array("width" => 90, "height" => 90),
								BX_RESIZE_IMAGE_EXACT,
								true
							);
							$res["FILES"][$key]["THUMBNAIL"] = $aImgNew["src"];
							$aImgNew = CFile::ResizeImageGet(
								$key,
								array("width" => $arParams["IMAGE_SIZE"], "height" => $arParams["IMAGE_SIZE"]),
								BX_RESIZE_IMAGE_PROPORTIONAL,
								true
							);
							$res["FILES"][$key]["SRC"] = $aImgNew["src"];
						}
					}
				}
			}

			$JSResult += array(
				'errorMessage' => (isset($arParams["~ERROR_MESSAGE"]) ? $arParams["~ERROR_MESSAGE"] : (isset($arParams["ERROR_MESSAGE"]) ? $arParams["ERROR_MESSAGE"] : '')),
				'okMessage' => (isset($arParams["~OK_MESSAGE"]) ? $arParams["~OK_MESSAGE"] : (isset($arParams["OK_MESSAGE"]) ? $arParams["OK_MESSAGE"] : '')),
				'status' => "success",
				'message' => $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->').$message,
				'messageBBCode' => $arParams["~RECORDS"][$record]["~POST_MESSAGE_TEXT"],
				'messageId' => array($arParams["ENTITY_XML_ID"], $record),
				'messageFields' => $res
			);
		}
		return $JSResult;
	}

	public function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	public function getUser()
	{
		global $USER;
		return $USER;
	}

	public function getDateTimeFormatted($timestamp, $arFormatParams)
	{
		return CComponentUtil::GetDateTimeFormatted($timestamp, (isset($arFormatParams["DATE_TIME_FORMAT"]) ? $arFormatParams["DATE_TIME_FORMAT"] : false));
	}
}