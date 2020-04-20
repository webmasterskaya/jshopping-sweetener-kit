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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
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
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $show_prods_from_subcategories;

	/**
	 * @param $subject
	 * @param $config
	 *
	 *
	 * @since 1.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->JSConfig                      = JSFactory::getConfig();
		$this->load_jquery_chosen_admin      = ($this->params->get('load_jquery_chosen_admin', 1)) ? true : false;
		$this->show_prods_from_subcategories = ($this->params->get('show_prods_from_subcategories', 1)) ? true : false;
	}

	/**
	 * @param $controller
	 *
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

	/**
	 * @param   string  $model
	 * @param   string  $adv_result
	 * @param   string  $adv_from
	 * @param   string  $adv_query
	 * @param   string  $order_query
	 * @param   string  $filters
	 *
	 *
	 * @since 1.0.0
	 */
	public function onBeforeQueryGetProductList($model, &$adv_result, &$adv_from, &$adv_query, &$order_query, &$filters)
	{
		if ($this->app->isClient('site') && $this->app->input->getCmd('option') == 'com_jshopping')
		{
			if ($model == 'category')
			{
				// Show products from subcategories
				if ($this->show_prods_from_subcategories)
				{
					$this->prepareQueryForSelectProdsFromSubcategories($adv_query);
					$order_query = 'GROUP BY prod.product_id ' . $order_query;
				}
			}
		}
	}

	/**
	 * @param $adv_query
	 *
	 *
	 * @since 1.0.0
	 */
	protected function prepareQueryForSelectProdsFromSubcategories(&$adv_query)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(['category_id', 'category_parent_id', 'ordering', 'category_publish']))
			->from($db->quoteName('#__jshopping_categories'))
			->where($db->quoteName('category_publish') . '=' . 1)
			->order($db->quoteName('category_parent_id') . ' ASC');

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$parentCategoryId     = $this->app->input->getCmd('category_id', 0);
		$selectFromCategories = [$parentCategoryId];

		if (!empty($categories))
		{
			foreach ($categories as $category)
			{
				if (in_array($category->category_parent_id, $selectFromCategories))
				{
					$selectFromCategories[] = $category->category_id;
					$adv_query              .= ' OR ' . $db->quoteName('pr_cat.category_id') . ' = ' . $db->quote($category->category_id);
				}
			}
		}
	}

	/**
	 * @param $model
	 * @param $adv_result
	 * @param $adv_from
	 * @param $adv_query
	 * @param $filters
	 *
	 *
	 * @since 1.0.0
	 */
	public function onBeforeQueryCountProductList($model, &$adv_result, &$adv_from, &$adv_query, &$filters)
	{
		if ($this->app->isClient('site') && $this->app->input->getCmd('option') == 'com_jshopping')
		{
			if ($model == 'category')
			{
				// Get count products with subcategories
				if ($this->show_prods_from_subcategories)
				{
					$this->prepareQueryForSelectProdsFromSubcategories($adv_query);
					$adv_result = 'COUNT(DISTINCT(prod.product_id))';
				}
			}
		}
	}
}