<?php

/**
 * This code is mainly a copy from the answer at:
 * https://stackoverflow.com/questions/6409167/symfony-2-multiple-and-dynamic-database-connection
 */

namespace App\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Symfony\Component\HttpFoundation\Session\Session;

final class CalibreConnectionWrapper extends Connection
{
    const SESSION_ACTIVE_DYNAMIC_CONN = 'active_dynamic_conn';

    /** @var Session */
    private $session;

    /** @var bool */
    private $_isConnected = false;

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function forceSwitch(string $dbPath)
    {
        if ($this->session->has(self::SESSION_ACTIVE_DYNAMIC_CONN)) {
            $current = $this->session->get(self::SESSION_ACTIVE_DYNAMIC_CONN);
            if ($current[0] === $dbPath) {
                return;
            }
        }

        $this->session->set(self::SESSION_ACTIVE_DYNAMIC_CONN, [
            $dbPath
        ]);

        if ($this->isConnected()) {
            $this->close();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if (! $this->session->has(self::SESSION_ACTIVE_DYNAMIC_CONN)) {
            throw new \InvalidArgumentException('You have to inject into valid context first');
        }

        if ($this->isConnected()) {
            return true;
        }

        $driverOptions = isset($params['driverOptions']) ? $params['driverOptions'] : array();

        $params = $this->getParams();
        $realParams = $this->session->get(self::SESSION_ACTIVE_DYNAMIC_CONN);
        $params['path'] = $realParams[0];

        $this->_conn = $this->_driver->connect($params, $params['user'], $params['password'], $driverOptions);

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        $this->_isConnected = true;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return $this->_isConnected;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if ($this->isConnected()) {
            parent::close();
            $this->_isConnected = false;
        }
    }
}