<?
global $mp3FolderName, $mp3Price, $mp3Currency, $mp3AuxiliaryPrefix, $mp3AccessTimeLength, $mp3AccessTimeType, $bMP3ConvertCurrency;

$mp3Price = 0.1;
$mp3Currency = "USD";
$mp3AccessTimeLength = 1;
$mp3AccessTimeType = "D";

global $arMP3Sums;
$arMP3Sums = array(
		1 => array("PRICE" => 10, "CURRENCY" => "USD"),
		2 => array("PRICE" => 20, "CURRENCY" => "USD"),
		3 => array("PRICE" => 30, "CURRENCY" => "USD"),
		4 => array("PRICE" => 40, "CURRENCY" => "USD"),
		5 => array("PRICE" => 500, "CURRENCY" => "RUR"),
		6 => array("PRICE" => 1000, "CURRENCY" => "RUR", "HIDDEN" => "Y")
	);

$mp3FolderName = "original";
$mp3AuxiliaryPrefix = "MP3_FILE_";
$bMP3ConvertCurrency = True;

global $mp3Path2Folder, $mp3Url2Folder, $mp3Path2Original, $mp3Url2Original;
$mp3Path2Folder = str_replace("\\", "/", __DIR__)."/";
$mp3Url2Folder = mb_substr(str_replace("\\", "/", __DIR__), mb_strlen($_SERVER["DOCUMENT_ROOT"]))."/";

$mp3Path2Original = $mp3Path2Folder.$mp3FolderName."/files/";
$mp3Url2Original = $mp3Url2Folder.$mp3FolderName."/";

function MP3DeliveryOrderCallback($productID, $userID, $bPaid, $orderID)
{
	global $DB;

	$productID = intval($productID);
	$userID = intval($userID);
	$bPaid = ($bPaid ? True : False);
	$orderID = intval($orderID);

	if ($userID <= 0)
		return False;

	if ($orderID <= 0)
		return False;

	if (!array_key_exists($productID, $GLOBALS["arMP3Sums"]))
		return False;

	if (!($arOrder = CSaleOrder::GetByID($orderID)))
		return False;

	$baseLangCurrency = CSaleLang::GetLangCurrency($arOrder["LID"]);

	$currentPrice = $GLOBALS["arMP3Sums"][$productID]["PRICE"];
	$currentCurrency = $GLOBALS["arMP3Sums"][$productID]["CURRENCY"];
	if ($GLOBALS["arMP3Sums"][$productID]["CURRENCY"] != $baseLangCurrency)
	{
		$currentPrice = CCurrencyRates::ConvertCurrency($GLOBALS["arMP3Sums"][$productID]["PRICE"], $GLOBALS["arMP3Sums"][$productID]["CURRENCY"], $baseLangCurrency);
		$currentCurrency = $baseLangCurrency;
	}

	if (!CSaleUserAccount::UpdateAccount($userID, ($bPaid ? $currentPrice : -$currentPrice), $currentCurrency, "MANUAL", $orderID))
		return False;

	return True;
}



global $arMP3Genres;
$arMP3Genres = array('Blues', 'Classic Rock', 'Country', 'Dance', 'Disco', 'Funk', 'Grunge', 'Hip-Hop', 'Jazz', 'Metal', 'New Age', 'Oldies', 'Other', 'Pop', 'R&B', 'Rap', 'Reggae', 'Rock', 'Techno', 'Industrial', 'Alternative', 'Ska', 'Death Metal', 'Pranks', 'Soundtrack', 'Euro-Techno', 'Ambient', 'Trip-Hop', 'Vocal', 'Jazz+Funk', 'Fusion',
		'Trance', 'Classical', 'Instrumental', 'Acid', 'House', 'Game', 'Sound Clip', 'Gospel', 'Noise', 'AlternRock', 'Bass', 'Soul', 'Punk', 'Space', 'Meditative', 'Instrumental Pop', 'Instrumental Rock', 'Ethnic', 'Gothic', 'Darkwave', 'Techno-Industrial', 'Electronic', 'Pop-Folk', 'Eurodance', 'Dream', 'Southern Rock', 'Comedy',
		'Cult', 'Gangsta', 'Top 40', 'Christian Rap', 'Pop/Funk', 'Jungle', 'Native American', 'Cabaret', 'New Wave', 'Psychadelic', 'Rave', 'Showtunes', 'Trailer', 'Lo-Fi', 'Tribal', 'Acid Punk', 'Acid Jazz', 'Polka', 'Retro', 'Musical', 'Rock & Roll', 'Hard Rock', 'Folk', 'Folk-Rock', 'National Folk', 'Swing', 'Fast Fusion', 'Bebob',
		'Latin', 'Revival', 'Celtic', 'Bluegrass', 'Avantgarde', 'Gothic Rock', 'Progressive Rock', 'Psychedelic Rock', 'Symphonic Rock', 'Slow Rock', 'Big Band', 'Chorus', 'Easy Listening', 'Acoustic', 'Humour', 'Speech', 'Chanson', 'Opera', 'Chamber Music', 'Sonata', 'Symphony', 'Booty Bass', 'Primus', 'Porn Groove', 'Satire', 'Slow Jam', 'Club',
		'Tango', 'Samba', 'Folklore', 'Ballad', 'Power Ballad', 'Rhythmic Soul', 'Freestyle', 'Duet', 'Punk Rock', 'Drum Solo', 'Acapella', 'Euro-House', 'Dance Hall'
	);

global $arMP3Version, $arMP3Layer, $arMP3CRC;
$arMP3Version = array("00" => 2.5, "10" => 2, "11" => 1);
$arMP3Layer = array("01" => 3, "10" => 2, "11" => 1);
$arMP3CRC = array("Yes", "No");

global $arMP3Bitrate;
$arMP3Bitrate = array(
		"0001" => array(32, 32, 32, 32, 8, 8), 
		"0010" => array(64, 48, 40, 48, 16, 16), 
		"0011" => array(96, 56, 48, 56, 24, 24), 
		"0100" => array(128, 64, 56, 64, 32, 32), 
		"0101" => array(160, 80, 64, 80, 40, 40), 
		"0110" => array(192, 96, 80, 96, 48, 48), 
		"0111" => array(224, 112, 96, 112, 56, 56), 
		"1000" => array(256, 128, 112, 128, 64, 64), 
		"1001" => array(288, 160, 128, 144, 80, 80), 
		"1010" => array(320, 192, 160, 160, 96, 96), 
		"1011" => array(352, 224, 192, 176, 112, 112), 
		"1100" => array(384, 256, 224, 192, 128, 128), 
		"1101" => array(416, 320, 256, 224, 144, 144), 
		"1110" => array(448, 384, 320, 256, 160, 160)
	);

global $arMP3BitIndex;
$arMP3BitIndex = array("1111" => "0", "1110" => "1", "1101" => "2", "1011" => "3", "1010" => "4", "1001" => "5", "0011" => "3", "0010" => 4, "0001" => "5");

global $arMP3Freq;
$arMP3Freq = array(
		"00" => array("11" => 44100, "10" => 22050, "00" => 11025),
		"01" => array("11" => 48000, "10" => 24000, "00" => 12000),
		"10" => array("11" => 32000, "10" => 16000, "00" => 8000)
	);

global $arMP3Mode, $arMP3Copy;
$arMP3Mode = array("00" => "Stereo", "01" => "Joint stereo", "10" => "Dual channel", "11" => "Mono");
$arMP3Copy = array("No", "Yes");

function MP3StripNulls($str)
{
	$res = explode(chr(0), $str);
	return chop($res[0]);
}

function ReadMP3Tags($filePath)
{
	$filePath = Trim($filePath);
	if ($filePath == '')
		return False;

	if (!$hFile = @fopen($filePath, "rb"))
		return False;

	$arMP3Tags = array();

	$tmpVal = fread($hFile, 4);
	if ($tmpVal == "RIFF")
	{
		$arMP3Tags["ftype"] = "Wave";
		fseek($hFile, 0);
		$tmpVal = fread($hFile, 128);
		$x = mb_strpos($tmpVal, "data");
		fseek($hFile, $x + 8);
		$tmpVal = fread($hFile, 4);
	}

	$flagsCol = "";
	for ($y = 0; $y < 4; $y++)
	{
		$x = decbin(ord($tmpVal[$y]));
		for ($i = 0; $i < (8 - mb_strlen($x)); $i++)
			$x .= "0";
		$flagsCol .= $x;
	}

	if (mb_substr($flagsCol, 1, 11) != "11111111111")
	{
		fseek($hFile, 4);
		$tmpVal = fread($hFile, 2048);
		for ($i = 0; $i < 2048; $i++)
		{
			if (ord($tmpVal[$i]) == 255 && mb_substr(decbin(ord($tmpVal[$i + 1])), 0, 3) == "111")
			{
				$tmpVal = mb_substr($tmpVal, $i, 4);
				$flagsCol = "";
				for ($y = 0; $y < 4;$y++)
				{
					$x = decbin(ord($tmpVal[$y]));
					for ($i = 0; $i < (8 - mb_strlen($x)); $i++)
						$x .= "0";
					$flagsCol .= $x;
				}
				break;
			}
		}
	}

	if ($flagsCol == "")
		return False;

	$len = filesize($filePath);
	$arMP3Tags["version"] = $GLOBALS["arMP3Version"][mb_substr($flagsCol, 11, 2)];
	$arMP3Tags["layer"] = $GLOBALS["arMP3Layer"][mb_substr($flagsCol, 13, 2)];
	$arMP3Tags["crc"] = $GLOBALS["arMP3CRC"][$flagsCol[15]];
	$arMP3Tags["bitrate"] = $GLOBALS["arMP3Bitrate"][mb_substr($flagsCol, 16, 4)][$GLOBALS["arMP3BitIndex"][mb_substr($flagsCol, 11, 4)]];
	$arMP3Tags["frequency"] = $GLOBALS["arMP3Freq"][mb_substr($flagsCol, 20, 2)][mb_substr($flagsCol, 11, 2)];
	$arMP3Tags["padding"] = $GLOBALS["arMP3Copy"][$flagsCol[22]];
	$arMP3Tags["mode"] = $GLOBALS["arMP3Mode"][mb_substr($flagsCol, 24, 2)];
	$arMP3Tags["copyright"] = $GLOBALS["arMP3Copy"][$flagsCol[28]];
	$arMP3Tags["original"] = $GLOBALS["arMP3Copy"][$flagsCol[29]];

	if ($arMP3Tags["layer"] == 1)
		$fsize = (12 * ($arMP3Tags["bitrate"] * 1000) / $arMP3Tags["frequency"] + $arMP3Tags["padding"]) * 4;
	else
		$fsize = 144 * (($arMP3Tags["bitrate"] * 1000) / $arMP3Tags["frequency"] + $arMP3Tags["padding"]);

	$arMP3Tags["lenght_sec"] = round($len / Round($fsize) / 38.37);
	$arMP3Tags["lenght"] = date("i:s", round($len / Round($fsize) / 38.37));

	if (!$len)
		$len = filesize($filePath);

	fseek($hFile, $len - 128);
	$tag = fread($hFile, 128);
	if (mb_substr($tag, 0, 3) == "TAG")
	{
		$arMP3Tags["file"] = $filePath;
		$arMP3Tags["tag"] = -1;

		$arMP3Tags["title"] = MP3StripNulls(mb_substr($tag, 3, 30));
		$arMP3Tags["artist"] = MP3StripNulls(mb_substr($tag, 33, 30));
		$arMP3Tags["album"] = MP3StripNulls(mb_substr($tag, 63, 30));
		$arMP3Tags["year"] = MP3StripNulls(mb_substr($tag, 93, 4));
		$arMP3Tags["comment"] = MP3StripNulls(mb_substr($tag, 97, 30));

		if (mb_strlen($arMP3Tags["comment"]) < 29)
		{
			if (Ord(mb_substr($tag, 125, 1)) == chr(0))
				$arMP3Tags["track"] = Ord(mb_substr($tag, 126, 1));
			else
				$arMP3Tags["track"] = 0;
		}
		else
		{
			$arMP3Tags["track"] = 0;
		}

		$arMP3Tags["genreid"] = Ord(mb_substr($tag, 127, 1));
		$arMP3Tags["genre"] = $GLOBALS["arMP3Genres"][$arMP3Tags["genreid"]];
		$arMP3Tags["filesize"] = $len;
	}
	else
	{
		$arMP3Tags["tag"] = 0;
		$arMP3Tags["filesize"] = $len;
	}


	if (!$arMP3Tags["title"])
	{
		$arMP3Tags["title"] = Str_replace("\\", "/", $filePath);
		$arMP3Tags["title"] = mb_substr($arMP3Tags["title"], mb_strrpos($arMP3Tags["title"], "/") + 1, 255);
	}
	fclose($hFile);

	return $arMP3Tags;
}
?>