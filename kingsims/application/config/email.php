<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// $ssl = ($this->smtp_crypto === 'ssl') ? 'ssl://' : '';

$config = Array(
    'protocol'  => 'smtp',
    'smtp_crypto'   => 'ssl',
    'smtp_host' => SMTP_HOST,
    'smtp_port' => 465,
    'smtp_user' => SMTP_USER,
    'smtp_pass' => SMTP_PASS,
    'newline'   => "\r\n",
    'wordwrap'  => true,
    'mailtype'  => 'html', 
    'charset'   => 'iso-8859-1'
);

