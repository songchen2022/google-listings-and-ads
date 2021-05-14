<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Admin\Product\Attributes;

use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\Checkbox;
use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\Form;
use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\InputInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\Number;
use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\Select;
use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\SelectWithTextInput;
use Automattic\WooCommerce\GoogleListingsAndAds\Admin\Input\Text;
use Automattic\WooCommerce\GoogleListingsAndAds\Exception\InvalidValue;
use Automattic\WooCommerce\GoogleListingsAndAds\Exception\ValidateInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\Attributes\AttributeInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\Attributes\WithValueOptionsInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class AttributesForm
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Admin\Product\Attributes
 */
class AttributesForm extends Form {

	use ValidateInterface;

	/**
	 * @var string[]
	 */
	protected $attribute_types = [];

	/**
	 * AttributesForm constructor.
	 *
	 * @param string[] $attribute_types
	 * @param array    $data
	 */
	public function __construct( array $attribute_types, array $data = [] ) {
		foreach ( $attribute_types as $attribute_type ) {
			$this->add_attribute( $attribute_type );
		}

		parent::__construct( $data );
	}

	/**
	 * Return the data used for the input's view.
	 *
	 * @return array
	 */
	public function get_view_data(): array {
		$view_data = parent::get_view_data();

		// add classes to hide/display attributes based on product type
		foreach ( $view_data['children'] as $index => $input ) {
			if ( ! isset( $this->attribute_types[ $index ] ) ) {
				continue;
			}
			$applicable_types = call_user_func( [ $this->attribute_types[ $index ], 'get_applicable_product_types' ] );
			if ( ! empty( $applicable_types ) ) {
				$input['gla_wrapper_class']  = $input['gla_wrapper_class'] ?? '';
				$input['gla_wrapper_class'] .= ' show_if_' . join( ' show_if_', $applicable_types );

				$view_data['children'][ $index ] = $input;
			}
		}

		return $view_data;
	}

	/**
	 * @param InputInterface     $input
	 * @param AttributeInterface $attribute
	 *
	 * @return InputInterface
	 */
	protected function init_input( InputInterface $input, AttributeInterface $attribute ) {
		$input->set_id( $attribute::get_id() )
			  ->set_label( $attribute::get_name() )
			  ->set_description( $attribute::get_description() )
			  ->set_name( $attribute::get_id() );

		$value_options = [];
		if ( $attribute instanceof WithValueOptionsInterface ) {
			$value_options = $attribute::get_value_options();
		}
		$value_options = apply_filters( "gla_product_attribute_value_options_{$attribute::get_id()}", $value_options );

		if ( ! empty( $value_options ) ) {
			if ( ! $input instanceof Select && ! $input instanceof SelectWithTextInput ) {
				return $this->init_input( new SelectWithTextInput(), $attribute );
			}

			// add a 'default' value option
			$value_options = [ '' => 'Default' ] + $value_options;

			$input->set_options( $value_options );
		}

		return $input;
	}

	/**
	 * Guesses what kind of input does the attribute need based on its value type and returns the input class name.
	 *
	 * @param AttributeInterface $attribute
	 *
	 * @return string Input class name
	 */
	protected function guess_input_type( AttributeInterface $attribute ): string {
		if ( $attribute instanceof WithValueOptionsInterface ) {
			return Select::class;
		}

		switch ( $attribute::get_value_type() ) {
			case 'integer':
			case 'int':
			case 'float':
			case 'double':
				$input_type = Number::class;
				break;
			case 'bool':
			case 'boolean':
				$input_type = Checkbox::class;
				break;
			default:
				$input_type = Text::class;
		}

		return $input_type;
	}

	/**
	 * Add an attribute to the form
	 *
	 * @param string $attribute_type An attribute class extending AttributeInterface
	 *
	 * @return AttributesForm
	 *
	 * @throws InvalidValue If the attribute type is invalid or an invalid input type is specified for the attribute.
	 */
	protected function add_attribute( string $attribute_type ): AttributesForm {
		$this->validate_interface( $attribute_type, AttributeInterface::class );

		$attribute       = new $attribute_type();
		$input_type      = $this->guess_input_type( $attribute );
		$attribute_input = $this->init_input( new $input_type(), $attribute );
		$attribute_id    = call_user_func( [ $attribute_type, 'get_id' ] );
		$this->add( $attribute_input, $attribute_id );

		$this->attribute_types[ $attribute_id ] = $attribute_type;

		return $this;
	}
}
