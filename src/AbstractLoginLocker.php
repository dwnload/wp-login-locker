<?php declare(strict_types=1);

namespace TheFrosty\WpLoginLocker;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class AbstractLoginLocker
 *
 * @package TheFrosty\WpLoginLocker
 */
abstract class AbstractLoginLocker extends AbstractHookProvider implements
    HttpFoundationRequestInterface,
    PluginAwareInterface,
    WpHooksInterface
{

    use HooksTrait, PluginAwareTrait, HttpFoundationRequestTrait;
}
