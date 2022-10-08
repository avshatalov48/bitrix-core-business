import Address from './address/address';
import Factory from './factory';
import UIAddress from './ui-address/ui-address';
import State from './state';
import BaseFeature from './address/features/basefeature';
import MapFeature from './address/features/mapfeature';
import AutocompleteFeature from './address/features/autocompletefeature';
import FieldsFeature from './address/features/fieldsfeature';

/* A set of widgets for using to deal with addresses */
export {

	// Main Widget
	Address,

	// Widget features
	BaseFeature,

	MapFeature,
	AutocompleteFeature,
	FieldsFeature,

	// Widget factory
	Factory,

	// Possible widget states
	State,

	/* Address field for the ui.forms.
	 * This can be used as an example of the widget implementation
	 */
	UIAddress,
};

// Register fields for ui.entity-editor
UIAddress.registerField();
