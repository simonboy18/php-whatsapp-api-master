<?php
namespace CarlosIO\WhatsApp\Protocol;

class ProtocolNode
{
    public $_tag;
    public $_attributeHash;
    public $_children;
    public $_data;

    public function __construct($tag, $attributeHash, $children, $data)
    {
        $this->_tag = $tag;
        $this->_attributeHash = $attributeHash;
        $this->_children = $children;
        $this->_data = $data;
    }

    public function NodeString($indent = "")
    {
        $ret = "\n" . $indent . "<" . $this->_tag;
        if ($this->_attributeHash != NULL) {
            foreach ($this->_attributeHash as $key => $value) {
                $ret .= " " . $key . "=\"" . $value . "\"";
            }
        }
        $ret .= ">";
        if (strlen($this->_data) > 0) {
            $ret .= $this->_data;
        }
        if ($this->_children) {
            foreach ($this->_children as $child) {
                $ret .= $child->NodeString($indent . "  ");
            }
            $ret .= "\n" . $indent;
        }
        $ret .= "</" . $this->_tag . ">";

        return $ret;
    }

    public function getAttribute($attribute)
    {
        $ret = "";
        if (isset($this->_attributeHash[$attribute])) {
            $ret = $this->_attributeHash[$attribute];
        }

        return $ret;
    }

    public function getChild($tag)
    {
        $ret = NULL;
        if ($this->_children) {
            foreach ($this->_children as $child) {
                if (strcmp($child->_tag, $tag) == 0) {
                    return $child;
                }
                $ret = $child->getChild($tag);
                if ($ret) {
                    return $ret;
                }
            }
        }

        return NULL;
    }

    public function hasChild($tag)
    {
        return $this->getChild($tag)==null ? false : true;
    }

    public function refreshTimes($offset=0)
    {
        if (isset($this->_attributeHash['id'])) {
            $id = $this->_attributeHash['id'];
            $parts = explode('-',$id);
            $parts[0] = time()+$offset;
            $this->_attributeHash['id'] = implode('-',$parts);
        }

        if (isset($this->_attributeHash['t'])) {
            $this->_attributeHash['t'] = time();
        }
    }
}
