<?php
namespace Yauphp\Web;

/**
 *  路由接口
 * @author Tomix
 *
 */
interface IRoute
{
    /**
     * 上下文路径
     */
    function getContextPath();

    /**
     * 控制器名称
     */
    function getControllerName();

    /**
     * 操作名称
     */
    function getActionName();

    /**
     * 视图文件
     */
    function getViewFile();

    /**
     * 初始化参数
     */
    function getInitParams();
}

