<?php declare(strict_types=1);

namespace Dwnload\WpLoginLocker\Actions;

use Dwnload\WpLoginLocker\Login\LastLoginColumns;
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
     * Create a email notifying the user someone has logged in.
     *
     * @param int $user_id
     */
    protected function userRegisterAction(int $user_id)
    {
        \add_user_meta($user_id, LastLoginColumns::LAST_LOGIN_IP_META_KEY, $this->getIP());
        \add_user_meta($user_id, LastLoginColumns::LAST_LOGIN_TIME_META_KEY, \time());
    }
}
