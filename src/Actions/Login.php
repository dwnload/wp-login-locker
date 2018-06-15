<?php

namespace Dwnload\WpLoginLocker\Actions;

use Dwnload\WpLoginLocker\Login\LastLoginColumns;
use Dwnload\WpLoginLocker\Login\WpLogin;
use Dwnload\WpLoginLocker\LoginLocker;
use Dwnload\WpLoginLocker\Plugins\WpUserProfiles\UserEmailSection;
use Dwnload\WpLoginLocker\Utilities\GeoUtilTrait;
use Dwnload\WpLoginLocker\WpMail\WpMail;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class Login
 * @package Dwnload\WpLoginLocker\Actions
 */
class Login extends AbstractHookProvider implements WpHooksInterface
{
    use GeoUtilTrait, HooksTrait;

    const SUBJECT = 'New login to %1$s account';

    /**
     * @var WpMail $wp_mail
     */
    private $wp_mail;

    /**
     * Add class hooks.
     */
    public function addHooks()
    {
        $this->addAction('wp_login', [$this, 'wpLoginAction'], 10, 2);
    }

    /**
     * Create a email notifying the user someone has logged in (if their notifications aren't off).
     * Also adds user meta data of their IP address and login time.
     *
     * @param string $user_login
     * @param \WP_User $user
     */
    protected function wpLoginAction(string $user_login, \WP_User $user)
    {
        $current_ip = $this->getIP();
        $last_login_ip = \get_user_meta($user->ID, LastLoginColumns::LAST_LOGIN_IP_META_KEY);
        $user_notification = \get_user_meta($user->ID, UserEmailSection::USER_META_KEY, true);

        /**
         * If the current IP does not match their last login IP
         * (and the user has login notifications 'on'), send a notification.
         */
        if ($current_ip !== \end($last_login_ip) && empty($user_notification)) {
            $this->wp_mail = new WpMail();
            $this->wp_mail->__set('pretext', $this->getEmailPretext());
            $this->wp_mail->send(
                $user->user_email,
                \sprintf(self::SUBJECT, $this->getHomeUrl()),
                $this->getEmailMessage($user)
            );
        }

        /**
         * Action when a user logs-in you can hook into.
         *
         * @param string $current_ip The current users IP address.
         * @param array $last_login_ip An array of the users last login IP's.
         * @param mixed $user_notification Whether the users notification preferences are enabled.
         */
        \do_action( LoginLocker::HOOK_PREFIX . 'wp_login', $current_ip, $last_login_ip, $user_notification );

        /**
         * Update the current users login meta data
         * (regardless of current IP or notification settings)
         */
        \add_user_meta($user->ID, LastLoginColumns::LAST_LOGIN_IP_META_KEY, $current_ip, true);
        \add_user_meta($user->ID, LastLoginColumns::LAST_LOGIN_TIME_META_KEY, \time(), true);
        unset($current_ip, $last_login_ip, $user_notification, $this->wp_mail);
    }

    /**
     * Get the pretext content.
     *
     * @return string
     */
    private function getEmailPretext(): string
    {
        \ob_start();
        include $this->getPlugin()->getDirectory() . 'templates/email/messages/action-login-pretext.php';
        $content = \ob_get_clean();

        /**
         * %1$s Site name
         */
        return \sprintf($content, $this->wp_mail->getFromName());
    }

    /**
     * Get our notification message from our messages templates.
     *
     * @param \WP_User $user
     *
     * @return string
     */
    private function getEmailMessage(\WP_User $user): string
    {
        \ob_start();
        include $this->getPlugin()->getDirectory() . 'templates/email/messages/action-login-notice.php';
        $content = \ob_get_clean();

        /**
         * Add our auth check key and the users email so they can access the
         * login page if they don't have "access" by a cookie session. Force re-auth
         * on login URL render so they have to re-enter there credentials.
         */
        $login_url = \add_query_arg(
            [
                WpLogin::AUTH_CHECK_KEY => \sanitize_email($user->user_email),
            ],
            \wp_login_url('', true)
        );

        /**
         * %1$s User first and last name (display name)
         * %2$s User agent
         * %3$s IP address
         * %4$s Login URL
         * %5$s Site name
         * %6$s Site email
         */
        return sprintf($content,
            $this->getUserName($user),
            $this->getUserAgent(),
            $this->getIP(),
            \esc_url($login_url),
            $this->wp_mail->getFromName(),
            $this->wp_mail->getFromAddress()
        );
    }

    /**
     * Return a user name based on the current WP_User. Checks whether they
     * have setup their first name\ or, display name before using their login
     * user name.
     *
     * @param \WP_User $user
     *
     * @return string
     */
    private function getUserName(\WP_User $user): string
    {
        if (!empty($user->first_name)) {
            return $user->first_name;
        } elseif (!empty($user->display_name)) {
            return $user->display_name;
        }

        return $user->user_login;
    }

    /**
     * Returns the site url host.
     *
     * @return string
     */
    private function getHomeUrl(): string
    {
        return \parse_url(\home_url(), PHP_URL_HOST);
    }
}
