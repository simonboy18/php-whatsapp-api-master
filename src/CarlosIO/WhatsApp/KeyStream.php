<?php
namespace CarlosIO\WhatsApp;

class KeyStream
{
    private $_rc4;
    private $_key;

    public function __construct($key)
    {
        $this->_rc4 = new Rc4($key, 256);
        $this->_key = $key;
    }

    public function encode($data, $offset, $length, $append = true)
    {
        $d = $this->_rc4->cipher($data, $offset, $length);
        $h = substr(hash_hmac('sha1', $d, $this->_key, true), 0, 4);

        return ($append) ? $d.$h : $h.$d;
    }

    public function decode($data, $offset, $length)
    {
        /* TODO: Hash check */

        return $this->_rc4->cipher($data, $offset + 4, $length - 4);
    }
}
