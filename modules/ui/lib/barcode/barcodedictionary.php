<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\UI\Barcode;

class BarcodeDictionary
{
	public const FORMAT_PNG = 'png';
	public const FORMAT_GIF = 'gif';
	public const FORMAT_JPEG = 'jpeg';
	public const FORMAT_SVG = 'svg';

	public const TYPE_UPC_A = 'upc-a';
	public const TYPE_UPC_E = 'upc-e';
	public const TYPE_EAN8 = 'ean-8';
	public const TYPE_EAN13 = 'ean-13';
	public const TYPE_EAN13_PAD = 'ean-13-pad';
	public const TYPE_EAN13_NOPAD = 'ean-13-nopad';
	public const TYPE_EAN128 = 'ean-128';
	public const TYPE_CODE39 = 'code-39';
	public const TYPE_CODE39_ASCII = 'code-39-ascii';
	public const TYPE_CODE93 = 'code-93';
	public const TYPE_CODE93_ASCII = 'code-93-ascii';
	public const TYPE_CODE128 = 'code-128';
	public const TYPE_CODABAR = 'codabar';
	public const TYPE_ITF = 'itf';
	public const TYPE_QR = 'qr';
	public const TYPE_QR_L = 'qr-l';
	public const TYPE_QR_M = 'qr-m';
	public const TYPE_QR_Q = 'qr-q';
	public const TYPE_QR_H = 'qr-h';
	public const TYPE_DMTX = 'dmtx';
	public const TYPE_DMTX_S = 'dmtx-s';
	public const TYPE_DMTX_R = 'dmtx-r';
	public const TYPE_GS1_DMTX = 'gs1-dmtx';
	public const TYPE_GS1_DMTX_S = 'gs1-dmtx-s';
	public const TYPE_GS1_DMTX_R = 'gs1-dmtx-r';
}