<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Block as BlockCore;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\PublicActionResult;

Loc::loadMessages(__FILE__);

class Block
{
	/**
	 * Local function for minimization code.
	 * @param string $action Action code.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param string $selector Selector.
	 * @param array $params Additional params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	private static function cardAction($action, $lid, $block, $selector, array $params = array())
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();
		$landing = Landing::createInstance($lid);
		// try find the block in landing instance
		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				// action with card  of block
				if (strpos($selector, '@') !== false)
				{
					list($selector, $position) = explode('@', $selector);
				}
				else
				{
					$position = -1;
				}
				if (
					strtolower($action) == 'clonecard' &&
					isset($params['content'])
				)
				{
					$res = $blocks[$block]->$action(
						$selector,
						$position,
						Manager::sanitize(
							$params['content'], $bad
						)
					);
				}
				else
				{
					$res = $blocks[$block]->$action($selector, $position);
				}
				if ($res)
				{
					$result->setResult($blocks[$block]->save());
				}
				if ($blocks[$block]->getError()->isEmpty())
				{
					$landing->touch();
				}
				$result->setError($blocks[$block]->getError());
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}
		$result->setError($landing->getError());
		$result->setError($error);
		return $result;
	}

	/**
	 * Clone card in block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param string $selector Selector.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function cloneCard($lid, $block, $selector)
	{
		Landing::setEditMode();
		return self::cardAction('cloneCard', $lid, $block, $selector);
	}

	/**
	 * Add card in block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param string $selector Selector.
	 * @param string $content Content of card.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function addCard($lid, $block, $selector, $content)
	{
		Landing::setEditMode();
		return self::cardAction(
			'cloneCard',
			$lid,
			$block,
			$selector,
			array(
				'content' => $content
			)
		);
	}

	/**
	 * Remove card from block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param string $selector Selector.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function removeCard($lid, $block, $selector)
	{
		Landing::setEditMode();
		return self::cardAction('removeCard', $lid, $block, $selector);
	}

	/**
	 * Change cards multiple.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $data Array with cards.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function updateCards($lid, $block, array $data)
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		Landing::setEditMode();

		$landing = Landing::createInstance($lid);
		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				$currBlock = $blocks[$block];
				$currBlock->updateCards((array)$data);
				$result->setResult($currBlock->save());
				$result->setError($currBlock->getError());
				if ($currBlock->getError()->isEmpty())
				{
					$landing->touch();
				}
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}
		$result->setError($landing->getError());
		$result->setError($error);

		return $result;
	}

	/**
	 * Change node name.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $data Array with selector and value.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function changeNodeName($lid, $block, array $data)
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		$content = array();
		Landing::setEditMode();

		// collect selectors in right array
		foreach ($data as $selector => $value)
		{
			if (strpos($selector, '@') !== false)
			{
				list($selector, $position) = explode('@', $selector);
			}
			else
			{
				$position = 0;
			}
			if (!isset($content[$selector]))
			{
				$content[$selector] = array();
			}
			$content[$selector][$position] = $value;
		}

		if (!empty($content))
		{
			$landing = Landing::createInstance($lid);
			// try find the block in landing instance
			if ($landing->exist())
			{
				$blocks = $landing->getBlocks();
				if (isset($blocks[$block]))
				{
					$blocks[$block]->changeNodeName($content);
					$result->setResult($blocks[$block]->save());
					$result->setError($blocks[$block]->getError());
					if ($blocks[$block]->getError()->isEmpty())
					{
						$landing->touch();
					}
				}
				else
				{
					$error->addError(
						'BLOCK_NOT_FOUND',
						Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
					);
				}
			}
			$result->setError($landing->getError());
		}
		else
		{
			$error->addError(
				'NODES_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NODES_NOT_FOUND')
			);
		}

		return $result;
	}

	/**
	 * Set new anchor to the block.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param string $data New anchor.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function changeAnchor($lid, $block, $data)
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		Landing::setEditMode();

		$landing = Landing::createInstance($lid);

		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				$blocks[$block]->setAnchor($data);
				$result->setResult($blocks[$block]->save());
				$result->setError($blocks[$block]->getError());
				if ($blocks[$block]->getError()->isEmpty())
				{
					$landing->touch();
				}
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Update nodes in block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $data Array with selector and value.
	 * @param array $additional Additional prams for save.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function updateNodes($lid, $block, array $data, array $additional = array())
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		$attributes = array();
		$components = array();
		$content = array();
		$data = (array) $data;

		Landing::setEditMode();

		// collect selectors in right array
		foreach ($data as $selector => $value)
		{
			if (strpos($selector, '@') !== false)
			{
				list($selector, $position) = explode('@', $selector);
			}
			else
			{
				$position = -1;
			}
			if (!isset($data[$selector]))
			{
				$data[$selector] = array();
			}
			if (isset($value['attrs']) && count($value) == 1)
			{
				if (strpos($selector, ':') !== false)
				{
					$components[$selector] = $value['attrs'];
				}
				else
				{
					$attributes[$selector][$position] = $value['attrs'];
				}
			}
			else
			{
				if (!isset($content[$selector]))
				{
					$content[$selector] = array();
				}
				$content[$selector][max(0, $position)] = $value;
			}
		}

		// data is not empty
		if (!empty($content) || !empty($attributes) || !empty($components))
		{
			$landing = Landing::createInstance($lid);
			// try find the block in landing instance
			if ($landing->exist())
			{
				$blocks = $landing->getBlocks();
				if (isset($blocks[$block]))
				{
					if (!empty($content))
					{
						$blocks[$block]->updateNodes($content, $additional);
					}
					if (!empty($attributes))
					{
						$blocks[$block]->setAttributes($attributes);
					}
					if (!empty($components))
					{
						// fix for security waf
						if (!$blocks[$block]->getRepoId())
						{
							$manifest = $blocks[$block]->getManifest();
							foreach ($components as $selector => &$attrs)
							{
								if (
									isset($manifest['nodes'][$selector]['waf_ignore']) &&
									$manifest['nodes'][$selector]['waf_ignore']
								)
								{
									$rawData = \Bitrix\Landing\PublicAction::getRawData();
									if (isset($rawData['data'][$selector]['attrs']))
									{
										$rawAttrs = $rawData['data'][$selector]['attrs'];
										foreach ($attrs as $attCode => &$attValue)
										{
											$attValue = $rawAttrs[$attCode];
											$attValue = \Bitrix\Main\Text\Encoding::convertEncoding(
												$attValue,
												'utf-8',
												SITE_CHARSET
											);
										}
									}
									unset($attValue);
								}
							}
							unset($attrs);
							$blocks[$block]->updateNodes($components, $additional);
						}
					}
					$result->setResult($blocks[$block]->save());
					$result->setError($blocks[$block]->getError());
					if ($blocks[$block]->getError()->isEmpty())
					{
						$landing->touch();
					}
				}
				else
				{
					$error->addError(
						'BLOCK_NOT_FOUND',
						Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
					);
				}
			}
			$result->setError($landing->getError());
		}
		else
		{
			$error->addError(
				'NODES_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NODES_NOT_FOUND')
			);
		}

		$result->setError($error);

		return $result;
	}

	/**
	 * Update any attributes in block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $data Array with selector and data.
	 * @param string $method Method for update.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	private static function updateAttributes($lid, $block, array $data, $method)
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		Landing::setEditMode();

		// try find the block in landing instance
		$landing = Landing::createInstance($lid);
		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				if (is_callable(array($blocks[$block], $method)))
				{
					$blocks[$block]->$method($data);
				}
				$result->setResult($blocks[$block]->save());
				$result->setError($blocks[$block]->getError());
				if ($blocks[$block]->getError()->isEmpty())
				{
					$landing->touch();
				}
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}
		$result->setError($landing->getError());
		$result->setError($error);

		return $result;
	}

	/**
	 * Update classes in block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $data Array with selector and data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function updateStyles($lid, $block, array $data)
	{
		return self::updateAttributes($lid, $block, $data, 'setClasses');
	}

	/**
	 * Update attributes in block by selector.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $data Array with selector and data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function updateAttrs($lid, $block, array $data)
	{
		if (is_array($data))
		{
			foreach ($data as $selector => $value)
			{
				if (strpos($selector, '@') !== false)
				{
					unset($data[$selector]);
					list($selector, $pos) = explode('@', $selector);
					if (
						!isset($data[$selector]) ||
						!is_array($data[$selector])
					)
					{
						$data[$selector] = [];
					}
					$data[$selector][$pos] = $value;
				}
			}
		}
		return self::updateAttributes($lid, $block, $data, 'setAttributes');
	}

	/**
	 * Get content as is from Block.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param boolean $editMode Edit mode if true.
	 * @param array $params Some params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getContent($lid, $block, $editMode = false, array $params = array())
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if ($editMode)
		{
			Landing::setEditMode();
		}

		$landing = Landing::createInstance($lid);
		// try find the block in landing instance
		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				if (!is_array($params))
				{
					$params = array();
				}

				$result->setResult(
					BlockCore::getBlockContent(
						$blocks[$block]->getId(),
						$editMode,
						$params
					)
				);
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Update content in the Block.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param string $content Block content.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function updateContent($lid, $block, $content)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		Landing::setEditMode();

		$landing = Landing::createInstance($lid);
		// try find the block in landing instance
		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				$blocks[$block]->saveContent(
					Manager::sanitize($content, $bad)
				);
				$result->setResult(
					$blocks[$block]->save()
				);
				$result->setError($blocks[$block]->getError());
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Get available blocks of landing.
	 * @param int $lid Landing id.
	 * @param array $params Some params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList($lid, array $params = array())
	{
		$result = new PublicActionResult();
		$lids = is_array($lid) ? $lid : [$lid];

		// some params
		if (
			isset($params['edit_mode']) &&
			$params['edit_mode']
		)
		{
			Landing::setEditMode();
		}

		// get list
		$data = array();
		foreach ($lids as $lid)
		{
			$landing = Landing::createInstance($lid, array(
				'deleted' => isset($params['deleted']) && $params['deleted']
			));
			if ($landing->exist())
			{
				foreach ($landing->getBlocks() as $i => $block)
				{
					if ($manifest = $block->getManifest())
					{
						$data[$i] = array(
							'id' => $block->getId(),
							'lid' => $lid,
							'code' => $block->getCode(),
							'name' => $manifest['block']['name'],
							'active' => $block->isActive(),
							'meta' => $block->getMeta()
						);

						foreach ($data[$i]['meta'] as &$meta)
						{
							$meta = (string)$meta;
						}
						unset($meta);

						if (
							isset($params['get_content']) &&
							$params['get_content']
						)
						{
							ob_start();
							$block->view();
							$data[$i]['content'] = ob_get_contents();
							$data[$i]['css'] = $block->getCSS();
							$data[$i]['js'] = $block->getJS();
							ob_end_clean();
						}
					}
				}
			}
			$result->setError($landing->getError());
		}

		$result->setResult(array_values($data));

		return $result;
	}

	/**
	 * Get one block of landing.
	 * @param int $block Block id.
	 * @param array $params Some params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getById($block, array $params = array())
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		// recognize landing by block
		$lid = BlockCore::getLandingIdByBlockId($block);
		if ($lid)
		{
			// and find this block in landing blocks
			$blocks = self::getList($lid, $params)->getResult();
			foreach ($blocks as $item)
			{
				if ($item['id'] == $block)
				{
					$result->setResult($item);
					return $result;
				}
			}
		}

		$error->addError(
			'BLOCK_NOT_FOUND',
			Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
		);
		$result->setError($error);

		return $result;
	}

	/**
	 * Get available blocks of landing.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $params Some params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getManifest($lid, $block, array $params = array())
	{
		$error = new \Bitrix\Landing\Error;
		$result = new PublicActionResult();

		if (
			isset($params['edit_mode']) &&
			$params['edit_mode']
		)
		{
			Landing::setEditMode();
		}

		$landing = Landing::createInstance($lid);

		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($blocks[$block]))
			{
				$manifest = $blocks[$block]->getManifest();
				$manifest['preview'] = $blocks[$block]->getPreview();
				$manifest['assets'] = $blocks[$block]->getAsset();
				$result->setResult($manifest);
			}
			else
			{
				$error->addError(
					'BLOCK_NOT_FOUND',
					Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
				);
			}
		}

		$result->setError($landing->getError());
		$result->setError($error);

		return $result;
	}

	/**
	 * Get manifest array as is from block.
	 * @param string $code Code name, format "namespace:code".
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getManifestFile($code)
	{
		$result = new PublicActionResult();

		if (strpos($code, ':') === false)
		{
			$code = 'bitrix:' . $code;
		}

		$manifest = BlockCore::getManifestFile($code);
		$result->setResult($manifest);

		return $result;
	}

	/**
	 * Get blocks from repository.
	 * @param string $section Section code.
	 * @param bool $withManifest Get repo with manifest.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getRepository($section = null, $withManifest = false)
	{
		$result = new PublicActionResult();
		$repo = \Bitrix\Landing\Block::getRepository($withManifest);

		if ($section === null)
		{
			$result->setResult($repo);
		}
		else
		{
			$result->setResult(
				isset($repo[$section]) ? $repo[$section] : false
			);
		}

		return $result;
	}

	/**
	 * Upload file by url or from FILE.
	 * @param int $block Block id.
	 * @param mixed $picture File url / file array.
	 * @param string $ext File extension.
	 * @param array $params Some file params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function uploadFile($block, $picture, $ext = false, array $params = array())
	{
		static $mixedParams = ['picture'];

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if (BlockCore::getLandingIdByBlockId($block))
		{
			$file = Manager::savePicture($picture, $ext, $params);
			if ($file)
			{
				File::addToBlock($block, $file['ID']);
				$result->setResult(array(
					'id' => $file['ID'],
					'src' => $file['SRC']
				));
			}
			else
			{
				$error->addError(
					'FILE_ERROR',
					Loc::getMessage('LANDING_FILE_ERROR')
				);
				$result->setError($error);
			}
		}
		else
		{
			$error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			$result->setError($error);
		}

		return $result;
	}
}