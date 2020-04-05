<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

CPageTemplate::IncludeLangFile(__FILE__);

class CBootstrapPageTemplate
{
	function GetDescription()
	{
		return array(
			"name" => GetMessage("bt_wizard_name"),
			"description" => GetMessage("bt_wizard_title"),
			"modules" => array("bitrix.eshop"),
		);
	}

	function GetFormHtml()
	{
		$s = '
<tr class="section">
	<td colspan="4">'.GetMessage("BT_TYPE").'</td>
</tr>
';
		$s .= '
<tr>
	<td style="vertical-align: top; padding-top:10px">
		<input type="radio" name="BT_COL" value="1" id="BT_COL_1" checked>
		<label for="BT_COL_1">'.GetMessage("BT_COL_1").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_1.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
	<td style="padding-top:10px">
		<input type="radio" name="BT_COL" value="2_1" id="BT_COL_2_1">
		<label for="BT_COL_2_1">'.GetMessage("BT_COL_2_1").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_2_1.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
	<td style="padding-top:10px">
		<input type="radio" name="BT_COL" value="1_2" id="BT_COL_1_2">
		<label for="BT_COL_1_2">'.GetMessage("BT_COL_1_2").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_1_2.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
	<td style="padding-top:10px">
		<input type="radio" name="BT_COL" value="1_2_1" id="BT_COL_1_2_1">
		<label for="BT_COL_1_2_1">'.GetMessage("BT_COL_1_2_1").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_1_2_1.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
</tr>
<tr>
	<td style="padding-top:20px">
		<input type="radio" name="BT_COL" value="1_1" id="BT_COL_1_1">
		<label for="BT_COL_1_1">'.GetMessage("BT_COL_1_1").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_1_1.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
	<td style="padding-top:20px">
		<input type="radio" name="BT_COL" value="1_1_1" id="BT_COL_1_1_1">
		<label for="BT_COL_1_1_1">'.GetMessage("BT_COL_1_1_1").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_1_1_1.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
	<td style="padding-top:20px">
		<input type="radio" name="BT_COL" value="5" id="BT_COL_5">
		<label for="BT_COL_5">'.GetMessage("BT_COL_5").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_5.png" style="margin-top: 7px; padding-left: 17px;"/><br>
	</td>
	<td style="padding-top:20px">
		<input type="radio" name="BT_COL" value="4" id="BT_COL_4">
		<label for="BT_COL_4">'.GetMessage("BT_COL_4").'</label><br>
		<img src="/bitrix/images/bitrix.eshop/col_4.png" style="margin-top: 7px; padding-left: 17px;"/>
	</td>
</tr>
';
		return $s;
	}

	function GetContent($arParams)
	{
		$gridHtml = '
<div class="row">';

		if (isset($_POST['BT_COL']))
		{
			switch ($_POST['BT_COL'])
			{
				case '1':
				{
					$gridHtml.= '
	<div class="col-xs-12"></div>';
					break;
				}
				case '2_1':
				{
					$gridHtml.= '
	<div class="col-sm-8"></div>
	<div class="col-sm-4"></div>';
					break;
				}
				case '1_2':
				{
					$gridHtml.= '
	<div class="col-sm-4"></div>
	<div class="col-sm-8"></div>';
					break;
				}
				case '1_2_1':
				{
					$gridHtml.= '
	<div class="col-sm-3"></div>
	<div class="col-sm-6"></div>
	<div class="col-sm-3"></div>';
					break;
				}
				case '1_1':
				{
					$gridHtml.= '
	<div class="col-sm-6"></div>
	<div class="col-sm-6"></div>';
					break;
				}
				case '1_1_1':
				{
					$gridHtml.= '
	<div class="col-sm-4"></div>
	<div class="col-sm-4"></div>
	<div class="col-sm-4"></div>';
					break;
				}
				case '5':
				{
					$gridHtml.= '
	<div class="col-sm-8"></div>
	<div class="col-sm-4"></div>
</div>
<div class="row">
	<div class="col-xs-12"></div>
</div>
<div class="row">
	<div class="col-sm-8"></div>
	<div class="col-sm-4"></div>';
					break;
				}
				case '4':
				{
					$gridHtml.= '
	<div class="col-sm-8"></div>
	<div class="col-sm-4"></div>
</div>
<div class="row">
	<div class="col-xs-4"></div>
	<div class="col-xs-8"></div>';
					break;
				}
			}
		}
		$gridHtml.= '
</div>
';

		$s = '<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>';
		$s.= $gridHtml;
		$s.= '<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>';
		return $s;
	}
}

$pageTemplate = new CBootstrapPageTemplate;
?>