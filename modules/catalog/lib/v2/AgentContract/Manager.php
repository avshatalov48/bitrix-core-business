<?php

namespace Bitrix\Catalog\v2\AgentContract;

use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;

class Manager
{
	public static function add(array $fields, array $products = []): Main\Result
	{
		$result = new Main\Result();

		if (empty($fields['TITLE']))
		{
			$fields['TITLE'] = '';
		}

		if (empty($fields['CREATED_BY']))
		{
			$fields['CREATED_BY'] = Main\Engine\CurrentUser::get()->getId();
		}

		$files = isset($fields['FILES']) && is_array($fields['FILES']) ? $fields['FILES'] : [];
		unset($fields['FILES']);

		$addResult = Catalog\AgentContractTable::add($fields);
		if ($addResult->isSuccess())
		{
			$id = $addResult->getId();
			$result->setData(['ID' => $id]);

			if ($products)
			{
				$addProductsResult = self::addProducts($id, $products);
				if (!$addProductsResult->isSuccess())
				{
					$result->addErrors($addProductsResult->getErrors());
				}
			}

			if ($files)
			{
				self::saveFiles($id, $files);
			}
		}
		else
		{
			$result->addErrors($addResult->getErrors());
		}

		return $result;
	}

	public static function update(int $id, array $fields, ?array $products = null): Main\Result
	{
		$result = new Main\Result();

		if (empty($fields['MODIFIED_BY']))
		{
			$fields['MODIFIED_BY'] = Main\Engine\CurrentUser::get()->getId();
		}

		$fields['DATE_MODIFY'] = new Main\Type\DateTime();

		$files['FILES'] = $fields['FILES'];
		$files['FILES_del'] = $fields['FILES_del'] ?? [];
		unset($fields['FILES'], $fields['FILES_del']);

		$files = self::prepareFilesToUpdate($files);

		$updateResult = Catalog\AgentContractTable::update($id, $fields);
		if ($updateResult->isSuccess())
		{
			if (!is_null($products))
			{
				$deleteProductsResult = self::deleteProductsByContractId($id);
				if (!$deleteProductsResult->isSuccess())
				{
					$result->addErrors($deleteProductsResult->getErrors());
				}

				if ($products)
				{
					$addProductsResult = self::addProducts($id, $products);
					if (!$addProductsResult->isSuccess())
					{
						$result->addErrors($addProductsResult->getErrors());
					}
				}
			}

			if ($files)
			{
				self::saveFiles($id, $files);
			}
		}
		else
		{
			$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}

	public static function delete(int $id): Main\Result
	{
		$result = new Main\Result();

		$deleteProductsResult = self::deleteProductsByContractId($id);
		if (!$deleteProductsResult->isSuccess())
		{
			$result->addErrors($deleteProductsResult->getErrors());
		}

		if ($result->isSuccess())
		{
			$deleteResult = Catalog\AgentContractTable::delete($id);
			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}

	private static function addProducts(int $id, array $products): Main\Result
	{
		$result = new Main\Result();

		$products = array_map(
			static function ($product) use ($id)
			{
				$product['CONTRACT_ID'] = $id;
				return $product;
			},
			$products
		);

		$addProductResult = Catalog\AgentProductTable::addMulti($products, true);
		if (!$addProductResult->isSuccess())
		{
			$result->addErrors($addProductResult->getErrors());
		}

		return $result;
	}

	private static function deleteProductsByContractId(int $id): Main\Result
	{
		$result = new Main\Result();

		$agentProductIterator = Catalog\AgentProductTable::getList([
			'select' => ['ID'],
			'filter' => ['=CONTRACT_ID' => $id],
		]);
		while ($agentProduct = $agentProductIterator->fetch())
		{
			$deleteProductResult = Catalog\AgentProductTable::delete($agentProduct['ID']);
			if (!$deleteProductResult->isSuccess())
			{
				$result->addErrors($deleteProductResult->getErrors());
			}
		}

		return $result;
	}

	public static function get(int $id): Main\Result
	{
		$result = new Main\Result();

		$agentContract = Catalog\AgentContractTable::getList([
			'select' => [
				'ID',
				'TITLE',
				'CONTRACTOR_ID',
				'DATE_MODIFY',
				'DATE_CREATE',
				'MODIFIED_BY',
				'CREATED_BY',
			],
			'filter' => ['=ID' => $id],
			'limit' => 1
		])->fetch();

		if ($agentContract)
		{
			$products = [];
			$productsIds = [];
			$sectionIds = [];

			$agentProductIterator = Catalog\AgentProductTable::getList([
				'select' => [
					'ID',
					'PRODUCT_ID',
					'PRODUCT_TYPE',
				],
				'filter' => [
					'=CONTRACT_ID' => $id,
				],
			]);
			while ($agentProduct = $agentProductIterator->fetch())
			{
				if ($agentProduct['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_SECTION)
				{
					$sectionIds[] = $agentProduct['PRODUCT_ID'];
				}

				if ($agentProduct['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_PRODUCT)
				{
					$productsIds[] = $agentProduct['PRODUCT_ID'];
				}

				$agentProduct['PRODUCT_NAME'] = '';
				$products[] = $agentProduct;
			}

			if ($products)
			{
				$sectionNames = [];
				$productNames = [];

				$sectionImages = [];
				if ($sectionIds)
				{
					$sectionIterator = Iblock\SectionTable::getList([
						'select' => ['ID', 'NAME', 'PICTURE'],
						'filter' => ['=ID' => array_unique($sectionIds)],
					]);
					while ($sectionData = $sectionIterator->fetch())
					{
						$sectionNames[$sectionData['ID']] = $sectionData['NAME'];
						if (!empty($sectionData['PICTURE']))
						{
							$sectionImages[$sectionData['ID']] = self::getImageSource((int)$sectionData['PICTURE']);
						}
					}
				}

				$productImages = [];
				$iblockProductMorePhotoMap = [];
				if ($productsIds)
				{
					$elementIterator = Iblock\ElementTable::getList([
						'select' => ['ID', 'NAME', 'IBLOCK_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
						'filter' => ['=ID' => array_unique($productsIds)],
					]);
					while ($elementData = $elementIterator->fetch())
					{
						$elementId = $elementData['ID'];
						$productNames[$elementId] = $elementData['NAME'];
						if (!empty($elementData['PREVIEW_PICTURE']))
						{
							$productImages[$elementId] = self::getImageSource((int)$elementData['PREVIEW_PICTURE']);
						}

						if (empty($element['IMAGE']) && !empty($elementData['DETAIL_PICTURE']))
						{
							$productImages[$elementId] = self::getImageSource((int)$elementData['DETAIL_PICTURE']);
						}

						if (empty($element['IMAGE']))
						{
							$iblockProductMorePhotoMap[$elementData['IBLOCK_ID']] ??= [];
							$iblockProductMorePhotoMap[$elementData['IBLOCK_ID']][] = $elementId;
						}
					}

					if (!empty($iblockProductMorePhotoMap))
					{
						$morePhotoIds = [];
						$iterator = PropertyTable::getList([
							'select' => ['ID', 'IBLOCK_ID'],
							'filter' => [
								'=IBLOCK_ID' => array_keys($iblockProductMorePhotoMap),
								'=CODE' => \CIBlockPropertyTools::CODE_MORE_PHOTO,
								'=ACTIVE' => 'Y',
							],
						]);

						if ($row = $iterator->fetch())
						{
							$morePhotoIds[$row['IBLOCK_ID']] = $row['ID'];
						}

						foreach ($morePhotoIds as $iblockId => $propertyId)
						{
							$elementIds = $iblockProductMorePhotoMap[$iblockId];
							$elementPropertyValues = array_fill_keys($elementIds, []);
							$offersFilter = [
								'IBLOCK_ID' => $iblockId,
								'ID' => $elementIds,
							];
							$propertyFilter = [
								'ID' => $propertyId,
							];
							\CIBlockElement::GetPropertyValuesArray($elementPropertyValues, $iblockId, $offersFilter, $propertyFilter);
							foreach ($elementPropertyValues as $productId => $properties)
							{
								if (empty($properties))
								{
									continue;
								}

								$morePhotoProperty = reset($properties);
								$value = $morePhotoProperty['VALUE'] ?? null;
								if (empty($value))
								{
									continue;
								}

								$propertyValue = is_array($value) ? reset($value) : $value['VALUE'];
								if ((int)$propertyValue > 0)
								{
									$productImages[$productId] = self::getImageSource((int)$propertyValue);
								}
							}
						}

					}
				}

				$products = array_map(
					static function ($product) use ($sectionNames, $productNames, $productImages, $sectionImages)
					{
						if ($product['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_SECTION)
						{
							$product['PRODUCT_NAME'] = $sectionNames[$product['PRODUCT_ID']];
							$product['IMAGE'] = $sectionImages[$product['PRODUCT_ID']] ?? null;
						}

						if ($product['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_PRODUCT)
						{
							$product['PRODUCT_NAME'] = $productNames[$product['PRODUCT_ID']];
							$product['IMAGE'] = $productImages[$product['PRODUCT_ID']] ?? null;
						}

						return $product;
					},
					$products
				);
			}

			$files = self::getFiles($id);

			$result->setData(
				array_merge(
					$agentContract,
					[
						'PRODUCTS' => $products,
						'FILES' => $files,
					]
				)
			);
		}

		return $result;
	}

	private static function saveFiles(int $contractId, array $files): void
	{
		if (empty($files))
		{
			return;
		}

		// load current file list
		$existingFiles = [];
		$fileMap = [];
		$agentContractFileIterator = Catalog\AgentContractFileTable::getList([
			'select' => ['ID', 'FILE_ID'],
			'filter' => ['=CONTRACT_ID' => $contractId],
		]);
		while ($agentContractFile = $agentContractFileIterator->fetch())
		{
			$id = (int)$agentContractFile['ID'];
			$fileId = (int)$agentContractFile['FILE_ID'];
			$existingFiles[$id] = [
				'ID' => $id,
				'FILE_ID' => $fileId,
			];
			$fileMap[$fileId] = $id;
		}

		// convert the new list of files to array format for each line if needed
		$files = static::convertFileList($fileMap, $files);
		if (empty($files))
		{
			return;
		}

		// checking that the passed set of document files is full
		foreach (array_keys($existingFiles) as $rowId)
		{
			if (!isset($files[$rowId]))
			{
				$files[$rowId] = $existingFiles[$rowId];
			}
		}

		// process file list
		$parsed = [];
		foreach ($files as $rowId => $row)
		{
			// replace or delete existing file
			if (
				is_int($rowId)
				&& is_array($row)
				&& isset($existingFiles[$rowId])
			)
			{
				// delete file
				if (
					isset($row['DEL'])
					&& $row['DEL'] === 'Y'
				)
				{
					$resultInternal = Catalog\AgentContractFileTable::delete($rowId);
					if ($resultInternal->isSuccess())
					{
						\CFile::Delete($existingFiles[$rowId]['FILE_ID']);
					}
				}
				// replace file
				elseif (
					isset($row['FILE_ID'])
				)
				{
					if ($row['FILE_ID'] !== $existingFiles[$rowId]['FILE_ID'])
					{
						$resultInternal = Catalog\AgentContractFileTable::update(
							$rowId,
							[
								'FILE_ID' => $row['FILE_ID'],
							]
						);
						if ($resultInternal->isSuccess())
						{
							\CFile::Delete($existingFiles[$rowId]['FILE_ID']);
						}
					}
				}
			}
			// save new file
			elseif (
				preg_match('/^n[0-9]+$/', $rowId, $parsed)
				&& is_array($row)
			)
			{
				// file already saved from external code
				if (isset($row['FILE_ID']))
				{
					$resultInternal = Catalog\AgentContractFileTable::add([
						'CONTRACT_ID' => $contractId,
						'FILE_ID' => $row['FILE_ID'],
					]);
					if ($resultInternal->isSuccess())
					{
						$id = (int)$resultInternal->getId();
						$fileMap[$row['FILE_ID']] = $id;
						$existingFiles[$id] = [
							'ID' => $id,
							'FILE_ID' => $row['FILE_ID'],
						];
					}
				}
				// save uploaded file
				elseif (
					isset($row['FILE_UPLOAD'])
					&& is_array($row['FILE_UPLOAD'])
				)
				{
					$row['FILE_UPLOAD']['MODULE_ID'] = 'catalog';
					$fileId = (int)\CFile::SaveFile(
						$row['FILE_UPLOAD'],
						'catalog',
						false,
						true
					);
					if ($fileId > 0)
					{
						$resultInternal = Catalog\AgentContractFileTable::add([
							'CONTRACT_ID' => $contractId,
							'FILE_ID' => $fileId,
						]);
						if ($resultInternal->isSuccess())
						{
							$id = (int)$resultInternal->getId();
							$fileMap[$fileId] = $id;
							$existingFiles[$id] = [
								'ID' => $id,
								'FILE_ID' => $fileId,
							];
						}
					}
				}
			}
		}
	}

	private static function convertFileList(array $fileMap, array $files): array
	{
		$formatArray = false;
		$formatOther = false;
		foreach ($files as $value)
		{
			if (is_array($value))
			{
				$formatArray = true;
			}
			else
			{
				$formatOther = true;
			}
		}

		if ($formatArray && $formatOther)
		{
			return [];
		}

		if ($formatArray)
		{
			return $files;
		}

		$counter = 0;
		$list = array_values(array_unique($files));
		$files = [];
		$parsed = [];
		foreach ($list as $value)
		{
			if (!is_string($value))
			{
				continue;
			}
			if (preg_match('/^delete([0-9]+)$/', $value, $parsed))
			{
				$value = (int)$parsed[1];
				if (isset($fileMap[$value]))
				{
					$id = $fileMap[$value];
					$files[$id] = [
						'DEL' => 'Y',
					];
				}
			}
			elseif (preg_match('/^[0-9]+$/', $value, $parsed))
			{
				$value = (int)$value;
				if (isset($fileMap[$value]))
				{
					$id = $fileMap[$value];
					$files[$id] = [
						'ID' => $id,
						'FILE_ID' => $value,
					];
				}
				else
				{
					$id = 'n' . $counter;
					$counter++;
					$files[$id] = [
						'ID' => null,
						'FILE_ID' => $value,
					];
				}
			}
		}

		return $files;
	}

	private static function getFiles(int $contractId): array
	{
		$files = Catalog\AgentContractFileTable::getList([
			'select' => ['FILE_ID'],
			'filter' => ['=CONTRACT_ID' => $contractId]
		])->fetchAll();

		return array_column($files, 'FILE_ID');
	}

	private static function prepareFilesToUpdate(array $fields): array
	{
		$filesExists = isset($fields['FILES']) && is_array($fields['FILES']);
		$filesDelete = isset($fields['FILES_del']) && is_array($fields['FILES_del']);
		if ($filesExists || $filesDelete)
		{
			$result = [];
			if ($filesExists)
			{
				$fileList = $fields['FILES'];
				Main\Type\Collection::normalizeArrayValuesByInt($fileList, false);
				$fileList = Main\UI\FileInputUtility::instance()->checkFiles(
					'files_uploader',
					$fileList
				);
				foreach ($fileList as $id)
				{
					$result[$id] = (string)$id;
				}
			}

			if ($filesDelete)
			{
				$deleteList = $fields['FILES_del'];
				Main\Type\Collection::normalizeArrayValuesByInt($deleteList, false);
				foreach ($deleteList as $id)
				{
					$result[$id] = 'delete' . $id;
				}
			}

			$fields['FILES'] = array_values($result);
			unset($result);
		}

		return $fields['FILES'];
	}

	private static function getImageSource(int $id): ?string
	{
		if ($id <= 0)
		{
			return null;
		}

		$file = \CFile::GetFileArray($id);
		if (!$file)
		{
			return null;
		}

		return Tools::getImageSrc($file, false) ?: null;
	}
}
