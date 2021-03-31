<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use League\Fractal\Serializer\DataArraySerializer;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    use Helpers;

    public function transformResponse($data, $transformer, $optional = [])
    {
        if (! is_array($optional)) {
            return false;
        }
        $defaults = ['serializer' => new DefaultSerializer(), 'meta' => null, 'includes' => null, 'response' => 200];
        $serializer = isset($optional['serializer']) ? $optional['serializer'] : $defaults['serializer'];
        $meta = isset($optional['meta']) ? $optional['meta'] : $defaults['meta'];
        $includes = isset($optional['includes']) ? $optional['includes'] : $defaults['includes'];
        $response = isset($optional['response']) ? $optional['response'] : $defaults['response'];
        $manager = fractal($data, $transformer, $serializer);
        if (isset($optional['paginator'])) {
            $manager->paginateWith($optional['paginator']);
        }

        return $manager->addMeta($meta)->parseIncludes($includes)->respond($response);
    }

    public function respondWithCreated($data, $transformer, $meta = null, $includes = null)
    {
        return $this->transformResponse($data, $transformer, $meta, $includes, 201);
    }

    public function setAPILocale($locale)
    {
        config(['app.locale' => $locale]);
        app()->setLocale($locale);
    }

    public $per_page = 20;
}

// reference: https://github.com/thephpleague/fractal/issues/90
class DefaultSerializer extends DataArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        if (false === $resourceKey) {
            return $data;
        }

        return [$resourceKey ?: 'data' => $data];
    }

    public function item($resourceKey, array $data)
    {
        if (false === $resourceKey) {
            return $data;
        }

        return [$resourceKey ?: 'data' => $data];
    }
}
