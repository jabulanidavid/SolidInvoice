<?php

/*
 * This file is part of the CSBill package.
 *
 * (c) Pierre du Plessis <info@customscripts.co.za>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CSBill\ClientBundle\Menu;

use Knp\Menu\ItemInterface;
use CSBill\CoreBundle\Menu\Builder\BuilderInterface;

class ClientMenu implements BuilderInterface {

	public function topMenu(ItemInterface $menu)
	{
		$menu->addChild('Clients', array('route' => '_clients_index'));
	}
}