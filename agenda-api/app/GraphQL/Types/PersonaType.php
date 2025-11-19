<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Persona;


class PersonaType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Persona',
        'description' => 'Tipo que representa una persona',
        'model' =>Persona::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'El ID de la persona',
            ],
            'nombres' => [
                'type' => Type::string(),
                'description' => 'Nombres de la persona',
            ],
            'apellidos' => [
                'type' => Type::string(),
                'description' => 'Apellidos de la persona',
            ],
            'ci' => [
                'type' => Type::string(),
                'description' => 'Carnet de identidad',
            ],
            'direccion' => [
                'type' => Type::string(),
                'description' => 'Dirección de la persona',
            ],
            'telefono' => [
                'type' => Type::string(),
                'description' => 'Teléfono de la persona',
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'Correo electrónico de la persona',
            ],

        ];
    }
}
