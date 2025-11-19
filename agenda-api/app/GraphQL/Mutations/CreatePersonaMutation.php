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

class CreatePersonaMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPersona',
        'description' => 'A mutation'
    ];

    public function type(): Type
    {
        return GraphQL::type('Persona');
    }

    public function args(): array
    {
        return [
            'nombres' => ['type' => Type::nonNull(Type::string())],
            'apellidos' => ['type' => Type::nonNull(Type::string())],
            'ci' => ['type' => Type::nonNull(Type::string())],
            'direccion' => ['type' => Type::string()],
            'telefono' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $persona = Persona::create($args);
        return $persona;
    }
}
