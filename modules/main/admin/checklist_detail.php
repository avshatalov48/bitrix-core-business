<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!defined('NOT_CHECK_PERMISSIONS') || NOT_CHECK_PERMISSIONS !== true)
{
	if (!$USER->CanDoOperation('view_other_settings'))
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/checklist.php");

\Bitrix\Main\Localization\Loc::loadMessages(__DIR__."/checklist.php");

$APPLICATION->AddHeadString('
	<style type="text/css">
		p,ul,li{font-size:100%!important;}
	</style>
');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CJSCore::Init(Array('popup'));

$checklist = new CCheckList();
$arPoints = $checklist->GetPoints();

if($_REQUEST["TEST_ID"] && $arPoints[$_REQUEST["TEST_ID"]])
{
	$arTestID = $_REQUEST["TEST_ID"];
	$htmlTestID = htmlspecialcharsbx($arTestID);
	$jsTestID = CUtil::JSEscape($arTestID);

	$arPosition = 0;
	foreach($arPoints as $k=>$v)
	{
		$arPosition++;
		if ($k==$arTestID)
			break;
	}
	$arTotal = count($arPoints);
	if(!empty($arPoints[$arTestID]["STATE"]["COMMENTS"]["SYSTEM"]["DETAIL"]))
		$display="inline-block";
	else
		$display="none";
	$display_result = (!empty($arPoints[$arTestID]["STATE"]["COMMENTS"]["SYSTEM"])? "block" : "none");
	$APPLICATION->RestartBuffer();?>


<?

$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("CL_TAB_TEST"), "ICON" => "checklist_detail", "TITLE" => GetMessage("CL_TEST_NAME").': '.$arPoints[$arTestID]["NAME"].'&nbsp;('.$htmlTestID.')'),
		array("DIV" => "edit2", "TAB" => GetMessage("CL_TAB_DESC"), "ICON" => "checklist_detail", "TITLE" => GetMessage('CL_TAB_DESC')),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);


$tabControl->Begin();

$tabControl->BeginNextTab();
?>

			<div id="bx_test_result" style="display:<?=$display_result;?>" class="checklist-popup-test">
				<div class="checklist-popup-name-test"><?=GetMessage("CL_RESULT_TEST");?>:</div>
				<div>
					<span id="system_comment"><?=(!empty($arPoints[$arTestID]["STATE"]["COMMENTS"]["SYSTEM"]["PREVIEW"]))?$arPoints[$arTestID]["STATE"]["COMMENTS"]["SYSTEM"]["PREVIEW"]:"&mdash;";?></span>
					<div><span class="checklist-popup-test-link" onclick="ShowDetailComment()" id="show_detail_link"><?=GetMessage("CL_MORE_DETAILS");?></span></div>
					<div style="display:none" id="detail_system_comment_<?=$htmlTestID;?>">
						<div class="checklist-system-textarea"><?=preg_replace("/\r\n|\r|\n/",'<br>',$arPoints[$arTestID]["STATE"]["COMMENTS"]["SYSTEM"]["DETAIL"] ?? '');?></div>
					</div>
				</div>
			</div>
			<?if(isset($arPoints[$arTestID]["AUTO"]) && $arPoints[$arTestID]["AUTO"] == "Y"):?>
				<div class="checklist-popup-start-test-block checklist-popup-name-test">
					<a id="bx_start_button_detail" onclick="StartPointAutoCheck()" class="adm-btn adm-btn-green adm-btn">
						<span class="checklist-button-cont" style="color: #ffffff; font-weight: bold"><?=GetMessage("CL_AUTOTEST_START");?></span>
					</a>
					<span id="bx_per_point_done" class="checklist-popup-start-test-text"></span>
				</div>
			<?endif;?>
			<div id="check_list_comments" class="checklist-popup-result-test-block">
				<div class="checklist-popup-result-form">
					<div class="checklist-form-textar-block">
						<div class="checklist-form-textar-status"><?=GetMessage("CL_STATUS_COMMENT");?></div>
						<div class="checklist-dot-line"></div>
						<div OnClick = "BX('performer_comment_edit_area').style.display ='block'; this.style.display='none';BX('performer_comment').focus();" OnMouseOver="BX.addClass(this,'checklist-form-textar');BX.removeClass(this,'checklist-form-textar-non-active');" OnMouseOut="BX.addClass(this,'checklist-form-textar-non-active');BX.removeClass(this,'checklist-form-textar');" id="performer_comment_area"  class="checklist-form-textar-non-active" ><?=preg_replace("/\r\n|\r|\n/",'<br>', htmlspecialcharsbx($arPoints[$arTestID]["STATE"]["COMMENTS"]["PERFOMER"] ?? ''));?></div>
						<div id="performer_comment_edit_area" style="display:none;"><textarea id="performer_comment" OnBlur = "SaveStatus(); BX('performer_comment_area').style.display ='block';BX('performer_comment_edit_area').style.display='none';CopyText(this,BX('performer_comment_area'));" class="checklist-form-textar"><?=htmlspecialcharsbx($arPoints[$arTestID]["STATE"]["COMMENTS"]["PERFOMER"] ?? '')?></textarea></div>
					</div>
				</div>
			</div>
			<div class="checklist-popup-test">
				<div class="checklist-popup-name-test"><?=GetMessage("CL_TEST_STATUS");?></div>
				<div class="checklist-popup-tes-status-wrap" id="checklist-popup-tes-status">
								<span class="checklist-popup-tes-status"><span
										class="checklist-popup-tes-waiting-l"></span><span
										class="checklist-popup-tes-waiting-c"><?=GetMessage("CL_W_STATUS");?></span><span
										class="checklist-popup-tes-waiting-r"></span><input name="checklist-form-radio" type="radio" value="W" id="W_status" /></span>
								<span
										class="checklist-popup-tes-status"><span
										class="checklist-popup-tes-successfully-l"></span><span
										class="checklist-popup-tes-successfully-c"><?=GetMessage("CL_A_STATUS");?></span><span
										class="checklist-popup-tes-successfully-r"></span><input name="checklist-form-radio" type="radio" value="A" id="A_status" /></span>
								<span
										class="checklist-popup-tes-status"><span
										class="checklist-popup-tes-fails-l"></span><span
										class="checklist-popup-tes-fails-c"><?=GetMessage("CL_F_STATUS");?></span><span
										class="checklist-popup-tes-fails-r"></span><input name="checklist-form-radio" value="F" id="F_status" name="checklist-form-radio" type="radio"  /></span><span
						class="checklist-popup-tes-status"><span
						class="checklist-popup-tes-not-necessarily-l"></span><span
						class="checklist-popup-tes-not-necessarily-c"><?=GetMessage("CL_S_STATUS");?></span><span
						class="checklist-popup-tes-not-necessarily-r"></span><input name="checklist-form-radio" type="radio" value="S" id="S_status" /></span>
				</div>
			</div>
<?

$tabControl->BeginNextTab();
?>
				<div class="checklist-popup-test">
					<div class="checklist-popup-name-test"><?=GetMessage("CL_DESC");?></div>
						<?if($arPoints[$arTestID]["DESC"]):
						?><div class="checklist-popup-test-text">
							<div class="checklist-popup-result-form"><p><?
							echo $arPoints[$arTestID]["DESC"];
						?></p></div></div><?
						else:
							echo '<p>'.GetMessage("CL_EMPTY_DESC").'</p>';
						endif;?>
				</div>
				<div class="checklist-popup-test">
					<div class="checklist-popup-name-test"><?=GetMessage("CL_NOW_TO_TEST_IT");?></div>
						<?if($arPoints[$arTestID]["HOWTO"]):?>
						<div class="checklist-popup-test-text">
							<div class="checklist-popup-result-form checklist-popup-code">
								<?=$arPoints[$arTestID]["HOWTO"];?>
						</div></div>
						<?else:?>
							<?=GetMessage("CL_EMPTY_DESC");?>
						<?endif;?>
				</div>
				<?if($arPoints[$arTestID]["AUTOTEST_DESC"]):?>
				<div class="checklist-popup-test">
					<div class="checklist-popup-name-test"><?=GetMessage("CL_NOW_AUTOTEST_WORK");?></div>
					<div class="checklist-popup-test-text">
						<div class="checklist-popup-result-form checklist-popup-code">
							<?=$arPoints[$arTestID]["AUTOTEST_DESC"]?>
						</div>
					</div>
				</div>
			<?endif;?>
	</div>
	<script>
	var test_is_run = false;
	var testID = "<?=$jsTestID;?>";
	var step = 0;
	var currentStatus ="<?=$arPoints[$arTestID]["STATE"]["STATUS"]?>";
	BX("<?=$arPoints[$arTestID]["STATE"]["STATUS"]?>_status").checked = true;
	BX("show_detail_link").style.display='<?=$display;?>';
	var test_buttons={
		buttons:BX.findChildren(BX('checklist-popup-tes-status'), {className:'checklist-popup-tes-status'}),
		clickable:function(){
			for(var i=0; i<this.buttons.length; i++){
				BX.bind(this.buttons[i], 'click', this.active_button);
			}
		},
		active_button:function(event){
				if (test_is_run == true)
				{
					ShowStatusAlert("bx_autotest_btn",'<?=GetMessageJS("CL_NEED_TO_STOP");?>');
					return;
				}
				for(var i=0;i<test_buttons.buttons.length; i++){
					if(test_buttons.buttons[i]==this){
						BX.addClass(this,'checklist-popup-tes-active');
						BX.findChild(this,{tagName:'input'},false).checked=true;
						SaveStatus(BX.findChild(this,{tagName:'input'},false));
					}
					else{
						BX.removeClass(test_buttons.buttons[i], 'checklist-popup-tes-active');
						BX.findChild(test_buttons.buttons[i],{tagName:'input'},false).checked=false;
					}
				}
			}
		};
		test_buttons.clickable();
		BX.addClass(BX(currentStatus+"_status").parentNode,'checklist-popup-tes-active');
		if (BX('performer_comment_area').innerHTML.length<=0)
		{
			BX('performer_comment_area').style.color="#999";
			BX('performer_comment_area').style.fontWeight="lighter";
			BX('performer_comment_area').innerHTML = '<?=GetMessageJS("CL_NO_COMMENT");?>';
		}

	function ShowDetailComment()
	{
		var content = BX.create("DIV",{
			props:{},
			html:BX("detail_system_comment_"+testID).innerHTML.replace(/\r\n|\r|\n/g,'<br>')
		});
		if(!DetailWindow)
		{

			DetailWindow = new BX.CAdminDialog(
			{
				title: '<?=GetMessageJS("CL_MORE_DETAILS");?>',
				head: "",
				content: content,
				icon: "head-block",
				resizable: true,
				draggable: true,
				height: "400",
				width: "700",
				buttons: [BX.CAdminDialog.btnClose]
			}

			);
		}
		else
		{
			DetailWindow.SetContent(content);
		}

		BX.onCustomEvent(this,"onAfterDetailReportShow",[{reportNode:content, id: testID,parent:DetailWindow}]);
		DetailWindow.Show();
	}

	function ShowStatusAlert(bindElement,text,hide,class_name)
	{
		if (!hide)
			hide = false;
		if (!class_name)
			class_name = "checklist-alert-comment";
		var bx_info = document.createElement('div');
		BX.addClass(bx_info,class_name);
		bx_info.innerHTML = text;
		var bx_alert = BX.PopupWindowManager.create(
			"bx_alert"+Math.random(),
			BX(bindElement),
			{
				autoHide : true,
				lightShadow : true,
				closeIcon:false,
				angle:true,
				offsetLeft:50,
				zIndex:100100
			}
		);
		bx_alert.setContent(bx_info);
		if(hide == true)
		BX(bindElement).onmouseout = function(){bx_alert.close();};
		BX.addCustomEvent(Dialog,"OnWindowClose",function(){if (bx_alert) bx_alert.close();});
		bx_alert.show();
	}

	function ShowPopupDetail(_this)
	{
		ShowStatusAlert(_this.id,'<?=GetMessageJS("CL_MORE_DETAILS_INF");?>',true,"checklist-alert-comment-detail");
	}

	function SaveStatus(_this)
	{
		var status = currentStatus;
		if (_this)
		{
			status = _this.value;
			if (status == currentStatus)
				return;
		}
		if (status == "S" || status == "A")
		{
			if (BX("performer_comment").value.replace(/ /g, '').length < 2)
			{
				BX(currentStatus+"_status").checked = true;
				BX.addClass(BX(currentStatus+"_status").parentNode,'checklist-popup-tes-active');
				if (_this)
				{
					BX.removeClass(BX(_this.value+"_status").parentNode,'checklist-popup-tes-active');
					_this.checked = false;
				}
				ShowStatusAlert("performer_comment_area",'<?=GetMessageJS("CL_EMPTY_COMMENT");?>');
				return;
			}
		}

		currentStatus = status;
		BX(currentStatus+"_status").checked = true;
		ShowWaitWindow();
		Dialog.hideNotify();
		var query_str = "ACTION=update&STATUS="+status+"&TEST_ID="+testID+"&COMMENTS=Y"+"&perfomer_comment="+BX("performer_comment").value+"&lang=<?=LANG;?>";
		if (_this)
			query_str+="&CAN_SHOW_CP_MESSAGE=Y";
		BX.ajax.post("/bitrix/admin/checklist.php?bxpublic=Y&<?=bitrix_sessid_get()?>",query_str,TestResultSimple);
	}

	function StartPointAutoCheck()
	{
		var callback = function(data)
		{
			try
			{
				var json_data=eval("(" +data+")");
				var show_result = false;
				var buttons = BX.findChildren(BX('checklist-popup-tes-status'), {className:'checklist-popup-tes-status'});
				if (json_data.STATUS || stoptest == true)
				{
					if (json_data.STATUS)
					{
						BX("show_detail_link").style.display = "none";
						BX("detail_system_comment_<?=$jsTestID;?>").innerHTML = "";
						currentStatus = json_data.STATUS;
						RefreshCheckList(json_data);
						for(var i=0; i<buttons.length; i++)
						BX.removeClass(buttons[i], 'checklist-popup-tes-active');
						BX.addClass(BX(json_data.STATUS+"_status").parentNode,'checklist-popup-tes-active');
						BX(json_data.STATUS+"_status").checked = true;
						if (json_data.SYSTEM_MESSAGE.PREVIEW)
						{
							BX("system_comment").innerHTML = json_data.SYSTEM_MESSAGE.PREVIEW;
							show_result = true;
						}
						if (json_data.SYSTEM_MESSAGE.DETAIL)
						{

							BX("show_detail_link").style.display = "inline-block";
							ShowPopupDetail(BX("show_detail_link"));
							BX("detail_system_comment_<?=$jsTestID;?>").innerHTML = "<div class=\"checklist-system-textarea\">"+json_data.SYSTEM_MESSAGE.DETAIL.replace(/\r\n|\r|\n/g,'<br>')+"</div>";
							show_result = true;
						}
						if (show_result == true)
							BX("bx_test_result").style.display = "block";
						else
							BX("bx_test_result").style.display = "none";
						BX(json_data.STATUS+"_status").checked = true;

						if (json_data.CAN_CLOSE_PROJECT == "Y")
							ShowCloseProject();
					}
					BX("bx_per_point_done").innerHTML = '<?=GetMessageJS("CL_AUTOTEST_DONE")?>';

					var buttonText = BX.findChild(BX("bx_start_button_detail"), {className:'checklist-button-cont'}, true, false);
					buttonText.innerHTML = '<?=GetMessageJS("CL_AUTOTEST_START");?>';

					step = 0;
					test_is_run = false;
				}
				else if (json_data.IN_PROGRESS == "Y")
				{
					BX("bx_per_point_done").innerHTML = '<?=GetMessageJS("CL_PERCENT_LIVE")?>'+" "+json_data.PERCENT+"%";
					BX.ajax.post("/bitrix/admin/checklist.php","ACTION=update&autotest=Y&bxpublic=Y&TEST_ID="+testID+"&STEP="+(++step)+"&lang=<?=LANG;?>&<?=bitrix_sessid_get()?>",callback);
				}
				else
				{
					loadButton("bx_start_button_detail");
					step = 0;
					test_is_run = false;
				}
			}catch(e){
					loadButton("bx_start_button_detail");
					step = 0;
					test_is_run = false;
					stoptest = false;
				}
		};

		if (test_is_run == true)
		{
			var buttonText = BX.findChild(BX("bx_start_button_detail"), {className:'checklist-button-cont'}, true, false);
			buttonText.innerHTML = '<?=GetMessageJS("CL_END_TEST_PROCCESS");?>';
			stoptest = true;
			return;
		}
		BX("bx_per_point_done").innerHTML = "";
		test_is_run = true;
		stoptest = false;
		buttonText = BX.findChild(BX("bx_start_button_detail"), {className:'checklist-button-cont'}, true, false);
		buttonText.innerHTML = '<?=GetMessageJS("CL_END_TEST");?>';
		BX.ajax.post("/bitrix/admin/checklist.php","ACTION=update&autotest=Y&bxpublic=Y&TEST_ID="+testID+"&STEP="+step+"&lang=<?=LANG;?>&<?=bitrix_sessid_get()?>",callback);
	}

	function Move(action)
	{
		var data = null;
		var testtitle = false;
		if (action == "prev")
			current = prev;
		if (action == "next")
			current = next;
		Dialog.hideNotify();
		ShowWaitWindow();
		BX.ajax.post(
			"/bitrix/admin/checklist_detail.php?TEST_ID="+arStates["POINTS"][current].TEST_ID+"&lang=<?=LANG;?>&bxpublic=Y&<?=bitrix_sessid_get()?>",
			data,
			function(data)
			{
				ReCalc(current);
				testtitle = arStates["POINTS"][current].NAME+" - "+arStates["POINTS"][current].TEST_ID;
				if (arStates["POINTS"][current].IS_REQUIRE == "Y")
					testtitle = testtitle+" ("+'<?=GetMessageJS("CL_TEST_IS_REQUIRE");?>'+")";

				Dialog.SetTitle(testtitle);
				Dialog.SetContent(data);
				CloseWaitWindow();
			}
		);
	}

	function CopyText(_this,toDiv)
	{
		var text='';
		if (_this.value.length>0)
		{
			text = jsUtils.htmlspecialchars(_this.value);
			text=text.replace(/\r\n|\r|\n/g,'<br>');
			toDiv.style.color="black";
			toDiv.style.fontWeight="normal";
		}
		else
		{
			toDiv.style.color="#999";
			toDiv.style.fontWeight="lighter";
			text='<?=GetMessageJS("CL_NO_COMMENT");?>';
		}

		toDiv.innerHTML = text;
	}
	</script>
	<?
	$tabControl->End();
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");?>

