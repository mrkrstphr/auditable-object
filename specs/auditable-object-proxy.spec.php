<?php

use mrkrstphr\AuditableObject\AuditableObjectInterface;
use mrkrstphr\AuditableObject\AuditableObjectProxy;

describe(AuditableObjectProxy::class, function () {
    describe('__call', function () {
        beforeEach(function () {
            $this->object = new class implements AuditableObjectInterface {
                protected $attribute;

                public $auditTrail = [];

                public function getAttribute()  { return $this->attribute; }
                public function setAttribute($attribute) { $this->attribute = $attribute; }

                public function addAuditTrail($attribute, $previous, array $args, $process = null, $user = null) {
                    $this->auditTrail[] = [
                        'attribute' => $attribute,
                        'previous' => $previous,
                        'new' => $args[0],
                        'process' => $process,
                        'user' => $user
                    ];
                }
            };
        });

        it('should pass the call through', function () {
            $proxy = new AuditableObjectProxy($this->object);
            $proxy->setAttribute(8675309);

            expect($this->object->getAttribute())->to->equal(8675309);
        });

        it('should throw an exception if the method doesn\'t exist on the object', function () {
           $actual = null;

           try {
               $proxy = new AuditableObjectProxy($this->object);
               $proxy->setFoo('bar');
           } catch (Exception $e) {
               $actual = $e;
           }

           expect($actual)->to->be->instanceof(RuntimeException::class);
        });

        it('should log an audit trail', function () {
            $proxy = new AuditableObjectProxy($this->object);
            $proxy->setProcess('manual entry');
            $proxy->setUser('Bill');
            $proxy->setAttribute('abc');

            expect($this->object->auditTrail)->to->have->length(1);
            expect($this->object->auditTrail[0])->to->equal([
                'attribute' => 'Attribute',
                'previous' => null,
                'new' => 'abc',
                'process' => 'manual entry',
                'user' => 'Bill',
            ]);


            $proxy->setProcess('computer');
            $proxy->setUser(null);
            $proxy->setAttribute('xyz');

            expect($this->object->auditTrail)->to->have->length(2);
            expect($this->object->auditTrail[1])->to->equal([
                'attribute' => 'Attribute',
                'previous' => 'abc',
                'new' => 'xyz',
                'process' => 'computer',
                'user' => null,
            ]);
        });
    });
});
