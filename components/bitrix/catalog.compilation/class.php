<?

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\Component\ElementList;
use Bitrix\Crm\Timeline;
use Bitrix\SalesCenter\Integration\CatalogManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CIntranetToolbar $INTRANET_TOOLBAR
 */

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class CatalogCompilationComponent extends ElementList
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(false)->setMultiIblockMode(false)->setPaginationMode(true);
		$this->setSeparateLoading(true);
		$this->setCompilationData();
	}

	private function setCompilationData(): void
	{
		$this->arResult['IS_COMPILATION_EXISTS'] = false;

		if (!Loader::includeModule('salescenter'))
		{
			return;
		}

		$compilationProducts = [];
		$compilationHashString = \Bitrix\Main\Context::getCurrent()->getRequest()->get('compilationId');
		if ($compilationHashString)
		{
			$decodeCompilationIdResult = CatalogManager::getInstance()->decodeCompilationId($compilationHashString);
			if (!$decodeCompilationIdResult->isSuccess())
			{
				return;
			}

			$compilationId = $decodeCompilationIdResult->getData()['COMPILATION_ID'];
			$compilation = CatalogManager::getInstance()->getCompilationById($compilationId);
			if (!$compilation)
			{
				return;
			}
			$this->arResult['IS_COMPILATION_EXISTS'] = true;

			$compilationProducts = $compilation['PRODUCT_IDS'];
			$this->addViewedCompilationStateToTimeline($compilation);
			if ($compilation['DEAL_ID'])
			{
				$dealId = (int)$compilation['DEAL_ID'];
				$chatId = $compilation['CHAT_ID'] ? (int)$compilation['CHAT_ID'] : null;
				$session = Main\Application::getInstance()->getSession();
				$session->set(
					'CATALOG_CURRENT_COMPILATION_DATA',
					[
						'DEAL_ID' => $dealId,
						'CHAT_ID' => $chatId,
					]
				);
			}
		}

		if (empty($this->globalFilter) && !empty($compilationProducts))
		{
			$compilationProducts = static::getProductsMap($compilationProducts);
			if (!empty($compilationProducts))
			{
				$this->globalFilter = [
					'ID' => array_unique(array_values($compilationProducts))
				];
			}
		}
	}

	protected function addViewedCompilationStateToTimeline($productCompilation): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		$timelineParams = [
			'SETTINGS' => [
				'DEAL_ID' => (int)$productCompilation['DEAL_ID'],
				'COMPILATION_ID' => (int)$productCompilation['ID'],
				'COMPILATION_CREATION_DATE' => $productCompilation['CREATION_DATE'],
			]
		];

		Timeline\ProductCompilationController::getInstance()->onCompilationViewed(
			$productCompilation['DEAL_ID'],
			$timelineParams
		);
	}

	public function executeComponent()
	{
		if (empty($this->globalFilter))
		{
			$this->includeComponentTemplate();

			return false;
		}

		return parent::executeComponent();
	}
}
