<?php

/**
 * Description of ApiQueryMaker
 *
 * @author Ivan Batić <ivan.batic@live.com>
 */
class ApiRequestParser
{

    protected $relationList = array();

    public function breakdown(array $apiPath)
    {
        foreach ($apiPath as $key => $value) {
            if (is_string($value) && !is_numeric($value)) {
                $this->relationList[] = camel_case($value);
            }
        }
        return $this;
    }

    public function getRelationList()
    {
        return $this->relationList;
    }

    public function setRelationList($relationList)
    {
        $this->relationList = $relationList;
        return $this;
    }

}
