<?php

namespace Bitrix\UI\Controller;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;
use Bitrix\UI\FileUploader\Chunk;
use Bitrix\UI\FileUploader\ControllerResolver;
use Bitrix\UI\FileUploader\Uploader;
use Bitrix\UI\FileUploader\UploaderController;
use Bitrix\UI\FileUploader\UploaderError;

class FileUploader extends Controller
{
	public function configureActions()
	{
		$configureActions = [];

		$configureActions['preview'] = [
			'-prefilters' => [
				ActionFilter\Csrf::class,
				ActionFilter\HttpMethod::class,
			],
			'+prefilters' => [
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_GET]),
				// new ActionFilter\CheckImageSignature(),
			]
		];

		$configureActions['download'] = [
			'-prefilters' => [
				ActionFilter\Csrf::class,
				ActionFilter\HttpMethod::class,
			],
			'+prefilters' => [
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_GET]),
			]
		];

		return $configureActions;
	}

	protected function getDefaultPreFilters(): array
	{
		return [
			new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
			new ActionFilter\Csrf(),
			new ActionFilter\CloseSession(),
		];
	}

	/**
	 * Returns the whitelist of UploadControllers.
	 * You have to override this method for custom ajax controllers.
	 * @return array|null
	 */
	protected function getAvailableControllers(): ?array
	{
		return null;
	}

	public function getAutoWiredParameters(): array
	{
		$request = $this->getRequest();

		return [
			new ExactParameter(
				UploaderController::class,
				'controller',
				function ($className, string $controller, string $controllerOptions = null) {
					try
					{
						$options = empty($controllerOptions) ? [] : Json::decode($controllerOptions);
						$options = is_array($options) ? $options : [];
					}
					catch (\Exception $e)
					{
						$options = [];
					}

					if (is_subclass_of($this, FileUploader::class))
					{
						$availableControllers = $this->getAvailableControllers();
						if ($availableControllers === null)
						{
							$this->addError(new Error(
								'You have to override "getAvailableControllers" method for your custom ajax controller.'
							));

							return null;
						}

						[$moduleId, $className] = ControllerResolver::resolveName($controller);
						$className = strtolower(ltrim($className, '\\'));

						$availableNames = array_map(function($name) {
							return strtolower(ltrim($name, '\\'));
						}, $availableControllers);

						if (
							!in_array(strtolower($controller), $availableNames)
							&& !in_array($className, $availableNames)
						)
						{
							$this->addError(new Error('Invalid Controller Name.'));

							return null;
						}
					}

					return ControllerResolver::createController($controller, $options);
				}
			),
			new Parameter(
				Chunk::class,
				function ($className) use ($request) {
					$result = Chunk::createFromRequest($request);
					if ($result->isSuccess())
					{
						return $result->getData()['chunk'];
					}
					else
					{
						$this->addErrors($result->getErrors());

						return null;
					}
				}
			),
		];
	}

	public function processBeforeAction(Action $action): bool
	{
		$contentLength = (int)$this->getRequest()->getServer()->get('CONTENT_LENGTH');
		$maxFileSize = min(
			\CUtil::unformat(ini_get('upload_max_filesize')),
			\CUtil::unformat(ini_get('post_max_size'))
		);

		if ($contentLength > $maxFileSize)
		{
			$this->addError(new UploaderError(UploaderError::TOO_BIG_REQUEST));

			return false;
		}

		return parent::processBeforeAction($action);
	}

	public function uploadAction(UploaderController $controller, Chunk $chunk, string $token = null): array
	{
		$uploader = new Uploader($controller);
		$uploadResult = $uploader->upload($chunk, $token);
		if ($uploadResult->isSuccess())
		{
			return $uploadResult->jsonSerialize();
		}
		else
		{
			$this->addErrors($uploadResult->getErrors());
		}

		return [];
	}

	public function loadAction(UploaderController $controller, array $fileIds): array
	{
		$uploader = new Uploader($controller);
		$loadResults = $uploader->load($fileIds);

		return [
			'files' => $loadResults,
		];
	}

	public function downloadAction(UploaderController $controller, string $fileId)
	{
		$uploader = new Uploader($controller);
		$loadResults = $uploader->load([$fileId]);
		$loadResult = $loadResults->getAll()[0] ?? null;

		if ($loadResult === null)
		{
			$this->addError(new UploaderError(UploaderError::FILE_LOAD_FAILED));
		}
		else if ($loadResult->isSuccess())
		{
			$fileId = $loadResult->getFile() ? $loadResult->getFile()->getFileId() : 0;
			if ($fileId > 0)
			{
				return Response\BFile::createByFileId($fileId);
			}
			else
			{
				$this->addError(new UploaderError(UploaderError::FILE_FIND_FAILED));
			}
		}
		else
		{
			$this->addErrors($loadResult->getErrors());
		}

		return [];
	}

	public function previewAction(UploaderController $controller, string $fileId)
	{
		$uploader = new Uploader($controller);
		$loadResults = $uploader->load([$fileId]);
		$loadResult = $loadResults->getAll()[0] ?? null;

		if ($loadResult === null)
		{
			$this->addError(new UploaderError(UploaderError::FILE_LOAD_FAILED));
		}
		else if ($loadResult->isSuccess())
		{
			$imageId = $loadResult->getFile() ? $loadResult->getFile()->getFileId() : 0;
			$imageData = \CFile::getFileArray($imageId);

			if (is_array($imageData))
			{
				// Sync with \Bitrix\UI\FileUploader\Uploader::getFileInfo
				$response = new Response\ResizedImage($imageData, 300, 300);
				$response->setResizeType(BX_RESIZE_IMAGE_PROPORTIONAL);
				$response->setCacheTime(86400);

				return $response;
			}
			else
			{
				$this->addError(new UploaderError(UploaderError::FILE_FIND_FAILED));
			}
		}
		else
		{
			$this->addErrors($loadResult->getErrors());
		}

		return [];
	}

	public function removeAction(UploaderController $controller, array $fileIds): array
	{
		$uploader = new Uploader($controller);
		$removeResult = $uploader->remove($fileIds);

		return [
			'files' => $removeResult,
		];
	}
}
