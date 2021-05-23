<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$component = $this->__component;
//01*
//Для скрытия параметров от злых людей сохраним их в сессии,
//а в публичку отдадим ключ.
//02*
//Эти параметры (пока один) так и так доступны через URL и
//защищать его нет особого смысла (только сессия распухнет)
$arSessionParams = array(
	"PAGE_PARAMS" => array("ELEMENT_ID"),
);
//03*
//Пробегаем по параметрам чщательно складывая их в хранилище
foreach($arParams as $k=>$v)
	if(strncmp("~", $k, 1) && !in_array($k, $arSessionParams["PAGE_PARAMS"]))
		$arSessionParams[$k] = $v;
//04*
//Эти "параметры" нам понадобятся для правильного подключения компонента в AJAX вызове
$arSessionParams["COMPONENT_NAME"] = $component->GetName();
$arSessionParams["TEMPLATE_NAME"] = $component->GetTemplateName();
if($parent = $component->GetParent())
{
	$arSessionParams["PARENT_NAME"] = $parent->GetName();
	$arSessionParams["PARENT_TEMPLATE_NAME"] = $parent->GetTemplateName();
	$arSessionParams["PARENT_TEMPLATE_PAGE"] = $parent->GetTemplatePage();
}
//05*
//а вот и ключ!
$idSessionParams = md5(serialize($arSessionParams));

//06*
//Модифицируем arResult компонента.
//Эти данные затем будут извлекаться из кеша
//И записываться в сессию
$component->arResult["AJAX"] = array(
	"SESSION_KEY" => $idSessionParams,
	"SESSION_PARAMS" => $arSessionParams,
);

//07*
//Эта переменная для использования в шаблоне
$arResult["~AJAX_PARAMS"] = array(
	"SESSION_PARAMS" => $idSessionParams,
	"PAGE_PARAMS" => array(
		"ELEMENT_ID" => $arParams["ELEMENT_ID"],
	),
	"sessid" => bitrix_sessid(),
	"AJAX_CALL" => "Y",
);
//08*
//Она будет прозрачно передана в аяксовый пост
$arResult["AJAX_PARAMS"] = CUtil::PhpToJSObject($arResult["~AJAX_PARAMS"]);
//09*
//Продолжение экскурсии в файле template.php
?>
