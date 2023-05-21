<?php
namespace Yauphp\Web\Internal;

use Yauphp\Web\IRoute;
use Yauphp\Config\IConfigurable;
use Yauphp\Config\IConfiguration;
use Yauphp\Common\Util\StringUtils;

/**
 * 内置路由
 * 在执行配置注入时自动执行加载
 * @author Tomix
 *
 */
class Route implements IRoute, IConfigurable
{
    /**
     * 上下文路径
     * @var string
     */
    private $m_contextPath;

    /**
     * 上下文路径别名
     */
    private $m_contextPathAlias;

    /**
     * 当前控制器名称
     * @var string
     */
    private $m_controllerName;

    /**
     * 当前操作名称
     * @var string
     */
    private $m_actionName;

    /**
     * 当前视图模板文件
     * @var string
     */
    private $m_viewFile;

    /**
     * 初始化参数
     * @var string
     */
    private $m_initParams=[];

    /**
     * 配置
     * @var IConfiguration
     */
    private $m_config=null;

    /**
     * 配置节点名
     * @var string
     */
    private $m_cfgSection="route";

    /**
     * 默认控制器名
     * @var string
     */
    private $m_defaultControllerName="Home";

    /**
     * 默认操作名
     * @var string
     */
    private $m_defaultActionName="index";

    /**
     * 路由配置节点名
     * @param string $value
     */
    public function setConfigSection($value){
        $this->m_cfgSection=$value;
    }

    /**
     * 默认控制器名
     * @param string $value
     */
    public function setDefaultControllerName($value){
        $this->m_defaultActionName=$value;
    }

    /**
     * 默认操作名
     * @param string $value
     */
    public function setDefaultActionName($value){
        $this->m_defaultActionName=$value;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Yauphp\Config\IConfigurable::setConfiguration()
     */
    public function setConfiguration(IConfiguration $value)
    {
        $this->m_config=$value;
        $this->load();
    }

    /**
     * 获取配置实例
     * @return \Yauphp\Config\IConfiguration
     */
    public function getConfiguration() : \Yauphp\Config\IConfiguration{
        return $this->m_config;
    }

    /**
     * 上下文路径
     */
    public function getContextPath(){
        return $this->m_contextPath;
    }

    /**
     * 上下文路径(别名)
     */
    public function getContextPathAlias(){
        return $this->m_contextPathAlias;
    }

    /**
     * 控制器名称
     */
    public function getControllerName(){
        return $this->m_controllerName;
    }

    /**
     * 操作名称
     */
    public function getActionName(){
        return $this->m_actionName;
    }

    /**
     * 视图文件
     */
    public function getViewFile(){
        return $this->m_viewFile;
    }

    /**
     * 初始化参数
     */
    public function getInitParams(){
        return $this->m_initParams;
    }

    /**
     * 加载
     */
    private function load() {
        $config=$this->getConfiguration()->getConfigValues($this->m_cfgSection);
        $uri=$_SERVER["REQUEST_URI"];
        if(strpos($uri, "?")>0){
            $uri=substr($uri, 0,strpos($uri, "?"));
        }
        foreach($config as $contextPath=>$context){
            $_contextPath=$contextPath != "/" ? $contextPath : "";
            $alias = array_key_exists("alias",$context)?$context["alias"]:"";
            $_alias = $alias != "/" ? $alias : "";
            $namespace=trim($context["namespace"],"\\");
            $rules=$context["rules"];
            foreach ($rules as $rule){
                $url=ltrim($rule["url"],"/");
                $urlAlias="";
                if(empty($url)){
                    if(!empty($_alias)){
                        $urlAlias=$_alias;
                    }
                    $url=$_contextPath;
                }else{
                    if(!empty($_alias)){
                        $urlAlias=$_alias."/".$url;
                    }
                    $url=$_contextPath."/".$url;
                }
                $url_mode="/".str_replace("/", "\/", $url)."/";
                $matches=[];
                $isMatch=preg_match($url_mode,$uri,$matches);
                if(!$isMatch && !empty($urlAlias)){
                    $url_mode="/".str_replace("/", "\/", $urlAlias)."/";
                    $isMatch=preg_match($url_mode,$uri,$matches);
                }
                if($isMatch){
                    //controller
                    $_controller=$this->m_defaultControllerName;
                    if(array_key_exists("controller", $rule)){
                        $_controller=trim($rule["controller"],"\\");
                    }
                    $controller=$namespace."\\".$_controller;

                    //action
                    $action=array_key_exists("action", $rule) ? $rule["action"] : $this->m_defaultActionName;

                    //view
                    $view=array_key_exists("view", $rule) ? $rule["view"] : "";

                    //params
                    $_params=array_key_exists("params",$rule)?$rule["params"]:[];

                    //replace matches
                    for($index = 1;$index < count($matches);$index++){
                        $match=$matches[$index];
                        //if(empty($match)){
                        //    continue;
                        //}
                        //转为驼峰命名法,首字母大小写不变
                        $first=substr($match, 0,1);
                        $_match=StringUtils::toHumpString($match);
                        $_match=$first.substr($_match, 1);
                        $srch="$".(string)$index;
                        $controller=str_replace($srch, ucfirst($_match), $controller);//控制器首字母大写
                        $action=str_replace($srch, $_match, $action);
                        $view=str_replace($srch, $_match, $view);
                        foreach ($_params as $key=>$value){
                            $_params[$key]=str_replace($srch, $match, $value);
                        }
                    }

                    //属性
                    $this->m_contextPath=$contextPath;
                    $this->m_contextPathAlias=$alias;
                    $this->m_controllerName=$controller;
                    $this->m_actionName=$action;
                    $this->m_viewFile=$view;
                    $this->m_initParams=$_params;
                    if($this->m_contextPath==""){
                        $this->m_contextPath="/";
                    }
                    return;
                }
            }
        }
    }
}