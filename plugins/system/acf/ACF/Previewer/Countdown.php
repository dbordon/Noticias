<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace ACF\Previewer;

defined('_JEXEC') or die;

class Countdown extends Field
{
	/**
	 * The field framework widget name.
	 * 
	 * @var  string
	 */
	protected $field = 'Countdown';
	
	/**
	 * The theme used.
	 * 
	 * @var  string
	 */
	private $theme;

	public function __construct($data = [], $payload = [])
	{
		parent::__construct($data, $payload);
		
		// Set theme
		$this->theme = $this->getTheme();
	}

	/**
	 * Render the field.
	 * 
	 * @return  string
	 */
	public function onSetup()
	{
		$this->payload = [
			// Field values
			'countdown_type' => 'evergreen',
			'timezone' => 'server',
			'dynamic_days' => '1',
			'dynamic_hours' => '12',
			'dynamic_minutes' => '59',
			'dynamic_seconds' => '59',
		
			// Countdown End Action
			'finish_text' => $this->fieldParams->get('finish_text', ''),
			'redirect_url' => $this->fieldParams->get('redirect_url', ''),
			'countdown_action' => 'restart',
		
			// Preset
			'theme' => $this->theme,
			'format' => $this->fieldParams->get('format', ''),
		
			// Unit Display
			'days' => $this->fieldParams->get('days') === '1',
			'days_label' => $this->fieldParams->get('days_label'),
			'hours' => $this->fieldParams->get('hours') === '1',
			'hours_label' => $this->fieldParams->get('hours_label'),
			'minutes' => $this->fieldParams->get('minutes') === '1',
			'minutes_label' => $this->fieldParams->get('minutes_label'),
			'seconds' => $this->fieldParams->get('seconds') === '1',
			'seconds_label' => $this->fieldParams->get('seconds_label'),
			'separator' => $this->fieldParams->get('separator') === '1',
			'double_zeroes_format' => $this->fieldParams->get('double_zeroes_format') === '1',
		
			// Unit Item
			'item_size' => $this->fieldParams->get('item_size_responsive.item_size'),
			'item_padding' => $this->fieldParams->get('item_padding_control.item_padding'),
			'gap' => $this->fieldParams->get('item_gap.gap'),
			'item_border_style' => $this->fieldParams->get('border.style'),
			'item_border_width' => $this->fieldParams->get('border.width'),
			'item_border_color' => $this->fieldParams->get('border.color'),
			'item_background_color' => $this->fieldParams->get('item_background_color'),
			'item_border_radius' => $this->fieldParams->get('item_border_radius_control.item_border_radius'),
		
			// Unit Digits Container
			'digits_wrapper_min_width' => $this->fieldParams->get('digits_wrapper_custom_width') === '1' ? $this->fieldParams->get('digits_wrapper_min_width') : null,
			'digits_wrapper_padding' => $this->fieldParams->get('digits_wrapper_padding_control.digits_wrapper_padding'),
			'digits_wrapper_border_radius' => $this->fieldParams->get('digits_wrapper_border_radius_control.digits_wrapper_border_radius'),
			'digits_wrapper_background_color' => $this->fieldParams->get('digits_wrapper_background_color'),
			
			// Unit Digit
			'digits_font_size' => $this->fieldParams->get('digits_font_size_control.digits_font_size'),
			'digits_font_weight' => $this->fieldParams->get('digits_font_weight'),
			'digit_min_width' => $this->fieldParams->get('digits_custom_width') === '1' ? $this->fieldParams->get('digits_min_width') : null,
			'digits_padding' => $this->fieldParams->get('digits_padding_control.digits_padding'),
			'digit_border_radius' => $this->fieldParams->get('digits_border_radius_control.digits_border_radius'),
			'digits_gap' => $this->fieldParams->get('digits_gap_control.digits_gap'),
			'digit_background_color' => $this->fieldParams->get('digit_background_color'),
			'digit_text_color' => $this->fieldParams->get('digit_text_color'),
		
			// Unit Label
			'label_font_size' => $this->fieldParams->get('label_font_size_control.label_font_size'),
			'label_font_weight' => $this->fieldParams->get('label_font_weight'),
			'unit_label_margin_top' => $this->fieldParams->get('unit_label_margin_top'),
			'unit_label_text_color' => $this->fieldParams->get('unit_label_text_color'),
		];

		$this->widget = new \NRFramework\Widgets\Countdown(json_decode(json_encode($this->payload), true));
	}

	/**
	 * Return all assets used by this field.
	 * 
	 * @return  void
	 */
	public function getAssets()
	{
		$exclude_breakpoints = [];

		// If we are not viewing the fullscreen previewer,
		// then get the widget's Custom CSS of the desktop
		// due to the IFrame having small width and triggering the tablet/mobile CSS breakpoints
		if (!$this->data->get('fullscreen'))
		{
			$exclude_breakpoints = ['tablet', 'mobile'];
		}
		
		return [
			'css' => \NRFramework\Widgets\Countdown::getCSS($this->theme),
			'js' =>  \NRFramework\Widgets\Countdown::getJS(),
			'custom_css' => $this->widget->getWidgetCSS($exclude_breakpoints)
		];
	}

	/**
	 * Get the theme.
	 * 
	 * @return  string
	 */
	private function getTheme()
	{
		$preset_source = $this->fieldParams->get('preset_source', 'preset');
		$preset = $this->fieldParams->get('preset', '1');

		// Determine theme
		return $preset_source === 'custom' ? 'custom' : ($preset === '8' ? 'oneline' : 'default');
	}
}