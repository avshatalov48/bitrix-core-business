<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

global $USER;

if ( ! $USER->IsAdmin() )
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/learning/classes/general/legacy/converter_to_11.5.0.php');
IncludeModuleLangFile(__FILE__);

if (isset($_REQUEST['learning_process']) && ($_REQUEST['learning_process'] === 'Y'))
{
	require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php');

	$processedSummary    = 0;
	$processedAtLastStep = false;

	if (isset($_REQUEST['processedSummary']))
		$processedSummary = (int) $_REQUEST['processedSummary'];

	try
	{
		$rc = CLearnInstall201203ConvertDB::run();

		if ($rc <> '')
			throw new Exception($rc);

		$processedAtLastStep = (int) CLearnInstall201203ConvertDB::$items_processed;
	}
	catch (Exception $e)
	{
		;	// nothing to do here
	}

	?>
	<script>
		CloseWaitWindow();
	</script>
	<?php

	if (is_int($processedAtLastStep))
		$processedSummary += $processedAtLastStep;

	if ($processedAtLastStep === false)
	{
		CAdminMessage::ShowMessage(
			array(
				'MESSAGE' => GetMessage('LEARNING_CONVERT_FAILED'),
				'DETAILS' => GetMessage('LEARNING_PROCESSED_SUMMARY') 
					. ' <b>' . $processedSummary . '</b>'
					. '<div id="learning_convert_finish"></div>',
				'HTML'    => true,
				'TYPE'    => 'ERROR'
			)
		);

		?>
		<script>
			StopConvert();
		</script>
		<?php
	}
	elseif (CLearnInstall201203ConvertDB::_IsAlreadyConverted())
	{
		CAdminMessage::ShowMessage(
			array(
				'MESSAGE' => GetMessage('LEARNING_CONVERT_COMPLETE'),
				'DETAILS' => GetMessage('LEARNING_PROCESSED_SUMMARY') 
					. ' <b>' . $processedSummary . '</b>'
					. '<div id="learning_convert_finish"></div>',
				'HTML'    => true,
				'TYPE'    => 'OK'
			)
		);

		CAdminNotify::DeleteByTag('learning_convert_11_5_0');

		?>
		<script>
			EndConvert();
		</script>
		<?php
	}
	else
	{
		CAdminMessage::ShowMessage(
			array(
				'MESSAGE' => GetMessage('LEARNING_CONVERT_IN_PROGRESS'),
				'DETAILS' => GetMessage('LEARNING_PROCESSED_SUMMARY') 
					. ' <b>' . $processedSummary . '</b>',
				'HTML'    => true,
				'TYPE'    => 'OK'
			)
		);

		?>
		<script>
			DoNext(<?php echo $processedSummary; ?>);
		</script>
		<?php
	}

	require($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin_js.php');
}
else
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_CONVERT_TITLE'));

	$aTabs = array(
		array(
			'DIV'   => 'edit1', 
			'TAB'   => GetMessage('LEARNING_CONVERT_TAB'), 
			'ICON'  => 'main_user_edit', 
			'TITLE' => GetMessage('LEARNING_CONVERT_TAB_TITLE')
		)
	);

	$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

	?>
	<script>
	var learning_stop;

	function StartConvert(maxMessage)
	{
		learning_stop = false;
		document.getElementById('convert_result_div').innerHTML = '';
		document.getElementById('stop_button').disabled         = false;
		document.getElementById('start_button').disabled        = true;
		DoNext(0, 0, 100);
	}

	function StopConvert()
	{
		learning_stop = true;
		document.getElementById('stop_button').disabled  = true;
		document.getElementById('start_button').disabled = false;
	}

	function EndConvert()
	{
		learning_stop = true;
		document.getElementById('stop_button').disabled  = true;
		document.getElementById('start_button').disabled = true;
	}

	function DoNext(processedSummary)
	{
		var queryString = 'learning_process=Y&lang=<?php echo htmlspecialcharsbx(LANG); ?>';

		queryString += '&<?php echo bitrix_sessid_get(); ?>';
		queryString += '&processedSummary=' + parseInt(processedSummary);

		if ( ! learning_stop )
		{
			ShowWaitWindow();
			BX.ajax.post(
				'learning_convert.php?' + queryString,
				{},
				function(result)
				{
					document.getElementById('convert_result_div').innerHTML = result;
					if (BX('learning_convert_finish') != null)
					{
						CloseWaitWindow();
						EndConvert();
					}
				}
			);
		}

		return false;
	}
	</script>

	<div id='convert_result_div'>
	</div>

	<form method='POST' action='<?php 
		echo $APPLICATION->GetCurPage(); ?>?lang=<?php 
		echo htmlspecialcharsbx(LANG); 
	?>'>
		<?php
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		$tabControl->Buttons();
		?>

		<input type='button' id='start_button' 
			value='<?php echo GetMessage('LEARNING_CONVERT_START_BUTTON')?>' 
			onclick='StartConvert();');>
		<input type='button' id='stop_button' disabled="disabled" 
			value='<?php echo GetMessage('LEARNING_CONVERT_STOP_BUTTON')?>' 
			onclick='StopConvert();'>

		<?php
		$tabControl->End();
		?>
	</form>

	<script>
		//StartConvert();
	</script>

	<?php
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
}
