phoneNumberConfirmed:
           type: boolean
           options:
             default: 0
       twoFactorEnabled:
           type: boolean
           options:
             default: 0
       lockoutEndDateUtc:
           type: string
           length: 350
           nullable: true
       lockoutEnabled:
           type: boolean
           options:
             default: 1
       accessFailedCount:
           type: integer
           nullable: true
       roles:
           type: string
           length: 350
           nullable: true

$louis21user->setLockoutEndDateUtc();
$louis21user->setLockoutEnabled();
$louis21user->setAccessFailedCount();
$louis21user->setRoles();
