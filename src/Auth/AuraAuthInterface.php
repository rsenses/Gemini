<?php

namespace App\Auth;

interface AuraAuthInterface
{
    public function loginService($email, $password);

    public function logoutService();

    public function resumeService();

    public function getUserId();

    public function getUserName();

    public function getStatus();
}
