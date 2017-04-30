<?php

namespace mrkrstphr\AuditableObject;

/**
 * Interface AuditableObjectInterface
 * @package mrkrstphr\AuditableObject
 */
interface AuditableObjectInterface
{
    /**
     * @param string $attribute
     * @param mixed $previous
     * @param array $args
     * @param mixed $process
     * @param mixed $user
     */
    public function addAuditTrail($attribute, $previous, array $args, $process = null, $user = null);
}
