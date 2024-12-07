<?

use Bitrix\Main\Engine\ActionFilter\Csrf;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CMainUIFilterAjaxController extends \Bitrix\Main\Engine\Controller
{
	protected function getDefaultPreFilters()
	{
		return [
			new Csrf(),
		];
	}

	/**
	 * @return \Bitrix\Main\UI\Filter\Options
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getOptions()
	{
		static $options = null;

		if ($options === null)
		{
			$app = \Bitrix\Main\Application::getInstance();
			$request = $app->getContext()->getRequest();
			$params = $request->getPost('params');
			$options = new \Bitrix\Main\UI\Filter\Options(
				$params['FILTER_ID'], null, $params['commonPresetsId'] ?? null
			);
		}

		return $options;
	}


	/**
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function makeResponse()
	{
		$options = static::getOptions();

		return [
			'currentPresetId' => $options->getCurrentFilterId(),
			'currentPresetData' => $options->getFilterSettings($options->getCurrentFilterId()),
			'defaultPresetId' => $options->getDefaultFilterId()
		];
	}

	/**
	 * Sets filter
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function setFilterAction($data = [])
	{
		$options = static::getOptions();
		$options->setFilterSettings($data['preset_id'], $data);
		$options->save();

		return static::makeResponse();
	}

	/**
	 * Sets array of filters
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function setFilterArrayAction($data = [])
	{
		$options = static::getOptions();
		$options->setFilterSettingsArray($data);
		$options->save();

		return static::makeResponse();
	}

	/**
	 * Restores filter
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function restoreFilterAction($data = [])
	{
		$options = static::getOptions();
		$options->restore($data);
		$options->save();

		return static::makeResponse();
	}

	/**
	 * Removes filter
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function removeFilterAction($data = [])
	{
		$options = static::getOptions();
		$options->deleteFilter($data['preset_id'], $data['is_default']);
		$options->save();

		return static::makeResponse();
	}

	/**
	 * Pins preset
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function pinPresetAction($data = [])
	{
		$options = static::getOptions();
		static::getOptions()->pinPreset($data['preset_id']);
		$options->save();

		return static::makeResponse();
	}

	/**
	 * Sets tmp preset options
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function setTmpPresetAction($data = [])
	{
		$options = static::getOptions();
		$options->setFilterSettings('tmp_filter', $data);
		$options->save();

		return static::makeResponse();
	}

	/**
	 * Checks date format
	 * @param $value
	 * @param $format
	 * @return array
	 */
	public function checkDateFormatAction($value, $format)
	{
		$phpDateFormat = Bitrix\Main\Type\DateTime::convertFormatToPhp($format);

		return [
			"result" => $value === "" || Bitrix\Main\Type\DateTime::isCorrect($value, $phpDateFormat),
		];
	}

	/**
	 * Only for analytics
	 * @return boolean
	 */
	public function limitAnalyticsAction()
	{
		return true;
	}
}