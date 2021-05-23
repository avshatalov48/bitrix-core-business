import {DiscountType, ProductCalculator, TaxForSumStrategy} from 'catalog.product-calculator';

function assertEqualFields(actual, expected)
{
	// console.log('----------------------');

	Object.entries(expected).forEach(([key, value], index) => {
		// console.log(actual[key], value);
		assert.strictEqual(actual[key], value, key);
	});

}

const defaultFields = {
	QUANTITY: 0,
	PRICE: 0,
	PRICE_EXCLUSIVE: 0,
	PRICE_NETTO: 0,
	PRICE_BRUTTO: 0,
	CUSTOMIZED: 'N',
	DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
	DISCOUNT_RATE: 0,
	TAX_INCLUDED: 'N',
	TAX_RATE: 0
};

describe('ProductCalculator::Base', () => {
	it('Should be a function', () => {
		assert(typeof ProductCalculator === 'function');
	});

	it('Initial fields should be immutable after calculations', () => {
		const calculator = new ProductCalculator(defaultFields);
		calculator.calculateDiscount(10);
		assertEqualFields(calculator.getFields(), defaultFields);
	});
});

describe('ProductCalculator::round', () => {
	it('Should calculate SUM 4000 with PRICE 8000 and discount 50% and 10% TAX included', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 417.86,
					PRICE_BRUTTO: 835.71,
					PRICE_EXCLUSIVE: 379.87,
					PRICE_NETTO: 759.74,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_ROW: 379.87,
					DISCOUNT_SUM: 379.87,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10
				},
				expected: {
					QUANTITY: 1,
					PRICE: 4000,
					PRICE_BRUTTO: 8000,
					PRICE_EXCLUSIVE: 3636.37,
					PRICE_NETTO: 7272.73,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_ROW: 3636.36,
					DISCOUNT_SUM: 3636.36,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 363.64,
					SUM: 4000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(8000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate SUM 4000 with PRICE 8000 and DISCOUNT 50% and 10% TAX for TaxForSumStrategy', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 417.86,
					PRICE_BRUTTO: 835.71,
					PRICE_EXCLUSIVE: 379.87,
					PRICE_NETTO: 759.74,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_ROW: 379.87,
					DISCOUNT_SUM: 379.87,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10
				},
				expected: {
					QUANTITY: 1,
					PRICE: 4000,
					PRICE_EXCLUSIVE: 4000,
					PRICE_NETTO: 8000,
					PRICE_BRUTTO: 8000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 4000,
					DISCOUNT_ROW: 4000,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 363.64,
					SUM: 4000
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 459.65,
					PRICE_EXCLUSIVE: 417.86,
					PRICE_NETTO: 835.71,
					PRICE_BRUTTO: 919.28,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 417.85,
					DISCOUNT_ROW: 417.85,
					TAX_INCLUDED: 'N',
					TAX_RATE: 10
				},
				expected: {
					QUANTITY: 1,
					PRICE: 4400,
					PRICE_EXCLUSIVE: 4000,
					PRICE_NETTO: 8000,
					PRICE_BRUTTO: 8800,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 4000,
					DISCOUNT_ROW: 4000,
					TAX_INCLUDED: 'N',
					TAX_RATE: 10,
					TAX_SUM: 400,
					SUM: 4400
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			calculator.setCalculationStrategy(new TaxForSumStrategy(calculator));
			const actualFields = calculator.calculatePrice(8000);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculatePrice', () => {
	it('Should throw error if price is lesser than 0', () => {
		const calculator = new ProductCalculator(defaultFields);

		assert.throws(
			() => calculator.calculatePrice(-100),
			Error,
			'Price must be equal or greater than zero.'
		);
		assert.doesNotThrow(
			() => calculator.calculatePrice(0),
			Error,
			'Price must be equal or greater than zero.'
		);
		assert.doesNotThrow(
			() => calculator.calculatePrice(100),
			Error,
			'Price must be equal or greater than zero.'
		);
	});

	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculatePrice(10);
			const fields2 = calculator.calculatePrice(10);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate prices and 0 sum for 0 quantity', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0
				},
				expected: {
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000
				},
				expected: {
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(3000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate customized if price changed to 3000', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					CUSTOMIZED: 'N'
				},
				expected: {
					CUSTOMIZED: 'Y'
				}
			},
			{
				initial: {
					...defaultFields,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					CUSTOMIZED: 'N'
				},
				expected: {
					CUSTOMIZED: 'Y'
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(3000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate customized if price changed to 0', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					CUSTOMIZED: 'N'
				},
				expected: {
					CUSTOMIZED: 'Y'
				}
			},
			{
				initial: {
					...defaultFields,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					CUSTOMIZED: 'Y'
				},
				expected: {
					CUSTOMIZED: 'Y'
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should return sum with default precision', () => {
		const fieldSets = [
			{
				initial: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					CUSTOMIZED: 'N'
				},
				expected: {
					PRICE: 222.22,
					PRICE_NETTO: 222.22,
					PRICE_BRUTTO: 222.22,
					PRICE_EXCLUSIVE: 222.22,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					SUM: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					CUSTOMIZED: 'Y'
				}
			},
			{
				initial: {
					QUANTITY: 1,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					CUSTOMIZED: 'N'
				},
				expected: {
					PRICE: 222.22,
					PRICE_NETTO: 222.22,
					PRICE_BRUTTO: 222.22,
					PRICE_EXCLUSIVE: 222.22,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					SUM: 222.22,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					CUSTOMIZED: 'Y'
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(222.222);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate prices with 10 monetary discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 10,
					DISCOUNT_ROW: 0
				},
				expected: {
					PRICE: 2990,
					PRICE_EXCLUSIVE: 2990,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0.33,
					DISCOUNT_SUM: 10,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 990,
					PRICE_EXCLUSIVE: 990,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 10,
					DISCOUNT_ROW: 20
				},
				expected: {
					PRICE: 2990,
					PRICE_EXCLUSIVE: 2990,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0.33,
					DISCOUNT_SUM: 10,
					DISCOUNT_ROW: 20,
					SUM: 5980
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 2990,
					PRICE_EXCLUSIVE: 2990,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0.33,
					DISCOUNT_SUM: 10,
					DISCOUNT_ROW: 20
				},
				expected: {
					PRICE: 2990,
					PRICE_EXCLUSIVE: 2990,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0.33,
					DISCOUNT_SUM: 10,
					DISCOUNT_ROW: 20,
					SUM: 5980
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(3000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate from 500 to 1000 price with 500 monetary discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000
				},
				expected: {
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000,
					SUM: 1000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(1000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate from 1000 to 500 price with 500 monetary discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000
				},
				expected: {
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(500);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate from 1000 to 0 price with 500 monetary discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000
				},
				expected: {
					PRICE: -500,
					PRICE_EXCLUSIVE: -500,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000,
					SUM: -1000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate prices with 10 percent discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0
				},
				expected: {
					PRICE: 2700,
					PRICE_EXCLUSIVE: 2700,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					SUM: 0,
					DISCOUNT_SUM: 300,
					DISCOUNT_ROW: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 900,
					PRICE_EXCLUSIVE: 900,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 200
				},
				expected: {
					PRICE: 2700,
					PRICE_EXCLUSIVE: 2700,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 300,
					DISCOUNT_ROW: 600
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(3000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate from 500 to 1000 price with 50 percent discounts', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 250,
					PRICE_EXCLUSIVE: 250,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 250,
					DISCOUNT_ROW: 500
				},
				expected: {
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000,
					SUM: 1000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(1000);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate from 1000 to 500 price with 50 percent discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000
				},
				expected: {
					PRICE: 250,
					PRICE_EXCLUSIVE: 250,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 250,
					DISCOUNT_ROW: 500,
					SUM: 500
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(500);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate from 1000 to 0 price with 50 percent discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000
				},
				expected: {
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculatePrice(0);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateQuantity', () => {
	it('Should throw error if quantity is lesser than 0', () => {
		const calculator = new ProductCalculator(defaultFields);

		assert.throws(
			() => calculator.calculateQuantity(-100),
			Error,
			'Quantity must be equal or greater than zero.'
		);
		assert.doesNotThrow(
			() => calculator.calculateQuantity(0),
			Error,
			'Quantity must be equal or greater than zero.'
		);
		assert.doesNotThrow(
			() => calculator.calculateQuantity(100),
			Error,
			'Quantity must be equal or greater than zero.'
		);
	});

	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateQuantity(10);
			const fields2 = calculator.calculateQuantity(10);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate sum for 0 quantity', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000
				},
				expected: {
					QUANTITY: 0,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateQuantity(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discount sum for 0 quantity', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 1000
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateQuantity(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate sum for 50 quantity', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0
				},
				expected: {
					QUANTITY: 50,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 50,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000
				},
				expected: {
					QUANTITY: 50,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					SUM: 150000
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 150,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000
				},
				expected: {
					QUANTITY: 50,
					PRICE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					PRICE_EXCLUSIVE: 3000,
					SUM: 150000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateQuantity(50);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discount sum for 50 quantity', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500
				},
				expected: {
					QUANTITY: 50,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 25000,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 50,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 25000
				},
				expected: {
					QUANTITY: 50,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 500,
					PRICE_BRUTTO: 500,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 500,
					DISCOUNT_ROW: 25000,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateQuantity(50);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate sum for 1.111 quantity with default precision', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0
				},
				expected: {
					QUANTITY: 1.11,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 50,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000
				},
				expected: {
					QUANTITY: 1.11,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					SUM: 3330
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateQuantity(1.111);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateDiscount', () => {
	it('Should calculate discounts for 0 UNDEFINED discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000
				},
				expected: {
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 6000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateDiscount(10);
			const fields2 = calculator.calculateDiscount(10);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate discounts for 0 MONETARY discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100
				},
				expected: {
					QUANTITY: 2,
					PRICE: 400,
					PRICE_EXCLUSIVE: 400,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 800
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for 0 PERCENTAGE discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100
				},
				expected: {
					QUANTITY: 2,
					PRICE: 400,
					PRICE_EXCLUSIVE: 400,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 800
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for 100 UNDEFINED discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000
				},
				expected: {
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 6000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for 100 MONETARY discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 100,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100
				},
				expected: {
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 200,
					SUM: 600
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 33.34,
					SUM: 200
				},
				expected: {
					QUANTITY: 2,
					PRICE: -20,
					PRICE_EXCLUSIVE: -16.67,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 120,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 200,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: -6.67,
					SUM: -40
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for 100 PERCENTAGE discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 400,
					DISCOUNT_ROW: 800,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 100,
					PRICE_EXCLUSIVE: 166.66,
					PRICE_NETTO: 166.66,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 33.34,
					SUM: 200
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 166.66,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 166.66,
					DISCOUNT_ROW: 333.32,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 0,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for -100 MONETARY discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: -100,
					DISCOUNT_ROW: -100,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100
				},
				expected: {
					QUANTITY: 2,
					PRICE: 500,
					PRICE_EXCLUSIVE: 500,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: -25,
					DISCOUNT_SUM: -100,
					DISCOUNT_ROW: -200,
					SUM: 1000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(-100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for -100 PERCENTAGE discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					PRICE_EXCLUSIVE: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 300,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 100
				},
				expected: {
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscount(-100);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateDiscountType', () => {
	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateDiscountType(DiscountType.PERCENTAGE);
			const fields2 = calculator.calculateDiscountType(DiscountType.PERCENTAGE);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate discounts for UNDEFINED discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000
				},
				expected: {
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 6000
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 400,
					PRICE_EXCLUSIVE: 400,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 800
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 3000,
					DISCOUNT_ROW: 6000,
					SUM: 0
				},
				expected: {
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 6000
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscountType(DiscountType.UNDEFINED);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for MONETARY discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000
				},
				expected: {
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 6000
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 3000,
					DISCOUNT_ROW: 6000,
					SUM: 0
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 3000,
					DISCOUNT_ROW: 6000,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				},
				expected: {
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscountType(DiscountType.MONETARY);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for PERCENTAGE discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000
				},
				expected: {
					QUANTITY: 2,
					PRICE: 3000,
					PRICE_EXCLUSIVE: 3000,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 6000
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 3000,
					DISCOUNT_ROW: 6000,
					SUM: 0
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 3000,
					PRICE_BRUTTO: 3000,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 3000,
					DISCOUNT_ROW: 6000,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				},
				expected: {
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateDiscountType(DiscountType.PERCENTAGE);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateRowDiscount', () => {
	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateRowDiscount(10);
			const fields2 = calculator.calculateRowDiscount(10);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate discounts for 0 row discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 400,
					PRICE_EXCLUSIVE: 400,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 800
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 400,
					PRICE_EXCLUSIVE: 400,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 800
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				},
				expected: {
					QUANTITY: 2,
					PRICE: 400,
					PRICE_EXCLUSIVE: 400,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 800
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateRowDiscount(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for 100 row discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 100,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				},
				expected: {
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 200,
					PRICE_EXCLUSIVE: 200,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 50,
					DISCOUNT_SUM: 200,
					DISCOUNT_ROW: 400,
					SUM: 400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 800,
					PRICE_EXCLUSIVE: 800,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: -100,
					DISCOUNT_SUM: -400,
					DISCOUNT_ROW: -800,
					SUM: 1600
				},
				expected: {
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateRowDiscount(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate discounts for -100 row discount', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: -100,
					DISCOUNT_ROW: -100,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 450,
					PRICE_EXCLUSIVE: 450,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: -12.5,
					DISCOUNT_SUM: -50,
					DISCOUNT_ROW: -100,
					SUM: 900
				},
				expected: {
					QUANTITY: 2,
					PRICE: 450,
					PRICE_EXCLUSIVE: 450,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: -12.5,
					DISCOUNT_SUM: -50,
					DISCOUNT_ROW: -100,
					SUM: 900
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				},
				expected: {
					QUANTITY: 2,
					PRICE: 450,
					PRICE_EXCLUSIVE: 450,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: -12.5,
					DISCOUNT_SUM: -50,
					DISCOUNT_ROW: -100,
					SUM: 900
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateRowDiscount(-100);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateTax', () => {
	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateTax(10);
			const fields2 = calculator.calculateTax(10);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate taxes for 0 tax rate', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					TAX_RATE: 0,
					TAX_INCLUDED: 'N',
					SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 100
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateTax(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate taxes for 100 tax rate', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					TAX_RATE: 0,
					TAX_INCLUDED: 'N',
					SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 100,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 200,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 200,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 100,
					TAX_SUM: 100,
					SUM: 200
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 50,
					PRICE_NETTO: 50,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 100,
					TAX_SUM: 50,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				},
				expected: {
					QUANTITY: 2,
					PRICE: 700,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 800,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 100,
					TAX_SUM: 700,
					SUM: 1400
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateTax(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate taxes for -100 tax rate', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					TAX_RATE: 0,
					TAX_INCLUDED: 'N',
					SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: -100,
					TAX_SUM: -0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: -100,
					TAX_SUM: -100,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: -100,
					TAX_SUM: -200,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: Infinity,
					PRICE_NETTO: Infinity,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: -100,
					TAX_SUM: -Infinity,
					SUM: 100
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateTax(-100);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateTaxIncluded', () => {
	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateTaxIncluded('Y');
			const fields2 = calculator.calculateTaxIncluded('Y');
			assertEqualFields(fields1, fields2);
			const fields3 = calculator.calculateTaxIncluded('N');
			const fields4 = calculator.calculateTaxIncluded('N');
			assertEqualFields(fields3, fields4);
		});
	});

	it('Should calculate taxes for INCLUDED tax rate', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					TAX_RATE: 0,
					TAX_INCLUDED: 'N',
					SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 990,
					PRICE_EXCLUSIVE: 900,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 10,
					TAX_SUM: 90,
					SUM: 990
				},
				expected: {
					QUANTITY: 1,
					PRICE: 900,
					PRICE_EXCLUSIVE: 818.18,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 90.91,
					DISCOUNT_ROW: 90.91,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 81.82,
					SUM: 900
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 700,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 800,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 100,
					TAX_SUM: 700,
					SUM: 1400
				},
				expected: {
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 150,
					PRICE_NETTO: 200,
					PRICE_BRUTTO: 400,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 100,
					TAX_SUM: 300,
					SUM: 600
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateTaxIncluded('Y');
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate taxes for NON-INCLUDED tax rate', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					TAX_RATE: 0,
					TAX_INCLUDED: 'N',
					SUM: 0
				},
				expected: {
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 900,
					PRICE_EXCLUSIVE: 818.18,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 90.91,
					DISCOUNT_ROW: 90.91,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 81.82,
					SUM: 900
				},
				expected: {
					QUANTITY: 1,
					PRICE: 990,
					PRICE_EXCLUSIVE: 900,
					PRICE_NETTO: 1000,
					PRICE_BRUTTO: 1100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 10,
					TAX_SUM: 90,
					SUM: 990
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 300,
					PRICE_EXCLUSIVE: 150,
					PRICE_NETTO: 200,
					PRICE_BRUTTO: 400,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 25,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 100,
					TAX_SUM: 300,
					SUM: 600
				},
				expected: {
					QUANTITY: 2,
					PRICE: 700,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 800,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 100,
					TAX_SUM: 700,
					SUM: 1400
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateTaxIncluded('N');
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateRowSum', () => {
	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 900,
				PRICE_EXCLUSIVE: 818.18,
				PRICE_NETTO: 909.09,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 90.91,
				DISCOUNT_ROW: 90.91,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			const fields1 = calculator.calculateRowSum(100);
			const fields2 = calculator.calculateRowSum(100);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate sums for 0 row total sum', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 400,
					DISCOUNT_ROW: 800,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 83.33,
					DISCOUNT_ROW: 83.33,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 0,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateRowSum(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate sums for 100 row total sum', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: -100,
					DISCOUNT_ROW: -100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 16.67,
					DISCOUNT_SUM: 16.67,
					DISCOUNT_ROW: 16.67,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 900,
					PRICE_EXCLUSIVE: 818.18,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 90.91,
					DISCOUNT_ROW: 181.82,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 163.64,
					SUM: 1800
				},
				expected: {
					QUANTITY: 2,
					PRICE: 50,
					PRICE_EXCLUSIVE: 45.45,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 95,
					DISCOUNT_SUM: 863.64,
					DISCOUNT_ROW: 1727.28,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 9.09,
					SUM: 100
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateRowSum(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate sums for -100 row total sum', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: -0,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 200,
					DISCOUNT_SUM: 166.66,
					DISCOUNT_ROW: 166.66,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: -16.67,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -83.33,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 183.33,
					DISCOUNT_SUM: 183.33,
					DISCOUNT_ROW: 183.33,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: -16.67,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 900,
					PRICE_EXCLUSIVE: 818.18,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 90.91,
					DISCOUNT_ROW: 181.82,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 163.64,
					SUM: 1800
				},
				expected: {
					QUANTITY: 2,
					PRICE: -49.99,
					PRICE_EXCLUSIVE: -45.45,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 105,
					DISCOUNT_SUM: 954.54,
					DISCOUNT_ROW: 1909.08,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: -9.09,
					SUM: -100
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			const actualFields = calculator.calculateRowSum(-100);
			assertEqualFields(actualFields, set.expected);
		});
	});
});

describe('ProductCalculator::calculateTaxForSumStrategy', () => {
	it('Calculation result should be equal after the same calculations', () => {
		const fieldSets = [
			{
				...defaultFields
			},
			{
				QUANTITY: 1,
				PRICE: 1000,
				PRICE_EXCLUSIVE: 900,
				PRICE_NETTO: 900,
				PRICE_BRUTTO: 1000,
				CUSTOMIZED: 'Y',
				DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
				DISCOUNT_RATE: 10,
				DISCOUNT_SUM: 100,
				DISCOUNT_ROW: 100,
				TAX_INCLUDED: 'Y',
				TAX_RATE: 10,
				TAX_SUM: 81.82,
				SUM: 900
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set);
			calculator.setCalculationStrategy(new TaxForSumStrategy(calculator));
			const fields1 = calculator.calculateRowSum(100);
			const fields2 = calculator.calculateRowSum(100);
			assertEqualFields(fields1, fields2);
		});
	});

	it('Should calculate sums for 0 row total sum', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 350,
					PRICE_EXCLUSIVE: 350,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 12.5,
					DISCOUNT_SUM: 50,
					DISCOUNT_ROW: 100,
					SUM: 700
				},
				expected: {
					QUANTITY: 2,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 400,
					PRICE_BRUTTO: 400,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 400,
					DISCOUNT_ROW: 800,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 0
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 100,
					DISCOUNT_SUM: 83.33,
					DISCOUNT_ROW: 83.33,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 0,
					SUM: 0
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			calculator.setCalculationStrategy(new TaxForSumStrategy(calculator));
			const actualFields = calculator.calculateRowSum(0);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate sums for 100 row total sum', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: -100,
					DISCOUNT_ROW: -100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: 0,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 0,
					DISCOUNT_ROW: 0,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 16.67,
					DISCOUNT_SUM: 16.67,
					DISCOUNT_ROW: 16.67,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 900,
					PRICE_EXCLUSIVE: 818.18,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 90.91,
					DISCOUNT_ROW: 181.82,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 163.64,
					SUM: 1800
				},
				expected: {
					QUANTITY: 2,
					PRICE: 45.45,
					PRICE_EXCLUSIVE: 45.45,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 95,
					DISCOUNT_SUM: 863.64,
					DISCOUNT_ROW: 1727.28,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 8.26,
					SUM: 100
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			calculator.setCalculationStrategy(new TaxForSumStrategy(calculator));
			const actualFields = calculator.calculateRowSum(100);
			assertEqualFields(actualFields, set.expected);
		});
	});

	it('Should calculate sums for -100 row total sum', () => {
		const fieldSets = [
			{
				initial: {
					...defaultFields,
					QUANTITY: 0,
					PRICE: 0,
					PRICE_EXCLUSIVE: 0,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					SUM: 0
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -100,
					PRICE_NETTO: 0,
					PRICE_BRUTTO: 0,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 0,
					DISCOUNT_SUM: 100,
					DISCOUNT_ROW: 100,
					TAX_INCLUDED: 'N',
					TAX_RATE: 0,
					TAX_SUM: -0,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 100,
					PRICE_EXCLUSIVE: 83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: 16.67,
					SUM: 100
				},
				expected: {
					QUANTITY: 1,
					PRICE: -83.33,
					PRICE_EXCLUSIVE: -83.33,
					PRICE_NETTO: 83.33,
					PRICE_BRUTTO: 100,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 200,
					DISCOUNT_SUM: 166.66,
					DISCOUNT_ROW: 166.66,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 20,
					TAX_SUM: -13.89,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 1,
					PRICE: 120,
					PRICE_EXCLUSIVE: 100,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: 20,
					SUM: 120
				},
				expected: {
					QUANTITY: 1,
					PRICE: -100,
					PRICE_EXCLUSIVE: -83.33,
					PRICE_NETTO: 100,
					PRICE_BRUTTO: 120,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 183.33,
					DISCOUNT_SUM: 183.33,
					DISCOUNT_ROW: 183.33,
					TAX_INCLUDED: 'N',
					TAX_RATE: 20,
					TAX_SUM: -16.67,
					SUM: -100
				}
			},
			{
				initial: {
					...defaultFields,
					QUANTITY: 2,
					PRICE: 900,
					PRICE_EXCLUSIVE: 818.18,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.PERCENTAGE,
					DISCOUNT_RATE: 10,
					DISCOUNT_SUM: 90.91,
					DISCOUNT_ROW: 181.82,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: 163.64,
					SUM: 1800
				},
				expected: {
					QUANTITY: 2,
					PRICE: -45.45,
					PRICE_EXCLUSIVE: -45.45,
					PRICE_NETTO: 909.09,
					PRICE_BRUTTO: 1000,
					CUSTOMIZED: 'Y',
					DISCOUNT_TYPE_ID: DiscountType.MONETARY,
					DISCOUNT_RATE: 105,
					DISCOUNT_SUM: 954.54,
					DISCOUNT_ROW: 1909.08,
					TAX_INCLUDED: 'Y',
					TAX_RATE: 10,
					TAX_SUM: -8.26,
					SUM: -100
				}
			}
		];

		fieldSets.forEach((set) => {
			const calculator = new ProductCalculator(set.initial);
			calculator.setCalculationStrategy(new TaxForSumStrategy(calculator));
			const actualFields = calculator.calculateRowSum(-100);
			assertEqualFields(actualFields, set.expected);
		});
	});
});