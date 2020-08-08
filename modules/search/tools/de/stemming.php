<?php

function stemming_letter_de()
{
	return "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNMÄÖÜäöüß";
}

function stemming_stop_de($sWord)
{
	if(mb_strlen($sWord) < 2)
		return false;
	static $stop_list = false;
	if(!$stop_list)
	{
		$stop_list = array (
			"QUOTE"=>0, "HTTP"=>0, "WWW"=>0, "RU"=>0, "IMG"=>0, "GIF"=>0, "aber"=>0,
			"alle"=>0, "allem"=>0, "allen"=>0, "aller"=>0, "alles"=>0,
			"als"=>0, "also"=>0, "am"=>0, "an"=>0,
			"ander"=>0, "andere"=>0, "anderem"=>0, "anderen"=>0, "anderer"=>0,
			"anderes"=>0, "anderm"=>0, "andern"=>0, "anderr"=>0, "anders"=>0,
			"auch"=>0, "auf"=>0, "aus"=>0, "bei"=>0, "bin"=>0,
			"bis"=>0, "bist"=>0, "da"=>0, "damit"=>0, "dann"=>0,
			"der"=>0, "den"=>0, "des"=>0, "dem"=>0, "die"=>0, "das"=>0,
			"daß"=>0,
			"derselbe"=>0, "derselben"=>0, "denselben"=>0, "desselben"=>0, "demselben"=>0,
			"dieselbe"=>0, "dieselben"=>0, "dasselbe"=>0,
			"dazu"=>0,
			"dein"=>0, "deine"=>0, "deinem"=>0, "deinen"=>0, "deiner"=>0, "deines"=>0,
			"denn"=>0,
			"derer"=>0, "dessen"=>0,
			"dich"=>0, "dir"=>0, "du"=>0,
			"dies"=>0, "diese"=>0, "diesem"=>0, "diesen"=>0, "dieser"=>0, "dieses"=>0,
			"doch"=>0, "dort"=>0,
			"durch"=>0,
			"ein"=>0, "eine"=>0, "einem"=>0, "einen"=>0, "einer"=>0, "eines"=>0,
			"einig"=>0, "einige"=>0, "einigem"=>0, "einigen"=>0, "einiger"=>0, "einiges"=>0,
			"einmal"=>0,
			"er"=>0, "ihn"=>0, "ihm"=>0,
			"es"=>0, "etwas"=>0,
			"euer"=>0, "eure"=>0, "eurem"=>0, "euren"=>0, "eurer"=>0, "eures"=>0,
			"für"=>0, "gegen"=>0, "gewesen"=>0, "hab"=>0, "habe"=>0,
			"haben"=>0, "hat"=>0, "hatte"=>0, "hatten"=>0, "hier"=>0,
			"hin"=>0, "hinter"=>0,
			"ich"=>0, "mich"=>0, "mir"=>0,
			"ihr"=>0, "ihre"=>0, "ihrem"=>0, "ihren"=>0, "ihrer"=>0, "ihres"=>0, "euch"=>0,
			"im"=>0, "in"=>0, "indem"=>0, "ins"=>0, "ist"=>0,
			"jede"=>0, "jedem"=>0, "jeden"=>0, "jeder"=>0, "jedes"=>0,
			"jene"=>0, "jenem"=>0, "jenen"=>0, "jener"=>0, "jenes"=>0,
			"jetzt"=>0, "kann"=>0,
			"kein"=>0, "keine"=>0, "keinem"=>0, "keinen"=>0, "keiner"=>0, "keines"=>0,
			"können"=>0, "könnte"=>0, "machen"=>0, "man"=>0,
			"manche"=>0, "manchem"=>0, "manchen"=>0, "mancher"=>0, "manches"=>0,
			"mein"=>0, "meine"=>0, "meinem"=>0, "meinen"=>0, "meiner"=>0, "meines"=>0,
			"mit"=>0, "muss"=>0, "musste"=>0, "nach"=>0, "nicht"=>0,
			"nichts"=>0, "noch"=>0, "nun"=>0, "nur"=>0, "ob"=>0,
			"oder"=>0, "ohne"=>0, "sehr"=>0,
			"sein"=>0, "seine"=>0, "seinem"=>0, "seinen"=>0, "seiner"=>0, "seines"=>0,
			"selbst"=>0, "sich"=>0,
			"sie"=>0, "ihnen"=>0,
			"sind"=>0, "so"=>0,
			"solche"=>0, "solchem"=>0, "solchen"=>0, "solcher"=>0, "solches"=>0,
			"soll"=>0, "sollte"=>0, "sondern"=>0, "sonst"=>0, "über"=>0, "um"=>0, "und"=>0,
			"uns"=>0, "unse"=>0, "unsem"=>0, "unsen"=>0, "unser"=>0, "unses"=>0,
			"unter"=>0, "viel"=>0, "vom"=>0, "von"=>0, "vor"=>0,
			"während"=>0, "war"=>0, "waren"=>0, "warst"=>0, "was"=>0,
			"weg"=>0, "weil"=>0, "weiter"=>0,
			"welche"=>0, "welchem"=>0, "welchen"=>0, "welcher"=>0, "welches"=>0,
			"wenn"=>0, "werde"=>0, "werden"=>0, "wie"=>0, "wieder"=>0,
			"will"=>0, "wir"=>0, "wird"=>0, "wirst"=>0, "wo"=>0,
			"wollen"=>0, "wollte"=>0, "würde"=>0, "würden"=>0, "zu"=>0,
			"zum"=>0, "zur"=>0, "zwar"=>0, "zwischen"=>0,
		);
		if(defined("STEMMING_STOP_DE"))
		{
			foreach(explode(",", STEMMING_STOP_DE) as $word)
			{
				$word = trim($word);
				if($word <> '')
					$stop_list[$word]=0;
			}
		}
	}
	return !array_key_exists($sWord, $stop_list);
}

function stemming_de($word)
{
	$vowels = "AEIOUYÄÖÜ";
	//First, replace ß by ss
	$word = str_replace("ß", "SS", $word); //Actually ß in uppercase is already SS
	//put u and y between vowels into lower case
	$word=preg_replace("/([$vowels])U([$vowels])/", "\\1u\\2", $word);
	$word=preg_replace("/([$vowels])Y([$vowels])/", "\\1y\\2", $word);
	$word_len = mb_strlen($word);

	//In any word, R1 is the region after the first non-vowel following a vowel,
	//or the end of the word if it contains no such a non-vowel.
	$R1=0;
	while( ($R1<$word_len) && (mb_strpos($vowels, mb_substr($word, $R1, 1)) === false))
		$R1++;
	while( ($R1<$word_len) && (mb_strpos($vowels, mb_substr($word, $R1, 1)) !== false))
		$R1++;
	if($R1<$word_len)
		$R1++;

	//R2 is the region after the first non-vowel following a vowel in R1,
	//or is the null region at the end of the word if there is no such non-vowel.
	$R2=$R1;
	while( ($R2<$word_len) && (mb_strpos($vowels, mb_substr($word, $R2, 1)) === false))
		$R2++;
	while( ($R2<$word_len) && (mb_strpos($vowels, mb_substr($word, $R2, 1)) !== false))
		$R2++;
	if($R2<$word_len)
		$R2++;

	//R1 is adjusted so that the region before it contains at least 3 letters.
	if($R1 < 3)
		$R1 = 3;

	//Define a valid s-ending as one of b, d, f, g, h, k, l, m, n, r or t.
	$s_ending = "BDFGHKLMNRT";

	//Define a valid st-ending as the same list, excluding letter r.
	$st_ending = "BDFGHKLMNT";

	$word_r1 = mb_substr($word, $R1);

	//Step 1:
	//Search for the longest among the following suffixes
	//(a) em   ern   er
	//and delete if in R1
	if(preg_match("/(ERN|EM|ER)$/", $word_r1, $match))
	{
		$word = mb_substr($word, 0, -mb_strlen($match[1]));
	}
	//(b) e   en   es
	//If an ending of group (b) is deleted, and the ending is preceded by niss, delete the final s
	elseif(preg_match("/(ES|EN|E)$/", $word_r1, $match))
	{
		$word = mb_substr($word, 0, -mb_strlen($match[1]));
		if(preg_match("/NISS$/", $word))
			$word = mb_substr($word, 0, -1);
	}
	//(c) s (preceded by a valid s-ending)
	//the letter of the valid s-ending is not necessarily in R1
	elseif(mb_substr($word_r1, -1) == "S" && preg_match("/[$s_ending]S$/", $word))
	{
		$word = mb_substr($word, 0, -1);
	}

	$word_r1 = mb_substr($word, $R1);
	//Step 2:
	//Search for the longest among the following suffixes,
	//(a) en   er   est
	//and delete if in R1.
	if(preg_match("/(EST|EN|ER)$/", $word_r1, $match))
		$word = mb_substr($word, 0, -mb_strlen($match[1]));
	//(b) st (preceded by a valid st-ending, itself preceded by at least 3 letters)
	elseif(preg_match("/ST$/", $word_r1) && preg_match("/.{3,}[$st_ending]ST$/", $word))
		$word = mb_substr($word, 0, -2);

	//Step 3: d-suffixes (*)
	//Search for the longest among the following suffixes, and perform the action indicated.

	$word_r2 = mb_substr($word, $R2);
	//keit
	//    delete if in R2
	//    if preceded by lich or ig, delete if in R2
	if(preg_match("/KEIT$/", $word_r2))
	{
		$word = mb_substr($word, 0, -4);
		$word_r2 = mb_substr($word, $R2);
		if(preg_match("/(LICH|IG)$/", $word_r2, $match))
		{
			$word = mb_substr($word, 0, -mb_strlen($match[1]));
		}
	}
	// lich   heit
	//     delete if in R2
	//     if preceded by er or en, delete if in R1
	elseif(preg_match("/(LICH|HEIT)$/", $word_r2))
	{
		$word = mb_substr($word, 0, -4);
		$word_r1 = mb_substr($word, $R1);
		if(preg_match("/(ER|EN)$/", $word_r1))
		{
			$word = mb_substr($word, 0, -2);
		}
	}
	// end   ung
	//     delete if in R2
	//     if preceded by ig, delete if in R2 and not preceded by e
	elseif(preg_match("/(END|UNG)$/", $word_r2))
	{
		$word = mb_substr($word, 0, -3);
		$word_r2 = mb_substr($word, $R2);
		if(preg_match("/(^|[^E])(IG)$/", $word_r2))
		{
			$word = mb_substr($word, 0, -2);
		}
	}
	// ig   ik   isch
	//     delete if in R2 and not preceded by e
	elseif(preg_match("/(^|[^E])(IG|IK|ISCH)$/", $word_r2, $match))
	{
		$word = mb_substr($word, 0, -mb_strlen($match[2]));
	}

	//Finally,
	//turn U and Y back into lower case, and remove the umlaut accent from a, o and u.
	$word = str_replace(array("u", "y", "Ä", "Ö", "Ü"), array("U", "Y", "A", "O", "U"), $word);

	return $word;
}

function stemming_upper_de($sText)
{
	return str_replace(array("Ä", "Ö", "Ü"), array("A", "O", "U"), ToUpper($sText, "de"));
}
?>
