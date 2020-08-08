<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class CmdActivityAdd extends CmdBase
{
	const ACTIVITY_PRIORITY_MEDIUM = 2;  //\CCrmActivityPriority::Medium,
	const ACTIVITY_PROVIDER_ID = 'REST_APP';
	const ACTIVITY_PROVIDER_TYPE_ID = 'ESHOP';
	const ACTIVITY_TYPE_ID = 6;
	const CONTENT_TYPE_PLAIN_TEXT = 1;  //\CCrmContentType::PlainText,
	const ACTIVITY_NOTIFY_TYPE_NONE = 0; //\CCrmActivityNotifyType::None,

	public function fill()
	{
		parent::fill();

		$fields = $this->query->get('fields');

		$fields['PROVIDER_ID'] = static::ACTIVITY_PROVIDER_ID;
		$fields['PROVIDER_TYPE_ID'] = static::ACTIVITY_PROVIDER_TYPE_ID;
		//$fields['TYPE_ID'] = static::ACTIVITY_TYPE_ID;
		$fields['START_TIME'] = new \Bitrix\Main\Type\DateTime;
		$fields['COMPLETED'] = 'Y';
		$fields['PRIORITY'] = static::ACTIVITY_PRIORITY_MEDIUM;
		$fields['DESCRIPTION_TYPE'] = static::CONTENT_TYPE_PLAIN_TEXT;
		$fields['NOTIFY_TYPE'] = static::ACTIVITY_NOTIFY_TYPE_NONE;
		$fields['IS_RETURN_CUSTOMER'] = 'N';

		$this->query->setValues(['fields'=>$fields]);

		return $this;
	}

	protected function getCmdName()
	{
		return Registry::getRegistry()[Registry::CRM_ACTIVITY_ADD_NAME];
	}
}