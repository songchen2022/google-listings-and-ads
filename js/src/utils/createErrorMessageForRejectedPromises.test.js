/**
 * Internal dependencies
 */
import createErrorMessageForRejectedPromises from './createErrorMessageForRejectedPromises';
import createMessageForMultipleErrors from '.~/utils/createMessageForMultipleErrors';

describe( 'createErrorMessageForRejectedPromises', () => {
	const successPromise = () => new Promise( ( resolve ) => resolve( 'OK' ) );
	const rejectedPromise = () =>
		new Promise( ( r, reject ) => reject( 'Failed' ) );

	afterEach( () => {
		jest.clearAllMocks();
	} );

	it( 'Called with no rejected promises, calls `createMessageForMultipleErrors` with an empty array and `true`', async () => {
		const promises = [
			successPromise(),
			successPromise(),
			successPromise(),
		];
		const promisesName = [ 'Promise A', 'Promise B', 'Promise C' ];

		const message = await createErrorMessageForRejectedPromises(
			promises,
			promisesName
		);

		expect( message ).toBe( createMessageForMultipleErrors( [], true ) );
	} );

	it( 'Called with one rejected promise, calls `createMessageForMultipleErrors` with its name, and `true`', async () => {
		const promises = [
			successPromise(),
			rejectedPromise(),
			successPromise(),
		];
		const promisesName = [ 'Promise A', 'Promise B', 'Promise C' ];

		const message = await createErrorMessageForRejectedPromises(
			promises,
			promisesName
		);

		expect( message ).toBe(
			createMessageForMultipleErrors( [ 'Promise B' ], true )
		);
	} );

	it( 'Called with multiple rejected promises, calls `createMessageForMultipleErrors` with their names, and `true`', async () => {
		const promises = [
			successPromise(),
			rejectedPromise(),
			rejectedPromise(),
			rejectedPromise(),
		];
		const promisesName = [
			'Promise A',
			'Promise B',
			'Promise C',
			'Promise D',
		];

		const message = await createErrorMessageForRejectedPromises(
			promises,
			promisesName
		);

		expect( message ).toBe(
			createMessageForMultipleErrors(
				[ 'Promise B', 'Promise C', 'Promise D' ],
				true
			)
		);
	} );

	it( 'Called with all rejected promises, calls `createMessageForMultipleErrors` with their names, and `false`', async () => {
		const promises = [
			rejectedPromise(),
			rejectedPromise(),
			rejectedPromise(),
		];
		const promisesName = [ 'Promise A', 'Promise B', 'Promise C' ];

		const message = await createErrorMessageForRejectedPromises(
			promises,
			promisesName
		);

		expect( message ).toBe(
			createMessageForMultipleErrors(
				[ 'Promise A', 'Promise B', 'Promise C' ],
				false
			)
		);
	} );
} );
