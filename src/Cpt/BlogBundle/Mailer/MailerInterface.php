<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Mailer;

use Cpt\BlogBundle\Interfaces\Entity\CommentInterface;

interface MailerInterface
{
    /**
     *
     * @return mixed
     */
    public function sendCommentNotification(CommentInterface $comment);
}
