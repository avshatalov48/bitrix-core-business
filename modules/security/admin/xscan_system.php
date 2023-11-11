<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm('');
}

IncludeModuleLangFile(__FILE__);

if (function_exists('mb_internal_encoding'))
{
	mb_internal_encoding('ISO-8859-1');
}

$strError = '';
$file = '';

$APPLICATION->SetTitle(GetMessage("BITRIX_XSCAN_SYSTEM"));
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");

?>

<div>
	<?= GetMessage("BITRIX_XSCAN_SYSTEM_INFO") ?>
</div>

<style>
    .xscan-code {
        background-color: #fff;
        padding: 10px;
        max-width: 1200px;
        overflow-x: auto;
    }
</style>

<?php

function exec_enabled()
{
	$disabled = explode(',', ini_get('disable_functions'));
	return !in_array('exec', $disabled);
}

if (exec_enabled())
{
	$output = null;
	$retval = null;

	exec('whoami', $output, $retval);

	if ($retval === 0)
	{
		echo "<h4>&gt; whoami</h4>";
		$output = htmlspecialcharsbx(implode("\n", $output));
		echo "<pre class=\"xscan-code\">$output</pre>";
	}

	$output = null;
	$retval = null;
	exec('ps ux -u `whoami`', $output, $retval);

	if ($retval === 0)
	{
		echo "<h4>&gt; ps ux -u `whoami`</h4>";
		$output = htmlspecialcharsbx(implode("\n", $output));
		echo "<pre class=\"xscan-code\">$output</pre>";
	}

	$output = null;
	$retval = null;
	exec('crontab -l', $output, $retval);
	if ($retval === 0)
	{
		echo "<h4>&gt; crontab -l</h4>";
		$output = htmlspecialcharsbx(implode("\n", $output));
		echo "<pre class=\"xscan-code\">$output</pre>";
	}
	else
	{
		echo "<h4>&gt; crontab -l</h4>";
		echo "<pre class=\"xscan-code\">no corntab</pre>";
	}

	$output = null;
	$retval = null;
	exec('last -i `whoami`', $output, $retval);

	if ($retval === 0)
	{
		echo "<h4>&gt; last -i `whoami`</h4>";
		$output = htmlspecialcharsbx(implode("\n", $output));
		echo "<pre class=\"xscan-code\">$output</pre>";
	}

	$output = null;
	$retval = null;
	exec('cat ~/.ssh/authorized_keys', $output, $retval);

	if ($retval === 0)
	{
		echo "<h4>&gt; cat ~/.ssh/authorized_keys</h4>";
		$output = htmlspecialcharsbx(implode("\n", $output));
		echo "<pre class=\"xscan-code\">$output</pre>";

		$output = null;
		$retval = null;
		exec('stat ~/.ssh/authorized_keys', $output, $retval);

		if ($retval === 0)
		{
			echo "<h4>&gt; stat ~/.ssh/authorized_keys</h4>";
			$output = htmlspecialcharsbx(implode("\n", $output));
			echo "<pre class=\"xscan-code\">$output</pre>";
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
