<?php
namespace CarlosIO\WhatsApp\Protocol;

class BinTreeNodeWriter
{
    private $_output;
    private $_tokenMap = array();
    private $_key;

    public function __construct($dictionary)
    {
        for ($i = 0; $i < count($dictionary); $i++) {
            if (strlen($dictionary[$i]) > 0) {
                $this->_tokenMap[$dictionary[$i]] = $i;
            }
        }
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function StartStream($domain, $resource)
    {
        $attributes = array();
        $header = "WA";
        $header .= $this->writeInt8(1);
        $header .= $this->writeInt8(2);

        $attributes["to"] = $domain;
        $attributes["resource"] = $resource;
        $this->writeListStart(count($attributes) * 2 + 1);

        $this->_output .= "\x01";
        $this->writeAttributes($attributes);
        $ret = $header.$this->flushBuffer();

        return $ret;
    }

    public function write($node)
    {
        if ($node == NULL) {
            $this->_output .= "\x00";
        } else {
            $this->writeInternal($node);
        }

        return $this->flushBuffer();
    }

    protected function writeInternal($node)
    {
        $len = 1;
        if ($node->_attributeHash != NULL) {
            $len += count($node->_attributeHash) * 2;
        }
        if (count($node->_children) > 0) {
            $len += 1;
        }
        if (strlen($node->_data) > 0) {
            $len += 1;
        }
        $this->writeListStart($len);
        $this->writeString($node->_tag);
        $this->writeAttributes($node->_attributeHash);
        if (strlen($node->_data) > 0) {
            $this->writeBytes($node->_data);
        }
        if ($node->_children) {
            $this->writeListStart(count($node->_children));
            foreach ($node->_children as $child) {
                $this->writeInternal($child);
            }
        }
    }

    protected function flushBuffer()
    {
        $data = (isset($this->_key)) ? $this->_key->encode($this->_output, 0, strlen($this->_output)) : $this->_output;
        $size = strlen($data);
        $ret  = $this->writeInt8(isset($this->_key) ? (1 << 4) : 0);
        $ret .= $this->writeInt16($size);
        $ret .= $data;
        $this->_output = "";

        return $ret;
    }

    protected function writeToken($token)
    {
        if ($token < 0xf5) {
            $this->_output .= chr($token);
        } elseif ($token <= 0x1f4) {
            $this->_output .= "\xfe" . chr($token - 0xf5);
        }
    }

    protected function writeJid($user, $server)
    {
        $this->_output .= "\xfa";
        if (strlen($user) > 0) {
            $this->writeString($user);
        } else {
            $this->writeToken(0);
        }
        $this->writeString($server);
    }

    protected function writeInt8($v)
    {
        $ret = chr($v & 0xff);

        return $ret;
    }

    protected function writeInt16($v)
    {
        $ret = chr(($v & 0xff00) >> 8);
        $ret .= chr(($v & 0x00ff) >> 0);

        return $ret;
    }

    protected function writeInt24($v)
    {
        $ret = chr(($v & 0xff0000) >> 16);
        $ret .= chr(($v & 0x00ff00) >> 8);
        $ret .= chr(($v & 0x0000ff) >> 0);

        return $ret;
    }

    protected function writeBytes($bytes)
    {
        $len = strlen($bytes);
        if ($len >= 0x100) {
            $this->_output .= "\xfd";
            $this->_output .= $this->writeInt24($len);
        } else {
            $this->_output .= "\xfc";
            $this->_output .= $this->writeInt8($len);
        }
        $this->_output .= $bytes;
    }

    protected function writeString($tag)
    {
        if (isset($this->_tokenMap[$tag])) {
            $key = $this->_tokenMap[$tag];
            $this->writeToken($key);
        } else {
            $index = strpos($tag, '@');
            if ($index) {
                $server = substr($tag, $index + 1);
                $user = substr($tag, 0, $index);
                $this->writeJid($user, $server);
            } else {
                $this->writeBytes($tag);
            }
        }
    }

    protected function writeAttributes($attributes)
    {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->writeString($key);
                $this->writeString($value);
            }
        }
    }

    protected function writeListStart($len)
    {
        if ($len == 0) {
            $this->_output .= "\x00";
        } elseif ($len < 256) {
            $this->_output .= "\xf8" . chr($len);
        } else {
            $this->_output .= "\xf9" . chr($len);
        }
    }
}
