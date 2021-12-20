<?php
namespace Yauphp\Web\Internal\Out;

/**
 * Jsonp输出代理
 * @author Tomix
 *
 */
class Jsonp extends Base
{
    protected $m_callback="";

    public function setCallback($value)
    {
        $this->m_callback=$value;
    }

    /**
     * 构造
     * @param mixed $content
     */
    public function __construct($content="",$callback="")
    {
        parent::__construct($content);
        $this->m_callback=$callback;
    }

    /**
     * 输出
     * {@inheritDoc}
     * @see \swiftphp\web\out\Base::output()
     */
    public function output()
    {
        echo $this->m_callback . "(" . json_encode($this->m_content) . ")";
    }
}

