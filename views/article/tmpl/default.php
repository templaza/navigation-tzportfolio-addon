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

if($row = $this -> row):
    $this -> document -> addStyleSheet(TZ_Portfolio_PlusUri::root(true).'/addons/content/navigation/css/style.css');
?>

<div class="navigation-addon-container position-relative d-flex justify-content-between">
    <div class="previous justify-content-start">
<?php if ($row->prev_url) :
    $direction = $lang->isRtl() ? 'right' : 'left'; ?>
        <a class="uk-link-reset" href="<?php echo $row->prev_url; ?>" rel="prev">
            <div class="card">
                <?php
                if ($this -> params -> get('show_media', 1)) {
                    echo '<div class="navigation-addon-media"><img src="' . $row->prev_media .'" alt="'.$row->prev_label.'" /></div>';
                }
                ?>
                <div class="card-body">
                    <?php
                    if ($this -> params -> get('show_navigation_text', 1)) {
                        echo '<div class="navigation-addon-text uk-text-meta"><i class="fa-solid fa-arrow-' . $direction . '"></i> ' . JText::_('JPREV') .'</div>';
                    }
                    if ($this -> params -> get('show_title', 0)) {
                        echo '<h6 class="navigation-addon-title uk-margin-small uk-margin-remove-bottom">'.$row->prev_label.'</h6>';
                    }
                    ?>
                </div>
            </div>
        </a>
<?php endif; ?>
    </div>
    <div class="next justify-content-end">
<?php if ($row->next_url) :
    $direction = $lang->isRtl() ? 'left' : 'right'; ?>
        <a class="uk-link-reset" href="<?php echo $row->next_url; ?>" rel="next">
            <div class="card">
                <?php
                if ($this -> params -> get('show_media', 1)) {
                    echo '<div class="navigation-addon-media"><img src="' . $row->next_media .'" alt="'.$row->next_label.'" /></div>';
                }
                ?>
                <div class="card-body">
                    <?php
                    if ($this -> params -> get('show_navigation_text', 1)) {
                        echo '<div class="navigation-addon-text uk-text-meta"><i class="fa-solid fa-arrow-' . $direction . '"></i> ' . JText::_('JNEXT') .'</div>';
                    }
                    if ($this -> params -> get('show_title', 0)) {
                        echo '<h6 class="navigation-addon-title uk-margin-small uk-margin-remove-bottom">'.$row->next_label.'</h6>';
                    }
                    ?>
                </div>
            </div>
        </a>

<?php endif; ?>
    </div>
</div>
<?php endif;