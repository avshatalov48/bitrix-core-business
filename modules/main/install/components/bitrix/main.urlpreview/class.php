<?php

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UrlPreview\UrlMetadataTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class UrlPreviewComponent extends \CBitrixComponent
{
	protected $editMode = false;
	protected $checkAccess = false;
	protected $multiple = false;
	protected $metadataId;
	protected $mobileApp = false;

	protected function prepareParams()
	{
		$this->editMode = isset($this->arParams['EDIT']) && $this->arParams['EDIT'] === 'Y';
		$this->mobileApp = isset($this->arParams['PARAMS']['MOBILE']) && $this->arParams['PARAMS']['MOBILE'] === 'Y';

		if($this->mobileApp)
			$this->setTemplateName('mobile');
		else
			$this->setTemplateName('.default');

		return $this;
	}

	/**
	 * Sets component arResult array
	 */
	protected function prepareData()
	{
		$this->arResult['METADATA'] = $this->arParams['METADATA'];
		$this->setDynamicPreview();

		$this->arResult['FIELD_NAME'] = $this->arParams['PARAMS']['arUserField']['FIELD_NAME'];
		if (isset($this->arResult['METADATA']['ID']) && $this->arResult['METADATA']['ID'] > 0)
		{
			$this->arResult['FIELD_VALUE'] = Main\UrlPreview\UrlPreview::sign($this->arResult['METADATA']['ID']);
		}
		else
		{
			$this->arResult['FIELD_VALUE'] = null;
		}

		$this->arResult['FIELD_ID'] = $this->arParams['PARAMS']['arUserField']['ID'] ?? null;
		$this->arResult['ELEMENT_ID'] = $this->arParams['PARAMS']['urlPreviewId'] ?? null;

		if(isset($this->arParams['~METADATA']['EMBED']) && $this->arParams['~METADATA']['EMBED'] != '')
		{
			$this->arResult['METADATA']['EMBED'] = $this->arParams['~METADATA']['EMBED'];
			if(mb_strpos($this->arResult['METADATA']['EMBED'], '<iframe') !== 0)
			{
				$this->arResult['METADATA']['EMBED'] = '<iframe class="urlpreview-iframe-html-embed" src="'.Main\UrlPreview\UrlPreview::getInnerFrameUrl($this->arResult['METADATA']['ID']).'" allowfullscreen="" width="'.Main\UrlPreview\UrlPreview::IFRAME_MAX_WIDTH.'" height="'.Main\UrlPreview\UrlPreview::IFRAME_MAX_HEIGHT.'" frameborder="0" onload="BXUrlPreview.adjustFrameHeight(this);"></iframe>';
			}
			$this->arResult['METADATA']['EMBED'] = $this->prepareFrame($this->arResult['METADATA']['EMBED']);
		}
		else
		{
			if(isset($this->arParams['METADATA']['EXTRA']['VIDEO']) && $this->arParams['METADATA']['EXTRA']['VIDEO'])
			{
				$this->arResult['METADATA']['EMBED'] = $this->invokePlayer();
			}
			else
			{
				$this->arResult['METADATA']['EMBED'] = null;
			}
		}

		$this->arResult['SELECT_IMAGE'] = (
				$this->editMode
				&& empty($this->arResult['METADATA']['EMBED'])
				&& isset($this->arResult['METADATA']['EXTRA'])
				&& is_array($this->arResult['METADATA']['EXTRA'])
				&& isset($this->arResult['METADATA']['EXTRA']['IMAGES'])
				&& is_array($this->arResult['METADATA']['EXTRA']['IMAGES'])
		);

		if($this->arResult['SELECT_IMAGE'])
		{
			$this->arResult['SELECTED_IMAGE'] = $this->arResult['METADATA']['EXTRA']['SELECTED_IMAGE'] ?: 0;
		}
		else
		{
			$this->arResult['METADATA']['CONTAINER']['CLASSES'] = "";

			if (
				isset($this->arResult['METADATA']['IMAGE_ID'])
				&& $this->arResult['METADATA']['IMAGE_ID'] > 0
				&& $imageFile = \CFile::GetFileArray($this->arResult['METADATA']['IMAGE_ID']))
			{
				$this->arResult['METADATA']['IMAGE'] = $imageFile['SRC'];
				if($imageFile['HEIGHT'] > $imageFile['WIDTH'] * 1.5)
				{
					$this->arResult['METADATA']['CONTAINER']['CLASSES'] .= " urlpreview__container-left";
				}
			}
			$this->arResult['SHOW_CONTAINER'] = isset($this->arResult['METADATA']['IMAGE']) && $this->arResult['METADATA']['IMAGE'] != ''
					|| isset($this->arResult['METADATA']['EMBED']) && $this->arResult['METADATA']['EMBED'] != '';

			if( isset($this->arResult['METADATA']['IMAGE'])
					&& $this->arResult['METADATA']['IMAGE'] != ''
					&& isset($this->arResult['METADATA']['EMBED'])
					&& $this->arResult['METADATA']['EMBED'] != ''
			)
			{
				$this->arResult['METADATA']['CONTAINER']['CLASSES'] .= " urlpreview__container-switchable";
				$this->arResult['METADATA']['CONTAINER']['CLASSES'] .= " urlpreview__container-hide-embed";
			}
		}
	}

	protected function prepareFrame($embed)
	{
		if($this->mobileApp)
		{
			$document = new Main\UrlPreview\HtmlDocument($embed, new Main\Web\Uri('/'));
			$attributes = $document->extractElementAttributes('iframe');
			if(!empty($attributes))
			{
				$attributes = $attributes[0];
				$attributes['height'] = '100%';
				$attributes['width'] = '100%';
				$attributes['class'] = isset($attributes['class']) ? $attributes['class'].' ' : '';
				$attributes['class'] .= 'bx-mobile-video-frame';
				$embed = '<iframe';
				foreach($attributes as $name => $value)
				{
					$embed .= ' '.$name.'="'.$value.'"';
				}
				$embed.= '></iframe>';
			}
		}

		return $embed;
	}

	/**
	 * Sets main element style
	 */
	protected function prepareStyle()
	{
		$this->arResult['STYLE'] = '';
		if(!isset($this->arResult['METADATA']['ID']))
		{
			$this->arResult['STYLE'] .= "display:none; ";
		}
		if(isset($this->arParams['PARAMS']['STYLE']))
		{
			$this->arResult['STYLE'] .= $this->arParams['PARAMS']['STYLE']."; ";
		}
	}

	protected function setDynamicPreview()
	{
		if (
			isset($this->arParams['METADATA']['TYPE'])
			&& $this->arParams['METADATA']['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC
		)
		{
			if (is_array($this->arParams['METADATA']['HANDLER']))
			{
				$module = $this->arParams['METADATA']['HANDLER']['MODULE'];
				$className = $this->arParams['METADATA']['HANDLER']['CLASS'];
				$buildMethod = $this->arParams['METADATA']['HANDLER']['BUILD_METHOD'];
				$parameters = $this->arParams['METADATA']['HANDLER']['PARAMETERS'];
				if (Loader::includeModule($module) && method_exists($className, $buildMethod))
				{
					$this->arResult['DYNAMIC_PREVIEW'] = $className::$buildMethod($parameters);
				}
			} else
			{
				$this->arResult['METADATA']['ID'] = null;
			}
		}
	}

	/**
	 * Include component bitrix:player to view html5 player. Returns html.
	 *
	 * @return string
	 */
	protected function invokePlayer()
	{
		global $APPLICATION;
		$params = array(
			'PATH' => $this->arParams['METADATA']['EXTRA']['VIDEO'],
			'PLAYER_TYPE' => 'videojs',
			'WIDTH' => '600',
			'HEIGHT' => '340',
		);
		if(isset($this->arParams['METADATA']['EXTRA']['VIDEO_TYPE']))
		{
			$params['TYPE'] = $this->arParams['METADATA']['EXTRA']['VIDEO_TYPE'];
		}
		if(isset($this->arParams['METADATA']['IMAGE']))
		{
			$params['PREVIEW'] = $this->arParams['METADATA']['IMAGE'];
		}
		$playerComponent = 'bitrix:player';
		if($this->mobileApp)
		{
			$playerComponent = 'bitrix:mobile.player';
		}
		ob_start();
		$APPLICATION->IncludeComponent($playerComponent, '', $params);
		return ob_get_clean();
	}

	/**
	 * Executes component
	 */
	public function executeComponent()
	{
		$this->prepareParams();
		if(
			!isset($this->arParams['METADATA']['ID'])
			|| $this->arParams['METADATA']['TYPE'] == UrlMetadataTable::TYPE_STATIC
			|| (
					$this->arParams['METADATA']['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC
					&& !$this->mobileApp
				)
		  )
		{
			$this->prepareData();
			$this->prepareStyle();
			if (
				isset($this->arParams['METADATA']['TYPE'])
				&& $this->arParams['METADATA']['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC
				&& $this->arResult['DYNAMIC_PREVIEW'] == ''
			)
			{
				return;
			}

			$this->includeComponentTemplate($this->editMode ? 'edit' : 'show');
		}
	}
}