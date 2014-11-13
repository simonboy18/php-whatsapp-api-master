<?php
namespace CarlosIO\WhatsApp\Exception;

class IncompleteMessageException extends CustomException
{
    private $_input;

    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function setInput($input)
    {
        $this->_input = $input;
    }

    public function getInput()
    {
        return $this->_input;
    }
}
