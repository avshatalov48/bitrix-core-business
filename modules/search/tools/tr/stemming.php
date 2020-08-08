<?php
// http://www.i18nguy.com/unicode/turkish-i18n.html

function stemming_letter_tr()
{
	return "abcçdefgğhıijklmnoöprsştuüvyz"."ABCÇDEFGĞHIIJKLMNOÖPRSŞTUÜVYZ"."âîûİiIı";
}

function stemming_upper_tr($sText)
{
	return str_replace(array("İ"), array("I"), ToUpper($sText));
}
