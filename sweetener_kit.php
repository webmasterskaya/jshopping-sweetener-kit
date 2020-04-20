<?php
/**
 * @package    JShopping - Sweetener kit
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - Webmasterskaya
 * @copyright  Copyright (c) 2020 Webmasterskaya. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

class plgJshoppingSweetener_Kit extends CMSPlugin
{
	/**
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var    jshopConfig
	 * @since  1.0.0
	 */
	protected $JSConfig;

	/**
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $load_jquery_chosen_admin;

	/**
	 * @param $subject
	 * @param $config
	 *
	 * @since 1.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->JSConfig                 = JSFactory::getConfig();
		$this->load_jquery_chosen_admin = ($this->params->get('load_jquery_chosen_admin', 1)) ? true : false;
	}

	/**
	 * @param $controller
	 *
	 * @since 1.0.0
	 */
	public function onAfterGetControllerAdmin(&$controller)
	{
		if ($this->app->isClient('administrator') && $this->app->input->getCmd('option') == 'com_jshopping')
		{
			// Load jQuery Chosen
			if ($this->load_jquery_chosen_admin)
			{
				HTMLHelper::_('formbehavior.chosen', 'select');
			}
		}
	}
}