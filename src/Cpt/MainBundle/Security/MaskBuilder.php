<?php

namespace Cpt\MainBundle\Security;

use Symfony\Component\Security\Acl\Permission\MaskBuilder as BaseMaskBuilder;

class MaskBuilder extends BaseMaskBuilder
{
    const MASK_COMMENT         = 256;
    const CODE_COMMENT         = 'T';
}

?>
