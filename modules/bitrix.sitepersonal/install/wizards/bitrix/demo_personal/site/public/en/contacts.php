<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Contact");
?> 
<?$APPLICATION->IncludeComponent("bitrix:main.feedback", "personal", Array(
	"USE_CAPTCHA" => "Y",	
	"OK_TEXT" => "Thank you! Your message has been submitted.",	
	"EMAIL_TO" => "",	
	"REQUIRED_FIELDS" => array(	
		0 => "NAME",
		1 => "EMAIL",
		2 => "MESSAGE",
	),
	"EVENT_MESSAGE_ID" => "",	
	),
	false
);?>

<h1>Contact Information</h1>
 
<div class="hr"></div>
 
<ul> 	 
  <li>E-mail: <a href="mailto:19Victoria84@gmail.com">19Victoria84@gmail.com</a>.</li>
 
  <li>Skype: Morrison_Victoria.</li>
 </ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>