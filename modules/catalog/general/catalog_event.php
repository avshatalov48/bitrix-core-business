<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CCatalogEvent
{
	function GetAuditTypes()
	{
		return array(
			"CAT_YAND_AGENT" => "[CAT_YAND_AGENT] ".Loc::getMessage("CAT_YAND_AGENT"),
			"CAT_YAND_FILE" => "[CAT_YAND_FILE] ".Loc::getMessage('CAT_YAND_FILE'),
		);
	}

	function GetYandexAgentEvent()
	{
		return array('CAT_YAND_AGENT','CAT_YAND_FILE');
	}

	function GetYandexAgentFilter()
	{
		return '&find_audit_type[]=CAT_YAND_AGENT&find_audit_type[]=CAT_YAND_FILE';
	}
}
?>