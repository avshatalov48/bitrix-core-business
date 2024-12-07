export class SelectorErrorCode
{
	static NOT_SELECTED_PRODUCT: string = 'NOT_SELECTED_PRODUCT';
	static FAILED_PRODUCT: string = 'FAILED_PRODUCT';

	static getCodes(): Array<string>
	{
		return [
			SelectorErrorCode.NOT_SELECTED_PRODUCT,
			SelectorErrorCode.FAILED_PRODUCT,
		];
	}
}
