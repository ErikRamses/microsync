<?php

namespace App\Api\V1\Transformers;

use App\models\Permission;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    /**
     * List of resources to include by default.
     *
     * @var array
     */
    protected $defaultIncludes = [
    ];

    /**
     * List of resources possible to include.
     *
     * @var array
     */
    protected $availableIncludes = [
    ];

    /**
     * Turn this item object into a generic array.
     *
     * @return array
     */
    public function transform(Permission $permission)
    {
        return [
          'id' => $permission->id,
          'name' => $permission->name,
        ];
    }
}
