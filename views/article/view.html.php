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

class PlgTZ_Portfolio_PlusContentNavigationViewArticle extends JViewLegacy{
    protected $row  = null;
    public function display($tpl = null){
        $this -> row    = $this -> get('Navigation');
        $this -> params =   $this -> get('State') ->get('params');
        parent::display($tpl);
    }
}
