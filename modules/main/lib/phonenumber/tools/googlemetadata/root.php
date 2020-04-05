<?php

namespace Bitrix\Main\PhoneNumber\Tools\GoogleMetadata;

use Bitrix\Main\PhoneNumber\Tools;
use Bitrix\Main\PhoneNumber\Tools\XmlField;
use Bitrix\Main\PhoneNumber\Tools\RegexField;

class Root extends Tools\XmlParser
{
	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/' => new Tools\XmlField('ROOT', array(
				'multiple' => false,
				'subParser' => new Territories()
			))
		);
	}
}

class Territories extends Tools\XmlParser
{
	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/territory/' => new Tools\XmlField('territory', array(
				'multiple' => true,
				'subParser' => new Territory()
			))
		);
	}
}

class Territory extends Tools\XmlParser
{
	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/territory/@id' => new Tools\XmlField('id'),
			'/phoneNumberMetadata/territories/territory/@countryCode' => new Tools\XmlField('countryCode'),
			'/phoneNumberMetadata/territories/territory/@mainCountryForCode' => new Tools\BoolField('mainCountryForCode'),
			'/phoneNumberMetadata/territories/territory/@leadingDigits' => new Tools\RegexField('leadingDigits'),
			'/phoneNumberMetadata/territories/territory/@preferredInternationalPrefix' => new Tools\XmlField('preferredInternationalPrefix'),
			'/phoneNumberMetadata/territories/territory/@internationalPrefix' => new Tools\XmlField('internationalPrefix'),
			'/phoneNumberMetadata/territories/territory/@nationalPrefix' => new Tools\XmlField('nationalPrefix'),
			'/phoneNumberMetadata/territories/territory/@nationalPrefixForParsing' => new Tools\XmlField('nationalPrefixForParsing'),
			'/phoneNumberMetadata/territories/territory/@nationalPrefixTransformRule' => new Tools\XmlField('nationalPrefixTransformRule'),
			'/phoneNumberMetadata/territories/territory/@preferredExtnPrefix' => new Tools\XmlField('preferredExtnPrefix'),
			'/phoneNumberMetadata/territories/territory/@nationalPrefixFormattingRule' => new Tools\XmlField('nationalPrefixFormattingRule'),
			'/phoneNumberMetadata/territories/territory/@nationalPrefixOptionalWhenFormatting' => new Tools\BoolField('nationalPrefixOptionalWhenFormatting'),
			'/phoneNumberMetadata/territories/territory/@carrierCodeFormattingRule' => new Tools\XmlField('carrierCodeFormattingRule'),
			'/phoneNumberMetadata/territories/territory/@mobileNumberPortableRegion' => new Tools\XmlField('mobileNumberPortableRegion'),
			'/phoneNumberMetadata/territories/territory/references/' => new Tools\XmlField('references', array(
				'subParser' => new References()
			)),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/' => new Tools\XmlField('availableFormats', array(
				'multiple' => true,
				'subParser' => new NumberFormat()
			)),
			'/phoneNumberMetadata/territories/territory/generalDesc/' => new Tools\XmlField('generalDesc', array(
				'subParser' => new GeneralDesc()
			)),
			'/phoneNumberMetadata/territories/territory/noInternationalDialling/' => new Tools\XmlField('noInternationalDialling', array(
				'subParser' => new NumberFormatDesc('noInternationalDialling')
			)),
        	'/phoneNumberMetadata/territories/territory/areaCodeOptional/' => new Tools\XmlField('areaCodeOptional', array(
        		'subParser' => new NumberFormatDesc('areaCodeOptional')
			)),
			'/phoneNumberMetadata/territories/territory/fixedLine/' => new Tools\XmlField('fixedLine', array(
				'subParser' => new NumberFormatDesc('fixedLine')
			)),
			'/phoneNumberMetadata/territories/territory/mobile/' => new Tools\XmlField('mobile', array(
				'subParser' => new NumberFormatDesc('mobile')
			)),
			'/phoneNumberMetadata/territories/territory/pager/' => new Tools\XmlField('pager', array(
				'subParser' => new NumberFormatDesc('pager')
			)),
			'/phoneNumberMetadata/territories/territory/tollFree/' => new Tools\XmlField('tollFree', array(
				'subParser' => new NumberFormatDesc('tollFree')
			)),
			'/phoneNumberMetadata/territories/territory/premiumRate/' => new Tools\XmlField('premiumRate', array(
				'subParser' => new NumberFormatDesc('premiumRate')
			)),
			'/phoneNumberMetadata/territories/territory/sharedCost/' => new Tools\XmlField('sharedCost', array(
				'subParser' => new NumberFormatDesc('sharedCost')
			)),
			'/phoneNumberMetadata/territories/territory/personalNumber/' => new Tools\XmlField('personalNumber', array(
				'subParser' => new NumberFormatDesc('personalNumber')
			)),
			'/phoneNumberMetadata/territories/territory/voip/' => new Tools\XmlField('voip', array(
				'subParser' => new NumberFormatDesc('voip')
			)),
			'/phoneNumberMetadata/territories/territory/uan/' => new Tools\XmlField('uan', array(
				'subParser' => new NumberFormatDesc('uan')
			)),
			'/phoneNumberMetadata/territories/territory/voicemail/' => new Tools\XmlField('voicemail', array(
				'subParser' => new NumberFormatDesc('voicemail')
			)),
		);
	}
}

class References extends Tools\XmlParser
{
	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/territory/references/sourceUrl/' => new XmlField('sourceUrl', array(
				'multiple' => true
			)),
		);
	}
}

class GeneralDesc extends Tools\XmlParser
{
	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/territory/generalDesc/nationalNumberPattern/' => new Tools\RegexField('nationalNumberPattern')
		);
	}
}

class NumberFormatDesc extends Tools\XmlParser
{
	protected $fieldName;
	public function __construct($fieldName)
	{
		$this->fieldName = $fieldName;
		return parent::__construct();
	}

	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/territory/' . $this->fieldName . '/possibleLengths/@national' => new Tools\PossibleLengthField('possibleLengthNational'),
			'/phoneNumberMetadata/territories/territory/' . $this->fieldName . '/possibleLengths/@localOnly' => new Tools\PossibleLengthField('possibleLengthLocalOnly'),
			'/phoneNumberMetadata/territories/territory/' . $this->fieldName . '/exampleNumber/' => new XmlField('exampleNumber'),
			'/phoneNumberMetadata/territories/territory/' . $this->fieldName . '/nationalNumberPattern/' => new RegexField('nationalNumberPattern')
		);
	}
}


class NumberFormat extends Tools\XmlParser
{
	public function getMap()
	{
		return array(
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/leadingDigits/' => new RegexField('leadingDigits', array('multiple' => true)),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/format/' => new XmlField('format'),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/intlFormat/' => new XmlField('intlFormat'),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/@nationalPrefixFormattingRule' => new XmlField('nationalPrefixFormattingRule'),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/@nationalPrefixOptionalWhenFormatting' => new Tools\BoolField('nationalPrefixOptionalWhenFormatting'),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/@carrierCodeFormattingRule' => new XmlField('carrierCodeFormattingRule'),
			'/phoneNumberMetadata/territories/territory/availableFormats/numberFormat/@pattern' => new XmlField('pattern'),
		);
	}
}

