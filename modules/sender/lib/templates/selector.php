<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Templates;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventResult;
use Bitrix\Main\Event;

use Bitrix\Sender\TemplateTable;
use Bitrix\Sender\Message;

Loc::loadMessages(__FILE__);

/**
 * Class Selector
 * @package Bitrix\Sender\Templates
 */
class Selector
{
	/** @var  string $messageCode Message code. */
	private $messageCode;

	/** @var  integer $version Version. */
	private $version;

	/** @var  integer $typeId Type ID. */
	private $typeId = null;

	/** @var  bool $includeTriggers Include triggers. */
	private $includeTriggers = true;

	/** @var  integer $id Template ID. */
	private $id = null;

	/** @var  integer $templateCounter Template counter. */
	private $templateCounter = 0;

	/**
	 * Create selector instance.
	 *
	 * @return static;
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * With default message code.
	 *
	 * @return $this
	 */
	public function withDefaultMessageCode()
	{
		$this->messageCode = Message\iBase::CODE_MAIL;
		return $this;
	}

	/**
	 * With message code.
	 *
	 * @param string $messageCode Message code.
	 * @return $this
	 */
	public function withMessageCode($messageCode)
	{
		$this->messageCode = $messageCode;
		return $this;
	}

	/**
	 * With minimal version.
	 *
	 * @param integer $version Version.
	 * @return $this
	 */
	public function withVersion($version)
	{
		$this->version = $version;
		return $this;
	}

	/**
	 * With type ID.
	 *
	 * @param integer $typeId Type ID.
	 * @return $this
	 */
	public function withTypeId($typeId)
	{
		$this->typeId = $typeId;
		return $this;
	}

	/**
	 * With id.
	 *
	 * @param integer $id Template ID.
	 * @return $this
	 */
	public function withId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * With triggers.
	 *
	 * @param bool $include Include triggers.
	 * @return $this
	 */
	public function withTriggers($include)
	{
		$this->includeTriggers = $include;
		return $this;
	}

	/**
	 * Get list.
	 *
	 * @return array
	 */
	public function get()
	{
		$list = $this->getTemplates(1);
		return count($list) > 0 ? $list[0] : null;
	}

	/**
	 * Get list.
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->getTemplates();
	}

	/**
	 * Return true if it has templates.
	 *
	 * @return bool
	 */
	public function hasAny()
	{
		return count($this->getList()) > 0;
	}

	/**
	 * Get categories of list.
	 *
	 * @return array
	 */
	public function getCategories()
	{
		$result = array();
		foreach($this->getList() as $template)
		{
			$categoryCode = $template['CATEGORY'];
			if (isset($result[$categoryCode]))
			{
				continue;
			}

			$result[$categoryCode] = array(
				'id' => $categoryCode,
				'name' => Category::getName(Category::getId($categoryCode))
			);
		}

		$result = array_values($result);
		usort(
			$result,
			function ($a, $b)
			{
				return Category::sortByCode($a['id'], $b['id']);
			}
		);

		return $result;
	}

	/**
	 * Get categorized list.
	 *
	 * @return array
	 */
	public function getCategorized()
	{
		$list = array();
		foreach($this->getList() as $template)
		{
			$list[$template['CATEGORY']][] = $template;
		}

		return $list;
	}

	private function getTemplates($limit = 0)
	{
		$result = array();
		$parameters = array(
			Type::getCode($this->typeId),
			$this->id,
			$this->messageCode
		);
		$event = new Event(
			'sender',
			'OnPresetTemplateList',
			$parameters
		);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == EventResult::ERROR)
			{
				continue;
			}

			$eventResultParameters = $eventResult->getParameters();
			if (!is_array($eventResultParameters))
			{
				continue;
			}

			foreach ($eventResultParameters as $template)
			{
				$template = $this->prepareTemplate($template);
				if (!$this->checkTemplate($template))
				{
					continue;
				}

				$result[] = $template;

				if ($limit && count($result) >= $limit)
				{
					return $result;
				}
			}
		}

		$providers = array(
			array('\Bitrix\Sender\Templates\Recent', 'onPresetTemplateList'),
			array('\Bitrix\Sender\Preset\Templates\Mail', 'onPresetTemplateList'),
			array('\Bitrix\Sender\Preset\Templates\Sms', 'onPresetTemplateList'),
			array('\Bitrix\Sender\Preset\Templates\Rc', 'onPresetTemplateList')
		);
		foreach ($providers as $provider)
		{
			if (!is_callable($provider))
			{
				continue;
			}

			$eventResult = call_user_func_array($provider, $parameters);
			if (!is_array($eventResult))
			{
				continue;
			}
			foreach ($eventResult as $template)
			{
				$template = $this->prepareTemplate($template);
				if (!$this->checkTemplate($template))
				{
					continue;
				}

				$result[] = $template;
				if ($limit && count($result) >= $limit)
				{
					return $result;
				}
			}
		}

		return $result;

	}

	private function checkTemplate($template)
	{
		if (count($template['FIELDS']) === 0)
		{
			return false;
		}

		if ($this->messageCode)
		{
			$messageCodes = $template['MESSAGE_CODE'];
			if (!is_array($messageCodes))
			{
				$messageCodes = array($messageCodes);
			}
			if (!in_array($this->messageCode, $messageCodes))
			{
				return false;
			}
		}

		if ($this->version && $template['VERSION'] < $this->version)
		{
			return false;
		}

		if (!$this->includeTriggers && $template['IS_TRIGGER'])
		{
			return false;
		}

		if (!in_array($template['TYPE'], Type::getCodes()))
		{
			return false;
		}

		if ($this->id && $this->id != $template['ID'])
		{
			return false;
		}

		return true;
	}

	private function prepareTemplate($template)
	{
		if (!is_array($template))
		{
			$template = array();
		}

		// type
		if (!isset($template['TYPE']))
		{
			$template['TYPE'] = Type::getCode(Type::ADDITIONAL);
		}
		// type
		if (!isset($template['IS_TRIGGER']))
		{
			$template['IS_TRIGGER'] = false;
		}

		// type
		if (!isset($template['ID']))
		{
			$template['ID'] = ++$this->templateCounter;
		}

		// fields of template
		if (!isset($template['FIELDS']) || !is_array($template['FIELDS']))
		{
			$template['FIELDS'] = [];
		}

		// segments of template
		if (!isset($template['SEGMENTS']) || !is_array($template['SEGMENTS']))
		{
			$template['SEGMENTS'] = [];
		}

		// dispatch of template
		if (!isset($template['DISPATCH']) || !is_array($template['DISPATCH']))
		{
			$template['DISPATCH'] = [];
		}

		// compatibility for mail templates
		if (isset($template['HTML']) && $template['HTML'] && count($template['FIELDS']) === 0)
		{
			$template['FIELDS']['MESSAGE'] = array(
				'CODE' => 'MESSAGE',
				'VALUE' => $template['HTML'],
				'ON_DEMAND' => TemplateTable::isContentForBlockEditor($template['HTML'])
			);
		}

		if (!isset($template['CATEGORY']) || !$template['CATEGORY'])
		{
			$template['CATEGORY'] = $template['TYPE'];
		}

		if (!isset($template['VERSION']) || !$template['VERSION'])
		{
			$template['VERSION'] = 2;
		}

		if (!isset($template['HINT']) || !$template['HINT'])
		{
			$template['HINT'] = '';
		}

		if (!isset($template['HOT']) || !$template['HOT'])
		{
			$template['HOT'] = false;
		}
		$template['HOT'] = (bool) $template['HOT'];

		// default message code is MAIL
		if (!isset($template['MESSAGE_CODE']) || !$template['MESSAGE_CODE'])
		{
			$template['MESSAGE_CODE'] = Message\iBase::CODE_MAIL;
		}

		// compatibility
		if ($template['MESSAGE_CODE'] === Message\iBase::CODE_MAIL)
		{
			$template['IS_SUPPORT_BLOCK_EDITOR'] = $template['FIELDS']['MESSAGE']['ON_DEMAND'];
		}

		return $template;
	}

}