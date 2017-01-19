<?php
/**
 * @version    SVN: 
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.application.component.model');

/**
 * Methods supporting a list of Tjvendors records.
 *
 * @since  1.6
 */
class TjvendorsModelVendorFees extends JModelList
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'currency', 'b.`currency`',
				'percent_commission', 'b.`percent_commission`',
				'flat_commission', 'b.`flat_commission`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.id';
		}

		$this->setState('list.ordering', $orderCol);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_tjvendors');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('b.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$input = JFactory::getApplication()->input;
		$this->vendor_id = $input->get('vendor_id', '', 'INT');
		$vendor_id = $this->vendor_id;

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($db->quoteName(array('a.vendor_id','a.vendor_title','b.percent_commission','b.flat_commission','b.id','b.currency')));

		$query->from($db->quoteName('#__tjvendors_fee', 'b'));

	$query->join('LEFT', ($db->quoteName('#__tjvendors_vendors', 'a') . 'ON ' . $db->quoteName('b.vendor_id') . ' = ' . $db->quoteName('a.vendor_id') ));

		$query->where($db->quoteName('a.vendor_id') . ' = ' . $vendor_id);

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(b.currency LIKE ' . $search .
							'OR b.percent_commission LIKE' . $search .
							'OR b.flat_commission LIKE' . $search . ')');
			}

			$this->search = $search;
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
		$TjvendorsModelVendor = JModelLegacy::getInstance('Vendor', 'TjvendorsModel');
		$vendorDetail = $TjvendorsModelVendor->getItem();
		$vendorTitle = $vendorDetail->vendor_title;

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models');
		$VendorFeeModel = JModelLegacy::getInstance('VendorFee', 'TjvendorsModel');

		$input = JFactory::getApplication()->input;
		$curr = $input->get('curr', '', 'ARRAY');
		$currency = array();
		$i = 0;

		foreach ($items as $item)
		{
			if (empty($item->id))
			{
				$item->id = $VendorFeeModel->getVendorFeeId($item->vendor_id, $item->currency);
			}

			$currency[] = $item->currency;
			$this->vendor_title = $item->vendor_title;
			$items[$i++] = $item;
		}

		$resultCurrency = array_diff($curr, $currency);

		if (empty($this->search))
		{
			foreach ($resultCurrency as $result)
			{
				$items[$i]->vendor_id = $this->vendor_id;
				$items[$i]->vendor_title = $vendorTitle;
				$items[$i]->currency = $result;
				$items[$i]->percent_commission = 0.0;
				$items[$i++]->flat_commission = 0.0;
			}
		}

		return $items;
	}

	/**
	 * Method for delete vendors
	 *
	 * @param   Integer  $tj_vendors_id  Id
	 *
	 * @return flag
	 */
	public function deleteVendorfee($tj_vendors_id)
	{
		$tjvendorsid = implode(',', $tj_vendors_id);

		if ($tjvendorsid)
		{
			$db = JFactory::getDbo();
			$query = "DELETE FROM #__tjvendors_fee where id IN (" . $tjvendorsid . ")";
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
			else
			{
				return true;
			}
		}
	}
}
