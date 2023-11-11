<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use CFile;
use CFileInput;

final class FileFieldAssembler extends FieldAssembler
{
	private int $iblockId;
	private array $files;

	public function __construct(int $iblockId)
	{
		$this->iblockId = $iblockId;

		parent::__construct(
			$this->getPropertyColumnsIds()
		);
	}

	private function getPropertyColumnsIds(): array
	{
		$result = [];

		$rows = PropertyTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $this->iblockId,
				'=PROPERTY_TYPE' => PropertyTable::TYPE_FILE,
				'USER_TYPE' => null,
			],
		]);
		foreach ($rows as $row)
		{
			$result[] = ElementPropertyProvider::getColumnIdByPropertyId((int)$row['ID']);
		}

		return $result;
	}

	public function prepareRows(array $rowList): array
	{
		if (empty($this->getColumnIds()))
		{
			return $rowList;
		}

		$fileIds = [];
		foreach ($rowList as $row)
		{
			foreach ($this->getColumnIds() as $columnId)
			{
				$value = $row['data'][$columnId] ?? null;
				if (is_array($value))
				{
					foreach ($value as $valueItem)
					{
						$fileIds[] = (int)$valueItem;
					}
				}
				elseif (isset($value))
				{
					$fileIds[] = (int)$value;
				}
			}
		}

		if (empty($fileIds))
		{
			return $rowList;
		}

		$this->preloadFiles($fileIds);

		return parent::prepareRows($rowList);
	}

	protected function prepareRow(array $row): array
	{
		$row['columns'] ??= [];

		foreach ($this->getColumnIds() as $columnId)
		{
			$value = $row['data'][$columnId] ?? null;
			if ($value !== null || is_array($value))
			{
				// edit
				$row['data']['~' . $columnId] ??= $this->getDataForEdit($value);

				// view
				$row['columns'][$columnId] ??= $this->getImageHtml($columnId, $value);
			}
		}

		return $row;
	}

	/**
	 * Html image code.
	 *
	 * @param string $columnId
	 * @param array|int $value
	 *
	 * @return string
	 */
	private function getImageHtml(string $columnId, $value): string
	{
		if (Loader::includeModule('fileman'))
		{
			return CFileInput::Show(
				'',
				$value,
				[
					'IMAGE' => 'Y',
					'IMAGE_POPUP' => 'N',
					'PATH' => 'N',
					'FILE_SIZE' => 'N',
					'DIMENSIONS' => 'N',
					'MAX_SIZE' => [
						'W' => 50,
						'H' => 50,
					],
					'MIN_SIZE' => [
						'W' => 1,
						'H' => 1,
					],
				],
				[
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				]
			);
		}

		global $APPLICATION;

		/**
		 * @var \CMain $APPLICATION
		 */

		try
		{
			ob_start();

			$APPLICATION->IncludeComponent('bitrix:main.file.input', '', [
				'MODULE_ID' => 'catalog',
				'MULTIPLE'=> 'Y',
				'ALLOW_UPLOAD' => 'N',
				'INPUT_NAME' => $columnId,
				'INPUT_VALUE' => $value,
			]);

			return ob_get_contents();
		}
		finally
		{
			ob_end_clean();
		}
	}

	private function getFileSrc(int $fileId): ?string
	{
		$file = $this->files[$fileId] ?? null;
		if ($file)
		{
			return CFile::GetFileSRC($file);
		}

		return null;
	}

	private function getDataForEdit($value)
	{
		if (is_array($value))
		{
			$result = [];

			foreach ($value as $valueItem)
			{
				$result[] = $this->getFileSrc((int)$valueItem);
			}

			return $result;
		}
		elseif (isset($value))
		{
			return $this->getFileSrc((int)$value);
		}

		return null;
	}

	private function preloadFiles(array $fileIds): void
	{
		$this->files = [];

		if (empty($fileIds))
		{
			return;
		}

		$rows = CFile::GetList([], [
			'@ID' => $fileIds,
		]);
		while ($row = $rows->Fetch())
		{
			$this->files[$row['ID']] = $row;
		}
	}
}
