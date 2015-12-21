<?php

/*
 * This file is part of CSBill project.
 *
 * (c) 2013-2015 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace CSBill\CoreBundle\Menu;

use Knp\Menu\ItemInterface as BaseInterface;

interface ItemInterface extends BaseInterface
{
    /**
     * @param string $type
     *
     * @return $this
     */
    public function addDivider($type = '');

    /**
     * @return bool
     */
    public function isDivider();
}
