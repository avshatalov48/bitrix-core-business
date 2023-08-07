<?php
namespace Bitrix\Report\VisualConstructor;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\Extension;
use Bitrix\Report\VisualConstructor\Helper\Filter;

/**
 * Class AnalyticBoard
 * @package Bitrix\Report\VisualConstructor
 */
class AnalyticBoard
{
	private $title;
	private $boardKey;
	private $machineKey;
	private $filter;
	private $batchKey = null;
	private $group = null;
	private $buttons = [];
	private $disabled = false;
	private $stepperEnabled = false;
	private $stepperIds = [];
	private $limited = false;
	private $limitComponentParams = [];
	private $isExternal = false;
	private $externalUrl = "";
	private $isSliderSupport = true;
	private $options;
	private $setOptionsCallback;

	public function __construct(string $boardId = '', array $options = [])
	{
		$this->options = $options;

		if ($boardId)
		{
			$this->setBoardKey($boardId);

			$configurationButton = new BoardComponentButton(
				'bitrix:report.analytics.config.control',
				'',
				[
					'BOARD_ID' => $this->getBoardKey(),
					'BOARD_OPTIONS' => $this->getOptions(),
				]
			);
			$this->addButton($configurationButton);
			//$this->addButton(new BoardButton(' '));
		}
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getBoardKey()
	{
		return $this->boardKey;
	}

	/**
	 * @param mixed $boardKey
	 */
	public function setBoardKey($boardKey)
	{
		$this->boardKey = $boardKey;
	}

	/**
	 * @return mixed
	 */
	public function getMachineKey()
	{
		return $this->machineKey ?: $this->boardKey;
	}

	/**
	 * @param mixed $machineKey
	 */
	public function setMachineKey($machineKey)
	{
		$this->machineKey = $machineKey;
	}

	/**
	 * @return Filter
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * @param Filter $filter
	 */
	public function setFilter(Filter $filter)
	{
		$this->filter = $filter;
	}

	/**
	 * @return string
	 */
	public function getBatchKey()
	{
		return $this->batchKey;
	}

	/**
	 * @param string $batchKey
	 */
	public function setBatchKey($batchKey)
	{
		$this->batchKey = $batchKey;
	}

	/**
	 * @return bool
	 */
	public function isNestedInBatch()
	{
		return $this->batchKey !== null;
	}

	/**
	 * @param BoardButton $button
	 */
	public function addButton(BoardButton $button)
	{
		$this->buttons[] = $button;
	}

	public function getButtonsContent()
	{
		$result = [
			'html' => '',
			'assets' => [
				'js' => [],
				'css' => [],
				'string' => [],
			]
		];
		if ($this->isDisabled())
		{
			return $result;
		}
		$buttons = $this->getButtons();
		foreach ($buttons as $button)
		{
			$result['html'] .= $button->process()->getHtml();
			foreach ($button->getJsList() as $jsPath)
			{
				$result['assets']['js'][] = $jsPath;
			}

			foreach ($button->getCssList() as $cssPath)
			{
				$result['assets']['css'][] = $cssPath;
			}

			foreach ($button->getStringList() as $string)
			{
				$result['assets']['string'][] = $string;
			}
		}

		return $result;
	}

	/**
	 * @return BoardButton[]
	 */
	public function getButtons()
	{
		return $this->buttons;
	}

	/**
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}

	/**
	 * @param bool $disabled
	 */
	public function setDisabled($disabled)
	{
		$this->disabled = $disabled;
	}

	public function addFeedbackButton()
	{
		$feedbackButton = new BoardComponentButton('bitrix:ui.feedback.form', '', [
			'ID' => 'crm-analytics',
			'VIEW_TARGET' => null,
			'FORMS' => [
				['zones' => ['com.br'], 'id' => '58','lang' => 'br', 'sec' => 'k6be5r'],
				['zones' => ['es'], 'id' => '60','lang' => 'la', 'sec' => '1shpig'],
				['zones' => ['de'], 'id' => '62','lang' => 'de', 'sec' => 'dj2q8l'],
				['zones' => ['ua'], 'id' => '66','lang' => 'ua', 'sec' => 't5y7px'],
				['zones' => ['ru', 'kz', 'by'], 'id' => '68','lang' => 'ru', 'sec' => 'h6thh2'],
				['zones' => ['en'], 'id' => '64','lang' => 'en', 'sec' => '776ire'],
			],
			'PRESETS' => [
				'BOARD_KEY' => $this->getBoardKey(),
				'sender_page' => $this->getTitle()
			]

		]);
		$this->addButton($feedbackButton);
	}

	/**
	 * @return bool
	 */
	public function isStepperEnabled()
	{
		return $this->stepperEnabled;
	}

	public function getStepperIds()
	{
		return $this->stepperIds;
	}

	public function setStepperIds($stepperIds = [])
	{
		$this->stepperIds =$stepperIds;
	}
	/**
	 * @param bool $stepperEnabled
	 */
	public function setStepperEnabled($stepperEnabled)
	{
		$this->stepperEnabled = $stepperEnabled;
	}

	public function isLimited()
	{
		return $this->limited;
	}

	private function getLimitComponentOptions()
	{
		return $this->limitComponentParams;
	}

	public function getLimitComponentName()
	{
		$componentParams = $this->getLimitComponentOptions();
		if (!isset($componentParams['NAME']))
		{
			throw new ArgumentException("Component name do not isset");
		}
		return $componentParams['NAME'];
	}

	public function getLimitComponentTemplateName()
	{
		$componentOptions = $this->getLimitComponentOptions();
		if (!isset($componentOptions['TEMPLATE_NAME']))
		{
			return '';
		}
		return $componentOptions['TEMPLATE_NAME'];
	}

	public function getLimitComponentParams()
	{
		$componentOptions = $this->getLimitComponentOptions();
		if (!isset($componentOptions['PARAMS']))
		{
			return [];
		}
		return $componentOptions['PARAMS'];
	}

	public function setLimit($limitComponentParams, $limit = false)
	{
		$this->limitComponentParams = $limitComponentParams;
		$this->limited = $limit;
	}

	/**
	 * @return bool
	 */
	public function isExternal(): bool
	{
		return $this->isExternal;
	}

	/**
	 * @param bool $isExternal
	 */
	public function setExternal(bool $isExternal): void
	{
		$this->isExternal = $isExternal;
	}

	/**
	 * @return string
	 */
	public function getExternalUrl(): string
	{
		return $this->externalUrl;
	}

	/**
	 * @param string $externalUrl
	 */
	public function setExternalUrl(string $externalUrl): void
	{
		$this->externalUrl = $externalUrl;
	}

	/**
	 * @return bool
	 */
	public function isSliderSupport(): bool
	{
		return $this->isSliderSupport;
	}

	/**
	 * @param bool $isSliderSupport
	 */
	public function setSliderSupport(bool $isSliderSupport): void
	{
		$this->isSliderSupport = $isSliderSupport;
	}

	public function getDisplayComponentName()
	{
		if ($this->isDisabled())
		{
			return 'bitrix:report.analytics.empty';
		}
		elseif ($this->isLimited())
		{
			return $this->getLimitComponentName();
		}
		else
		{
			return 'bitrix:report.visualconstructor.board.base';
		}
	}

	public function getDisplayComponentTemplate()
	{
		return ($this->isLimited() ? $this->getLimitComponentTemplateName() : "");
	}

	public function getDisplayComponentParams()
	{
		if ($this->isDisabled())
		{
			return [];
		}
		elseif ($this->isLimited())
		{
			return $this->getLimitComponentParams();
		}
		else
		{
			return [
				'BOARD_ID' => $this->getBoardKey(),
				'IS_DEFAULT_MODE_DEMO' => false,
				'IS_BOARD_DEFAULT' => true,
				'FILTER' => $this->getFilter(),
				'BOARD_BUTTONS' => $this->getButtons(),
				'IS_ENABLED_STEPPER' => $this->isStepperEnabled(),
				'STEPPER_IDS' => $this->getStepperIds()
			];
		}
	}

	/**
	 * Special actions to perform with board reset, required by some boards
	 */
	public function resetToDefault()
	{
		// nothing here
	}

	/**
	 * @return null
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param null $group
	 */
	public function setGroup($group): void
	{
		$this->group = $group;
	}

	public function toggleOption(string $optionName)
	{
		$found = false;
		foreach ($this->options as $optionFields)
		{
			if ($optionFields['NAME'] === $optionName)
			{
				$found = true;
				break;
			}
		}

		if (!$found)
		{
			throw new SystemException("Unknown option {$optionName} for the board {$this->boardKey}");
		}

		if (!is_callable($this->setOptionsCallback))
		{
			throw new SystemException("setOptionsCallback is not callable for the board {$this->boardKey}");
		}

		call_user_func($this->setOptionsCallback, $optionName, !$optionFields['VALUE']);
	}

	public function registerSetOptionsCallback(callable $cb)
	{
		$this->setOptionsCallback = $cb;
	}

	public function getOptions() : array
	{
		return $this->options;
	}
}