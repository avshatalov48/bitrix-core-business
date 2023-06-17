<?php

namespace Bitrix\Main\UI\Viewer;

use Bitrix\Main\Application;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Viewer\Transformation\TransformerManager;
use Bitrix\Main\Web\MimeType;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Security;
use Bitrix\Main\Engine\Response;

final class PreviewManager
{
	public const GET_KEY_TO_SEND_PREVIEW_CONTENT    = 'ibxPreview';
	public const GET_KEY_TO_SHOW_IMAGE              = 'ibxShowImage';
	public const HEADER_TO_SEND_PREVIEW             = 'BX-Viewer';
	public const HEADER_TO_RESIZE_IMAGE             = 'BX-Viewer-image';
	public const HEADER_TO_GET_SOURCE               = 'BX-Viewer-src';
	public const HEADER_TO_RUN_FORCE_TRANSFORMATION = 'BX-Viewer-force-transformation';
	public const HEADER_TO_CHECK_TRANSFORMATION     = 'BX-Viewer-check-transformation';

	private const SALT_FILE_ID = 'previewfile';

	/** @var TransformerManager  */
	private $transformer;
	private array $rendererList = [];
	private static bool $disableCatchingViewByUser = false;

	/**
	 * @var HttpRequest
	 */
	private $httpRequest;

	public function __construct(HttpRequest $httpRequest = null)
	{
		$this->httpRequest = $httpRequest? : Context::getCurrent()->getRequest();
		$this->transformer = $this->buildTransformer();
		$this->buildViewRendererList();
	}

	private function buildTransformer(): ?TransformerManager
	{
		if (!Loader::includeModule('transformer'))
		{
			return null;
		}

		return new TransformerManager();
	}

	private function buildViewRendererList(): void
	{
		$default = [
			Renderer\Pdf::class,
			Renderer\Video::class,
			Renderer\Audio::class,
			Renderer\Image::class,
			Renderer\Code::class,
		];

		$event = new Event('main', 'onPreviewRendererBuildList');
		$event->send();

		$additionalList = [];
		foreach ($event->getResults() as $result)
		{
			if ($result->getType() != EventResult::SUCCESS)
			{
				continue;
			}
			$result = $result->getParameters();
			if (!is_array($result))
			{
				throw new SystemException('Wrong event result. Must be array.');
			}

			foreach ($result as $class)
			{
				if (!is_string($class) || !class_exists($class))
				{
					throw new SystemException('Wrong event result. There is not a class.');
				}

				if (!is_subclass_of($class, Renderer\Renderer::class, true))
				{
					throw new SystemException("Wrong event result. {$class} is not a subclass of " . Renderer\Renderer::class);
				}

				$additionalList[] = $class;
			}
		}

		$this->rendererList = array_merge($additionalList, $default);
	}

	public static function disableCatchingViewByUser(): void
	{
		self::$disableCatchingViewByUser = true;
	}

	public static function enableCatchingViewByUser(): void
	{
		self::$disableCatchingViewByUser = false;
	}

	public static function isDisabledCatchingViewByUser(): bool
	{
		 return self::$disableCatchingViewByUser;
	}

	public function isInternalRequest($file, $options): bool
	{
		if (self::isDisabledCatchingViewByUser())
		{
			return false;
		}

		if (!is_array($file) || empty($file['ID']))
		{
			return false;
		}

		if (!empty($options['prevent_work_with_preview']))
		{
			return false;
		}

		if ($this->httpRequest->get(self::GET_KEY_TO_SEND_PREVIEW_CONTENT))
		{
			return true;
		}

		if ($this->httpRequest->get(self::GET_KEY_TO_SHOW_IMAGE))
		{
			return true;
		}

		if ($this->httpRequest->getHeader(self::HEADER_TO_SEND_PREVIEW))
		{
			return true;
		}

		if ($this->httpRequest->getHeader(self::HEADER_TO_RUN_FORCE_TRANSFORMATION))
		{
			return true;
		}

		if ($this->httpRequest->getHeader(self::HEADER_TO_CHECK_TRANSFORMATION))
		{
			return true;
		}

		if ($this->httpRequest->getHeader(self::HEADER_TO_RESIZE_IMAGE))
		{
			return true;
		}

		return false;
	}

	public function processViewByUserRequest($file, $options): void
	{
		self::disableCatchingViewByUser();

		$response = null;
		if ($this->httpRequest->get(self::GET_KEY_TO_SEND_PREVIEW_CONTENT))
		{
			$response = $this->sendPreviewContent($file, $options);
		}
		elseif ($this->httpRequest->get(self::GET_KEY_TO_SHOW_IMAGE))
		{
			$this->showImage($file, $options);
		}
		elseif ($this->httpRequest->getHeader(self::HEADER_TO_RUN_FORCE_TRANSFORMATION))
		{
			$response = $this->sendPreview($file, true);
		}
		elseif ($this->httpRequest->getHeader(self::HEADER_TO_CHECK_TRANSFORMATION))
		{
			$response = $this->checkTransformation($file);
		}
		elseif ($this->httpRequest->getHeader(self::HEADER_TO_SEND_PREVIEW))
		{
			$response = $this->sendPreview($file);
		}
		elseif ($this->httpRequest->getHeader(self::HEADER_TO_RESIZE_IMAGE))
		{
			$response = $this->sendResizedImage($file);
		}

		if (($response instanceof Response\BFile) && isset($options['cache_time']))
		{
			$response->setCacheTime($options['cache_time']);
		}

		if ($response instanceof Main\Response)
		{
			/** @global \CMain $APPLICATION */
			global $APPLICATION;

			$APPLICATION->RestartBuffer();

			Application::getInstance()->end(0, $response);
		}
	}

	protected function sendPreviewContent($file, $options): ?Response\BFile
	{
		$possiblePreviewFileId = $this->unsignFileId($this->httpRequest->get(self::GET_KEY_TO_SEND_PREVIEW_CONTENT));
		$filePreview = $this->getFilePreviewEntryByFileId($file['ID']);
		if (!$filePreview || empty($filePreview['PREVIEW_ID']) || $filePreview['PREVIEW_ID'] != $possiblePreviewFileId)
		{
			return null;
		}

		FilePreviewTable::update($filePreview['ID'], [
			'TOUCHED_AT' => new DateTime(),
		]);

		//get name for preview file
		return Response\BFile::createByFileId(
			$filePreview['PREVIEW_ID'],
			$file['ORIGINAL_NAME']
		);
	}

	protected function showImage($file, $options): void
	{
		if (!is_array($options))
		{
			$options = [];
		}
		$options['force_download'] = false;
		$options['prevent_work_with_preview'] = true;
		\CFile::viewByUser($file, $options);
	}

	protected function sendResizedImage($file): ?Response\ResizedImage
	{
		$fileView = $this->getByImage($file['ID'], $this->getSourceUri());
		if (!$fileView)
		{
			return null;
		}

		return $fileView->render();
	}

	protected function prepareRenderParameters($file): array
	{
		$getContentType = function() use ($file){
			$filePreviewData = $this->getViewFileData($file);
			if (!$filePreviewData)
			{
				return null;
			}

			return $filePreviewData['CONTENT_TYPE'];
		};

		$getSourceUri = function() use ($file){
			$filePreviewData = $this->getViewFileData($file);
			if (!$filePreviewData)
			{
				return null;
			}

			return $this->getSourceUri()->addParams([
				self::GET_KEY_TO_SEND_PREVIEW_CONTENT => $this->signFileId($filePreviewData['ID']),
			]);
		};

		return [
			'alt' => [
				'contentType' => $getContentType->bindTo($this, $this),
				'sourceUri' => $getSourceUri->bindTo($this, $this),
			],
		];
	}

	protected function checkTransformation($file): Response\AjaxJson
	{
		return new Response\AjaxJson([
			'transformation' => (bool)$this->getViewFileData($file),
		]);
	}

	protected function sendPreview($file, $forceTransformation = false): Response\AjaxJson
	{
		$render = $this->buildRenderByFile(
			$file['ORIGINAL_NAME'],
			$file['CONTENT_TYPE'],
			$this->getSourceUri(),
			$this->prepareRenderParameters($file)
		);
		if ($render instanceof Renderer\Stub || $forceTransformation)
		{
			$filePreviewData = $this->getViewFileData($file);
			if ($filePreviewData)
			{
				$sourceUri = $this->getSourceUri()->addParams([
					self::GET_KEY_TO_SEND_PREVIEW_CONTENT => $this->signFileId($filePreviewData['ID']),
				]);

				$render = $this->buildRenderByFile(
					$filePreviewData['ORIGINAL_NAME'],
					$filePreviewData['CONTENT_TYPE'],
					$sourceUri
				);
			}
			else
			{
				$generatePreview = $this->generatePreview($file['ID']);
				if ($generatePreview instanceof Main\Result)
				{
					if ($generatePreview->isSuccess())
					{
						return Response\AjaxJson::createSuccess($generatePreview->getData());
					}

					return Response\AjaxJson::createError($generatePreview->getErrorCollection());
				}

				return Response\AjaxJson::createError();
			}
		}

		if ($render)
		{
			return Response\AjaxJson::createSuccess([
				'html' => $render->render(),
				'data' => $render->getData(),
			]);
		}

		return Response\AjaxJson::createError();
	}

	public function setPreviewImageId($fileId, $previewImageId)
	{
		$alreadyPreview = $this->getFilePreviewEntryByFileId($fileId);
		if (isset($alreadyPreview['ID']))
		{
			$result = FilePreviewTable::update($fileId, [
				'PREVIEW_IMAGE_ID' => $previewImageId,
			]);
		}
		else
		{
			$result = FilePreviewTable::add([
				'FILE_ID' => $fileId,
				'PREVIEW_IMAGE_ID' => $previewImageId,
			]);
		}

		return $result;
	}

	public function generatePreview($fileId): ?Main\Result
	{
		if (!$this->transformer)
		{
			return null;
		}

		//todo return status (OK, WAIT, ERROR) or some result.

		return $this->transformer->transform($fileId);
	}

	protected function getSourceUri(): Uri
	{
		$sourceSrc = $this->httpRequest->getHeader(self::HEADER_TO_GET_SOURCE);
		if (!$sourceSrc)
		{
			$sourceSrc = $this->httpRequest->getRequestUri();
		}

		return new Uri($sourceSrc);
	}

	protected function signFileId($fileId): string
	{
		$signer = new Security\Sign\Signer();

		return $signer->sign(
			base64_encode(serialize($fileId)),
			self::SALT_FILE_ID
		);
	}

	protected function unsignFileId($signedString)
	{
		$signer = new Security\Sign\Signer();

		$unsignedParameters = $signer->unsign(
			$signedString,
			self::SALT_FILE_ID
		);

		return unserialize(base64_decode($unsignedParameters), ['allowed_classes' => false]);
	}

	public function getByImage($fileId, Uri $sourceUri): ?Renderer\Image
	{
		$fileData = $this->getFileData($fileId);
		if (!$fileData)
		{
			return null;
		}

		$renderer = $this->buildRenderByFile(
			$fileData['ORIGINAL_NAME'],
			$fileData['CONTENT_TYPE'],
			$sourceUri,
			[
				'originalImage' => $fileData,
			]
		);
		if (!($renderer instanceof Renderer\Image))
		{
			return null;
		}

		return $renderer;
	}

	protected function getViewFileData(array $fileData)
	{
		static $cache = [];

		if (empty($fileData['ID']))
		{
			return null;
		}

		if (isset($cache[$fileData['ID']]) || array_key_exists($fileData['ID'], $cache))
		{
			return $cache[$fileData['ID']];
		}

		$filePreview = $this->getFilePreviewEntryByFileId($fileData['ID']);
		if (!$filePreview)
		{
			$cache[$fileData['ID']] = null;

			return null;
		}

		$cache[$fileData['ID']] = $this->getFileData($filePreview['PREVIEW_ID']);

		return $cache[$fileData['ID']];
	}

	protected function getFilePreviewEntryByFileId($fileId)
	{
		$row = FilePreviewTable::getList([
			'filter' => [
				'=FILE_ID' => $fileId,
			],
			'limit' => 1,
		])->fetch();

		return $row;
	}

	protected function buildRenderByFile($originalName, $contentType, Uri $sourceUri, array $options = [])
	{
		$options['contentType'] = $contentType;
		$rendererClass = $this->getRenderClassByFile([
			'contentType' => $contentType,
			'originalName' => $originalName,
		]);

		$reflectionClass = new \ReflectionClass($rendererClass);
		/** @see \Bitrix\Main\UI\Viewer\Renderer\Renderer::__construct */
		return $reflectionClass->newInstance($originalName, $sourceUri, $options);
	}

	public function getRenderClassByFile(array $file)
	{
		$contentType = $file['contentType'];
		$originalName = $file['originalName'];
		$rendererClass = $this->findRenderClassByContentType($contentType);
		if (!$rendererClass)
		{
			$contentTypeByName = MimeType::getByFilename($originalName);
			$rendererClass = $this->findRenderClassByContentType($contentTypeByName);
		}

		$rendererClass = $rendererClass? : Renderer\Stub::class;

		if ($this->shouldRestrictBySize($file, $rendererClass))
		{
			return Renderer\RestrictedBySize::class;
		}

		return $rendererClass;
	}

	private function shouldRestrictBySize(array $file, $rendererClass): bool
	{
		if (!isset($file['size']))
		{
			return false;
		}

		$size = $file['size'];
		$restriction = $rendererClass::getSizeRestriction();
		if ($restriction !== null && $size > $restriction)
		{
			return true;
		}

		return false;
	}

	private function findRenderClassByContentType($contentType)
	{
		foreach ($this->rendererList as $rendererClass)
		{
			/** @var Renderer\Renderer $rendererClass */
			if (in_array($contentType, $rendererClass::getAllowedContentTypes(), true))
			{
				return $rendererClass;
			}
		}

		return null;
	}

	/**
	 * Temporary dirty hack. It is here while we do not solve the problem of race conditions in b_file managed cache.
	 * Get file data from b_file on $fileId.
	 *
	 * @param int $fileId
	 * @param bool $cacheCleaned
	 * @return array|false
	 */
	protected function getFileData($fileId, $cacheCleaned = false)
	{
		$fileId = (int)$fileId;

		$fileData = \CFile::GetFileArray($fileId);
		if ($fileData === false && !$cacheCleaned)
		{
			global $DB;
			$strSql = "SELECT ID FROM b_file WHERE ID=" . $fileId;
			$dbResult = $DB->Query($strSql, false, "FILE: " . __FILE__ . "<br>LINE: " . __LINE__);
			if ($dbData = $dbResult->Fetch())
			{
				\CFile::CleanCache($fileId);

				return $this->getFileData($fileId, true);
			}
		}

		return $fileData;
	}
}
