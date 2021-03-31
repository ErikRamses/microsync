<?php

namespace App\Api\V1\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
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
    public function transform(User $user)
    {
        return [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'username' => $user->username,
          'created_at' => (null != $user->created_at) ? $user->created_at->toDateTimeString() : null,
          'updated_at' => (null != $user->updated_at) ? $user->updated_at->toDateTimeString() : null,
          'role' => $user->hasRoles()->get(),
        ];
    }
}
