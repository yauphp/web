<?php
namespace Yauphp\Web;

use Yauphp\Http\IFilter;
use Yauphp\Config\IConfigurable;
use Yauphp\Config\IConfiguration;
use Yauphp\Web\Internal\ControllerFactory;
use Yauphp\Http\Context;
use Yauphp\Http\FilterChain;
use Yauphp\Web\Internal\Out\Base;
use Yauphp\Web\Internal\Out\HtmlView;
use Yauphp\Http\IOutput;
use Yauphp\Logger\ILogger;

/**
 * MVC模型入口,Web过滤器
 * @author Tomix
 *
 */
class WebFilter implements IFilter,IConfigurable
{
    /**
     * 是否调试模式(此属性会传递给控制器)
     * @var string
     */
    private $m_debug=false;

    /**
     * 路由实例
     * @var IRoute
     */
    private $m_route;

    /**
     * 配置实例
     * @var IConfiguration
     */
    private $m_config;

    /**
     * 控制器工厂
     * @var IControllerFactory
     */
    private $m_controllerFactory=null;

    /**
     * 错误控制器类型名
     * @var IController
     */
    private $m_errorController;

    /**
     * 日志记录器
     * @var ILogger
     */
    private $m_logger;

    /**
     * 是否为调试模式
     * @param bool $value
     */
    public function setDebug($value){
        $this->m_debug=$value;
    }

    /**
     * 注入日志记录器
     * @param ILogger $value
     */
    public function setLogger(ILogger $value){
        $this->m_logger=$value;
    }

    /**
     * 控制器工厂实例
     * @param IControllerFactory $value
     */
    public function setControllerFactory(IControllerFactory $value){
        $this->m_controllerFactory=$value;
    }

    /**
     * 设置错误控制器
     * @param IController $value
     */
    public function setErrorController(IController $value){
        $this->m_errorController=$value;
    }


    /**
     * 注入路由实例
     * @param IRoute $value
     */
    public function setRoute(IRoute $value){
        $this->m_route=$value;
    }

    /**
     * 注入配置实例
     * @param IConfiguration $value
     */
    public function setConfiguration(IConfiguration $value){
        $this->m_config=$value;
    }

    /**
     * 执行过滤方法
     * @param Context $context
     */
    public function filter(Context $context, FilterChain $chain){
        //不应拦截过滤链
        //找不到控制器或激活不了方法时，转发到错误控制器的_404方法
        //找不到错误控制器或激活不了_404方法时,直接输出错误模板

        try{
            //         echo "area:".$this->getRoute()->getAreaName()."\r\n";
            //         echo "areaPrefix:".$this->getRoute()->getAreaPrefix()."\r\n";
            //         echo "controller:".$this->getRoute()->getControllerName()."\r\n";
            //         echo "action:".$this->getRoute()->getActionName()."\r\n";
            //         echo "view:".$this->getRoute()->getViewFile()."\r\n";
            //         var_dump($this->getRoute()->getInitParams());

            //路由
            if(empty($this->m_route)){
                throw new \Exception("No route instance set to current filter");
            }

            //控制器工厂
            $controllerFactory=$this->m_controllerFactory;

            //如果外部没有注入,从对象工厂创建默认的控制器工厂
            if(!$controllerFactory){
                $controllerFactory=$this->m_config->getObjectFactory()->createByClass(ControllerFactory::class);
            }

            //创建控制器实例
            $controller = $controllerFactory->create($this->m_route->getControllerName());

            //注入控制器属性
            $controller->setContext($context);

            //注入控制器属性(路由)
            $controller->setContextPath($this->m_route->getContextPath());
            $controller->setContextPathAlias($this->m_route->getContextPathAlias());
            $controller->setViewFile($this->m_route->getViewFile());
            $controller->setInitParams($this->m_route->getInitParams());

            //激活控制器方法后，返回一个IOutput代理对象，并让response的输出代理指向该对象
            $model = $controller->invoke($this->m_route->getActionName());
            if(!empty($model)){
                if($model instanceof IOutput){
                    $context->getResponse()->setOutput($model);
                }else{
                    $context->getResponse()->setOutput(new Base($model));
                }
            }
        }catch (\Exception $ex){
            //调试状态下,直接向外抛出异常;否则调用错误控制器输出404
            if(!$this->m_debug && !is_null($this->m_errorController)){

                //logs
                if(!empty($this->m_logger)){
                    try {
                        $this->m_logger->logException($ex,"runtime");
                    }catch(\Exception $ee){}
                }

                //注入控制器属性
                $this->m_errorController->setContext($context);
                $this->m_errorController->setDebug($this->m_debug);
                try{
                    //激活404方法
                    $actionName="_404";
                    $model = $this->m_errorController->invoke($actionName);
                    if(!empty($model)){
                        if($model instanceof IOutput){
                            $context->getResponse()->setOutput($model);
                        }else{
                            $context->getResponse()->setOutput(new Base($model));
                        }
                    }
                }catch (\Exception $e){
                    throw $e;
                }
            }else {
                throw $ex;
            }
        }

        $chain->filter($context);
    }
}
