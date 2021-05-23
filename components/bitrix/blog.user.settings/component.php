<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arParams["BLOG_URL"]);
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);
if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER_SETTINGS_EDIT"] = trim($arParams["PATH_TO_USER_SETTINGS_EDIT"]);
if($arParams["PATH_TO_USER_SETTINGS_EDIT"] == '')
	$arParams["PATH_TO_USER_SETTINGS_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_settings_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

if ($arParams["BLOG_URL"] <> '')
{
	if($arParams["SET_TITLE"]=="Y")
		$APPLICATION->SetTitle(GetMessage("B_B_US_TITLE"));
		
	if ($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]))
	{
		if($arBlog["ACTIVE"] == "Y")
		{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if($arGroup["SITE_ID"] == SITE_ID)
			{
				$arResult["Blog"] = $arBlog;

				if (CBlog::CanUserManageBlog($arBlog["ID"], intval($USER->GetID())))
				{
					if($arParams["SET_TITLE"]=="Y")
						$APPLICATION->SetTitle(str_replace("#NAME#", $arBlog["NAME"], GetMessage("B_B_US_TITLE_BLOG")));

					$errorMessage = "";
					$okMessage = "";

					if (intval($GLOBALS["del_id"]) > 0)
					{
						if(check_bitrix_sessid())
						{
							CBlogUser::AddToUserGroup($GLOBALS["del_id"], $arBlog["ID"], array(), "", BLOG_BY_USER_ID, BLOG_CHANGE);

							$dbCandidate = CBlogCandidate::GetList(
								array(),
								array("BLOG_ID" => $arBlog["ID"], "USER_ID" => intval($GLOBALS["del_id"]))
							);
							if ($arCandidate = $dbCandidate->Fetch())
							{
								CBlogCandidate::Delete($arCandidate["ID"]);
								$okMessage = GetMessage("B_B_US_DELETE_OK").".<br />";
							}
						}
						else
							$errorMessage .= GetMessage("BLOG_BLOG_SESSID_WRONG")."<br />";
					}
					
					if (isset($_REQUEST["add_friend"]) && is_array($_REQUEST["add_friend"]))
					{
						if(check_bitrix_sessid())
						{
							foreach ($_REQUEST["add_friend"] as $key => $friend)
							{
								$arFriendUsers = Array();
								if ($friend <> '')
								{
									$arUserID = array();
									$dbUsers = CBlogUser::GetList(
										array(),
										array(
												"GROUP_BLOG_ID" => $arBlog["ID"],
											),
										array("ID", "USER_ID")
										);
									while($arUsers = $dbUsers->Fetch())
									{
										$arFriendUsers[] = $arUsers["USER_ID"];
									}

									$dbSearchUser = CBlog::GetList(array(), array("URL" => $friend), false, false, array("ID", "OWNER_ID"));
									if($arSearchUser = $dbSearchUser->Fetch())
									{
										$arUserID[] = $arSearchUser["OWNER_ID"];
									}

									/*
									$dbSearchUser = CBlog::GetList(array(), array("NAME" => $friend), false, false, array("ID", "OWNER_ID"));
									while(($arSearchUser = $dbSearchUser->Fetch()) && !in_array($arSearchUser["OWNER_ID"], $arUserID))
										$arUserID[] = $arSearchUser["OWNER_ID"];
									*/

									$canUseAlias = COption::GetOptionString("blog", "allow_alias", "Y");
									if ($canUseAlias === "Y")
									{
										$dbSearchUser = CBlogUser::GetList(array(), array("ALIAS" => $friend), false, false, array("ID", "USER_ID"));
										if(($arSearchUser = $dbSearchUser->Fetch()) && !in_array($arSearchUser["USER_ID"], $arUserID))
										{
											$arUserID[] = $arSearchUser["USER_ID"];
										}
									}

									$dbSearchUser = CUser::GetList(($b = "LOGIN"), ($o = "ASC"), array("LOGIN_EQUAL" => $friend));
									if(($arSearchUser = $dbSearchUser->Fetch()) && !in_array($arSearchUser["ID"], $arUserID))
									{
										$arUserID[] = $arSearchUser["ID"];
									}

									$usersCount = count($arUserID);
									if ($usersCount > 0)
									{
										for ($i = 0; $i < $usersCount; $i++)
										{
											if($arUserID[$i] != $arBlog["OWNER_ID"] && !in_array($arUserID[$i], $arFriendUsers))
											{
												$dbCandidate = CBlogCandidate::GetList(
													array(),
													array("BLOG_ID" => $arBlog["ID"], "USER_ID" => $arUserID[$i])
												);
												if ($dbCandidate->Fetch())
												{
													$okMessage .= str_replace("#NAME#", "[".$arUserID[$i]."] ".htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_ALREADY_WANT")).".<br />";
												}
												else
												{
													if (CBlogCandidate::Add(array("BLOG_ID" => $arBlog["ID"], "USER_ID" => $arUserID[$i])))
													{
														$okMessage .= str_replace("#NAME#", "[".$arUserID[$i]."] ".htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_ADDED")).".<br />";
														
														$BlogUser = CBlogUser::GetByID($arUserID[$i], BLOG_BY_USER_ID); 
														$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);
														
														$dbUser = CUser::GetByID($arUserID[$i]);
														$arUser = $dbUser->GetNext();
														$AuthorName = CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"], $arUser["SECOND_NAME"]);
														$dbUser = CUser::GetByID($arBlog["OWNER_ID"]);
														$arUserBlog = $dbUser->GetNext();
														if ($serverName == '')
														{
															if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
																$serverName = SITE_SERVER_NAME;
															else
																$serverName = COption::GetOptionString("main", "server_name", "");
															if ($serverName == '')
																$serverName = $_SERVER["SERVER_NAME"];
														}

														$arMailFields = Array(
																"BLOG_ID" => $arBlog["ID"],
																"BLOG_NAME" => $arBlog["NAME"],
																"BLOG_URL" => $arBlog["URL"],
																"BLOG_ADR" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_BLOG"]), array("blog" => $arBlog["URL"])),
																"USER_ID" => $arUserID[$i],
																"USER" => $AuthorName,
																"USER_URL" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_USER"]), array("user_id" => $arUserID[$i])),
																"EMAIL_FROM" => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
															);
														$arF1 = $arF2 = $arMailFields;
														$arF1["EMAIL_TO"] = $arUser["EMAIL"];
														$arF2["EMAIL_TO"] = $arUserBlog["EMAIL"];
														CEvent::Send("BLOG_YOU_TO_BLOG", SITE_ID, $arF1);
														CEvent::Send("BLOG_USER_TO_YOUR_BLOG", SITE_ID, $arF2);

													}
													else
														$errorMessage .= str_replace("#NAME#", "[".$arUserID[$i]."] ".htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_ADD_ERROR")).".<br />";
												}
											}
										}
									}
									else
									{
										$errorMessage .= str_replace("#NAME#", htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_NOT_FOUND")).".<br />";
									}
								}
							}
						}
						else
							$errorMessage .= GetMessage("BLOG_BLOG_SESSID_WRONG")."<br />";
					}

					$arResult["ERROR_MESSAGE"] = $errorMessage;
					$arResult["OK_MESSAGE"] = $okMessage;

					$canUseAlias = COption::GetOptionString("blog", "allow_alias", "Y");
					if ($canUseAlias == "Y")
						$arOrderBy = array("ALIAS" => "ASC", "USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC");
					else
						$arOrderBy = array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC");

					$dbUsers = CBlogCandidate::GetList(
						$arOrderBy,
						array("BLOG_ID" => $arBlog["ID"]),
						false,
						false,
						array("ID", "USER_ID", "BLOG_USER_ALIAS", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME")
					);
					$arResult["Candidate"] = Array();
					while($arUsers = $dbUsers->GetNext())
					{
						$arUsers["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUsers["USER_ID"]));
						$arUsers["NameFormated"] = CBlogUser::GetUserName($arUsers["BLOG_USER_ALIAS"], $arUsers["USER_NAME"], $arUsers["USER_LAST_NAME"], $arUsers["USER_LOGIN"], $arUsers["USER_SECOND_NAME"]);
						$arUsers["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS_EDIT"], array("user_id" => $arUsers["USER_ID"], "blog"=>$arBlog["URL"]));
						$arUsers["urlToDelete"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("del_id=".$arUsers["USER_ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid")));
						$arResult["Candidate"][] = $arUsers;
					}

					$dbUsers = CBlogUser::GetList(
						$arOrderBy,
						array("GROUP_BLOG_ID" => $arBlog["ID"]),
						array("ID", "USER_ID", "ALIAS", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME")
					);
					$arResult["Users"] = Array();
					while($arUsers = $dbUsers->GetNext())
					{
						$arUsers["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUsers["USER_ID"]));
						$arUsers["NameFormated"] = CBlogUser::GetUserName($arUsers["BLOG_USER_ALIAS"], $arUsers["USER_NAME"], $arUsers["USER_LAST_NAME"], $arUsers["USER_LOGIN"], $arUsers["USER_SECOND_NAME"]);
						$arUsers["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS_EDIT"], array("user_id" => $arUsers["USER_ID"], "blog"=>$arBlog["URL"]));
						$arUsers["urlToDelete"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("del_id=".$arUsers["USER_ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid")));
											
						$dbUserGroups = CBlogUserGroup::GetList(
							array(),
							array(
								"USER2GROUP_USER_ID" => $arUsers["USER_ID"],
								"BLOG_ID" => $arBlog["ID"]
							),
							false,
							false,
							array("ID", "NAME")
						);
						$bNeedComa = False;
						while ($arUserGroups = $dbUserGroups->GetNext())
						{
							if ($bNeedComa)
								$arUsers["groupsFormated"] .= ", ";
							$arUsers["groups"][] = $arUserGroups;
							$arUsers["groupsFormated"] .= $arUserGroups["NAME"];
							$bNeedComa = True;
						}
						
						$arResult["Users"][] = $arUsers;
					}
				}
				else
				{
					$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_RIGHT");
				}
			}
			else
				$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
		}
		else
			$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
	}
	else
		$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
}
else
{
	$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
}
	
$this->IncludeComponentTemplate();
?>
