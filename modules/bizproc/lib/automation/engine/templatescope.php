<?php

namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class TemplateScope
{
	/** @var array */
	private $complexDocumentType;
	/** @var string | int | null */
	private $documentCategoryId;
	/** @var string */
	private $documentStatus;

	/** @var string | null */
	private $categoryName;
	/** @var string | null */
	private $statusName;
	/** @var string | null */
	private $statusColor;

	public function __construct(array $documentType, $categoryId, string $status)
	{
		$this->complexDocumentType = $documentType;
		$this->documentCategoryId = $categoryId;
		$this->documentStatus = $status;
	}

	public function getTemplate(): ?Template
	{
		try
		{
			$template = new Template($this->complexDocumentType, $this->documentStatus);
		}
		catch (\Exception $exception)
		{
			$template = null;
		}

		return $template;
	}

	public function setNames(?string $categoryName, ?string $statusName): self
	{
		$this->categoryName = $categoryName;
		$this->statusName = $statusName;

		return $this;
	}

	public function setStatusColor(string $color): self
	{
		$this->statusColor = $color;

		return $this;
	}

	public function getId(): string
	{
		return "{$this->getModuleId()}_{$this->getDocumentType()}_{$this->getCategoryId()}_{$this->getStatusId()}";
	}

	public function getModuleId(): string
	{
		return $this->getComplexDocumentType()[0];
	}

	public function getDocumentType(): string
	{
		return $this->getComplexDocumentType()[2];
	}

	public function getComplexDocumentType(): array
	{
		return $this->complexDocumentType;
	}

	public function getCategoryId()
	{
		return $this->documentCategoryId;
	}

	public function getStatusId(): string
	{
		return $this->documentStatus;
	}

	public function toArray(): array
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

		return [
			'DocumentType' => [
				'Type' => $this->getComplexDocumentType(),
				'Name' => $documentService->getDocumentTypeName($this->complexDocumentType),
			],
			'Category' => [
				'Id' => $this->getCategoryId(),
				'Name' => $this->categoryName,
			],
			'Status' => [
				'Id' => $this->getStatusId(),
				'Name' => $this->statusName,
				'Color' => $this->statusColor,
			],
		];
	}

	public static function fromArray(array $rawScope): ?TemplateScope
	{
		if (
			is_array($rawScope['DocumentType'] ?? null)
			&& is_array($rawScope['Category'] ?? null)
			&& is_array($rawScope['Status'] ?? null)
		)
		{
			$scope = new static(
				$rawScope['DocumentType']['Type'] ?? [],
				$rawScope['Category']['Id'] ?? null,
				$rawScope['Status']['Id'] ?? '',
			);
			$scope->setNames(
				$rawScope['Category']['Name'] ?? null,
				$rawScope['Status']['Name'] ?? null
			);

			if (isset($rawScope['Status']['Color']) && is_string($rawScope['Status']['Color']))
			{
				$scope->setStatusColor($rawScope['Status']['Color']);
			}

			return $scope;
		}

		return null;
	}
}