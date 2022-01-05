<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (is_string($_REQUEST["backurl"]) && mb_strpos($_REQUEST["backurl"], "/") === 0)
{
	LocalRedirect($_REQUEST["backurl"]);
}

$APPLICATION->SetTitle("Вход на сайт");
?>
<p class="notetext">Вы зарегистрированы и успешно авторизовались.</p>

<p><a href="/">Вернуться на главную страницу</a></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>