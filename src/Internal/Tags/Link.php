<?php
namespace Yauphp\Web\Internal\Tags;

/**
 * 链接标签
 * @author Tomix
 *
 */
class Link extends TagBase
{
    /**
     * 获取标签渲染后的内容
     * {@inheritDoc}
     * @see \swiftphp\web\tags\TagBase::getContent()
     */
    public function getContent(&$outputParams=[])
    {
        $str="<a";
        foreach ($this->getAttributes() as $key => $val){
            $str.=" ".$key."=\"".$val."\"";
        }
        $str.=">".$this->getInnerHtml()."</a>";
        return $str;
    }
}

