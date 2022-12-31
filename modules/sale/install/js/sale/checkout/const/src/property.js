export const Property = Object.freeze({
    validate: {
        failure: 'Property.Validate.failure',
        successful: 'Property.Validate.successful',
        unvalidated: 'Property.Validate.unvalidated'
    },
    type: {
       name: 'NAME',
       email: 'EMAIL',
       phone: 'PHONE',
       string: 'STRING',
       number: 'NUMBER',
       checkbox: 'Y/N',
       date: 'DATE',
       datetime: 'DATETIME',
       enum: 'ENUM',
       undefined: 'UNDEFINED'
    }
});