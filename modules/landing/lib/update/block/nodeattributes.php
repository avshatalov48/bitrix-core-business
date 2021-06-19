<?php
namespace Bitrix\Landing\Update\Block;


use Bitrix\Landing\Manager;
use Bitrix\Landing\Subtype\Form;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;
use Bitrix\Landing\Block;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\DOM\Element;

final class NodeAttributes extends Stepper
{
	const CONTINUE_EXECUTING = true;
	const STOP_EXECUTING = false;
	const OPTION_NAME = 'blocks_attrs_update';
	const OPTION_STATUS_NAME = 'blocks_attrs_update_status';
	const STEP_PORTION = 1;    //count of block CODES to step
	
	protected static $moduleId = 'landing';
	protected $dataToUpdate = array();
	protected $blocksToUpdate = array();
	protected $sitesToUpdate = array();
	protected $status = array();
	protected $codesToStep = array();
	
	/**
	 * get progress from option or set default
	 */
	private function loadCurrentStatus()
	{
//		saved in option
		$this->status = Option::get('landing', self::OPTION_STATUS_NAME, '');
		$this->status = ($this->status !== '' ? @unserialize($this->status, ['allowed_classes' => false]) : array());
		$this->status = (is_array($this->status) ? $this->status : array());

//		or default
		if (empty($this->status))
		{
//			get codes from all updaters options
			$count = 0;
			$params = array();
			foreach (Option::getForModule('landing') as $key => $option)
			{
				if (mb_strpos($key, self::OPTION_NAME) === 0 && $key != self::OPTION_STATUS_NAME)
				{
					$option = ($option !== '' ? @unserialize($option, ['allowed_classes' => false]) : array());
					
					if(!isset($option['BLOCKS']))
					{
						Option::delete('landing', array('name' => $key));
						continue;
					}

//					save params
					$params[$key] = $option['PARAMS'];
//					count of all blocks - to progress-bar
					$filter = array(
						'CODE' => array_keys($option['BLOCKS']),
						'DELETED' => 'N',
					);
					if (
						isset($option['PARAMS']['UPDATE_PUBLISHED_SITES']) &&
						$option['PARAMS']['UPDATE_PUBLISHED_SITES'] != 'Y'
					)
					{
						$filter['PUBLIC'] = 'N';
					}
					
					$res = BlockTable::getList(array(
						'select' => array(
							new \Bitrix\Main\Entity\ExpressionField(
								'CNT', 'COUNT(*)'
							),
						),
						'filter' => $filter,
					));
					if ($row = $res->fetch())
					{
						$count += $row['CNT'];
					}
				}
			}
			
			$this->status['COUNT'] = $count;
			$this->status['STEPS'] = 0;
			$this->status['SITES_TO_UPDATE'] = array();
			$this->status['UPDATER_ID'] = '';
			$this->status['PARAMS'] = $params;
			
			Option::set('landing', self::OPTION_STATUS_NAME, serialize($this->status));
		}
	}
	
	/**
	 * May be several Options for update data. They have uniqueId. Find ID of first option
	 *
	 * @return string
	 */
	private function getUpdaterUniqueId()
	{
//		continue processing current updater
		if ($this->status['UPDATER_ID'] !== '')
		{
			return $this->status['UPDATER_ID'];
		}
		
		$updaterOptions = Option::getForModule('landing');
		$allOptions = preg_grep('/' . self::OPTION_NAME . '.+/', array_keys($updaterOptions));
		$allOptions = array_diff($allOptions, array(self::OPTION_STATUS_NAME));    // remove status option from list
		sort($allOptions);
		
		if (!empty($allOptions))
		{
			return str_replace(self::OPTION_NAME, '', $allOptions[0]);
		}
		else
		{
			return '';
		}
	}
	
	
	public function execute(array &$result)
	{
//		nothing to update
		$this->loadCurrentStatus();

		if (!$this->status['COUNT'])
		{
			self::finish();
			
			return self::STOP_EXECUTING;
		}

//		find option. If nothing - we update all
		$this->status['UPDATER_ID'] = $this->getUpdaterUniqueId();

		if (!$this->status['UPDATER_ID'])
		{
			self::finish();
			
			return self::STOP_EXECUTING;
		}
		
		$this->processBlocks();

//		was processing all data for current option
		if (!is_array($this->dataToUpdate['BLOCKS']) || empty($this->dataToUpdate['BLOCKS']))
		{
			$this->finishOption();
		}

		$result['count'] = $this->status['COUNT'];
		$result['steps'] = $this->status['STEPS'];
		
		return self::CONTINUE_EXECUTING;
	}
	
	
	/**
	 * Additional operations before stop executing
	 */
	private static function finish()
	{
		self::clearOptions();
		self::removeCustomEvents();
	}
	
	
	/**
	 * If no more blocks to update - remove all data options and status
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private static function clearOptions()
	{
		foreach (Option::getForModule('landing') as $key => $option)
		{
			if (mb_strpos($key, self::OPTION_NAME) === 0)
			{
				Option::delete('landing', array('name' => $key));
			}
		}
	}
	
	
	/**
	 * Create option name by base name and unique ID
	 * @return string
	 */
	private function getOptionName()
	{
		return self::OPTION_NAME . $this->status['UPDATER_ID'];
	}
	
	
	private function collectBlocks()
	{
		$this->dataToUpdate = Option::get(self::$moduleId, $this->getOptionName());
		$this->dataToUpdate = ($this->dataToUpdate !== '' ? @unserialize($this->dataToUpdate, ['allowed_classes' => false]) : array());
		$this->codesToStep = array_unique(array_keys($this->dataToUpdate['BLOCKS']));
		$this->codesToStep = array_slice($this->codesToStep, 0, self::STEP_PORTION);

//		load BLOCKS
		$filter = array(
			'CODE' => $this->codesToStep,
			'DELETED' => 'N',
		);
		if (
			isset($this->status['PARAMS'][$this->getOptionName()]['UPDATE_PUBLISHED_SITES']) &&
			$this->status['PARAMS'][$this->getOptionName()]['UPDATE_PUBLISHED_SITES'] != 'Y'
		)
		{
			$filter['PUBLIC'] = 'N';
		}
		
		$resBlock = BlockTable::getList(array(
			'filter' => $filter,
			'select' => array(
				'ID',
				'SORT',
				'CODE',
				'ACTIVE',
				'PUBLIC',
				'DELETED',
				'CONTENT',
				'LID',
				'SITE_ID' => 'LANDING.SITE_ID',
			),
			'order' => array(
				'CODE' => 'ASC',
				'ID' => 'ASC',
			),
		));
		
		while ($row = $resBlock->fetch())
		{
			$this->blocksToUpdate[$row['CODE']][$row['ID']] = new Block($row['ID'], $row);
			if (count($this->blocksToUpdate) > self::STEP_PORTION)
			{
				unset($this->blocksToUpdate[$row['CODE']]);
				break;
			}

//			save sites ID for current blocks to reset cache later
			$this->sitesToUpdate[$row['ID']] = $row['SITE_ID'];
		}
	}

	
	private function processBlocks()
	{
		$this->collectBlocks();
		
		foreach ($this->blocksToUpdate as $code => $blocks)
		{
			foreach ($blocks as $block)
			{
				if (is_array($this->dataToUpdate['BLOCKS'][$code]) && !empty($this->dataToUpdate['BLOCKS'][$code]))
				{
					$this->updateBlock($block);

//					after processing block save site ID to update cache later (only if update needed)
					if (
						isset($this->status['PARAMS'][$this->getOptionName()]['UPDATE_PUBLISHED_SITES']) &&
						$this->status['PARAMS'][$this->getOptionName()]['UPDATE_PUBLISHED_SITES'] == 'Y'
					)
					{
						$this->status['SITES_TO_UPDATE'][] = $this->sitesToUpdate[$block->getId()];
					}
				}
				
				$this->status['STEPS']++;
			}
		}
		
		$this->finishStep();
	}
	
	private function updateBlock(Block $block)
	{
		$code = $block->getCode();
		$doc = $block->getDom();

		foreach ($this->dataToUpdate['BLOCKS'][$code]['NODES'] as $selector => $rules)
		{
			$resultList = $doc->querySelectorAll($selector);

//			prepare ATTRS
			$nodeAttrs = array();
			if (is_array($rules['ATTRS_ADD']) && !empty($rules['ATTRS_ADD']))
			{
				$nodeAttrs = array_merge($nodeAttrs, $rules['ATTRS_ADD']);
			}
			if (is_array($rules['ATTRS_REMOVE']) && !empty($rules['ATTRS_REMOVE']))
			{
				$nodeAttrs = array_merge($nodeAttrs, array_fill_keys(array_values($rules['ATTRS_REMOVE']), ''));
			}

//			PROCESS
			foreach ($resultList as $nth => $resultNode)
			{
//				FILTER
//				use until cant add some filters in DOM\Parser
				if (is_array($rules['FILTER']) && !empty($rules['FILTER']))
				{
					$notFilterd = false;
//					By content. May have 'NOT' key
					if (
						isset($rules['FILTER']['CONTENT']) && is_array($rules['FILTER']['CONTENT']) &&
						(
							$rules['FILTER']['CONTENT']['VALUE'] != $resultNode->getInnerHTML() ||
							(
								$rules['FILTER']['CONTENT']['NOT'] &&
								$rules['FILTER']['CONTENT']['VALUE'] == $resultNode->getInnerHTML()
							)
						)
					)
					{
						$notFilterd = true;
					}

//					by position in DOM
					if (
						isset($rules['FILTER']['NTH']) && is_array($rules['FILTER']['NTH']) &&
						isset($rules['FILTER']['NTH']['VALUE']) &&
						$nth + 1 != $rules['FILTER']['NTH']['VALUE']
					)
					{
						$notFilterd = true;
					}
					
					if ($notFilterd)
					{
						continue;
					}
				}

//				CLASSES
				$classesChange = false;
				$nodeClasses = $resultNode->getClassList();
				if (is_array($rules['CLASSES_REMOVE']) && !empty($rules['CLASSES_REMOVE']))
				{
					$nodeClasses = array_diff($nodeClasses, $rules['CLASSES_REMOVE']);
					$classesChange = true;
				}
				
				if (is_array($rules['CLASSES_ADD']) && !empty($rules['CLASSES_ADD']))
				{
					$nodeClasses = array_merge($nodeClasses, $rules['CLASSES_ADD']);
					$classesChange = true;
				}
				
				if (is_array($rules['CLASSES_REPLACE']) &&
					array_key_exists('PATTERN', $rules['CLASSES_REPLACE']) &&
					array_key_exists('REPLACE', $rules['CLASSES_REPLACE']))
				{
					$nodeClassesStr = implode(' ', $nodeClasses);
					$nodeClassesReplace = preg_replace(
						'/' . $rules['CLASSES_REPLACE']['PATTERN'] . '/i',
						$rules['CLASSES_REPLACE']['REPLACE'],
						$nodeClassesStr
					);
					if ($nodeClassesReplace !== null)
					{
						$nodeClasses = explode(' ', $nodeClassesReplace);
						$classesChange = true;
					}
				}
				
//				APPLY changes
				$nodeClasses = array_unique($nodeClasses);
				if ($classesChange)
				{
					$resultNode->setClassName(implode(' ', $nodeClasses));
				}

//				ID
				if ($rules['ID_REMOVE'] && $rules['ID_REMOVE'] == 'Y')
				{
					$resultNode->removeAttribute('id');
				}

//				ATTRS
				foreach ($nodeAttrs as $name => $value)
				{
//					reduce string (in attributes may be a complex data)
					$value = str_replace(array("\n", "\t"), "", $value);
					
					if ($value)
					{
						$resultNode->setAttribute($name, is_array($value) ? json_encode($value) : $value);
					}
					else
					{
						$resultNode->removeAttribute($name);
					}
				}

//				REMOVE NODE
				if (isset($rules['NODE_REMOVE']) && $rules['NODE_REMOVE'] === true)
				{
					$resultNode->getParentNode()->removeChild($resultNode);
				}
				
//				REPLACE CONTENT by regexp
//				be CAREFUL!
				if (
					isset($rules['REPLACE_CONTENT']) && is_array($rules['REPLACE_CONTENT']) &&
					array_key_exists('regexp', $rules['REPLACE_CONTENT']) &&
					array_key_exists('replace', $rules['REPLACE_CONTENT'])
				)
				{
					$innerHtml = $resultNode->getInnerHTML();
					$innerHtml = preg_replace($rules['REPLACE_CONTENT']['regexp'], $rules['REPLACE_CONTENT']['replace'], $innerHtml);
					if($innerHtml <> '')
					{
						$resultNode->setInnerHTML($innerHtml);
					}
				}
			}


//			add CONTAINER around nodes.
			if (
				isset($rules['CONTAINER_ADD']) && is_array($rules['CONTAINER_ADD']) &&
				isset($rules['CONTAINER_ADD']['CLASSES']) &&
				!empty($resultList)
			)
			{
				if (!is_array($rules['CONTAINER_ADD']['CLASSES']))
				{
					$rules['CONTAINER_ADD']['CLASSES'] = [$rules['CONTAINER_ADD']['CLASSES']];
				}
//				check if container exist
				$firstNode = $resultList[0];
				$parentNode = $firstNode->getParentNode();
				$parentClasses = $parentNode->getClassList();
				if (!empty(array_diff($rules['CONTAINER_ADD']['CLASSES'], $parentClasses)))
				{
//					param TO_EACH - add container to each element. Default (false) - add container once to all nodes
					if (!isset($rules['CONTAINER_ADD']['TO_EACH']) || $rules['CONTAINER_ADD']['TO_EACH'] !== true)
					{
						$containerNode = new Element($rules['CONTAINER_ADD']['TAG'] ? $rules['CONTAINER_ADD']['TAG'] : 'div');
						$containerNode->setOwnerDocument($doc);
						$containerNode->setClassName(implode(' ', $rules['CONTAINER_ADD']['CLASSES']));
						$parentNode->insertBefore($containerNode, $firstNode);
						foreach ($resultList as $resultNode)
						{
							$parentNode->removeChild($resultNode);
							$containerNode->appendChild($resultNode);
						}
					}
					else
					{
						foreach ($resultList as $resultNode)
						{
							$containerNode = new Element($rules['CONTAINER_ADD']['TAG'] ? $rules['CONTAINER_ADD']['TAG'] : 'div');
							$containerNode->setOwnerDocument($doc);
							$containerNode->setClassName(implode(' ', $rules['CONTAINER_ADD']['CLASSES']));
							$parentNode->insertBefore($containerNode, $resultNode);
							
							$parentNode->removeChild($resultNode);
							$containerNode->appendChild($resultNode);
						}
					}
				}
			}
		}
		$block->saveContent($doc->saveHTML());

//		updates COMPONENTS params
		if (is_array($this->dataToUpdate['BLOCKS'][$code]['UPDATE_COMPONENTS']))
		{
			foreach ($this->dataToUpdate['BLOCKS'][$code]['UPDATE_COMPONENTS'] as $selector => $params)
			{
				$block->updateNodes(array($selector => $params));
			}
		}

//		if need remove PHP - we must use block content directly, not DOM parser
		if (
			$this->dataToUpdate['BLOCKS'][$code]['CLEAR_PHP'] &&
			$this->dataToUpdate['BLOCKS'][$code]['CLEAR_PHP'] == 'Y'
		)
		{
			$content = $block->getContent();
			$content = preg_replace('/<\?.*\?>/s', '', $content);
			$block->saveContent($content);
		}

//		change block SORT
		if (
			$this->dataToUpdate['BLOCKS'][$code]['SET_SORT'] &&
			is_numeric($this->dataToUpdate['BLOCKS'][$code]['SET_SORT'])
		)
		{
			$block->setSort($this->dataToUpdate['BLOCKS'][$code]['SET_SORT']);
		}

		$block->save();
	}
	
	
	private function finishStep()
	{
//		processed blocks must be removed from data
		foreach ($this->codesToStep as $code)
		{
			unset($this->dataToUpdate['BLOCKS'][$code]);
		}
		
		Option::set('landing', $this->getOptionName(), serialize($this->dataToUpdate));
		Option::set('landing', self::OPTION_STATUS_NAME, serialize($this->status));
	}
	
	private function finishOption()
	{
//		clean cloud sites cache only if needed
		$this->updateSites();

//		finish current updater id, try next
		Option::delete('landing', array('name' => $this->getOptionName()));
		$this->status['SITES_TO_UPDATE'] = array();
		$this->status['UPDATER_ID'] = '';
		Option::set('landing', self::OPTION_STATUS_NAME, serialize($this->status));
	}
	
	
	private function updateSites()
	{
		if (
			isset($this->status['PARAMS'][$this->getOptionName()]['UPDATE_PUBLISHED_SITES']) &&
			$this->status['PARAMS'][$this->getOptionName()]['UPDATE_PUBLISHED_SITES'] == 'Y' &&
			Loader::includeModule('bitrix24')
			
			&& false
//			dbg: need this?
		)
		{
			foreach (array_unique($this->status['SITES_TO_UPDATE']) as $siteId)
			{
				if (intval($siteId))
				{
//					Site::update($siteId, array());
				}
			}
		}
	}
	
	
	/**
	 * Before delete block handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function disableBlockDelete(Entity\Event $event)
	{
		if (\Bitrix\Landing\Update\Stepper::checkAgentActivity('\Bitrix\Landing\Update\Block\NodeAttributes'))
		{
			$result = new Entity\EventResult();
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_BLOCK_DISABLE_DELETE'),
					'BLOCK_DISABLE_DELETE'
				),
			));
			
			return $result;
		}
		else
		{
			self::removeCustomEvents();
		}
	}
	
	/**
	 * Before publication landing handler.
	 * @param \Bitrix\Main\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function disablePublication(\Bitrix\Main\Event $event)
	{
		if (\Bitrix\Landing\Update\Stepper::checkAgentActivity('\Bitrix\Landing\Update\Block\NodeAttributes'))
		{
			$result = new Entity\EventResult;
			$result->setErrors(array(
				new \Bitrix\Main\Entity\EntityError(
					Loc::getMessage('LANDING_DISABLE_PUBLICATION'),
					'LANDING_DISABLE_PUBLICATION'
				),
			));
			
			return $result;
		}
		else
		{
			self::removeCustomEvents();
		}
	}
	
	
	/**
	 * If agent not exist - we must broke events, to preserve infinity blocking publication and delete
	 */
	public static function removeCustomEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler(
			'landing',
			'\Bitrix\Landing\Internals\Block::OnBeforeDelete',
			'landing',
			'\Bitrix\Landing\Update\Block\NodeAttributes',
			'disableBlockDelete');
		$eventManager->unregisterEventHandler(
			'landing',
			'onLandingPublication',
			'landing',
			'\Bitrix\Landing\Update\Block\NodeAttributes',
			'disablePublication'
		);
	}
	
	
	/**
	 * Update form domain, when updated b24 connector
	 * @param Event $event
	 * @deprecated
	 */
	public static function updateFormDomainByConnector($event)
	{
		trigger_error(
			"Now using embedded forms, no need domain. You must remove updateFormDomainByConnector() call",
			E_USER_WARNING
		);
	}
	
	/**
	 * Set data for NodeUpdater to updating form domain
	 *
	 * @param array $domains
	 * @deprecated
	 */
	public static function updateFormDomain($domains = array())
	{
		trigger_error(
			"Now using embedded forms, no need domain. You must remove updateFormDomain() call",
			E_USER_WARNING
		);
	}

	/**
	 * Code for updater.php see in landing/dev/updater/nodeattributesupdaters.pph
	 */
}