<?php

namespace Bitrix\Bizproc\Result;

use Bitrix\Disk\AttachedObject;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\MimeType;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;

class MobileResultHandler implements DeviceResultHandler
{
	protected string $workflowId;

	public function __construct(string $workflowId)
	{
		$this->workflowId = $workflowId;
	}

	public function handle(RenderedResult $renderedResult = null): array
	{
		static $cache = [];

		$files = [];
		$noResult = [
			'text' => Loc::getMessage('BIZPROC_RESULT_BP_WORKFLOW_NO_RESULT'),
			'status' => \Bitrix\Bizproc\Result\RenderedResult::NO_RESULT,
			'files' => $files
		];
		$text = $this->extractFromSource($renderedResult->text ?? '', $files);

		if (is_null($renderedResult))
		{
			if (isset($cache[$this->workflowId]))
			{
				return $cache[$this->workflowId];
			}

			$state = WorkflowStateTable::getByPrimary(
				$this->workflowId,
				['select' => ['STARTED_BY', 'MODULE_ID', 'ENTITY', 'DOCUMENT_ID']]
			)->fetchObject();
			if (!$state)
			{
				$cache[$this->workflowId] = $noResult;

				return $cache[$this->workflowId];
			}

			$startedBy = $state->getStartedBy();
			if (empty($startedBy))
			{
				$startedBy = \CCrmBizProcHelper::getDocumentResponsibleId($state->getComplexDocumentId());
			}

			$userName = \CBPViewHelper::getUserFullNameById($startedBy);

			if ($userName)
			{
				$userLink = '[URL=/company/personal/user/' . $startedBy . '/]' . $userName . '[/URL]';
				$text = Loc::getMessage('BIZPROC_RESULT_BP_WORKFLOW_RESULT_USER', ['#USER#' => $userLink]) ?? '';

				$cache[$this->workflowId] = [
					'text' => $text,
					'status' => \Bitrix\Bizproc\Result\RenderedResult::USER_RESULT,
					'files' => $files
				];

				return $cache[$this->workflowId];
			}

			$cache[$this->workflowId] = $noResult;

			return $cache[$this->workflowId];
		}

		switch ($renderedResult->status)
		{
			case RenderedResult::BB_CODE_RESULT:
				return [
					'text' => $text,
					'status' => $renderedResult->status,
					'files' => $files,
				];

			case RenderedResult::USER_RESULT:
				return [
					'text' => Loc::getMessage(
						'BIZPROC_RESULT_BP_WORKFLOW_RESULT_USER', ['#USER#' => $renderedResult->text]
					) ?? '',
					'status' => $renderedResult->status,
					'files' => $files,
				];

			case RenderedResult::NO_RIGHTS:
				return [
					'text' => Loc::getMessage('BIZPROC_RESULT_BP_RESULT_NO_RIGHTS') ?? '',
					'status' => $renderedResult->status,
					'files' => $files,
				];
			case RenderedResult::NO_RESULT:
				return $noResult;
		}
	}

	private function extractFromSource(string $sourceText, array &$files): string
	{
		$sourceText = preg_replace_callback(
			'|\[url\s*=\s*/bitrix/tools/bizproc_show_file\.php\?([^]]+)]|',
			$this->getFileLinksReplacer($files),
			$sourceText,
		);

		if (Loader::includeModule('disk'))
		{
			$sourceText = preg_replace_callback(
				'|\[url\s*=\s*/bitrix/tools/disk/uf.php\?([^]]+)]|',
				$this->getDiskFileLinksReplacer($files),
				$sourceText
			);
		}

		return $sourceText;
	}

	private function getFileLinksReplacer(array& $files): callable
	{
		return function ($matches) use (&$files)
		{
			parse_str(htmlspecialcharsback($matches[1]), $query);
			$fileId = $query['i'] ?? null;
			if (isset($fileId))
			{
				$fileId = (int)$fileId;
				$file = \CFile::GetFileArray($fileId);
				if (!$file)
				{
					return $matches[0];
				}
				$uri = 'fid://' . $fileId;
				$fileName = $file['ORIGINAL_NAME'] ?: $file['FILE_NAME'];
				$files[$uri] = [
					'id' => (int)$file['ID'],
					'name' => $fileName,
					'url' => $file['SRC'],
					'type' => MimeType::getByFilename($fileName),
				];

				return '[url=' . $uri . ']';
			}

			return $matches[0];
		};
	}

	private function getDiskFileLinksReplacer(array& $files): callable
	{
		return function ($matches) use (&$files)
		{
			parse_str(htmlspecialcharsback($matches[1]), $query);
			$attachedModel = AttachedObject::loadById($query['attachedId'] ?? null);
			$diskFile = $attachedModel?->getFile();
			if (isset($diskFile))
			{
				$file = \CFile::GetFileArray($diskFile->getFileId());
				if (!$file)
				{
					return $matches[0];
				}

				$uri = 'fid://' . $diskFile->getFileId();
				$fileName = $file['ORIGINAL_NAME'] ?: $file['FILE_NAME'];
				$files[$uri] = [
					'id' => (int)$file['ID'],
					'name' => $fileName,
					'url' => $file['SRC'],
					'type' => MimeType::getByFilename($fileName),
				];

				return '[url=' . $uri . ']';
			}

			return $matches[0];
		};
	}
}
