<?php

namespace App\Api\V1\Transformers;

use App\models\Role;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract
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
    public function transform(Role $role)
    {
        return [
          'id' => $role->id,
          'name' => $role->name,
        ];
    }
}
