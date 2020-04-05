<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Numerator;

Loc::loadMessages(__FILE__);

/**
 */
class MainNumeratorEditSequence extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
	protected $gridId = 'NUMERATOR_EDIT_SEQUENCE_GRID';
	/**
	 * @var Numerator
	 */
	private $numerator;
	const DELIMITER = '__NUMK__';

	/** @inheritdoc */
	public function executeComponent()
	{
		if ($this->arParams['IS_SLIDER'] || $this->request->get('IFRAME'))
		{
			$this->arResult['IS_SLIDER'] = true;
		}
		$numeratorSequences = [];
		$this->processGridActions();
		if ((isset($this->arParams['NUMERATOR_ID']) && is_numeric($this->arParams['NUMERATOR_ID'])))
		{
			$this->numerator = Numerator::load($this->arParams['NUMERATOR_ID']);
		}
		if (!$this->numerator)
		{
			$this->addError(Loc::getMessage('MAIN_NUMERATOR_EDIT_SEQUENCE_ERROR_NUMERATOR_NOT_FOUND'));
		}
		else
		{
			$numeratorSequences = $this->getNumbers();
		}

		$this->arResult['GRID_ID'] = $this->gridId;

		$this->arResult['FORM_ID'] = isset($this->arParams['FORM_ID']) ? $this->arParams['FORM_ID'] : '';
		$this->arResult['TAB_ID'] = isset($this->arParams['TAB_ID']) ? $this->arParams['TAB_ID'] : '';

		$this->arResult['HEADERS'] = [
			[
				'id' => 'TEXT_KEY',
				'name' => Loc::getMessage('NUMERATOR_EDIT_SEQUENCE_COLUMN_HEADER_TEXT_KEY'),
				'default' => true,
			],
			[
				'id' => 'NEXT_NUMBER',
				'name' => Loc::getMessage('NUMERATOR_EDIT_SEQUENCE_COLUMN_HEADER_NEXT_NUMBER'),
				'default' => true,
				'editable' => [
					'TYPE' => \Bitrix\Main\Grid\Editor\Types::TEXT,
				],
			],
		];

		$gridOptions = new CGridOptions($this->arResult['GRID_ID']);
		$gridSorting = $gridOptions->GetSorting([]);
		$this->arResult['SORT'] = $gridSorting['sort'];
		$this->arResult['SORT_VARS'] = $gridSorting['vars'];

		$items = [];

		$count = 0;
		foreach ($numeratorSequences as $index => $numerator)
		{
			$fields['~TEXT_KEY'] = $numerator['TEXT_KEY'];
			$fields['TEXT_KEY'] = htmlspecialcharsbx($numerator['TEXT_KEY']);

			$fields['~NEXT_NUMBER'] = $numerator['NEXT_NUMBER'];
			$fields['NEXT_NUMBER'] = htmlspecialcharsbx($numerator['NEXT_NUMBER']);

			$fields['~ID'] = $numerator['TEXT_KEY'] . self::DELIMITER . $numerator['NUMERATOR_ID'] . self::DELIMITER . $numerator['NEXT_NUMBER'];
			$fields['ID'] = htmlspecialcharsbx($numerator['TEXT_KEY'] . self::DELIMITER . $numerator['NUMERATOR_ID'] . self::DELIMITER . $numerator['NEXT_NUMBER']);

			$items[] = $fields;
			$count++;
		}
		$this->arResult['ROWS_COUNT'] = $count;

		$this->arResult['ITEMS'] = &$items;
		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getNumbers()
	{
		$config = $this->numerator->getConfig();
		$numResults = \Bitrix\Main\Numerator\Model\NumeratorSequenceTable::getList([
			'filter' => ['=NUMERATOR_ID' => $config[Numerator::getType()]['id']],
			'select' => ['NUMERATOR_ID', 'TEXT_KEY', 'NEXT_NUMBER', 'KEY'],
		])
			->fetchAll();
		return $numResults;
	}


	/**
	 * @param $errorMessage
	 */
	private function addError($errorMessage)
	{
		$this->arResult['MESSAGES'][] = [
			'TYPE' => \Bitrix\Main\Grid\MessageType::ERROR,
			'TITLE' => Loc::getMessage('MAIN_NUMERATOR_EDIT_SEQUENCE_ERROR_TITLE'),
			'TEXT' => $errorMessage,
		];
	}

	private function processEdit()
	{
		if (empty($this->request->getPost('FIELDS')))
		{
			return;
		}
		foreach ($this->request->getPost('FIELDS') as $compoundKeyString => $sourceFields)
		{
			if (!(isset($sourceFields['NEXT_NUMBER'])
				  && $sourceFields['NEXT_NUMBER'] !== '' && is_numeric($sourceFields['NEXT_NUMBER']))
			)
			{
				$this->addError(Loc::getMessage('MAIN_NUMERATOR_EDIT_SEQUENCE_ERROR_NUMBER_NOT_NUMERIC'));
				continue;
			}
			if (stristr($compoundKeyString, self::DELIMITER) === false)
			{
				continue;
			}
			$compoundKey = explode(self::DELIMITER, $compoundKeyString);
			if (count($compoundKey) < 3)
			{
				continue;
			}
			$hash = $compoundKey[0];
			$numId = $compoundKey[1];
			if (!($numId && is_numeric($numId)))
			{
				continue;
			}
			$numerator = Numerator::load($numId);
			if (!$numerator)
			{
				continue;
			}

			$dbNextNumber = $compoundKey[2];
			if ($dbNextNumber && is_numeric($dbNextNumber))
			{
				if ((int)$sourceFields['NEXT_NUMBER'] <= (int)$dbNextNumber)
				{
					$this->addError(Loc::getMessage('MAIN_NUMERATOR_EDIT_SEQUENCE_ERROR_NUMBER_LESS'));
				}
				else
				{
					$res = $numerator->setNextSequentialNumber($sourceFields['NEXT_NUMBER'], $dbNextNumber, $hash);
					if (!$res->isSuccess())
					{
						$errors = $res->getErrors();
						$error = $errors[0];
						$this->addError($error->getMessage());
					}
				}
			}
		}
	}

	/**
	 */
	private function processGridActions()
	{
		$postAction = 'action_button_' . $this->gridId;
		if ($this->request->getPost($postAction) !== null && check_bitrix_sessid())
		{
			if ($this->request->getPost($postAction) == 'edit')
			{
				$this->processEdit();
			}
		}
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}
}