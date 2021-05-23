export type DiscountTypes = DiscountType.UNDEFINED | DiscountType.MONETARY | DiscountType.PERCENTAGE;

export class DiscountType
{
	static UNDEFINED = 0;
	static MONETARY = 1;
	static PERCENTAGE = 2;
}