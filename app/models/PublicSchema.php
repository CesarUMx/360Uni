<?php

class PublicSchema extends Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->getModelsManager()->setModelSchema($this, "public");
    }
}