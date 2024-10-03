<?php

class TablaExterna extends Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->setConnectionService("dbUsuarios");
        $this->getModelsManager()->setModelSchema($this, "public");

    }
}