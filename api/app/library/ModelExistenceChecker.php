<?php

/**
 * Description of ModelExistenceChecker
 *
 * @author Ivan Batić <ivan.batic@live.com>
 */
class ModelExistenceChecker
{

    /**
     * Checks if models exist
     * @param array|string $models
     * @throws InvalidArgumentException
     */
    public static function modelsExist($models)
    {
        if (!is_array($models)) {
            if (is_string($models)) {
                $models = [$models];
            } else {
                throw new InvalidArgumentException('Model argument must be either an array or a string');
            }
        }
        $failures = [];
        foreach (array_filter($models) as $model) {
            $modelClass = ucfirst(camel_case(str_singular($model)));
            if (!is_a($modelClass, '\Eloquent', true)) {
                $failures[] = $modelClass;
            }
        }
        return empty($failures) ? true : $failures;
    }

}
