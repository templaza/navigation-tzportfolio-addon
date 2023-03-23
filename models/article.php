<?php
/*------------------------------------------------------------------------

# Navigation Addon

# ------------------------------------------------------------------------

# Author:    DuongTVTemPlaza

# Copyright: Copyright (C) 2016 tzportfolio.com. All Rights Reserved.

# @License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Website: http://www.tzportfolio.com

# Technical Support:  Forum - http://tzportfolio.com/forum

# Family website: http://www.templaza.com

-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Filesystem\File;
class PlgTZ_Portfolio_PlusContentNavigationModelArticle extends TZ_Portfolio_PlusPluginModelItem
{

    public function getNavigation(){

        $app   = JFactory::getApplication();
        $view  = $app->input->get('view');
        $print = $app->input->getBool('print');

        if ($print)
        {
            return false;
        }

        if($row = $this -> getItem()){
            $db         = JFactory::getDbo();
            $user       = JFactory::getUser();
            $lang       = JFactory::getLanguage();
            $nullDate   = $db->getNullDate();
            $params     = $this -> getState('params');
            $addonParams    = $this -> addon -> params;

            $date       = JFactory::getDate();
            $now        = $date->toSql();

            $uid        = $row->id;
            $option     = 'com_tz_portfolio_plus';
            $canPublish = $user->authorise('core.edit.state', $option . '.article.' . $row->id);

            /**
             * The following is needed as different menu items types utilise a different param to control ordering.
             * For Blogs the `orderby_sec` param is the order controlling param.
             * For Table and List views it is the `orderby` param.
             **/
            $params_list = $params->toArray();

            if (array_key_exists('orderby_sec', $params_list))
            {
                $order_method = $params->get('orderby_sec', '');
            }
            else
            {
                $order_method = $params->get('orderby', '');
            }

            // Additional check for invalid sort ordering.
            if ($order_method == 'front')
            {
                $order_method = '';
            }

            // Get the order code
            $orderDate = $params->get('order_date');
            $queryDate = $this->getQueryDate($orderDate);

            // Determine sort order.
            switch ($order_method)
            {
                case 'date' :
                    $orderby = $queryDate;
                    break;
                case 'rdate' :
                    $orderby = $queryDate . ' DESC ';
                    break;
                case 'alpha' :
                    $orderby = 'a.title';
                    break;
                case 'ralpha' :
                    $orderby = 'a.title DESC';
                    break;
                case 'hits' :
                    $orderby = 'a.hits';
                    break;
                case 'rhits' :
                    $orderby = 'a.hits DESC';
                    break;
                case 'order' :
                    $orderby = 'a.ordering';
                    break;
                case 'author' :
                    $orderby = 'a.created_by_alias, u.name';
                    break;
                case 'rauthor' :
                    $orderby = 'a.created_by_alias DESC, u.name DESC';
                    break;
                case 'front' :
                    $orderby = 'f.ordering';
                    break;
                default :
                    $orderby = 'a.ordering';
                    break;
            }

            $xwhere = ' AND (a.state = 1 OR a.state = -1)'
                . ' AND (publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($now) . ')'
                . ' AND (publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($now) . ')';

            // Array of articles in same category correctly ordered.
            $query = $db->getQuery(true);

            // Sqlsrv changes
            $case_when = ' CASE WHEN ' . $query->charLength('a.alias', '!=', '0');
            $a_id = $query->castAsChar('a.id');
            $case_when .= ' THEN ' . $query->concatenate(array($a_id, 'a.alias'), ':');
            $case_when .= ' ELSE ' . $a_id . ' END as slug';

            $case_when1 = ' CASE WHEN ' . $query->charLength('cc.alias', '!=', '0');
            $c_id = $query->castAsChar('cc.id');
            $case_when1 .= ' THEN ' . $query->concatenate(array($c_id, 'cc.alias'), ':');
            $case_when1 .= ' ELSE ' . $c_id . ' END as catslug';

            $mainCategory   = null;
            $catid          = 'm.catid = ' . (int) $row->catid;
            if(!$params -> get('navigation_article_in', 0)) {
                $mainCategory   = ' AND m.main = 1';
            }else{
                if(isset($row -> second_categories) && $row -> second_categories) {
                    $scatid = JArrayHelper::getColumn($row->second_categories, 'id');
                    $catid  = 'm.catid IN('.((int) $row -> catid).','.implode(',',$scatid).')';
                }
            }

            $query->select('a.id, a.title, m.catid, a.language, a.type, a.media,' . $case_when . ',' . $case_when1)
                ->from('#__tz_portfolio_plus_content AS a')
                -> join('INNER', '#__tz_portfolio_plus_content_category_map AS m ON m.contentid = a.id'.$mainCategory)
                ->join('LEFT', '#__tz_portfolio_plus_categories AS cc ON cc.id = m.catid')
                -> where($catid.' AND a.state = ' . (int) $row->state
                    . ($canPublish ? '' : ' AND a.access IN (' . implode(",", JAccess::getAuthorisedViewLevels($user->id)) . ') ') . $xwhere
                );

            $query -> where('a.state = ' . (int) $row->state
                . ($canPublish ? '' : ' AND a.access IN (' . implode(",", JAccess::getAuthorisedViewLevels($user->id)) . ') ') . $xwhere
            );
            $query->order($orderby);

            if ($app->isClient('site') && $app->getLanguageFilter())
            {
                $query->where('a.language in (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');
            }

            $db->setQuery($query);
            $list = $db->loadObjectList('id');

            // This check needed if incorrect Itemid is given resulting in an incorrect result.
            if (!is_array($list))
            {
                $list = array();
            }

            reset($list);

            // Location of current content item in array list.
            $location = array_search($uid, array_keys($list));
            $rows     = array_values($list);

            $row->prev = null;
            $row->next = null;

            if ($location - 1 >= 0)
            {
                // The next content item cannot be in the array position -1.
                $row->next = $rows[$location - 1];
            }

            if (($location + 1) < count($rows))
            {
                // The previous content item cannot be in an array position greater than the number of array postions.
                $row->prev = $rows[$location + 1];
            }

            if ($row->prev)
            {
                $row->prev_label = $row->prev->title;
                $row->prev_url = JRoute::_(TZ_Portfolio_PlusHelperRoute::getArticleRoute($row->prev->slug, $row->prev->catid, $row->prev->language));
                $row->prev_media = self::getMedia($row->prev, $row->prev->type, $params->get('media_prefix', ''));
            }
            else
            {
                $row->prev_label = '';
                $row->prev_url = '';
                $row->prev_media = '';
            }

            if ($row->next)
            {
                $row->next_label = $row->next->title;
                $row->next_url = JRoute::_(TZ_Portfolio_PlusHelperRoute::getArticleRoute($row->next->slug, $row->next->catid, $row->next->language));
                $row->next_media = self::getMedia($row->next, $row->next->type, $params->get('media_prefix', ''));
            }
            else
            {
                $row->next_label = '';
                $row->next_url = '';
                $row->next_media = '';
            }
            return $row;
        }
        return false;
    }

    private static function getQueryDate($orderDate)
    {
        $db = JFactory::getDbo();

        switch ($orderDate)
        {
            // Use created if modified is not set
            case 'modified' :
                $queryDate = ' CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END';
                break;

            // Use created if publish_up is not set
            case 'published' :
                $queryDate = ' CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END ';
                break;

            // Use created as default
            case 'created' :
            default :
                $queryDate = ' a.created ';
                break;
        }

        return $queryDate;
    }

    private static function getMedia($row, $type, $prefix) {
        $url = '';
        $media  =   json_decode($row->media);
        switch ($type) {
            case 'image' :
                $image_url_ext = File::getExt($media -> image -> url);
                $size = $prefix ? $prefix : 'o';
                $image_url = str_replace('.' . $image_url_ext, '_' . $size . '.'
                    . $image_url_ext, $media -> image -> url);

                $url = JURI::base( true ) . '/' . $image_url;
                break;
            case 'grid_gallery':
                if ( is_array($media -> grid_gallery -> data) && count($media -> grid_gallery -> data) ) {
                    if ($prefix || $prefix != 'o') {
                        $url  =   'images/tz_portfolio_plus/grid_gallery/'.$row -> id.'/resize/'
                            . File::stripExt($media -> grid_gallery -> data[0])
                            . '_' . $prefix . '.' . File::getExt($media -> grid_gallery -> data[0]);
                    } else {
                        $url  =   'images/tz_portfolio_plus/grid_gallery/'.$row -> id.'/'.$media -> grid_gallery -> data[0];
                    }
                }
                break;
        }
        return $url;
    }
}

