<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Models;

class FTP
{
    private string $serverIp;
    private string $user;
    private string $password;
    private string $uploadDir;

    public function __construct(
        Config $config
    )
    {
        $this->serverIp = $config->getConfigValue('FTP_SERVER_IP');
        $this->user = $config->getConfigValue('FTP_USER');
        $this->password = $config->getConfigValue('FTP_PASSWORD');
        $this->uploadDir = $config->getConfigValue('uploadDir');
    }

    public function upload($file): bool
    {
        $remote_file = $this->uploadDir . $file;
        $ftp = ftp_connect($this->serverIp);
        $login_result = ftp_login($ftp, $this->user, $this->password);
        $success = ftp_put($ftp, $remote_file, $file, FTP_ASCII);
        ftp_close($ftp);
        unlink($file);

        if ($success) {
            return true;
        }

        return false;
    }
}