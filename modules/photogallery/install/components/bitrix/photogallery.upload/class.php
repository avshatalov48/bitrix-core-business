<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once(__DIR__.'/functions.php');

class CPhotogalleryUpload extends \CBitrixComponent implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	/*@var Main\ErrorCollection */
	protected $errors;
	protected $iblockId = null;
	protected $gallery = null;
	protected $section = null;
	protected $uploader = null;
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errors = new Main\ErrorCollection();
	}

	public function onPrepareComponentParams($params)
	{
		if (!CModule::IncludeModule('photogallery'))
		{
			$this->errors[] = new Main\Error(Loc::getMessage('P_MODULE_IS_NOT_INSTALLED'));
		}
		if (!CModule::IncludeModule('iblock'))
		{
			$this->errors[] = new Main\Error(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
		}
		if ($params['BEHAVIOUR'] == 'USER' && empty($params['USER_ALIAS']))
		{
			$this->errors->setError(new Main\Error(Loc::getMessage('P_GALLERY_EMPTY')));
		}

		$params['IBLOCK_TYPE'] = trim($params['IBLOCK_TYPE']);
		$params['IBLOCK_ID'] = intval($params['IBLOCK_ID']);
		$params['SECTION_ID'] = intval($params['SECTION_ID']);
		$params['USER_ALIAS'] = trim($params['USER_ALIAS']);
		$params['BEHAVIOUR'] = ($params['BEHAVIOUR'] == 'USER' ? 'USER' : 'SIMPLE');
		$params['GALLERY_SIZE'] = intval($params['GALLERY_SIZE']) * 1024 * 1024;

		$params['PERMISSION_EXTERNAL'] = trim($params['PERMISSION']);

		if ($this->errors->count() <= 0)
		{
			$gallery = new CPGalleryInterface(
				array(
					'IBlockID' => $params['IBLOCK_ID'],
					'GalleryID' => $params['BEHAVIOUR'] == 'USER' ? $params['USER_ALIAS'] : null,
					'Permission' => $params['PERMISSION_EXTERNAL']),
				array(
					'cache_time' => $params['CACHE_TIME'],
					'set_404' => $params['SET_STATUS_404']
				)
			);

			$this->iblockId = $params['IBLOCK_ID'];
			$this->gallery = $gallery->Gallery;
			if ($params['BEHAVIOUR'] == 'USER' && !$this->gallery)
			{
				$this->errors->setError(new Main\Error('Gallery was not found.'));
			}

			$params['PERMISSION'] = $gallery->User['Permission'];
			if ($params['SECTION_ID'] > 0)
			{
				$res = $gallery->GetSection($params['SECTION_ID'], $this->section);
				if ($res > 400)
				{
					$this->errors->setError(new Main\Error('Album was not found.'));
				}
				elseif ($res == 301)
				{
					$this->errors->setError(new Main\Error('Album has moved.', 301));
				}
			}
		}

		if ($params['PERMISSION'] < 'W')
		{
			$this->errors->setError(new Main\Error(Loc::getMessage('P_DENIED_ACCESS')));
		}

		$params['ABS_PERMISSION'] = CIBlock::GetPermission($params['IBLOCK_ID']);
		if ($params['ABS_PERMISSION'] < 'W'
			&& 0 < $params['GALLERY_SIZE']
			&& $this->gallery
			&& $params['GALLERY_SIZE'] < intval($this->gallery['UF_GALLERY_SIZE']))
		{
			$this->errors->setError(new Main\Error(Loc::getMessage('P_GALLERY_NOT_SIZE')));
		}
		if ($this->errors->isEmpty())
		{
			$this->prepareActionParams($params);
			$this->prepareWatermarkParams($params);
		}
		return $params;
	}

	private function prepareActionParams(&$params)
	{
		$params['NEW_ALBUM_NAME'] = GetMessage('P_NEW_ALBUM') <> '' ? GetMessage('P_NEW_ALBUM') : 'New album';
		$params['ALBUM_PHOTO_THUMBS_WIDTH'] = intval($params['ALBUM_PHOTO_THUMBS_WIDTH'] ?: 120);

		$params['UPLOAD_MAX_FILE_SIZE'] =  min(
				[
					_get_size(ini_get('post_max_size')),
					_get_size(ini_get('upload_max_filesize')),
				] + ($params['UPLOAD_MAX_FILE_SIZE'] > 0 ? [intval($params['UPLOAD_MAX_FILE_SIZE'])] : [])
			) * 1024 * 1024;

		// Sizes
		$params['SIZES'] = array(1280, 1024, 800);
		$params['ORIGINAL_SIZE'] = intval($params['ORIGINAL_SIZE']);
		if ($params['ORIGINAL_SIZE'] > 0 && !in_array($params['ORIGINAL_SIZE'], $params['SIZES']))
		{
			$params['SIZES'][] = $params['ORIGINAL_SIZE'];
		}
		$params['THUMBNAIL_SIZE'] = max(($params['THUMBNAIL_SIZE'] ?? 90), 50);

		// Additional sights
		$params['ADDITIONAL_SIGHTS'] = is_array($params['ADDITIONAL_SIGHTS']) ? $params['ADDITIONAL_SIGHTS'] : [];
		$params['PICTURES'] = [];
		if (!empty($params['ADDITIONAL_SIGHTS']) &&
			Main\Config\Option::get('photogallery', 'pictures') <> '' &&
			($additionalCopies = @unserialize(Main\Config\Option::get('photogallery', 'pictures'), ['allowed_classes' => false])))
		{
			foreach ($additionalCopies as $key => $val)
			{
				if (in_array(str_pad($key, 5, '_').$val['code'], $params['ADDITIONAL_SIGHTS']))
				{
					$params['PICTURES'][$val['code']] = array(
						'size' => $additionalCopies[$key]['size'],
						'quality' => $additionalCopies[$key]['quality']
					);
				}
			}
		}
		$params['MODERATION'] = ($params['MODERATION'] == 'Y' ? 'Y' : 'N');
		$params['PUBLIC_BY_DEFAULT'] = ($params['SHOW_PUBLIC'] == 'N' || $params['PUBLIC_BY_DEFAULT'] != 'N' ? 'Y' : 'N');
		$params['APPROVE_BY_DEFAULT'] = ($params['APPROVE_BY_DEFAULT'] == 'N' ? 'N' : 'Y');
	}

	private function prepareWatermarkParams(&$params)
	{
		if ($params['USE_WATERMARK'] !== 'Y' || !function_exists('gd_info'))
		{
			$params['USE_WATERMARK'] = 'N';
			return;
		}
		$params['WATERMARK_RULES'] = ($params['WATERMARK_RULES'] == 'ALL' ? 'ALL' : 'USER');
		$params['WATERMARK_TYPE'] = ($params['WATERMARK_TYPE'] == 'TEXT' ? 'TEXT' : 'PICTURE');
		$params['WATERMARK_TEXT'] = trim($params['WATERMARK_TEXT']);

		$params['SHOW_WATERMARK'] = $params['WATERMARK_RULES'] == 'ALL' ? 'N' : 'Y';

		// We have ugly default font but it's better than no font at all
		if (trim($params['PATH_TO_FONT']) === '')
		{
			$params['PATH_TO_FONT'] = 'default.ttf';
		}
		$params['WATERMARK_COLOR'] = '#'.trim($params['WATERMARK_COLOR'], ' #');
		$params['WATERMARK_SIZE'] = intval($params['WATERMARK_SIZE']);
		$params['WATERMARK_FILE'] = trim($params['WATERMARK_FILE']);

		$params['WATERMARK_FILE_ORDER'] = in_array($params['WATERMARK_FILE_ORDER'], ['usual', 'resize', 'repeat']) ? : 'usual';
		$params['WATERMARK_POSITION'] = trim($params['WATERMARK_POSITION']);

		$arPositions = array('TopLeft', 'TopCenter', 'TopRight', 'CenterLeft', 'Center', 'CenterRight', 'BottomLeft', 'BottomCenter', 'BottomRight');
		$arPositions2 = array('tl', 'tc', 'tr', 'ml', 'mc', 'mr', 'bl', 'bc', 'br');

		if (in_array($params['WATERMARK_POSITION'], $arPositions2))
			$params['WATERMARK_POSITION'] = str_replace($arPositions2, $arPositions, $params['WATERMARK_POSITION']);
		else
			$params['WATERMARK_POSITION'] = 'BottomRight';

		$params['WATERMARK_TRANSPARENCY'] = trim($params['WATERMARK_TRANSPARENCY']);
		$params['WATERMARK_MIN_PICTURE_SIZE'] = intval($params['WATERMARK_MIN_PICTURE_SIZE'] ?: 800);
	}

	private function prepareTemplateParams(&$params)
	{
		foreach ([
			'INDEX' => [],
			'GALLERY' => ['PAGE_NAME' => 'gallery'],
			'SECTION' => ['PAGE_NAME' => 'section', 'SECTION_ID' => '#SECTION_ID#'],
			'SECTION_EDIT' => ['PAGE_NAME' => 'section_edit', 'SECTION_ID' => '#SECTION_ID#'],
		] as $page => $pageParams)
		{
			if (empty($params[$page.'_URL']))
			{
				$uri = new Main\Web\Uri($this->request->getRequestUri());
				$uri->addParams(($pageParams + ($params['BEHAVIOUR'] == 'USER' ? ['USER_ALIAS' => '#USER_ALIAS#'] : [])));
				$params[$page.'_URL'] = $uri->getUri();
			}
			$params['~'.$page.'_URL'] = trim($params[$page.'_URL']);
			$params[$page.'_URL'] = htmlspecialcharsbx($params['~'.$page.'_URL']);
		}

		$test_str = '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=';
		if (strncmp(POST_FORM_ACTION_URI, $test_str, 52) === 0)
		{
			$sUrlPath = urldecode(mb_substr(POST_FORM_ACTION_URI, 52));
			$sUrlPath = CHTTP::urlDeleteParams($sUrlPath, array('view_mode', 'sessid', 'uploader_redirect'), true);
			$params['ACTION_URL'] = htmlspecialcharsbx('/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL='.urlencode($sUrlPath));
		}
		else
		{
			$params['ACTION_URL'] = CHTTP::urlDeleteParams(htmlspecialcharsback(POST_FORM_ACTION_URI), array('view_mode', 'sessid', 'uploader_redirect'), true);
		}

		$params["ACTION_URL"] = CHTTP::urlAddParams(
			$params["ACTION_URL"],
			[
				'analyticsLabel[action]' => 'uploadPhoto'
			]
		);

		$params['SUCCESS_URL'] = CHTTP::urlDeleteParams(CComponentEngine::MakePathFromTemplate($params['~SECTION_URL'],
			array('USER_ALIAS' => $params['USER_ALIAS'], 'SECTION_ID' => $params['SECTION_ID'])), array('sessid', 'uploader_redirect'), true);
		$params['REDIRECT_URL'] = $params['ACTION_URL'];
		$params['REDIRECT_URL'] = CHTTP::urlDeleteParams($params['REDIRECT_URL'], array('clear_cache', 'bitrix_include_areas', 'bitrix_show_mode', 'back_url_admin', 'bx_photo_ajax', 'change_view_mode_data', 'sessid', 'uploader_redirect'));
		$params['REDIRECT_URL'] .= (mb_strpos($params['REDIRECT_URL'], '?') === false ? '?' : '&').'uploader_redirect=Y&sessid='.bitrix_sessid();
	}

	public function executeComponent()
	{
		$this->arResult['GALLERY'] = $this->gallery;
		$this->arResult['SECTION'] = $this->section;
		if (!$this->errors->isEmpty())
		{
			if ($this->errors->getErrorByCode(303) !== null)
			{
				$url = CComponentEngine::MakePathFromTemplate(
					$this->arParams['~SECTION_URL'],
					[
						'USER_ALIAS' => $this->gallery ? $this->gallery['CODE'] : '',
						'SECTION_ID' => $this->arParams['SECTION_ID']
					]);
				LocalRedirect($url, false, '301 Moved Permanently');
			}
			ShowError($this->errors->getValues()[0]->getMessage());
			return false;
		}

		$this->prepareTemplateParams($this->arParams);
		$this->arParams['bxu'] = $this->getUploader();
		if ($this->request->isPost()
			&& check_bitrix_sessid()
			&& $this->request->getPost('save_upload') === 'Y')
		{
			$this->uploadAction();
		}

		return $this->__includeComponent();
	}

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			'BEHAVIOUR',
			'USER_ALIAS',
			'IBLOCK_TYPE',
			'IBLOCK_ID',
			'SECTION_ID',
			'USER_ALIAS',
			'BEHAVIOUR',
			'PERMISSION',
			'GALLERY_SIZE',

			'UPLOAD_MAX_FILE_SIZE',
			'ADDITIONAL_SIGHTS',
			'PATH_TO_FONT',
			'WATERMARK_MIN_PICTURE_SIZE',

			'MODERATION',
			'SHOW_PUBLIC',
			'PUBLIC_BY_DEFAULT',
			'APPROVE_BY_DEFAULT',
		];
	}

	public function getUploader()
	{
		if ($this->uploader !== null)
		{
			return $this->uploader;
		}
		// We use only square thumbnails, so we increase thumbnail size for better quality
		$thumbSize = round($this->arParams['THUMBNAIL_SIZE'] * 1.8);

		$copies = array(
			'real_picture' => array(
				'code' => 'real_picture',
				'width' => ($this->arParams['ORIGINAL_SIZE'] > 0 ? $this->arParams['ORIGINAL_SIZE'] : false),
				'height' => ($this->arParams['ORIGINAL_SIZE'] > 0 ? $this->arParams['ORIGINAL_SIZE'] : false)
			),
			'thumbnail' => array(
				'code' => 'thumbnail',
				'width' => $thumbSize,
				'height' => $thumbSize
			)
		);
		if (is_array($this->arParams['PICTURES']) && !empty($this->arParams['PICTURES']))
		{
			foreach ($this->arParams['PICTURES'] as $key => $val)
			{
				$copies[$key] = array(
					'code' => $key,
					'width' => $val['size'],
					'height' => $val['size']
				);
			}
		}

		$this->arParams['converters'] = $copies;
		$res = new CPhotoUploader(
			$this->arParams,
			$this->gallery,
			$this->arResult
		);

		$params = array(
			'copies' => array_diff_key($this->arParams['converters'], array('real_picture' => true)),
			'allowUpload' => 'I',
			'uploadFileWidth' => $this->arParams['ORIGINAL_SIZE'],
			'uploadFileHeight' => $this->arParams['ORIGINAL_SIZE'],
			'uploadMaxFilesize' => $this->arParams['UPLOAD_MAX_FILE_SIZE'],
			'events' => array(
				'onPackageIsStarted' => array($res, 'onBeforeUpload'),
				'onPackageIsContinued' => array($res, 'onBeforeUpload'),
				'onPackageIsFinished' => array($res, 'onAfterUpload'),
				'onFileIsUploaded' => array($res, 'handleFile')
			),
			'storage' => array(
				'moduleId' => 'photogallery'
			)
		);

		$this->uploader = new Main\UI\Uploader\Uploader($params);

		return $this->uploader;
	}

	public function uploadAction()
	{
		$this->getUploader()->checkPost();
	}

	public function uploadWatermarkFileAction()
	{
		if (isset($_REQUEST['watermark_iframe']) && $_REQUEST['watermark_iframe'] == 'Y' && check_bitrix_sessid())
		{
			$UploadError = false;
			$pathto = '';
			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				$file = $_FILES['watermark_img'];
				$checkImgMsg = CFile::CheckImageFile($file);
				if ($file['error'] != 0)
				{
					$UploadError = '[IU_WM01] '.GetMessage('P_WM_IMG_ERROR01');
				}
				elseif($checkImgMsg <> '' || $checkImgMsg === '')
				{
					$UploadError = '[IU_WM02] '.($checkImgMsg === '' ? GetMessage('P_WM_IMG_ERROR02') : $checkImgMsg);
				}
				else
				{
					$imgArray = CFile::GetImageSize($file['tmp_name']);
					if(is_array($imgArray))
					{
						$width = $imgArray[0];
						$height = $imgArray[1];
					}

					$pathto = CTempFile::GetDirectoryName(1).'/'.'watermark_'.md5($file['name']).GetFileExtension($file['name']);
					CheckDirPath($pathto);

					$pathtoRel = mb_substr($pathto, mb_strlen($_SERVER['DOCUMENT_ROOT']));

					if(!move_uploaded_file($file['tmp_name'], $pathto))
						$UploadError = '[IU_WM03] '.GetMessage('P_WM_IMG_ERROR03');
				}
			}
			$APPLICATION->RestartBuffer();
			?>
			<script>
				<?if ($UploadError === false && $pathto != ''):?>
				top.bxiu_wm_img_res = {path: '<?= CUtil::JSEscape($pathtoRel)?>', width: '<?= $width?>', height: '<?= $height?>'};
				<?elseif($UploadError !== false):?>
				top.bxiu_wm_img_res = {error: '<?= $UploadError?>'};
				<?endif;?>
			</script>
			<?
			die();
		}
	}

	public function getErrors()
	{

	}

	public function getErrorByCode($code)
	{

	}
}