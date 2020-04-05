<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (is_string($_REQUEST["backurl"]) && strpos($_REQUEST["backurl"], "/") === 0)
{
	LocalRedirect($_REQUEST["backurl"]);
}

$APPLICATION->SetTitle("Authorization");
?>
<p>You have successfully registered and authorized.</p>

<p><a href="<?=SITE_DIR?>">Back to home page</a></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>