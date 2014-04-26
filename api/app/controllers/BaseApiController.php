<?php

/**
 * Description of BaseApiController
 *
 * @author Ivan Batić <ivan.batic@live.com>
 */
class BaseApiController extends BaseController
{

    /** @var ApiResponse */
    protected $apiResponse;
    protected $requestParser;
    protected $responseBuilder;

    public function __construct(
        ApiResponse $apiResponse, ApiRequestParser $requestParser, \Api\ResponseBuilder $responseBuilder
    ) {
        $this->apiResponse     = $apiResponse;
        $this->requestParser   = $requestParser;
        $this->responseBuilder = $responseBuilder;
    }

    public function getIndex($model, $id = null, $requestPath = null)
    {
        if (!is_numeric($id)) {
            $requestPath = empty($requestPath) ? $id : ($id . '/' . $requestPath);
            $id          = null;
        }

        $this->responseBuilder
            ->setBaseModel(ucfirst(camel_case(str_singular($model))))
            ->setBaseModelId($id)
            ->setRelationPath($this->requestParser
                    ->breakdown($requestPath ? explode('/', $requestPath) : [])
                    ->getRelationList()
            )->setQueryParams(\Input::all());

        try {
            $results = $this->responseBuilder->build(\Input::get('return'));
        } catch (Exception $ex) {
            $this->apiResponse->addMessage('system_error', $ex->getMessage())
                ->setStatusCode(400);
            $results = [];
        }

        if (method_exists($results, 'toArray')) {
            $results = $results->toArray();
        }

        return \Response::json($this->apiResponse->setField('content', $results), $this->apiResponse->getStatusCode());
    }

    public function createIndex($model)
    {
        $modelClassName = ucfirst(camel_case(str_singular($model)));
        $modelName      = '\\' . ucfirst(camel_case(str_singular($model)));
        if (method_exists($modelName, 'validate')) {
            $validator = call_user_func("{$modelName}::validate", \Input::all());
        } else {
            $rules     = property_exists("{$modelName}", 'rules') ? $modelName::$rules : [];
            $validator = \Validator::make(\Input::all(), $rules);
        }

        if ($validator->fails()) {
            $this->apiResponse->setMessageBag($validator->getMessageBag());
        } else {
            $instance  = call_user_func("{$modelName}::create", \Input::all());
            $fillables = method_exists($instance, 'getFillable') ? $instance->getFillable() : [];
            foreach ($fillables as $field) {
                if (\Input::hasFile($field)) {
                    $file        = \Input::file($field);
                    $destination = \Config::get('app.media_directory') . "/{$modelClassName}";
                    $newFileName = "{$instance->id}_{$field}." . $file->getClientOriginalExtension();
                    $file->move($destination, $newFileName);
                    $instance->$field = "/media/{$modelClassName}/$newFileName";
                    $instance->save();
                }
            }

            $this->apiResponse->setField(snake_case(ltrim($modelClassName, '\\')), $instance->toArray());
        }

        return \Response::json($this->apiResponse, $this->apiResponse->getStatusCode());
    }

    public function updateIndex($model, $id)
    {
        // Validate method should exist for safety precautions
        $modelClassName = ucfirst(camel_case(str_singular($model)));
        $modelName      = '\\' . $modelClassName;
        if (method_exists($modelName, 'validate')) {
            $validator = call_user_func("{$modelName}::validate", \Input::all());
        } else {
            $rules     = property_exists("{$modelName}", 'rules') ? $modelName::$rules : [];
            $validator = \Validator::make(\Input::all(), $rules);
        }

        // If input is not valid, send messages about errors
        if ($validator->fails()) {
            $this->apiResponse->setMessageBag($validator->getMessageBag());
        } else {
            // Find an instance of the required object
            $instance = call_user_func("{$modelName}::find", $id);
            // If there is no such entry, notify the user
            if (!$instance) {
                $this->apiResponse->addMessage('error', "{$modelName} #{$id} could not be found");
            } else {
                // There's a required entry, update it
                // Check which fields are fillable
                $fillables = method_exists($instance, 'getFillable') ? $instance->getFillable() : [];
                foreach ($fillables as $field) {
                    if (\Input::has($field)) {
                        // Check if it's an uploadable image
                        $instance->$field = \Input::get($field);
                    }
                    if (\Input::hasFile($field)) {
                        $file        = \Input::file($field);
                        $destination = \Config::get('app.media_directory') . "/{$modelClassName}";
                        $newFileName = "{$instance->id}_{$field}." . $file->getClientOriginalExtension();
                        $file->move($destination, $newFileName);
                        $instance->$field = "/media/{$modelClassName}/$newFileName";
                    }
                }
                $instance->save();
                $this->apiResponse->setField(snake_case($modelClassName), $instance->toArray());
            }
        }

        return \Response::json($this->apiResponse, $this->apiResponse->getStatusCode());
    }

    public function deleteIndex($model, $id)
    {
        $modelName = ucfirst(camel_case(str_singular($model)));
        $destroy   = call_user_func("\\{$modelName}::destroy", $id);

        return \Response::json($this->apiResponse->setField('destroyed', $destroy));
    }

}
