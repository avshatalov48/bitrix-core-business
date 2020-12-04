<?php
// http://www.i18nguy.com/unicode/turkish-i18n.html

function stemming_letter_tr()
{
	return "abcçdefgðhýijklmnoöprsþtuüvyz"."ABCÇDEFGÐHIIJKLMNOÖPRSÞTUÜVYZ"."âîûÝiIý";
}

function stemming_upper_tr($sText)
{
	return str_replace(array("Ý"), array("I"), ToUpper($sText));
}
