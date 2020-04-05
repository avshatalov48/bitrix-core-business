<?php
namespace Bitrix\Main\Numerator\Service;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\SequentNumberGenerator;
use Bitrix\Main\Numerator\Model\NumeratorSequenceTable;
use Bitrix\Main\Numerator\Model\NumeratorTable;
use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Result;

/**
 * Class NumeratorRequestManager
 * @package Bitrix\Main\Numerator\Model
 */
class NumeratorRequestManager
{
	/*** @var \Bitrix\Main\HttpRequest */
	private $request;

	/**
	 * NumeratorRequestManager constructor.
	 * @param \Bitrix\Main\HttpRequest $request
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}

	/**
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult|Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveFromRequest()
	{
		$numeratorConfig = $this->request->getPost(Numerator::getType());
		$id = $numeratorConfig ['id'];
		if ($id)
		{
			$result = $this->updateNextSequentialNumber($id);
			if ($result->isSuccess())
			{
				$result = Numerator::update($id, $this->request->getPostList()->toArray());
			}
		}
		else
		{
			$numerator = Numerator::create();
			$result = $numerator->setConfig($this->request->getPostList()->toArray());
			if ($result->isSuccess())
			{
				$result = $numerator->save();
			}
		}
		return $result;
	}

	/**
	 * @param $id
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function updateNextSequentialNumber($id)
	{
		$sequenceConfig = $this->request->getPost(SequentNumberGenerator::getType());
		$result = new Result();
		if ($sequenceConfig !== null && is_array($sequenceConfig)
			&& array_key_exists('nextNumberForSequence', $sequenceConfig) && $sequenceConfig['nextNumberForSequence'])
		{
			$nextNumber = $sequenceConfig['nextNumberForSequence'];
			if (is_numeric($nextNumber))
			{
				$sequence = NumeratorTable::query()
					->registerRuntimeField('',
						new ReferenceField(
							'ref',
							NumeratorSequenceTable::class,
							['=this.ID' => 'ref.NUMERATOR_ID']
						)
					)
					->addSelect(('ID'))
					->addSelect('ref.NEXT_NUMBER', 'NEXT_NUMBER')
					->addSelect('ref.TEXT_KEY', 'TEXT_KEY')
					->where('ID', $id)
					->exec()
					->fetchAll();

				if ($sequence && count($sequence) == 1)
				{
					$numerator = Numerator::load($id);
					if ($numerator)
					{
						$res = $numerator->setNextSequentialNumber($nextNumber, $sequence[0]['NEXT_NUMBER'], $sequence[0]['TEXT_KEY']);
						if (!$res->isSuccess())
						{
							$errors = $res->getErrors();
							return $result->addError($errors[0]);
						}
					}
				}
			}
			else
			{
				return $result->addError(new Error(Loc::getMessage('MAIN_NUMERATOR_EDIT_ERROR_NUMBER_NOT_NUMERIC')));
			}
		}
		return $result;
	}
}
