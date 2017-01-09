<?php

namespace App\Session;

use PDO;

class SessionHandler implements \SessionHandlerInterface
{
    private $db;
    private $table;

    public function __construct(PDO $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function open($savePath, $sessionName)
    {
        $sql = "DELETE FROM $this->table WHERE timestamp < ?";
        $params = [
            time() - (3600 * 24),
        ];

        return $this->db->prepare($sql)->execute($params);
    }

    public function close()
    {
        $this->db = null; // only close lazy-connection

        return true;
    }

    public function read($id)
    {
        $sql = "SELECT data FROM $this->table WHERE session_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        if ($result = $stmt->fetch()) {
            return $result['data'];
        } else {
            return false;
        }
    }

    public function write($id, $data)
    {
        $sql = "REPLACE INTO $this->table (session_id, data, timestamp) VALUES(?, ?, ?)";
        $params = [
            $id,
            $data,
            time(),
        ];

        return $this->db->prepare($sql)->execute($params);
    }

    public function destroy($id)
    {
        $sql = "DELETE FROM $this->table WHERE session_id = ?";
        $params = [
            $id,
        ];

        return $this->db->prepare($sql)->execute($params);
    }

    public function gc($maxlifetime)
    {
        $sql = "DELETE FROM $this->table WHERE timestamp < ?";
        $params = [
            time() - intval($maxlifetime),
        ];

        return $this->db->prepare($sql)->execute($params);
    }
}
