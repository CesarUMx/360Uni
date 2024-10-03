<?php

class Catalogo extends Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->getModelsManager()->setModelSchema($this, "public");
        $this->setSource('catalogo_' . substr(strtolower(get_class($this)),1));
    }
}