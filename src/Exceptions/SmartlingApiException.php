<?php

namespace Smartling\Exceptions;

use Exception;

/**
 * Class SmartlingApiException
 * @package Smartling\Exceptions
 */
class SmartlingApiException extends \Exception
{
    const ERROR_OUTPUT_SEPARATOR = '---------------------------';
    
    /**
     * Errors.
     *
     * Each error contains next fields:
     *  - key: "parse.error"
     *  - message: "There was a problem loading your file"
     *  - details:
     *    [
     *       "errorId": "cse8rqnf",
     *    ]
     *
     * @var array
     */
    protected $errors = [];
    
    /**
     * SmartlingApiException constructor.
     *
     * @param string|array $errors
     * @param int          $code
     * @param \Exception   $previous
     */
    public function __construct($errors, $code = 0, \Exception $previous = null)
    {
        $message = '';
        
        if (is_string($errors)) {
            $message = $errors;
        } elseif (is_array($errors)) {
            $this->errors = $errors;
        }
        
        parent::__construct($message, $code, $previous);
        
    }
    
    /**
     * Return list of Smartling response errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Get list of errors by specified key.
     *
     * @param string $key
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getErrorsByKey($key)
    {
        if (!is_string($key)) {
            throw new \Exception('Key must be a string');
        }
        
        $errors = array_filter($this->errors, function ($el) use ($key) {
            return $el['key'] === $key;
        });
        
        return $errors;
    }
    
    /**
     * Format errors.
     *
     * @param string $title
     *
     * @return string
     */
    public function formatErrors($title = '')
    {
        $errorsStr = PHP_EOL;
        
        foreach ($this->errors as $k => $error) {
            $details = [];
            
            if (isset($error['details']) && is_array($error['details'])) {
                foreach ($error['details'] as $name => $value) {
                    $details[] = sprintf('%s:%s', $name, $value);
                }
            }
            
            $body = sprintf(
                'key: %smessage: %sdetails: %s',
                $error['key'] . PHP_EOL,
                $error['message'] . PHP_EOL,
                implode(' | ', $details) . PHP_EOL
            );
            $errorsStr .= $body . self::ERROR_OUTPUT_SEPARATOR . PHP_EOL;
        }
    
        $messageTemplate = $title . PHP_EOL
            . 'Response code: %s' . PHP_EOL
            . 'Response errors (%s): %s%s' . PHP_EOL;
    
        $output = vsprintf(
            $messageTemplate,
            [
                $this->getCode(),
                count($this->errors),
                PHP_EOL . self::ERROR_OUTPUT_SEPARATOR,
                $errorsStr,
            ]
        );
        
        return $output;
    }
}