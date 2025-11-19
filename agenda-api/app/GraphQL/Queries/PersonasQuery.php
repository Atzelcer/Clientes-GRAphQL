<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\SelectFields;
use GraphQL;
use App\Models\Persona;

class PersonasQuery extends Query
{
    protected $attributes = [
        'name' => 'personas',
        'description' => 'A query that retrieves a list of personas',
        'model' => Persona::class,

    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Persona'));
    }

    public function args(): array
    {
        return [
            // Define los argumentos de filtro si es necesario
            'id' => ['type' => Type::int()],
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
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $query = Persona::query()->select($select)->with($with);
        $query->where($args);


        return $query->get();
    }
}
