<?php

namespace Lib;

class Exception
{
    /**
     *                                |     顯示異常訊息    |    終止程序
     * LEVEL_NOTICE        |                X               |          X
     * LEVEL_WARNING       |                O               |          X
     * LEVEL_ERROR         |                O               |          O
     */
    const
        LEVEL_ERROR = 'LEVEL_ERROR',
        LEVEL_NOTICE = 'LEVEL_NOTICE',
        LEVEL_WARNING = 'LEVEL_WARNING';

    private
        $exception,
        $level,
        $message;

    function __construct()
    {
        $this->exception = new \Exception;
		
		//echo "<script>console.log(".json_encode("lib/Exception.php").";</script>";
		
    }

    function getMessage()
    {
        return $this->message;
    }

    function getTraceString()
    {
        $trace = explode("\n", $this->exception->getTraceAsString());

        $trace = array_reverse($trace); // reverse array to make steps line up chronologically

        $trace = array_slice($trace, 1, -2);//remove first {main} and last two (\userlog\Model::setException and call to this method)

        $length = count($trace);
        $array1 = [];
        for ($i = 0; $i < $length; ++$i) {
            $array1[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return 'Exception: ' . $this->getMessage() . ' ' . implode(' ', $array1);
    }

    function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    function output()
    {
        try {
            throw $this->exception;
        } catch (\Exception $exception) {
            if (SITE_EVN === 'production') {
                email(
                    EMAIL_ACCOUNT_INTRANET,
                    EMAIL_PASSWORD_INTRANET,
                    'pinpinbox userlog',
                    'lion@vmage.com.tw',
                    'Exception : ' . $this->level,
                    $this->getTraceString()
                );
            }

            switch ($this->level) {
                case self::LEVEL_ERROR:
                    $message = in_array(SITE_EVN, ['development', 'test']) ?
                        $this->getTraceString()
                        :
                        'Occur exception, please contact us.<br><br>Back to <a href="' . URL_ROOT . '">' . URL_ROOT . '</a>';

                    die($message);
                    break;

                case self::LEVEL_NOTICE:
                    break;

                case self::LEVEL_WARNING:
                    break;
            }
        }
    }
}