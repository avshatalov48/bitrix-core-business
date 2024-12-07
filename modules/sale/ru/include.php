<?
function Number2Word_Rus($source, $IS_MONEY = "Y", $currency = "")
{
	$result = '';

	$IS_MONEY = ((string)($IS_MONEY) == 'Y' ? 'Y' : 'N');
	$currency = (string)$currency;
	if ($currency == '' || $currency == 'RUR')
		$currency = 'RUB';
	else if ($currency == 'BYR')
		$currency = 'BYN';
	if ($IS_MONEY == 'Y')
	{
		if ($currency != 'RUB' && $currency != 'UAH' && $currency != 'KZT' && $currency != 'BYN')
			return $result;
	}

	$arNumericLang = array(
		"RUB" => array(
			"zero" => "ноль",
			"1c" => "сто ",
			"2c" => "двести ",
			"3c" => "триста ",
			"4c" => "четыреста ",
			"5c" => "пятьсот ",
			"6c" => "шестьсот ",
			"7c" => "семьсот ",
			"8c" => "восемьсот ",
			"9c" => "девятьсот ",
			"1d0e" => "десять ",
			"1d1e" => "одиннадцать ",
			"1d2e" => "двенадцать ",
			"1d3e" => "тринадцать ",
			"1d4e" => "четырнадцать ",
			"1d5e" => "пятнадцать ",
			"1d6e" => "шестнадцать ",
			"1d7e" => "семнадцать ",
			"1d8e" => "восемнадцать ",
			"1d9e" => "девятнадцать ",
			"2d" => "двадцать ",
			"3d" => "тридцать ",
			"4d" => "сорок ",
			"5d" => "пятьдесят ",
			"6d" => "шестьдесят ",
			"7d" => "семьдесят ",
			"8d" => "восемьдесят ",
			"9d" => "девяносто ",
			"5e" => "пять ",
			"6e" => "шесть ",
			"7e" => "семь ",
			"8e" => "восемь ",
			"9e" => "девять ",
			"1et" => "одна тысяча ",
			"2et" => "две тысячи ",
			"3et" => "три тысячи ",
			"4et" => "четыре тысячи ",
			"1em" => "один миллион ",
			"2em" => "два миллиона ",
			"3em" => "три миллиона ",
			"4em" => "четыре миллиона ",
			"1eb" => "один миллиард ",
			"2eb" => "два миллиарда ",
			"3eb" => "три миллиарда ",
			"4eb" => "четыре миллиарда ",
			"1e." => "один рубль ",
			"2e." => "два рубля ",
			"3e." => "три рубля ",
			"4e." => "четыре рубля ",
			"1e" => "один ",
			"2e" => "два ",
			"3e" => "три ",
			"4e" => "четыре ",
			"11k" => "11 копеек",
			"12k" => "12 копеек",
			"13k" => "13 копеек",
			"14k" => "14 копеек",
			"1k" => "1 копейка",
			"2k" => "2 копейки",
			"3k" => "3 копейки",
			"4k" => "4 копейки",
			"." => "рублей ",
			"t" => "тысяч ",
			"m" => "миллионов ",
			"b" => "миллиардов ",
			"k" => " копеек",
		),
		"BYN" => array(
			"zero" => "ноль",
			"1c" => "сто ",
			"2c" => "двести ",
			"3c" => "триста ",
			"4c" => "четыреста ",
			"5c" => "пятьсот ",
			"6c" => "шестьсот ",
			"7c" => "семьсот ",
			"8c" => "восемьсот ",
			"9c" => "девятьсот ",
			"1d0e" => "десять ",
			"1d1e" => "одиннадцать ",
			"1d2e" => "двенадцать ",
			"1d3e" => "тринадцать ",
			"1d4e" => "четырнадцать ",
			"1d5e" => "пятнадцать ",
			"1d6e" => "шестнадцать ",
			"1d7e" => "семнадцать ",
			"1d8e" => "восемнадцать ",
			"1d9e" => "девятнадцать ",
			"2d" => "двадцать ",
			"3d" => "тридцать ",
			"4d" => "сорок ",
			"5d" => "пятьдесят ",
			"6d" => "шестьдесят ",
			"7d" => "семьдесят ",
			"8d" => "восемьдесят ",
			"9d" => "девяносто ",
			"5e" => "пять ",
			"6e" => "шесть ",
			"7e" => "семь ",
			"8e" => "восемь ",
			"9e" => "девять ",
			"1et" => "одна тысяча ",
			"2et" => "две тысячи ",
			"3et" => "три тысячи ",
			"4et" => "четыре тысячи ",
			"1em" => "один миллион ",
			"2em" => "два миллиона ",
			"3em" => "три миллиона ",
			"4em" => "четыре миллиона ",
			"1eb" => "один миллиард ",
			"2eb" => "два миллиарда ",
			"3eb" => "три миллиарда ",
			"4eb" => "четыре миллиарда ",
			"1e." => "один белорусский рубль ",
			"2e." => "два белорусских рубля ",
			"3e." => "три белорусских рубля ",
			"4e." => "четыре белорусских рубля ",
			"1e" => "один ",
			"2e" => "два ",
			"3e" => "три ",
			"4e" => "четыре ",
			"11k" => "11 копеек",
			"12k" => "12 копеек",
			"13k" => "13 копеек",
			"14k" => "14 копеек",
			"1k" => "1 копейка",
			"2k" => "2 копейки",
			"3k" => "3 копейки",
			"4k" => "4 копейки",
			"." => "белорусских рублей ",
			"t" => "тысяч ",
			"m" => "миллионов ",
			"b" => "миллиардов ",
			"k" => " копеек",
		),
		"UAH" => array(
			"zero" => "нyль",
			"1c" => "сто ",
			"2c" => "двісті ",
			"3c" => "триста ",
			"4c" => "чотириста ",
			"5c" => "п'ятсот ",
			"6c" => "шістсот ",
			"7c" => "сімсот ",
			"8c" => "вісімсот ",
			"9c" => "дев'ятсот ",
			"1d0e" => "десять ",
			"1d1e" => "одинадцять ",
			"1d2e" => "дванадцять ",
			"1d3e" => "тринадцять ",
			"1d4e" => "чотирнадцять ",
			"1d5e" => "п'ятнадцять ",
			"1d6e" => "шістнадцять ",
			"1d7e" => "сімнадцять ",
			"1d8e" => "вісімнадцять ",
			"1d9e" => "дев'ятнадцять ",
			"2d" => "двадцять ",
			"3d" => "тридцять ",
			"4d" => "сорок ",
			"5d" => "п'ятдесят ",
			"6d" => "шістдесят ",
			"7d" => "сімдесят ",
			"8d" => "вісімдесят ",
			"9d" => "дев'яносто ",
			"5e" => "п'ять ",
			"6e" => "шість ",
			"7e" => "сім ",
			"8e" => "вісім ",
			"9e" => "дев'ять ",
			"1e." => "одна гривня ",
			"2e." => "дві гривні ",
			"3e." => "три гривні ",
			"4e." => "чотири гривні ",
			"1e" => "одна ",
			"2e" => "дві ",
			"3e" => "три ",
			"4e" => "чотири ",
			"1et" => "одна тисяча ",
			"2et" => "дві тисячі ",
			"3et" => "три тисячі ",
			"4et" => "чотири тисячі ",
			"1em" => "один мільйон ",
			"2em" => "два мільйона ",
			"3em" => "три мільйона ",
			"4em" => "чотири мільйона ",
			"1eb" => "один мільярд ",
			"2eb" => "два мільярда ",
			"3eb" => "три мільярда ",
			"4eb" => "чотири мільярда ",
			"11k" => "11 копійок",
			"12k" => "12 копійок",
			"13k" => "13 копійок",
			"14k" => "14 копійок",
			"1k" => "1 копійка",
			"2k" => "2 копійки",
			"3k" => "3 копійки",
			"4k" => "4 копійки",
			"." => "гривень ",
			"t" => "тисяч ",
			"m" => "мільйонів ",
			"b" => "мільярдів ",
			"k" => " копійок",
		),
		"KZT" => array(
			"zero" => "ноль",
			"1c" => "сто ",
			"2c" => "двести ",
			"3c" => "триста ",
			"4c" => "четыреста ",
			"5c" => "пятьсот ",
			"6c" => "шестьсот ",
			"7c" => "семьсот ",
			"8c" => "восемьсот ",
			"9c" => "девятьсот ",
			"1d0e" => "десять ",
			"1d1e" => "одиннадцать ",
			"1d2e" => "двенадцать ",
			"1d3e" => "тринадцать ",
			"1d4e" => "четырнадцать ",
			"1d5e" => "пятнадцать ",
			"1d6e" => "шестнадцать ",
			"1d7e" => "семнадцать ",
			"1d8e" => "восемнадцать ",
			"1d9e" => "девятнадцать ",
			"2d" => "двадцать ",
			"3d" => "тридцать ",
			"4d" => "сорок ",
			"5d" => "пятьдесят ",
			"6d" => "шестьдесят ",
			"7d" => "семьдесят ",
			"8d" => "восемьдесят ",
			"9d" => "девяносто ",
			"5e" => "пять ",
			"6e" => "шесть ",
			"7e" => "семь ",
			"8e" => "восемь ",
			"9e" => "девять ",
			"1et" => "одна тысяча ",
			"2et" => "две тысячи ",
			"3et" => "три тысячи ",
			"4et" => "четыре тысячи ",
			"1em" => "один миллион ",
			"2em" => "два миллиона ",
			"3em" => "три миллиона ",
			"4em" => "четыре миллиона ",
			"1eb" => "один миллиард ",
			"2eb" => "два миллиарда ",
			"3eb" => "три миллиарда ",
			"4eb" => "четыре миллиарда ",
			"1e." => "один тенге ",
			"2e." => "два тенге ",
			"3e." => "три тенге ",
			"4e." => "четыре тенге ",
			"1e" => "один ",
			"2e" => "два ",
			"3e" => "три ",
			"4e" => "четыре ",
			"11k" => "11 тиын",
			"12k" => "12 тиын",
			"13k" => "13 тиын",
			"14k" => "14 тиын",
			"1k" => "1 тиын",
			"2k" => "2 тиын",
			"3k" => "3 тиын",
			"4k" => "4 тиын",
			"." => "тенге ",
			"t" => "тысяч ",
			"m" => "миллионов ",
			"b" => "миллиардов ",
			"k" => " тиын",
		)
	);

	// k - penny
	if ($IS_MONEY == "Y")
	{
		$source = (string)((float)$source);
		$dotpos = mb_strpos($source, ".");
		if ($dotpos === false)
		{
			$ipart = $source;
			$fpart = '';
		}
		else
		{
			$ipart = mb_substr($source, 0, $dotpos);
			$fpart = mb_substr($source, $dotpos + 1);
			if ($fpart === false)
				$fpart = '';
		}
		;
		if (mb_strlen($fpart) > 2)
		{
			$fpart = mb_substr($fpart, 0, 2);
			if ($fpart === false)
				$fpart = '';
		}
		$fillLen = 2 - mb_strlen($fpart);
		if ($fillLen > 0)
			$fpart .= str_repeat('0', $fillLen);
		unset($fillLen);
	}
	else
	{
		$ipart = (string)((int)$source);
		$fpart = '';
	}

	if (is_string($ipart))
	{
		$ipart = preg_replace('/^[0]+/', '', $ipart);
	}

	$ipart1 = strrev($ipart);
	$ipart1Len = mb_strlen($ipart1);
	$ipart = "";
	$i = 0;
	while ($i < $ipart1Len)
	{
		$ipart_tmp = mb_substr($ipart1, $i, 1);
		// t - thousands; m - millions; b - billions;
		// e - units; d - scores; c - hundreds;
		if ($i % 3 == 0)
		{
			if ($i==0) $ipart_tmp .= "e";
			elseif ($i==3) $ipart_tmp .= "et";
			elseif ($i==6) $ipart_tmp .= "em";
			elseif ($i==9) $ipart_tmp .= "eb";
			else $ipart_tmp .= "x";
		}
		elseif ($i % 3 == 1) $ipart_tmp .= "d";
		elseif ($i % 3 == 2) $ipart_tmp .= "c";
		$ipart = $ipart_tmp.$ipart;
		$i++;
	}

	if ($IS_MONEY == "Y")
	{
		$result = $ipart.".".$fpart."k";
	}
	else
	{
		$result = $ipart;
		if ($result == '')
			$result = $arNumericLang[$currency]['zero'];
	}

	if (mb_substr($result, 0, 1) == ".")
		$result = $arNumericLang[$currency]['zero']." ".$result;

	$result = str_replace("0c0d0et", "", $result);
	$result = str_replace("0c0d0em", "", $result);
	$result = str_replace("0c0d0eb", "", $result);

	$result = str_replace("0c", "", $result);
	$result = str_replace("1c", $arNumericLang[$currency]["1c"], $result);
	$result = str_replace("2c", $arNumericLang[$currency]["2c"], $result);
	$result = str_replace("3c", $arNumericLang[$currency]["3c"], $result);
	$result = str_replace("4c", $arNumericLang[$currency]["4c"], $result);
	$result = str_replace("5c", $arNumericLang[$currency]["5c"], $result);
	$result = str_replace("6c", $arNumericLang[$currency]["6c"], $result);
	$result = str_replace("7c", $arNumericLang[$currency]["7c"], $result);
	$result = str_replace("8c", $arNumericLang[$currency]["8c"], $result);
	$result = str_replace("9c", $arNumericLang[$currency]["9c"], $result);

	$result = str_replace("1d0e", $arNumericLang[$currency]["1d0e"], $result);
	$result = str_replace("1d1e", $arNumericLang[$currency]["1d1e"], $result);
	$result = str_replace("1d2e", $arNumericLang[$currency]["1d2e"], $result);
	$result = str_replace("1d3e", $arNumericLang[$currency]["1d3e"], $result);
	$result = str_replace("1d4e", $arNumericLang[$currency]["1d4e"], $result);
	$result = str_replace("1d5e", $arNumericLang[$currency]["1d5e"], $result);
	$result = str_replace("1d6e", $arNumericLang[$currency]["1d6e"], $result);
	$result = str_replace("1d7e", $arNumericLang[$currency]["1d7e"], $result);
	$result = str_replace("1d8e", $arNumericLang[$currency]["1d8e"], $result);
	$result = str_replace("1d9e", $arNumericLang[$currency]["1d9e"], $result);

	$result = str_replace("0d", "", $result);
	$result = str_replace("2d", $arNumericLang[$currency]["2d"], $result);
	$result = str_replace("3d", $arNumericLang[$currency]["3d"], $result);
	$result = str_replace("4d", $arNumericLang[$currency]["4d"], $result);
	$result = str_replace("5d", $arNumericLang[$currency]["5d"], $result);
	$result = str_replace("6d", $arNumericLang[$currency]["6d"], $result);
	$result = str_replace("7d", $arNumericLang[$currency]["7d"], $result);
	$result = str_replace("8d", $arNumericLang[$currency]["8d"], $result);
	$result = str_replace("9d", $arNumericLang[$currency]["9d"], $result);

	$result = str_replace("0e", "", $result);
	$result = str_replace("5e", $arNumericLang[$currency]["5e"], $result);
	$result = str_replace("6e", $arNumericLang[$currency]["6e"], $result);
	$result = str_replace("7e", $arNumericLang[$currency]["7e"], $result);
	$result = str_replace("8e", $arNumericLang[$currency]["8e"], $result);
	$result = str_replace("9e", $arNumericLang[$currency]["9e"], $result);

	$result = str_replace("1et", $arNumericLang[$currency]["1et"], $result);
	$result = str_replace("2et", $arNumericLang[$currency]["2et"], $result);
	$result = str_replace("3et", $arNumericLang[$currency]["3et"], $result);
	$result = str_replace("4et", $arNumericLang[$currency]["4et"], $result);
	$result = str_replace("1em", $arNumericLang[$currency]["1em"], $result);
	$result = str_replace("2em", $arNumericLang[$currency]["2em"], $result);
	$result = str_replace("3em", $arNumericLang[$currency]["3em"], $result);
	$result = str_replace("4em", $arNumericLang[$currency]["4em"], $result);
	$result = str_replace("1eb", $arNumericLang[$currency]["1eb"], $result);
	$result = str_replace("2eb", $arNumericLang[$currency]["2eb"], $result);
	$result = str_replace("3eb", $arNumericLang[$currency]["3eb"], $result);
	$result = str_replace("4eb", $arNumericLang[$currency]["4eb"], $result);

	if ($IS_MONEY == "Y")
	{
		$result = str_replace("1e.", $arNumericLang[$currency]["1e."], $result);
		$result = str_replace("2e.", $arNumericLang[$currency]["2e."], $result);
		$result = str_replace("3e.", $arNumericLang[$currency]["3e."], $result);
		$result = str_replace("4e.", $arNumericLang[$currency]["4e."], $result);
	}
	else
	{
		$result = str_replace("1e", $arNumericLang[$currency]["1e"], $result);
		$result = str_replace("2e", $arNumericLang[$currency]["2e"], $result);
		$result = str_replace("3e", $arNumericLang[$currency]["3e"], $result);
		$result = str_replace("4e", $arNumericLang[$currency]["4e"], $result);
	}

	if ($IS_MONEY == "Y")
	{
		$result = str_replace("11k", $arNumericLang[$currency]["11k"], $result);
		$result = str_replace("12k", $arNumericLang[$currency]["12k"], $result);
		$result = str_replace("13k", $arNumericLang[$currency]["13k"], $result);
		$result = str_replace("14k", $arNumericLang[$currency]["14k"], $result);
		$result = str_replace("1k", $arNumericLang[$currency]["1k"], $result);
		$result = str_replace("2k", $arNumericLang[$currency]["2k"], $result);
		$result = str_replace("3k", $arNumericLang[$currency]["3k"], $result);
		$result = str_replace("4k", $arNumericLang[$currency]["4k"], $result);
	}

	if ($IS_MONEY == "Y")
	{
		if (mb_substr($result, 0, 1) == ".")
			$result = $arNumericLang[$currency]['zero']." ".$result;

		$result = str_replace(".", $arNumericLang[$currency]["."], $result);
	}

	$result = str_replace("t", $arNumericLang[$currency]["t"], $result);
	$result = str_replace("m", $arNumericLang[$currency]["m"], $result);
	$result = str_replace("b", $arNumericLang[$currency]["b"], $result);

	if ($IS_MONEY == "Y")
		$result = str_replace("k", $arNumericLang[$currency]["k"], $result);

	return (mb_strtoupper(mb_substr($result, 0, 1)).mb_substr($result, 1));
}