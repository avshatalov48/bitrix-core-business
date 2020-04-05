<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Trigger;
use Bitrix\Sender\MailingTable;

if (!Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = Controller\Action::create('createUsingPreset')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();

		$code = $request->get('code');
		$presets = MailingTable::getPresetMailingList();
		$data = null;
		foreach ($presets as $preset)
		{
			if ($preset['CODE'] === $code)
			{
				$data = $preset;
				break;
			}
		}
		if (!$data)
		{
			$content->getErrorCollection()->setError(new Error("Preset with code `$code` not found."));
			return;
		}

		$triggerFields = [];
		$triggerFields['START'] = Trigger\Settings::getArrayFromTrigger(
			Trigger\Manager::getOnce($data['TRIGGER']['START']['ENDPOINT'])
		);
		$triggerFields['END'] = Trigger\Settings::getArrayFromTrigger(
			Trigger\Manager::getOnce($data['TRIGGER']['END']['ENDPOINT'])
		);

		$entity = (new Entity\TriggerCampaign())
			->set('NAME', $data['NAME'])
			->set('DESCRIPTION', $data['DESC_USER'])
			->set('SITE_ID', SITE_ID)
			->set('TRIGGER_FIELDS', $triggerFields);

		$entity->save();
		if ($entity->hasErrors())
		{
			$content->getErrorCollection()->add($entity->getErrors());
			return;
		}

		Loader::includeModule('fileman');
		$defaultMessage = \Bitrix\Fileman\Block\Content\SliceConverter::SLICE_SECTION_ID . '/STYLES/page/';
		$defaultMessage = "<!--START $defaultMessage--><!--END $defaultMessage-->";

		$emailFromList = \Bitrix\Sender\MailingChainTable::getDefaultEmailFromList();
		foreach ($data['CHAIN'] as $letterData)
		{
			$letter = (new Entity\Letter())
				->set('IS_TRIGGER', 'Y')
				->set('CREATED_BY', Bitrix\Sender\Security\User::current()->getId())
				->set('CAMPAIGN_ID', $entity->getId())
				->set('TITLE', trim(str_replace('#SITE_NAME#:', '',$letterData['SUBJECT'])))
				->set('TIME_SHIFT', $letterData['TIME_SHIFT'])
				->set('TEMPLATE_ID', $letterData['TEMPLATE_ID'])
				->set('TEMPLATE_TYPE', $letterData['TEMPLATE_TYPE']);
			$config = $letter->getMessage()->getConfiguration();
			$config->set('SUBJECT', $letterData['SUBJECT']);
			$config->set('MESSAGE', $defaultMessage);
			$config->set('EMAIL_FROM', current($emailFromList));
			$config->set('TEMPLATE_ID', $letterData['TEMPLATE_ID']);
			$config->set('TEMPLATE_TYPE', $letterData['TEMPLATE_TYPE']);
			$result = $letter->getMessage()->saveConfiguration($config);
			if (!$result->isSuccess())
			{
				$content->getErrorCollection()->add($result->getErrors());
				return;
			}

			$letter->set('MESSAGE_ID', $config->getId());
			$letter->save();
			if ($letter->hasErrors())
			{
				$content->getErrorCollection()->add($entity->getErrors());
				return;
			}
			$entity->getChain()->addLetter($letter->getId());
		}

		$entity->getChain()->save();
	}
);
$actions[] = Controller\Action::create('activate')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->activate();
		\Bitrix\Sender\MailingTable::updateChainTrigger($request->get('id'));

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$actions[] = Controller\Action::create('deactivate')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->deactivate();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->remove();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$actions[] = Controller\Action::create('removeList')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$list = $request->get('id');
		if (!is_array($list) || empty($list))
		{
			return;
		}

		$content = $response->initContentJson();
		foreach ($list as $id)
		{
			$id = (int) $id;
			if (!$id)
			{
				return;
			}

			$entity = new Entity\TriggerCampaign($id);
			$entity->remove();
			if ($entity->hasErrors())
			{
				$content->getErrorCollection()->add($entity->getErrors());
				break;
			}
		}
	}
);
$checker = CommonAjax\Checker::getModifyLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();