<?php

namespace App\Auth;

use Exception;
use PDO;
use Aura\Auth\AuthFactory;
use Aura\Auth\Verifier\PasswordVerifier;
use Aura\Auth\Exception as AuraException;
use App\Entities\User;

class AuraAuth
{
    private $authFactory;
    private $auth;
    private $pdoAdapter;

    public function __construct(AuthFactory $authFactory, array $settings)
    {
        $this->authFactory = $authFactory;
        $this->auth = $this->authFactory->newInstance();

        $cols = [
            'user.email', // "AS username" is added by the adapter
            'user.password', // "AS password" is added by the adapter
            'user.user_id',
        ];
        $from = 'user';
        $where = 'user.is_active = 1';
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO("{$settings['driver']}:host={$settings['host']};dbname={$settings['database']};port={$settings['port']};charset={$settings['charset']}", $settings['username'], $settings['password'], $opt);
        $hash = new PasswordVerifier(PASSWORD_BCRYPT);
        $this->pdoAdapter = $this->authFactory->newPdoAdapter($pdo, $hash, $cols, $from, $where);
    }

    public function loginService($username, $password)
    {
        $loginService = $this->authFactory->newLoginService($this->pdoAdapter);
        try {
            return $loginService->login($this->auth, [
                'username' => $username,
                'password' => $password,
            ]);
        } catch (AuraException\UsernameNotFound $e) {
            throw new Exception('Username not Found');
        } catch (AuraException\MultipleMatches $e) {
            throw new Exception('Multiple Matches');
        } catch (AuraException\PasswordIncorrect $e) {
            throw new Exception('Password Incorrect');
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function logoutService()
    {
        $logoutService = $this->authFactory->newLogoutService($this->pdoAdapter);
        $logoutService->logout($this->auth);

        if ($this->auth->isAnon()) {
            return true;
        } else {
            return false;
        }
    }

    public function resumeService()
    {
        $resumeService = $this->authFactory->newResumeService($this->pdoAdapter, 14400);
        $resumeService->resume($this->auth);

        if (!$this->auth->isValid()) {
            return false;
        }

        return true;
    }

    public function getUserId()
    {
        return $this->auth->getUserData()['user_id'] ?? null;
    }

    public function getUserData()
    {
        if ($this->getUserId()) {
            return User::find($this->getUserId());
        }

        return null;
    }

    public function getClientId()
    {
        if ($this->getUserId()) {
            return User::find($this->getUserId())->client_id;
        }

        return null;
    }

    public function getUserName()
    {
        return $this->auth->getUserName();
    }

    public function getStatus()
    {
        return $this->auth->getStatus();
    }
}
