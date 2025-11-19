<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;
use GraphQL;
use App\Models\Persona;

class DeletePersonaMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deletePersona',
        'description' => 'Eliminar persona'
    ];

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::int())],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $persona = Persona::findOrFail($args['id']);
        $persona->delete();
        return true;
    }
}
