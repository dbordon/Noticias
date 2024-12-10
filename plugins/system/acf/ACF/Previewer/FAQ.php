<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace ACF\Previewer;

defined('_JEXEC') or die;

class FAQ extends Field
{
	/**
	 * The field framework widget name.
	 * 
	 * @var  string
	 */
	protected $field = 'FAQ';
	
	/**
	 * Render the field.
	 * 
	 * @return  string
	 */
	public function onSetup()
	{
		$value = [
			[
				'question' => 'Who should use EngageBox?',
				'answer' => 'EngageBox is the most powerful popup engine in the Joomla! market used by marketing agencies, bloggers, eCommerce websites, and all small businesses. If you want to grow your email list, improve your website conversions, and reduce cart abandonment, then you need EngageBox.'
			],
			[
				'question' => 'What\'s required to use EngageBox?',
				'answer' => 'EngageBox can be used in both Joomla 3 and Joomla 4. In detail, you will need an up-to-date version of Joomla 3.8.0 or higher, PHP 7.0.0 or higher, and MySQL 5 or higher.'
			],
			[
				'question' => 'Do I need coding skills to use EngageBox?',
				'answer' => 'Absolutely not! You can create and customize beautiful popups without any coding knowledge. We made it extremely user-friendly, so you can build a high-converting popup without hiring a developer.'
			],
			[
				'question' => 'What type of conversions can I expect?',
				'answer' => 'It will make a significant difference. Game-changing! Our average user sees over 500% increase in sales, customers base and growth in general.'
			],
			[
				'question' => 'What if a visitor has a pop-up adblocker enabled?',
				'answer' => 'Will still work! EngageBox produces popups in a way which can\'t be blocked by the browser’s pop-up blocking feature or 3rd party extensions such as AdBlock or uBlock.'
			],
		];
		
		$this->payload = [
			'value' => $value,
			'css_class' => ' template_' . $this->fieldParams->get('template'),
			'columns' => (int) $this->fieldParams->get('columns', 1),
			'item_gap' => $this->fieldParams->get('item_gap_control.item_gap', 20),
			'column_gap' => $this->fieldParams->get('column_gap_control.column_gap', 20),
			'item_background_color' => $this->fieldParams->get('background_color'),
			'item_border_radius' => $this->fieldParams->get('border_radius_control.item_border_radius'),
			'item_padding' => $this->fieldParams->get('padding_control.item_padding'),
			'question_font_size' => $this->fieldParams->get('question_font_size_control.question_font_size'),
			'question_text_color' => $this->fieldParams->get('question_text_color'),
			'answer_font_size' => $this->fieldParams->get('answer_font_size_control.answer_font_size'),
			'answer_text_color' => $this->fieldParams->get('answer_text_color'),
			'generate_faq' => $this->fieldParams->get('generate_faq', '0') === '1',
			'keep_one_question_open' => $this->fieldParams->get('keep_one_question_open', '0') === '1',
			'separator' => $this->fieldParams->get('separator', '0') === '1',
			'separator_color' => $this->fieldParams->get('separator_color'),
			'initial_state' => $this->fieldParams->get('initial_state', 'first-open')
		];

		$show_toggle_icon = $this->fieldParams->get('show_toggle_icon', '1') === '1';
		$this->payload['show_toggle_icon'] = $show_toggle_icon;
		if ($show_toggle_icon)
		{
			$this->payload['icon'] = $this->fieldParams->get('icon', 'arrow');
			$this->payload['icon_position'] = $this->fieldParams->get('icon_position', 'right');
		}

		$this->widget = new \NRFramework\Widgets\FAQ(json_decode(json_encode($this->payload), true));
	}

	/**
	 * Adds some extra CSS to the previewer's body.
	 * 
	 * @return  string
	 */
	protected function getFieldPreviewerCSS()
	{
		if ((int) $this->fieldParams->get('template') !== 4)
		{
			return;
		}
		
		return '
			body {
				background: #EBEBEB !important;
				padding: 20px !important;
			}
		';
	}
}