/**
 * Internal dependencies
 */
import useAppSelectDispatch from './useAppSelectDispatch';

/**
 * Get Refund Return Policy Page from API. Returns `{ hasFinishedResolution, data, invalidateResolution }`.
 *
 * `data` is an object of refund return policy mapping. e.g.:
 *
 * ```json
 * {
 * 		"refund_return_policy": "Refund and Return Policy"
 * }
 * ```
 */
const useRefundReturnPolicyPage = () => {
	return useAppSelectDispatch( 'getRefundReturnPolicyPage' );
};

export default useRefundReturnPolicyPage;
