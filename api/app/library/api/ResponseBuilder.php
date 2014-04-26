<?php

/**
 * Description of ApiResponseBuilder
 *
 * @author Ivan Batić <ivan.batic@live.com>
 */

namespace Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ResponseBuilder
{

    protected $baseModel;
    protected $baseModelId = null;
    protected $relationPath = [];
    protected $queryParams = [];
    protected $relationFilters = [];
    protected $queryFilters = [];
    protected $queryAggregate = null;
    protected $queryParamsModel = null;
    protected $queryReservedKeywords = ['return'];

    public function getBaseModel()
    {
        return $this->baseModel;
    }

    public function setBaseModel($baseModel)
    {
        $this->baseModel = $baseModel;

        return $this;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function setQueryParams($queryParams)
    {
        $this->queryParams = $queryParams;
        $this->parseQueryParams($this->queryParams);

        return $this;
    }

    protected function parseQueryParams(array $params)
    {
        // Parse where clauses
        if (isset($params['where'])) {
            foreach ($params['where'] as $key => $whereSection) {
                $func    = !is_numeric($key) ? $key : 'where';
                $trimmed = trim($whereSection, ',');
                if ($trimmed || is_numeric($trimmed)) {
                    $this->queryFilters[$func][] = explode(',', $trimmed);
                }
            }
        }
        unset($params['where']);

        // Parse apply-to control
        $models = $this->relationPath;
        array_unshift($models, $this->baseModel);
        $params['apply-to'] = isset($params['apply-to']) ? camel_case($params['apply-to']) : 'first';
        $key                = array_search($params['apply-to'] ? : null, $models);
        if ($key === false) {
            $params['apply-to'] == 'last' ? end($models) : reset($models);
            $key = key($models);
        }
        $this->queryParamsModel = $models[$key];
        unset($params['apply-to']);

        // Parse other methods
        foreach ($params as $method => $args) {
            if (in_array($method, $this->queryReservedKeywords)) {
                continue;
            }
            $trimmed = trim($args, ',');
            $args    = [];
            if ($trimmed || is_numeric($trimmed)) {
                $args = explode(',', $trimmed);
            }
            $methodName = camel_case($method);

            if (in_array($methodName, ['count', 'max', 'min', 'sum', 'avg'])) {
                $args                 = array_key_exists(0, $args) ? $args[0] : null;
                $this->queryAggregate = [$methodName => $args];
            } else {
                $this->queryFilters[$methodName][] = $args;
            }
        }
    }

    public function getRelationFilters()
    {
        return $this->relationFilters;
    }

    public function setRelationFilters($relationFilters)
    {
        $this->relationFilters = $relationFilters;

        return $this;
    }

    /**
     *
     * @param type $return Can return `tracked` or `base`
     *
     * @return type mixed
     * @throws \InvalidArgumentException
     */
    public function build($return = 'tracked')
    {
        if (!is_array($this->queryAggregate)) {
            $func = $this->getBaseModelId() ? 'find' : 'get';
            $arg  = $this->getBaseModelId() ? : null;
        } else {
            $func = key($this->queryAggregate);
            $arg  = $this->queryAggregate[key($this->queryAggregate)];
        }

        // Initialize query builder, id will always be larger than 0
        $builder = call_user_func("\\{$this->baseModel}::where", 'id', '>', '0');
        if ($this->baseModel == $this->queryParamsModel) {
            foreach ($this->queryFilters as $method => $filters) {
                foreach ($filters as $args) {
                    call_user_func_array([$builder, $method], $args);
                }
            }
        }

        $base = $tracked = $arg != null ? call_user_func([$builder, $func], $arg) : call_user_func([$builder, $func]);
        if (!$tracked) {
            throw new \InvalidArgumentException('Resource not found');
        }

        // Go through all specified relations
        foreach ($this->getRelationPath() as $relation) {
            if (!is_object($tracked)) {
                break;
            }
            // Load the relation
            $tracked->load([
                    $relation => function ($query) use ($relation) {
                            if ($this->queryParamsModel == $relation) {
                                foreach ($this->queryFilters as $method => $filters) {
                                    foreach ($filters as $args) {
                                        call_user_func_array([$query, $method], $args);
                                    }
                                }
                            }
                        }
                ]
            );

            if ($tracked instanceof Collection) {
                $tmp = new Collection();
                foreach ($tracked as $item) {
                    $rel = $item->getRelation($relation);
                    if ($rel instanceof Model) {
                        $tmp->add($rel);
                    } elseif ($rel instanceof Collection) {
                        foreach ($rel as $relationItem) {
                            $tmp->add($relationItem);
                        }
                    }
                }
                $tracked = $tmp;
            } else {
                $tracked = $tracked->getRelation($relation);
            }
        }

        return $return == 'nested' ? $base : $tracked;
    }

    public function getBaseModelId()
    {
        return $this->baseModelId;
    }

    public function setBaseModelId($baseModelId)
    {
        $this->baseModelId = (int)$baseModelId;

        return $this;
    }

    public function getRelationPath()
    {
        return $this->relationPath;
    }

    public function setRelationPath(array $relationPath)
    {
        $this->relationPath = $relationPath;

        return $this;
    }

}
