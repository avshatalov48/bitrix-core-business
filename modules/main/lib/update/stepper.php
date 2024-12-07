<?php

namespace Bitrix\Main\Update;

use Bitrix\Main;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

/**
 * Class Stepper
 * @package Bitrix\Main\Update
 * This class can be used if only you do not alter tables in DB. Agent will not be executed if module is not installed.
 * @example Code to bind agent in updater:
 * \Bitrix\Main\Update\Stepper::bindClass('Bitrix\Tasks\Update1701', 'tasks');
 * or
 * if ($updater->CanUpdateDatabase())
 * {
 *        $basePath = $updater->CanUpdateKernel() ? $updater->curModulePath.'/lib/somepath' : BX_ROOT.'/modules/lists/lib/somepath';
 *        if(include_once($_SERVER["DOCUMENT_ROOT"].$basePath."ecrmpropertyupdate.php"))
 *        {
 *            \Bitrix\Lists\SomePath\EcrmPropertyUpdate::bind();
 *        }
 * }
 */
abstract class Stepper
{
	const CONTINUE_EXECUTION = true;
	const FINISH_EXECUTION = false;
	const THRESHOLD_TIME = 20.0;
	const DELAY_COEFFICIENT = 0.5;

	protected static $moduleId = "main";
	private static $countId = 0;

	protected $outerParams = [];

	/**
	 * Returns HTML to show updates.
	 * @param array|string $ids
	 * @param string $title
	 * @return string
	 */
	public static function getHtml($ids = [], $title = "")
	{
		if (static::class !== __CLASS__)
		{
			$title = static::getTitle();
			$ids = [static::$moduleId => [static::class]];
			return call_user_func([__CLASS__, "getHtml"], $ids, $title);
		}

		$return = [];
		$count = 0;
		$steps = 0;

		if (is_string($ids))
		{
			$ids = [$ids => null];
		}

		foreach ($ids as $moduleId => $classesId)
		{
			$options = [];
			$category = "main.stepper." . $moduleId;

			if (is_string($classesId))
			{
				$classesId = [$classesId];
			}
			if (is_array($classesId))
			{
				foreach ($classesId as $classId)
				{
					if (($option = Option::get($category, $classId)) !== "")
					{
						$options[$classId] = $option;
					}
				}
			}
			elseif (is_null($classesId))
			{
				$options = Option::getForModule($category);
			}

			foreach ($options as $classId => $option)
			{
				$option = unserialize($option, ['allowed_classes' => false]);
				if (is_array($option))
				{
					$return[] = [
						"moduleId" => $moduleId,
						"class" => $classId,
						"title" => $option["title"],
						"steps" => $option["steps"],
						"count" => $option["count"],
					];
					$count += $option["count"];
					$steps += ($option["count"] > $option["steps"] ? $option["steps"] : $option["count"]);
				}
			}
		}

		$result = '';
		if (!empty($return))
		{
			$id = ++self::$countId;
			\CJSCore::Init(['update_stepper']);
			$title = empty($title) ? self::getTitle() : $title;
			$progress = $count > 0 ? intval($steps * 100 / $count) : 0;
			$result .= <<<HTML
<div class="main-stepper main-stepper-show" id="{$id}-container" data-bx-steps-count="{$count}">
	<div class="main-stepper-info" id="{$id}-title">{$title}</div>
	<div class="main-stepper-inner">
		<div class="main-stepper-bar">
			<div class="main-stepper-bar-line" id="{$id}-bar" style="width:{$progress}%;"></div>
		</div>
		<div class="main-stepper-steps"><span id="{$id}-steps">{$steps}</span> / <span id="{$id}-count">{$count}</span></div>
		<div class="main-stepper-error-text" id="{$id}-error"></div>
	</div>
</div>
HTML;
			$return = Json::encode($return);
			$result = <<<HTML
<div class="main-stepper-block">{$result}
<script>BX.ready(function(){ if (BX && BX["UpdateStepperRegister"]) { BX.UpdateStepperRegister({$id}, {$return}); }});</script>
</div>
HTML;
		}
		return $result;
	}

	public static function getTitle()
	{
		return Loc::getMessage("STEPPER_TITLE");
	}

	/**
	 * Executes an agent.
	 * @return string
	 */
	public static function execAgent()
	{
		global $pPERIOD;

		$updater = self::createInstance();
		$className = get_class($updater);

		$option = Option::get("main.stepper." . $updater->getModuleId(), $className);
		if ($option !== "")
		{
			$option = unserialize($option, ['allowed_classes' => false]);
		}
		$option = is_array($option) ? $option : [];

		$updater->setOuterParams(func_get_args());

		$startTime = microtime(true);

		if ($updater->execute($option) === self::CONTINUE_EXECUTION)
		{
			$executeTime = microtime(true) - $startTime;
			$threshold = (float)($option["thresholdTime"] ?? self::THRESHOLD_TIME);

			if ($executeTime > $threshold)
			{
				// delaying next execution time proportionally to the last execution time
				$delayCoefficient = (float)($option["delayCoefficient"] ?? self::DELAY_COEFFICIENT);

				/** @see main/classes/general/agent.php:498 */
				$pPERIOD = (int)round($executeTime + $executeTime * $delayCoefficient);
			}

			$option["steps"] = (int)($option["steps"] ?? 0);
			$option["count"] = (int)($option["count"] ?? 0);
			$option["lastTime"] = $executeTime;
			$option["totalTime"] = (float)($option["totalTime"] ?? 0.0) + $executeTime;
			$option["title"] = $updater::getTitle();

			Option::set("main.stepper." . $updater->getModuleId(), $className, serialize($option));

			return $className . '::execAgent(' . $updater::makeArguments($updater->getOuterParams()) . ');';
		}

		Option::delete('main.stepper.' . $updater->getModuleId(), ['name' => $className]);
		Option::delete('main.stepper.' . $updater->getModuleId(), ['name' => '\\' . $className]);

		return '';
	}

	/**
	 * @deprecated Does nothing.
	 */
	public function __destruct()
	{
	}

	/**
	 * Executes some action, and if return value is false, agent will be deleted.
	 * @param array $option Array with main data to show if it is necessary like {steps : 35, count : 7}, where steps is an amount of iterations, count - current position.
	 * @return boolean
	 */
	abstract function execute(array &$option);

	public function setOuterParams(array $outerParams): void
	{
		$this->outerParams = $outerParams;
	}

	public function getOuterParams(): array
	{
		return $this->outerParams;
	}

	/**
	 * It is possible to pass only integer and string values for now. But you can make your own method or extend this one.
	 * @param array $arguments
	 * @return string
	 */
	public static function makeArguments($arguments = []): string
	{
		if (is_array($arguments))
		{
			foreach ($arguments as $key => $val)
			{
				if (is_string($val))
				{
					$arguments[$key] = "'" . EscapePHPString($val, "'") . "'";
				}
				else
				{
					$arguments[$key] = intval($val);
				}
			}
			return implode(", ", $arguments);
		}
		return "";
	}

	/**
	 * Just a fabric method.
	 * @return Stepper
	 */
	public static function createInstance()
	{
		return new static;
	}

	/**
	 * Wrap-function to get moduleId.
	 * @return string
	 */
	public static function getModuleId()
	{
		return static::$moduleId;
	}

	/**
	 * Adds agent for current class.
	 * @param int $delay Delay for running agent
	 * @param array $withArguments Data that will available in $stepper->outerParams
	 * @return void
	 */
	public static function bind($delay = 300, $withArguments = [])
	{
		/** @var Stepper | string $c */
		$c = get_called_class();
		self::bindClass($c, $c::getModuleId(), $delay, $withArguments);
	}

	/**
	 * Adds agent for class $className for $moduleId module. Example for updater: \Bitrix\Main\Stepper::bindClass('\Bitrix\SomeModule\SomeClass', 'somemodule').
	 * @param string $className Class like \Bitrix\SomeModule\SomeClass extends Stepper.
	 * @param string $moduleId Module ID like somemodule.
	 * @param int $delay Delay for running agent
	 * @param array $withArguments
	 * @return void
	 */
	public static function bindClass($className, $moduleId, $delay = 300, $withArguments = [])
	{
		if (class_exists("\CAgent"))
		{
			$addAgent = true;
			$withArguments = is_array($withArguments) ? $withArguments : [];

			$delay = (int)$delay;
			if ($delay <= 0)
			{
				/** @var Stepper $className */
				$addAgent = ('' !== call_user_func_array([$className, "execAgent"], $withArguments));
			}

			if ($addAgent)
			{
				if (Option::get("main.stepper." . $moduleId, $className) === "")
				{
					Option::set("main.stepper." . $moduleId, $className, serialize([]));
				}
				\CTimeZone::Disable();
				\CAgent::AddAgent(
					$className . '::execAgent(' . (empty($withArguments) ? '' : call_user_func_array([$className, "makeArguments"], [$withArguments])) . ');',
					$moduleId,
					"Y",
					1,
					"",
					"Y",
					date(Main\Type\Date::convertFormatToPhp(\CSite::GetDateFormat()), time() + $delay),
					100,
					false,
					false
				);
				\CTimeZone::Enable();
			}
		}
		else
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$arguments = '';
			if (!empty($withArguments))
			{
				$arguments = class_exists($className)
					? call_user_func_array([$className, "makeArguments"], [$withArguments])
					: self::makeArguments($withArguments);
			}
			$name = $helper->forSql($className . '::execAgent(' . $arguments . ');', 2000);
			$className = $helper->forSql($className);
			$moduleId = $helper->forSql($moduleId);
			$agent = $connection->query("SELECT ID FROM b_agent WHERE MODULE_ID='" . $moduleId . "' AND NAME = '" . $name . "' AND USER_ID IS NULL")->fetch();
			if (!$agent)
			{
				$connection->query(
					"INSERT INTO b_agent (MODULE_ID, SORT, NAME, ACTIVE, AGENT_INTERVAL, IS_PERIOD, NEXT_EXEC) 
					VALUES ('" . $moduleId . "', 100, '" . $name . "', 'Y', 1, 'Y', " . ($delay > 0 ? $helper->addSecondsToDateTime($delay) : $helper->getCurrentDateTimeFunction()) . ")"
				);
				$merge = $helper->prepareMerge(
					'b_option',
					['MODULE_ID', 'NAME'],
					[
						'MODULE_ID' => 'main.stepper.' . $moduleId,
						'NAME' => $className,
						'VALUE' => 'a:0:{}',
					],
					[
						'VALUE' => 'a:0:{}',
					]
				);
				if ($merge)
				{
					$connection->Query($merge[0]);
				}
			}
		}
	}

	/**
	 * Just method to check request.
	 * @return void
	 */
	public static function checkRequest()
	{
		$result = [];
		$data = Context::getCurrent()->getRequest()->getPost("stepper");
		if (is_array($data))
		{
			foreach ($data as $stepper)
			{
				if (($option = Option::get("main.stepper." . $stepper["moduleId"], $stepper["class"])) !== "" &&
					($res = unserialize($option, ['allowed_classes' => false])) && is_array($res))
				{
					$r = [
						"moduleId" => $stepper["moduleId"],
						"class" => $stepper["class"],
						"steps" => $res["steps"],
						"count" => $res["count"],
					];
					$result[] = $r;
				}
			}
		}
		self::sendJson($result);
	}

	/**
	 * Sends json.
	 * @param $result
	 * @return void
	 */
	private static function sendJson($result)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		header('Content-Type:application/json; charset=UTF-8');

		echo Json::encode($result);
		\CMain::finalActions();
		die;
	}

	protected function writeToLog(\Exception $exception)
	{
		$application = HttpApplication::getInstance();
		$exceptionHandler = $application->getExceptionHandler();
		$exceptionHandler->writeToLog($exception);
	}
}
