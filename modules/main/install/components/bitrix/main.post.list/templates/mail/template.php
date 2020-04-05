<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

ob_start();
?>
	<tr>
		<td valign="top" align="center" style="#CLASSNAME#border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 0;padding: 12px 15px 12px 19px;vertical-align: top;width: 39px;">
			<img height="<?=$arParams["AVATAR_SIZE"]?>" width="<?=$arParams["AVATAR_SIZE"]?>" src="#AUTHOR_AVATAR#" alt="#AUTHOR_NAME#" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;height: 39px;width: 39px;">
		</td>
		<td valign="top" style="#CLASSNAME#border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;color: #000000;font-size: 13px;line-height: 20px;padding: 12px 15px 15px 0;">
			<span style="color: #0b66c3;font-size: 13px;font-weight: bold;line-height: 16px;vertical-align: top;">#AUTHOR_NAME#</span>
			&nbsp;
			<span style="color: #999999;font-size: 11px;line-height: 16px;vertical-align: top;">#DATE#</span>
			<br>
			#TEXT#
			#AFTER#
		</td>
	</tr>
<?
$template = preg_replace("/[\t\n]/", "", ob_get_clean());

if (!empty($arParams["RECORDS"]))
{
	?><tr>
		<td valign="top" align="left" style="border-collapse: collapse;border-spacing: 0;padding: 0 0 36px;">
			<table cellspacing="0" cellpadding="0" border="0" align="left" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;background-color: #f5f7f8;width: 100%;">
				<tr>
					<td border="0" valign="top" align="center" bgcolor="#ffffff" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 10px;padding: 0;vertical-align: top;width: 39px;border: none;background-color: #ffffff;height: 10px;text-align: center;"><?
						$src = $this->getFolder()."/images/comments-corner.gif";
						?><img src="<?=$src?>" alt="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;display: inline-block;vertical-align: top;">
					</td>
					<td border="0" bgcolor="#ffffff" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;color: #000000;font-size: 13px;line-height: 20px;padding: 0;border: none;background-color: #ffffff;height: 10px;"></td>
				</tr><?

				if (
					$arParams["COMMENTS_COUNT"] > 0
					&& $arParams["COMMENTS_ALL_COUNT"] > $arParams["COMMENTS_COUNT"]
				)
				{
					?><tr>
						<td valign="middle" colspan="2" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 0;padding: 0 0 0 16px;vertical-align: middle;width: 39px;color: #2067b0;height: 38px;font-size: 12px;">
							<a href="<?=$arParams["POST_URL"]?>" style="color: #146cc5;"><?=GetMessage('MPL_MAIL_MORE_COMMENTS', array("#NUM#" => ($arParams["COMMENTS_ALL_COUNT"] - $arParams["COMMENTS_COUNT"])))?></a>
						</td>
					</tr><?
				}

				$arParams["AVATAR_DEFAULT"] = $this->getFolder()."/images/userpic.gif";

				$i = 0;
				foreach ($arParams["RECORDS"] as $res)
				{
					$res["AUTHOR"] = (is_array($res["AUTHOR"]) ? $res["AUTHOR"] : array());
					$res["CLASSNAME"] = (
						$arParams["HIGHLIGHT"] == "Y"
						&& $i == (count($arParams["RECORDS"]) - 1)
							? 'background-color:#fffcee;'
							: ''
					);

					?><?=$this->__component->parseTemplate($res, $arParams, $template)?><?
					$i++;
				}

				?>
				<tr>
					<td valign="top" align="center" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 0;padding: 12px 15px 12px 19px;vertical-align: top;width: 39px;height: 39px;"><?
						$src = $this->getFolder()."/images/userpic.gif";
						?><img height="39" width="39" src="<?=$src?>" alt="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;height: 39px;width: 39px;">
					</td>
					<td style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;color: #80868e;font-size: 13px;line-height: 20px;padding: 12px 15px 15px 0;height: 39px;">
						<a href="<?=$arParams["POST_URL"]?>" style="color: #80868e;"><?=GetMessage('MPL_MAIL_ADD_COMMENT_LINK')?></a>
					</td>
				</tr>
			</table>
		</td>
	</tr><?
}
?>