<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (is_string($_REQUEST["backurl"]) && mb_strpos($_REQUEST["backurl"], "/") === 0)
{
	LocalRedirect($_REQUEST["backurl"]);
}

LocalRedirect(SITE_DIR);

$APPLICATION->SetTitle("Authorization");
?>
<p>You have successfully registered and authorized.</p>

<p>Use the administration toolbar on top of the screen for quick access to the site structure and content management tools. The top toolbar buttons are different for different site sections: some commands run static content operations while other manage dynamic content (news, catalog, galleries etc.).</p>

<p><a href="<?=SITE_DIR?>">Back to home page</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>