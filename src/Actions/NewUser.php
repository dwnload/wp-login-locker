<?php declare(strict_types=1);

namespace Dwnload\WpLoginLocker\Actions;

use Dwnload\WpLoginLocker\LoginLocker;
use Dwnload\WpLoginLocker\RequestsInterface;
use Dwnload\WpLoginLocker\Utilities\GeoUtilTrait;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class NewUser
 * @package Dwnload\WpLoginLocker\Actions
 */
class NewUser implements RequestsInterface, WpHooksInterface
{
    use GeoUtilTrait, HooksTrait;

    /**
     * Add class hooks.
     */
    public function addHooks()
    {
        $this->addAction('user_register', [$this, 'userRegisterAction']);
    }

    /**
     * On user registration, add their first unique meta of their IP address and login time.
     *
     * @param int $user_id The new users ID.
     */
    protected function userRegisterAction(int $user_id)
    {
        \add_user_meta($user_id, LoginLocker::LAST_LOGIN_IP_META_KEY, $this->getIP(), true);
        \add_user_meta($user_id, LoginLocker::LAST_LOGIN_TIME_META_KEY, \time(), true);
    }
}
