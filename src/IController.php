<?php
namespace Yauphp\Web;

use Yauphp\Http\Context;

/**
 * 控制器接口
 * @author Tomix
 *
 */
interface IController
{
    /**
     *  上下文
     * @param Context $value
     */
    function setContext(Context $value);

    /**
     * 区域名称
     * @param string $value
     */
    function setAreaName($value);

    /**
     * 视图文件
     * @param string $value
     */
    function setViewFile($value);

    /**
     * 初始化参数
     * @param array $value
     */
    function setInitParams($value);

    /**
     * 获取区域名
     */
    function getAreaName();

    /**
     * 获取当前激活的操作名
     */
    function getActionName();

    /**
     * 设置是否调试模式
     * @param bool $value
     */
    function setDebug($value);

    /**
     * 激活控制器方法
     * @param string $action
     */
    function invoke($action);
}

