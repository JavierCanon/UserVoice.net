<?php
/**
 * @package      Userideas
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Userideas\Helper;

use Prism\Helper\HelperInterface;
use Prism\Utilities\TagHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare the statuses of the items.
 *
 * @package      Userideas
 * @subpackage   Helpers
 */
class PrepareTagsHelper implements HelperInterface
{
    /**
     * Prepare the parameters of the items.
     *
     * @param array $data
     * @param array $options
     */
    public function handle(&$data, array $options = array())
    {
        if (count($data) > 0) {
            $ids = array();
            foreach ($data as $item) {
                $ids[] = $item->id;
            }

            if (count($ids) > 0) {
                $tagsHelper = new TagHelper();
                $tags       = $tagsHelper->getItemTags($ids, $options);

                if (count($tags) > 0) {
                    foreach ($data as $key => $item) {
                        $item->tags = (isset($tags[$item->id])) ? $tags[$item->id] : array();
                    }
                }
            }
        }
    }
}
