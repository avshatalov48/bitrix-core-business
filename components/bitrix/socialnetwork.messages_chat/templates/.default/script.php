<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$MESS = array();
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/../lang/".LANGUAGE_ID."/script.php");
include_once($path);
$MESS1 =& $MESS;
$GLOBALS["MESS"] = $MESS1 + $GLOBALS["MESS"];
?>
<script type="text/javascript">
var bSendForm = false;

if (typeof oErrors != "object")
	var oErrors = {};

oErrors['no_topic_name'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_TOPIC_NAME"))?>";
oErrors['no_topic_recip'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_RECIPIENT"))?>";
oErrors['no_message'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_MESSAGE"))?>";
oErrors['max_len1'] = "<?=CUtil::addslashes(GetMessage("JERROR_MAX_LEN1"))?>";
oErrors['max_len2'] = "<?=CUtil::addslashes(GetMessage("JERROR_MAX_LEN2"))?>";
oErrors['no_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_URL"))?>";
oErrors['no_title'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_TITLE"))?>";


if (typeof oText != "object")
	var oText = {};

oText['author'] = " <?=CUtil::addslashes(GetMessage("JQOUTE_AUTHOR_WRITES"))?>:\n";
oText['translit_en'] = "<?=CUtil::addslashes(GetMessage("FORUM_TRANSLIT_EN"))?>";
oText['enter_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL"))?>";
oText['enter_url_name'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL_NAME"))?>";
oText['enter_image'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_IMAGE"))?>";
oText['list_prompt'] = "<?=CUtil::addslashes(GetMessage("FORUM_LIST_PROMPT"))?>";


if (typeof oHelp != "object")
	var oHelp = {};

oHelp['B'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_BOLD"))?>";
oHelp['I'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_ITALIC"))?>";
oHelp['U'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_UNDER"))?>";
oHelp['FONT'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_FONT"))?>";
oHelp['COLOR'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_COLOR"))?>";
oHelp['CLOSE'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_CLOSE"))?>";
oHelp['URL'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_URL"))?>";
oHelp['IMG'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_IMG"))?>";
oHelp['QUOTE'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_QUOTE"))?>";
oHelp['LIST'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_LIST"))?>";
oHelp['CODE'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_CODE"))?>";
oHelp['CLOSE_CLICK'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_CLICK_CLOSE"))?>";
oHelp['TRANSLIT'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_TRANSLIT"))?>";

var smallEngLettersReg = new Array(/e'/g, /ch/g, /sh/g, /yo/g, /jo/g, /zh/g, /yu/g, /ju/g, /ya/g, /ja/g, /a/g, /b/g, /v/g, /g/g, /d/g, /e/g, /z/g, /i/g, /j/g, /k/g, /l/g, /m/g, /n/g, /o/g, /p/g, /r/g, /s/g, /t/g, /u/g, /f/g, /h/g, /c/g, /w/g, /~/g, /y/g, /'/g);
var smallRusLetters = new Array("ý", "÷", "ø", "¸", "¸", "æ", "þ", "þ", "ÿ", "ÿ", "à", "á", "â", "ã", "ä", "å", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ù", "ú", "û", "ü");

var capitEngLettersReg = new Array(
	/Ch/g, /Sh/g, 
	/Yo/g, /Zh/g, 
	/Yu/g, /Ya/g, 
	/E'/g, /CH/g, /SH/g, /YO/g, /JO/g, /ZH/g, /YU/g, /JU/g, /YA/g, /JA/g, /A/g, /B/g, /V/g, /G/g, /D/g, /E/g, /Z/g, /I/g, /J/g, /K/g, /L/g, /M/g, /N/g, /O/g, /P/g, /R/g, /S/g, /T/g, /U/g, /F/g, /H/g, /C/g, /W/g, /Y/g);
var capitRusLetters = new Array(
	"×", "Ø",
	"¨", "Æ",
	"Þ", "ß",
	"Ý", "×", "Ø", "¨", "¨", "Æ", "Þ", "Þ", "\ß", "\ß", "À", "Á", "Â", "Ã", "Ä", "Å", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ù", "Û");
	
// '//
var smallRusLettersReg = new Array(/ý/g, /÷/g, /ø/g, /¸/g, /¸/g,/æ/g, /þ/g, /þ/g, /ÿ/g, /ÿ/g, /à/g, /á/g, /â/g, /ã/g, /ä/g, /å/g, /ç/g, /è/g, /é/g, /ê/g, /ë/g, /ì/g, /í/g, /î/g, /ï/g, /ð/g, /ñ/g, /ò/g, /ó/g, /ô/g, /õ/g, /ö/g, /ù/g, /ú/g, /û/g, /ü/g );
var smallEngLetters = new Array("e", "ch", "sh", "yo", "jo", "zh", "yu", "ju", "ya", "ja", "a", "b", "v", "g", "d", "e", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "w", "~", "y", "'");

var capitRusLettersReg = new Array(
	/×(?=[^À-ß])/g, /Ø(?=[^À-ß])/g, 
	/¨(?=[^À-ß])/g, /Æ(?=[^À-ß])/g, 
	/Þ(?=[^À-ß])/g, /ß(?=[^À-ß])/g, 
	/Ý/g, /×/g, /Ø/g, /¨/g, /¨/g, /Æ/g, /Þ/g, /Þ/g, /ß/g, /ß/g, /À/g, /Á/g, /Â/g, /Ã/g, /Ä/g, /Å/g, /Ç/g, /È/g, /É/g, /Ê/g, /Ë/g, /Ì/g, /Í/g, /Î/g, /Ï/g, /Ð/g, /Ñ/g, /Ò/g, /Ó/g, /Ô/g, /Õ/g, /Ö/g, /Ù/g, /Ú/g, /Û/g, /Ü/g);
var capitEngLetters = new Array(
	"Ch", "Sh",
	"Yo", "Zh",
	"Yu", "Ya",
	"E", "CH", "SH", "YO", "JO", "ZH", "YU", "JU", "YA", "JA", "A", "B", "V", "G", "D", "E", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F", "H", "C", "W", "~", "Y", "'");

var messageTrSystem = "<?=CUtil::addslashes(GetMessage("SONET_CT94_SYSTEM"))?>";
var messageTrTalkOnline = "<?=CUtil::addslashes(GetMessage("SONET_CT94_ONLINE"))?>";
var messageTrTalkOutline = "<?=CUtil::addslashes(GetMessage("SONET_CT94_OFFLINE"))?>";
var messageTrError = "<?=CUtil::addslashes(GetMessage("SONET_CT94_ERROR"))?>";
var messageNewMessage = "<?=CUtil::addslashes(GetMessage("SONET_NEW_MESSAGE"))?>";
var messageNetworkError = "<?=CUtil::addslashes(GetMessage("SONET_NET_ERROR"))?>";
var messSoundOn = "<?=CUtil::addslashes(GetMessage("SONET_SOUND_ON"))?>";
var messSoundOff = "<?=CUtil::addslashes(GetMessage("SONET_SOUND_OFF"))?>";

var mmTrMonth = new Array('<?=CUtil::addslashes(GetMessage("SONET_CT94_1"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_2"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_3"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_4"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_5"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_6"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_7"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_8"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_9"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_10"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_11"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_12"))?>');

</script>