<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die;
}

use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Ui\EntityForm\FormConfigData;
use Bitrix\Ui\EntityForm\Scope;
use Bitrix\Ui\EntityForm\ScopeAccess;

Extension::load(['ui.icons']);

/**
 * Class UiFormConfig
 */
class UiFormConfig extends CBitrixComponent
{
	protected string $navParamName = 'page';
	protected array $defaultGridSort = ['ID' => 'desc'];

	public function executeComponent()
	{
		if (!Loader::includeModule('ui'))
		{
			return;
		}

		$request = $this->request;

		if ($request->isPost() && check_bitrix_sessid())
		{
			$moduleId = $request->get('MODULE_ID');
			$scopeAccess = ScopeAccess::getInstance($moduleId);

			if ($request->getPost('action_button_editor_scopes') === 'edit')
			{
				foreach ($request->getPost('FIELDS') as $scopeId => $row)
				{
					if (!$scopeAccess->canUpdate($scopeId))
					{
						continue;
					}

					$fields = [];
					if (!empty($row['NAME']))
					{
						$fields['NAME'] = $row['NAME'];
					}
					if (!empty($row['AUTO_APPLY_SCOPE']))
					{
						$fields['AUTO_APPLY_SCOPE'] = ($row['AUTO_APPLY_SCOPE'] === 'Y') ? 'Y' : 'N';
					}
					if (!empty($row['ON_ADD']))
					{
						$fields['ON_ADD'] = ($row['ON_ADD'] === 'Y') ? 'Y' : 'N';
					}
					if (!empty($row['ON_UPDATE']))
					{
						$fields['ON_UPDATE'] = ($row['ON_UPDATE'] === 'Y') ? 'Y' : 'N';
					}

					Scope::getInstance()->updateScope($scopeId, $fields);
				}
			}
			elseif ($request->getPost('action_button_editor_scopes') === 'delete')
			{
				$scopeId = $request->getPost('ID');
				if ($scopeAccess->canDelete($scopeId))
				{
					Scope::getInstance()->removeByIds($scopeId);
				}
			}
		}

		$data = FormConfigData::getInstance(
			$this->navParamName,
			preg_replace('/\W/', '', $this->arParams['MODULE_ID']),
			preg_replace('/\W/', '', $this->arParams['ENTITY_TYPE_ID']),
		)->prepare();

		$this->arResult['grid'] = $data['grid'];

		$GLOBALS['APPLICATION']->setTitle(Loc::getMessage('UI_FORM_CONFIG_TITLE'));

		$this->includeComponentTemplate();
	}

	/**
	 * @return Onchange
	 */
	protected function getOnChange(): Onchange
	{
		$onchange = new Onchange();

		$onchange->addAction(
			[
				'ACTION' => Actions::CALLBACK,
				'CONFIRM' => false,
				'DATA' => [
					['JS' => 'Grid.editSelectedSave()'],
				],
			],
		);

		return $onchange;
	}
}
