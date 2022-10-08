import LocationType from '../location/locationtype';

export default class AddressType extends LocationType
{
	static POSTAL_CODE = 50;

	static ADDRESS_LINE_2 = 600;
	static RECIPIENT_COMPANY = 700;
	static RECIPIENT = 710;
	static PO_BOX = 800;
}