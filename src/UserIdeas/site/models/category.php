<?php
/**
 * @package      Userideas
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class UserideasModelCategory extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'alpha', 'ralpha',
                'category', 'rcategory',
                'author', 'rauthor',
                'hits', 'rhits',
                'votes', 'rvotes',
                'date', 'rdate',
                'random',
                'order'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load the component parameters.
        $params = $app->getParams($this->option);
        $this->setState('params', $params);

        // Get category id
        $value = $app->getUserStateFromRequest($this->context . '.catid', 'id', 0, 'int');
        $this->setState('filter.category_id', $value);

        // Get status id
        $value = $app->input->getInt('filter_status');
        $this->setState('filter.status_id', $value);

        // Ordering
        $itemId       = $app->input->get('Itemid', 0, 'int');
        $contextOrder = $this->context . '.list.' . $itemId . '.filter_order';

        $container = Prism\Container::getContainer();
        $container->set(Userideas\Constants::CONTAINER_FILTER_ORDER_CONTEXT, $contextOrder);

        $orderCol = $app->getUserStateFromRequest($contextOrder, 'filter_order', '', 'string');
        if (!in_array($orderCol, $this->filter_fields, true)) {
            $orderCol = '';
        } else {
            $orderCol = $this->prepareOrderBy($orderCol);
        }
        $this->setState('list.ordering', $orderCol);

        /*$listOrder = $app->getUserStateFromRequest($this->context . '.list.' . $itemId . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''), true)) {
            $listOrder = 'ASC';
        }
        $this->setState('list.direction', $listOrder);*/

        // Pagination
        $value = $app->getUserStateFromRequest($this->context . '.list.' . $itemId . '.limit', 'limit', $params->get('items_limit'), 'uint');
        if (!$value) {
            $value = $app->input->getInt('limit', $app->get('list_limit', 20));
        }
        $this->setState('list.limit', $value);

        $this->setState('list.start', $app->input->getInt('limitstart', 0));
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.status_id');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.description, a.votes, a.record_date, a.catid, a.user_id, a.status_id, a.params, a.hits, a.access, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                'b.name AS author, b.username, ' .
                'c.title AS category, c.access AS category_access, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug, ' .
                'd.title AS status_title, d.params AS status_params, d.default AS status_default'
            )
        );
        $query->from($db->quoteName('#__uideas_items', 'a'));
        $query->leftJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id');
        $query->leftJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id');
        $query->leftJoin($db->quoteName('#__uideas_statuses', 'd') . ' ON a.status_id = d.id');

        // Category filter
        $categoryId = $this->getState('filter.category_id');
        $query->where('a.catid = ' . (int)$categoryId);

        // Filter by status
        $statusId = (int)$this->getState('filter.status_id');
        if ($statusId > 0) {
            $query->where('a.status_id = ' . (int)$statusId);
        }

        // Filter by state.
        $query->where('a.published = ' . (int)Prism\Constants::PUBLISHED);

        // Filter by access level.
        $user   = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $query
            ->where('a.access IN (' . $groups . ')')
            ->where('c.access IN (' . $groups . ')');

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    /**
     * Prepare a string used for ordering results.
     *
     * @param string $order
     *
     * @return string
     */
    protected function prepareOrderBy($order)
    {
        switch ($order) {
            case 'date':
                $orderBy = 'a.record_date';
                break;

            case 'rdate':
                $orderBy = 'a.record_date DESC';
                break;

            case 'alpha':
                $orderBy = 'a.title';
                break;

            case 'ralpha':
                $orderBy = 'a.title DESC';
                break;

            case 'hits':
                $orderBy = 'a.hits DESC';
                break;

            case 'rhits':
                $orderBy = 'a.hits';
                break;

            case 'author':
                $orderBy = 'author';
                break;

            case 'rauthor':
                $orderBy = 'author DESC';
                break;

            case 'random':
                $query   = $this->getDbo()->getQuery(true);
                /** @var JDatabaseQueryMysqli $query */

                $orderBy = $query->Rand();
                break;

            case 'order':
                $orderBy = 'a.ordering';
                break;

            case 'votes':
                $orderBy = 'a.votes DESC';
                break;

            case 'rvotes':
                $orderBy = 'a.votes';
                break;

            default:
                $orderBy = '';
                break;
        }

        return $orderBy;
    }

    protected function getOrderString()
    {
        $orderBy   = array();
        $orderCol  = $this->getState('list.ordering');
        $params    = $this->getState('params');

        if ($orderCol) {
            $orderBy[]   = $orderCol;
        }

        $itemOrderBy     = $params->get('orderby_sec', Prism\Constants::ORDER_MOST_RECENT_FIRST);
        $categoryOrderBy = $params->def('orderby_pri', '');
        $primary         = Prism\Utilities\QueryHelper::orderbyPrimary($categoryOrderBy);
        $secondary       = $this->prepareOrderBy($itemOrderBy);

        if ($primary !== '') {
            $orderBy[] = $primary;
        }

        if ($secondary !== '' and $orderCol !== $secondary) {
            $orderBy[] = $secondary;
        }

        return implode(', ', $orderBy);
    }

    public function getComments()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);

        $query
            ->select('a.item_id, COUNT(*) AS number')
            ->from($db->quoteName('#__uideas_comments', 'a'))
            ->group('a.item_id');

        $db->setQuery($query);

        return $db->loadAssocList('item_id', 'number');
    }
}
