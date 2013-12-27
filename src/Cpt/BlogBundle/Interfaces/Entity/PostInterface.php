<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Interfaces\Entity;

use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;

interface PostInterface extends PublicationInterface
{

     function getPermalinkGenerator();
     function getPublishedHomePage();
     function setPublishedHomePage($value);

}
