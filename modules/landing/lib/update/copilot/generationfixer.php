<?php

namespace Bitrix\Landing\Update\Copilot;

use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Model\GenerationsTable;
use Bitrix\Landing\Copilot\Model\StepsTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;

class GenerationFixer
{
	public static function fixImageStepFreeze(): ?string
	{
		$start = microtime(true);

		$query = StepsTable::query()
			->setSelect(['GENERATION_ID'])
			->where('STEP_ID', '=', 70)
			->where('STATUS', '=', 10)
			->where('GENERATION.STEP', '=', 40)
			->registerRuntimeField(
				'',
				new Reference(
					'GENERATION',
					GenerationsTable::getEntity(),
					['=this.GENERATION_ID' => 'ref.ID'],
					['join_type' => 'INNER']
				)
			)
			->exec()
		;

		while ($row = $query->fetch())
		{
			if (microtime(true) - $start > 30)
			{
				return __CLASS__ . '::' . __FUNCTION__ . '();';
			}

			$generation = new Generation();
			$generation->initById((int)$row['GENERATION_ID']);

			$refGen = new \ReflectionObject($generation);
			$propStep = $refGen->getProperty('step');
			$propStep->setAccessible(true);
			$propStep->setValue($generation, 70);

			$propScenarist = $refGen->getProperty('scenarist');
			$propScenarist->setAccessible(true);
			$scenarist = $propScenarist->getValue($generation);

			$refScenarist = new \ReflectionObject($scenarist);
			$propScenaristStep = $refScenarist->getProperty('stepId');
			$propScenaristStep->setAccessible(true);
			$propScenaristStep->setValue($scenarist, 70);

			$generation->execute();
		}

		return '';
	}
}