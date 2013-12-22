<?php
namespace Cpt\MainBundle\Security;

use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class PermissionMap extends BasicPermissionMap
{
    const PERMISSION_COMMENT        = 'COMMENT';

    public function __construct()
    {
        parent::__construct();

        // This basically says "If you have VIEW, REVIEW, EDIT..., OWNER, 
        // you have VIEW".
        $this->map[self::PERMISSION_COMMENT] = array(
            MaskBuilder::MASK_VIEW,
            MaskBuilder::MASK_COMMENT,
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        );
    }
}
?>
