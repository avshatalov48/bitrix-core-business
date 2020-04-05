<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Обратная связь");
?> 
<?$APPLICATION->IncludeComponent("bitrix:main.feedback", "personal", Array(
	"USE_CAPTCHA" => "Y",	// Использовать защиту от автоматических сообщений (CAPTCHA) для неавторизованных пользователей
	"OK_TEXT" => "Спасибо, ваше сообщение отправлено.",	// Сообщение, выводимое пользователю после отправки
	"EMAIL_TO" => "",	// E-mail, на который будет отправлено письмо
	"REQUIRED_FIELDS" => array(	// Обязательные поля для заполнения
		0 => "NAME",
		1 => "EMAIL",
		2 => "MESSAGE",
	),
	"EVENT_MESSAGE_ID" => "",	// Почтовые шаблоны для отправки письма
	),
	false
);?>

<h1>Контактная информация</h1>
 
<div class="hr"></div>
 
<ul> 	 
  <li>E-mail: <a href="mailto:19Victoria84@gmail.com">19Victoria84@gmail.com</a>.</li>
 
  <li>Skype: Fadeeva_Victoria.</li>
 </ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>