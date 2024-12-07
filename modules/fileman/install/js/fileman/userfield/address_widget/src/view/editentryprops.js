import { Address as AddressEntity } from 'location.core';

export type EditEntryProps = {
	wrapper: Element,
	address: ?AddressEntity,
	fieldName: string,
	fieldFormName: string,
	enableRemoveButton: boolean,
	showMap: boolean,
	initialAddressId: ?number,
	isCompactMode: ?boolean,
	showDetailsToggle?: boolean,
}
