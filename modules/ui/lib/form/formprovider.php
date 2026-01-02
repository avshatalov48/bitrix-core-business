<?php

namespace Bitrix\UI\Form;

/**
 * The FormProvider class provides a list of partner feedback forms with region zones, form IDs, languages, and security keys.
 *
 * For displaying partner feedback forms on the client side, use the PartnerForm class (see ui/partnerform/src/PartnerForm.js).
 *
 * Each form is associated with one or more zones (regions), a language code, and a unique security key.
 * The list is intended for localized feedback widgets or CRM forms.
 *
 * @package Bitrix\UI\Form
 */
class FormProvider
{
	/**
	 * Returns a list of feedback forms with their respective region zones, form IDs, languages, and security keys.
	 *
	 * Each form is associated with one or more zones (regions), a language code, and a unique security key.
	 * These forms are typically used to display localized feedback widgets or CRM forms.
	 * @return array[]
	 */
	public function getPartnerFormList(): array
	{
		$westZones = \Bitrix\UI\Form\FeedbackForm::getWestZones();

		return [
			['zones' => $westZones, 'id' => 34, 'lang' => 'en', 'sec' => '940xiq'],
			['zones' => $westZones, 'id' => 58, 'lang' => 'br', 'sec' => 'db0u10'],
			['zones' => $westZones, 'id' => 82, 'lang' => 'la', 'sec' => 'f5cg69'],
			['zones' => $westZones, 'id' => 84, 'lang' => 'pl', 'sec' => 'xaq93h'],
			['zones' => $westZones, 'id' => 86, 'lang' => 'tr', 'sec' => '6xbruy'],
			['zones' => $westZones, 'id' => 88, 'lang' => 'vn', 'sec' => '8g591b'],
			['zones' => $westZones, 'id' => 90, 'lang' => 'it', 'sec' => 'mc95rg'],
			['zones' => $westZones, 'id' => 92, 'lang' => 'de', 'sec' => 'uokzr7'],
			['zones' => $westZones, 'id' => 94, 'lang' => 'fr', 'sec' => 'gyzkzb'],
			['zones' => $westZones, 'id' => 96, 'lang' => 'ms', 'sec' => 'zaetk0'],
			['zones' => $westZones, 'id' => 108, 'lang' => 'id', 'sec' => '3gs5vj'],
			['zones' => $westZones, 'id' => 98, 'lang' => 'sc', 'sec' => '532jfn'],
			['zones' => $westZones, 'id' => 100, 'lang' => 'th', 'sec' => '1q4cis'],
			['zones' => $westZones, 'id' => 102, 'lang' => 'ja', 'sec' => 'c84t56'],
			['zones' => $westZones, 'id' => 104, 'lang' => 'tc', 'sec' => '6s7a1m'],
			['zones' => $westZones, 'id' => 110, 'lang' => 'ar', 'sec' => 'zfkgno'],
			['zones' => $westZones, 'id' => 106, 'lang' => 'ru', 'sec' => 'j1h7w4'],
			['zones' => $westZones, 'id' => 34, 'lang' => 'ua', 'sec' => '940xiq'],
			['zones' => $westZones, 'id' => 34, 'lang' => 'kz', 'sec' => '940xiq'],
			['zones' => ['ru'], 'id' => 3282, 'lang' => 'ru', 'sec' => '8x81lp'],
			['zones' => ['by'], 'id' => 3, 'lang' => 'ru', 'sec' => 'kde6g5'],
			['zones' => ['kz'], 'id' => 4, 'lang' => 'kz', 'sec' => 'id3qfm'],
			['zones' => ['kz'], 'id' => 2, 'lang' => 'ru', 'sec' => 'po3skw'],
			['zones' => ['kz'], 'id' => 6, 'lang' => 'en', 'sec' => 'gozt5o'],
			['zones' => ['uz'], 'id' => 7, 'lang' => 'ru', 'sec' => 'xjkuqu'],
		];
	}
}
