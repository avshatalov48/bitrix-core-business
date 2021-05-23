<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Site\Cookies;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSiteCookiesComponent extends LandingBaseComponent
{
	/**
	 * Saves agreements.
	 * @return bool
	 */
	protected function actionSave(): bool
	{
		if ($this->checkAccess())
		{
			$result = true;
			$siteId = $this->arParams['SITE_ID'];
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$currentRequest = $context->getRequest();
			$post = $currentRequest->getPostList()->getValues();

			// compare post agreements with system
			foreach ($this->arResult['AGREEMENTS']['ALL'] as $code => $agreement)
			{
				// custom cookies
				if ($agreement['SYSTEM'] == 'N')
				{
					if (array_key_exists('agreement_text_' . $code, $post))
					{
						$res = Cookies::updateAgreementForSite($agreement['ID'], [
							'TITLE' => $post['agreement_title_' . $code] ?? null,
							'CONTENT' => $post['agreement_text_' . $code]
						]);
						$this->addErrorFromResult($res);
						if (!$res->isSuccess())
						{
							$result = false;
						}
					}
					else
					{
						Cookies::removeAgreementsForSite($siteId, $code);
					}
					unset($post['agreement_title_' . $code]);
					unset($post['agreement_text_' . $code]);
					continue;
				}

				// system cookies
				if (array_key_exists('agreement_text_' . $code, $post))
				{
					$hashPostTitle = mb_strtolower(preg_replace('/[\s]+/is', '', $post['agreement_title_' . $code]));
					$hashOriginalTitle = mb_strtolower(preg_replace('/[\s]+/is', '', $agreement['~TITLE']));
					$hashPostContent = mb_strtolower(preg_replace('/[\s]+/is', '', $post['agreement_text_' . $code]));
					$hashOriginalContent = mb_strtolower(preg_replace('/[\s]+/is', '', $agreement['~CONTENT']));
					$hashMismatch = $hashPostTitle != $hashOriginalTitle ||
					                $hashPostContent != $hashOriginalContent;
					if ($agreement['ID'])
					{
						if ($hashMismatch)
						{
							$res = Cookies::updateAgreementForSite($agreement['ID'], [
								'ACTIVE' => $post['agreement_active_' . $code] ?? 'Y',
								'TITLE' => $post['agreement_title_' . $code] ?? null,
								'CONTENT' => $post['agreement_text_' . $code]
							]);
							$this->addErrorFromResult($res);
							if (!$res->isSuccess())
							{
								$result = false;
							}
						}
						else
						{
							Cookies::removeAgreementsForSite($siteId, $code);
						}
					}
					else if ($hashMismatch)
					{
						$res = Cookies::addAgreementForSite($siteId, [
							'CODE' => $code,
							'ACTIVE' => $post['agreement_active_' . $code] ?? 'Y',
							'TITLE' => $post['agreement_title_' . $code] ?? null,
							'CONTENT' => $post['agreement_text_' . $code]
						]);
						$this->addErrorFromResult($res);
						if (!$res->isSuccess())
						{
							$result = false;
						}
					}
					unset($post['agreement_title_' . $code]);
					unset($post['agreement_text_' . $code]);
				}
				else if ($agreement['ID'])
				{
					Cookies::removeAgreementsForSite($siteId, $code);
				}
			}

			// detect new custom cookies
			foreach ($post as $key => $value)
			{
				if (strpos($key, 'agreement_text_') === 0)
				{
					$dynamicKey = substr($key, strlen('agreement_text_'));
					$title = $post['agreement_title_' . $dynamicKey] ?? null;
					$content = $value;
					$res = Cookies::addAgreementForSite($siteId, [
						'CODE' => $dynamicKey,
						'TITLE' => $title,
						'CONTENT' => $content
					]);
					$this->addErrorFromResult($res);
					if (!$res->isSuccess())
					{
						$result = false;
					}
				}
			}

			return $result;
		}

		return false;
	}

	/**
	 * Check access to settings edit.
	 * @return bool
	 */
	protected function checkAccess(): bool
	{
		static $access = null;

		if ($access !== null)
		{
			return $access;
		}

		if ($this->arParams['SITE_ID'])
		{
			$access = Rights::hasAccessForSite(
				$this->arParams['SITE_ID'],
				Rights::ACCESS_TYPES['sett']
			);
		}
		else
		{
			$access = false;
		}

		return $access;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();
		$this->checkParam('SITE_ID', 0);
		$this->checkParam('TYPE', '');

		if ($init)
		{
			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);
		}

		if ($init && !$this->checkAccess())
		{
			$this->addError('ACCESS_DENIED', '', true);
		}
		else if ($init)
		{
			$agreements = Cookies::getAgreements($this->arParams['SITE_ID']);;
			$this->arResult['AGREEMENTS'] = [];
			$this->arResult['AGREEMENTS']['ALL'] = $agreements;
			$this->arResult['SITE_INCLUDES_SCRIPT'] = Cookies::isSiteIncludesScript(
				$this->arParams['SITE_ID']
			);
			$this->arResult['AGREEMENTS']['SYSTEM'] = array_filter($agreements, function($item) {
				return $item['SYSTEM'] == 'Y';
			});
			$this->arResult['AGREEMENTS']['CUSTOM'] = array_filter($agreements, function($item) {
				return $item['SYSTEM'] == 'N';
			});
		}

		parent::executeComponent();
	}
}