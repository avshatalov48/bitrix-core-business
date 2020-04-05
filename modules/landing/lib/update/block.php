<?php
namespace Bitrix\Landing\Update;

use \Bitrix\Landing\Block as BlockCore;
use \Bitrix\Landing\UpdateBlock;
use \Bitrix\Landing\Internals\BlockTable;

class Block extends \Bitrix\Main\Update\Stepper
{
	/**
	 * Items count for one step.
	 */
	const STEPPER_COUNT = 1;

	/**
	 * Target module for stepper.
	 * @var string
	 */
	protected static $moduleId = 'landing';

	/**
	 * Register new block for stepper update.
	 * @param string[] $codes Block codes.
	 * @param array $params Additional params.
	 * @return void
	 */
	public static function register($codes, array $params = [])
	{
		if (!is_array($codes))
		{
			$codes = [$codes];
		}
		
		$res = UpdateBlock::getList([
			'select' => [
				'ID', 'CODE'
			],
			'filter' => [
				'=CODE' => $codes
			]
		]);
		while ($row = $res->fetch())
		{
			UpdateBlock::update($row['ID'], [
				'LAST_BLOCK_ID' => 0,
				'PARAMS' => $params
			]);
			$codes = array_diff($codes, [$row['CODE']]);
		}
		
		if (!empty($codes))
		{
			foreach ($codes as $code)
			{
				UpdateBlock::add([
					'CODE' => $code,
					'PARAMS' => $params
				]);
			}
		}

		// reg stepper
		\Bitrix\Main\Update\Stepper::bindClass(
			'\Bitrix\Landing\Update\Block', 'landing', 60
		);
	}

	/**
	 * Unregister block for stepper update.
	 * @param string[] $codes Block codes.
	 * @return void
	 */
	public static function unregister($codes)
	{
		if (!is_array($codes))
		{
			$codes = [$codes];
		}
		
		$res = UpdateBlock::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=CODE' => $codes
			]
		]);
		while ($row = $res->fetch())
		{
			UpdateBlock::delete($row['ID']);
		}
	}

	/**
	 * Execute one step.
	 * @param array $filter Filter for step.
	 * @param int $count Updated count.
	 * @param int $limit Select limit.
	 * @param array Additional params.
	 * @return int Last updated id.
	 */
	public static function executeStep(array $filter, &$count = 0, $limit = 0, array $params = [])
	{
		$lastId = 0;

		$res = BlockTable::getList([
			'select' => [
				'ID', 'CONTENT'
			],
			'filter' => $filter,
			'order' => [
				'ID' => 'asc'
			],
			'limit' => $limit
		]);
		while ($row = $res->fetch())
		{
			$lastId = $row['ID'];
			$count++;

			// gets content from exist block
			$block = new BlockCore($row['ID']);
			$export = $block->export([
				'clear_form' => false
			]);

			// and apply to the new layout
			$newContent = BlockCore::getContentFromRepository(
				$block->getCode()
			);
			$block->saveContent($newContent);
			// update cards
			if ($export['cards'])
			{
				$block->updateCards(
					$export['cards']
				);
			}
			// update style
			if ($export['style'])
			{
				$updatedStyles = [];
				foreach ($export['style'] as $selector => $classes)
				{
					$classes = (array) $classes;
					if ($selector == '#wrapper')
					{
						$selector = '#' . $block->getAnchor($block->getId());
					}
					
					$addClasses = '';
					if (isset($params[$selector]['new_class']))
					{
						$addClasses = $params[$selector]['new_class'];
						if (!is_array($addClasses))
						{
							$addClasses = explode(' ', trim($addClasses));
						}
					}
					
					$removeClasses = '';
					if (isset($params[$selector]['remove_class']))
					{
						$removeClasses = $params[$selector]['remove_class'];
						if (!is_array($removeClasses))
						{
							$removeClasses = explode(' ', trim($removeClasses));
						}
					}
					
					foreach ($classes as $clPos => $clVal)
					{
//						changes by params
						if ($addClasses || $removeClasses)
						{
							$clVal = explode(' ', $clVal);
							
							if($addClasses)
							{
								$clVal = array_merge($clVal, $addClasses);
							}
							if($removeClasses)
							{
								$clVal = array_diff($clVal, $removeClasses);
							}
							$clVal = implode(' ', array_unique($clVal));
						}
						
						$selectorUpd = $selector . '@' . $clPos;
						if (!in_array($selectorUpd, $updatedStyles))
						{
							$updatedStyles[] = $selectorUpd;
							$block->setClasses(array(
								$selectorUpd => array(
									'classList' => (array) $clVal
								)
							));
						}
					}
				}
			}
			// update nodes
			if ($export['nodes'])
			{
				$block->updateNodes(
					$export['nodes']
				);
			}
			// update attrs
			if ($export['attrs'])
			{
				if (isset($export['attrs']['#wrapper']))
				{
					$wrapperCode = '#' . $block->getAnchor($block->getId());
					$export['attrs'][$wrapperCode] = $export['attrs']['#wrapper'];
					unset($export['attrs']['#wrapper']);
				}
				$block->setAttributes(
					$export['attrs']
				);
			}

			// and save block with new layout and old content
			$block->save();
		}

		return $lastId;
	}

	/**
	 * One step of stepper.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$finished = true;

		// check queue
		$res = UpdateBlock::getList([
			'select' => [
				'ID', 'CODE', 'LAST_BLOCK_ID', 'PARAMS'
			],
			'order' => [
				'ID' => 'asc'
			],
			'limit' => 1
		]);
		if (!($rowUpdate = $res->fetch()))
		{
			return false;
		}

		// gets common quantity
		$res = BlockTable::getList([
				'select' => [
					new \Bitrix\Main\Entity\ExpressionField(
						'CNT', 'COUNT(*)'
					)
				],
				'filter' => [
					'=CODE' => $rowUpdate['CODE']
				]
			]
		);
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}
		else
		{
			UpdateBlock::delete(
				$rowUpdate['ID']
			);
			return $this->execute($result);
		}

		// gets finished count
		$res = BlockTable::getList([
			'select' => [
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			],
			'filter' => [
				'<=ID' => $rowUpdate['LAST_BLOCK_ID'],
				'=CODE' => $rowUpdate['CODE']
			]
			]
		);
		if ($row = $res->fetch())
		{
			$result['steps'] = $row['CNT'];
		}

		// gets block group for update
		$lastId = $this::executeStep([
				'>ID' => $rowUpdate['LAST_BLOCK_ID'],
				'=CODE' => $rowUpdate['CODE']
			],
		 	$count,
		 	$this::STEPPER_COUNT,
		 	$rowUpdate['PARAMS']
		);
		if ($lastId > 0)
		{
			$finished = false;
		}

		// finish or continue
		if (!$finished)
		{
			UpdateBlock::update($rowUpdate['ID'], [
				'LAST_BLOCK_ID' => $lastId
			]);
			return true;
		}
		else
		{
			UpdateBlock::delete($rowUpdate['ID']);
			return true;//for check next item in UpdateBlock
		}
	}
}