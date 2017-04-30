<?php

namespace mrkrstphr\AuditableObject;

use RuntimeException;

/**
 * Class AuditableObjectProxy
 * @package mrkrstphr\AuditableObject
 */
class AuditableObjectProxy
{
    /**
     * @var AuditableObjectInterface
     */
    protected $object;

    /**
     * @var mixed
     */
    protected $process;

    /**
     * @var mixed
     */
    protected $user;

    /**
     * AuditableObjectProxy constructor.
     * @param AuditableObjectInterface $object
     */
    public function __construct($object)
    {
        if (!($object instanceof AuditableObjectInterface)) {
            throw new RuntimeException(
                sprintf('%s is not an instance of %s', get_class($object), AuditableObjectInterface::class)
            );
        }

		$this->object = $object;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (method_exists($this->object, $method)) {
            if (substr($method, 0, 3) === 'set') {
                $attribute = substr($method, 3);
                $getters = ['is' . $attribute, 'get' . $attribute];
                $previous = null;

                foreach ($getters as $getter) {
                    if (method_exists($this->object, $getter)) {
                        $previous = $this->object->{$getter}();
                    }
                }

                $this->object->addAuditTrail($attribute, $previous, $args, $this->process, $this->user);
            }

            return call_user_func_array([$this->object, $method], $args);
        }

        throw new RuntimeException(sprintf('Call to undefined method %s::%s', get_class($this->object), $method));
    }

    /**
     * @return mixed
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param mixed $process
     * @return $this
     */
    public function setProcess($process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
