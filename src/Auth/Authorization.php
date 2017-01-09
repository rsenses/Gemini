<?php

namespace App\Auth;

use InvalidArgumentException;
use Aura\Auth\Auth;
use App\Entities\User;

class Authorization
{
    private $auth;
    private $user;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        if ($this->auth->getStatus() === 'VALID') {
            $this->user = User::with('roles')
                ->findOrFail($this->auth->getUserId());
        }
    }


    public function hasPermission($permissions)
    {
        $roles = $this->user->roles;
        foreach ($roles as $role) {
            if (is_array($permissions)) {
                foreach ($permissions as $key => $permission) {
                    if (in_array($permission, $role->permissions)) {
                        return true;
                    }
                }
            } else {
                throw new InvalidArgumentException('Los permisos deben ser un array, string entregado', 1);
            }
        }

        return false;
    }

    public function hasRole($roles)
    {
        $userRoles = $this->user->roles;
        foreach ($userRoles as $userRole) {
            if (is_array($roles)) {
                if (in_array($userRole->name, $roles)) {
                    return true;
                }
            } else {
                throw new InvalidArgumentException('Los permisos deben ser un array, string entregado', 1);
            }
        }

        return false;
    }

    public function getStatus()
    {
        return $this->auth->getStatus();
    }
}
