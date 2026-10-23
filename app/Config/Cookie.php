<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cookie extends BaseConfig
{
    public string $prefix = '';
    public string $expires = '';
    public string $path = '/';
    public string $domain = '';
    public bool $secure = false;
    public bool $httponly = true;
    public string $samesite = 'Lax';
    public bool $raw = false;

    public function __construct()
    {
        parent::__construct();
        $this->secure = filter_var(env('cookie.secure', false), FILTER_VALIDATE_BOOLEAN);
    }
}
