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

class UpdatePersonaMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updatePersona',
        'description' => 'Actualizar persona'
    ];

    public function type(): Type
    {
        return GraphQL::type('Persona');
    }

    public function args(): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::int())],
            'nombres' => ['type' => Type::string()],
            'apellidos' => ['type' => Type::string()],
            'ci' => ['type' => Type::string()],
            'direccion' => ['type' => Type::string()],
            'telefono' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $persona = Persona::findOrFail($args['id']);
        $persona->update($args);
        return $persona;
    }
}
